<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\ConversationLecture;
use App\Models\ConversationMessage;
use App\Models\Cours;
use App\Models\Inscription;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Messagerie formateur ↔ apprenants.
 *
 * Deux formes de fil : `groupe` (tous les inscrits d'un cours) et `prive`
 * (formateur ↔ un apprenant). Les participants sont déduits des inscriptions,
 * jamais stockés.
 *
 * Mise à jour par interrogation périodique plutôt que WebSocket : le curseur
 * `?since=` ne renvoie que les messages postérieurs à un id connu, ce qui rend
 * le coût d'un sondage négligeable et évite d'ajouter un service temps réel au
 * déploiement.
 *
 * Endpoints (tous sous auth:sanctum) :
 *   GET  /api/conversations                        Liste + compteurs de non-lus
 *   GET  /api/conversations/non-lus                Total des non-lus (badge)
 *   POST /api/conversations/cours/{cours}/groupe   Ouvre/récupère le fil de groupe
 *   POST /api/conversations/cours/{cours}/prive    Ouvre/récupère un fil privé
 *   GET  /api/conversations/{conversation}/messages[?since=ID]
 *   POST /api/conversations/{conversation}/messages
 *   POST /api/conversations/{conversation}/lu
 */
class ConversationController extends Controller
{
    /** Liste des fils visibles, du plus récemment actif au plus ancien. */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversations = Conversation::query()
            ->visiblesPar($user)
            ->with(['cours:id,titre,formateur_id', 'apprenant:id,nom,prenom'])
            ->orderByRaw('COALESCE(dernier_message_le, created_at) DESC')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $conversations
                ->map(fn (Conversation $c) => $this->formater($c, $user))
                ->values(),
        ]);
    }

    /** Total des messages non lus, pour la pastille de navigation. */
    public function nonLus(Request $request): JsonResponse
    {
        $user = $request->user();

        $total = Conversation::query()
            ->visiblesPar($user)
            ->get()
            ->sum(fn (Conversation $c) => $c->nonLus($user));

        return response()->json([
            'success' => true,
            'data' => ['total' => $total],
        ]);
    }

    /** Ouvre (ou récupère) le fil de groupe d'un cours. */
    public function groupe(Request $request, Cours $cours): JsonResponse
    {
        $conversation = Conversation::pourCours($cours);

        if (! $conversation->autorise($request->user())) {
            return $this->refus();
        }

        $conversation->load(['cours:id,titre,formateur_id', 'apprenant:id,nom,prenom']);

        return response()->json([
            'success' => true,
            'data' => $this->formater($conversation, $request->user()),
        ]);
    }

    /** Ouvre (ou récupère) un fil privé avec un apprenant du cours. */
    public function prive(Request $request, Cours $cours): JsonResponse
    {
        $donnees = $request->validate([
            'apprenant_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $user = $request->user();
        $estFormateur = (int) $cours->formateur_id === (int) $user->id || $user->role === 'admin';

        // Un apprenant ne peut ouvrir qu'un fil le concernant lui-même.
        if (! $estFormateur && (int) $donnees['apprenant_id'] !== (int) $user->id) {
            return $this->refus();
        }

        // L'interlocuteur doit réellement suivre le cours.
        $inscrit = Inscription::where('apprenant_id', $donnees['apprenant_id'])
            ->where('cours_id', $cours->id)
            ->exists();

        if (! $inscrit) {
            return response()->json([
                'success' => false,
                'message' => 'Cet apprenant n\'est pas inscrit à ce cours.',
            ], 422);
        }

        $conversation = Conversation::privee($cours, (int) $donnees['apprenant_id']);

        if (! $conversation->autorise($user)) {
            return $this->refus();
        }

        $conversation->load(['cours:id,titre,formateur_id', 'apprenant:id,nom,prenom']);

        return response()->json([
            'success' => true,
            'data' => $this->formater($conversation, $user),
        ]);
    }

    /**
     * Messages d'un fil.
     *
     * `?since=ID` ne renvoie que les messages postérieurs : c'est ce qui rend
     * l'interrogation périodique peu coûteuse.
     */
    public function messages(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        if (! $conversation->autorise($user)) {
            return $this->refus();
        }

        $depuis = (int) $request->query('since', 0);

        $requete = $conversation->messages()
            ->with('expediteur:id,nom,prenom,role,avatar')
            ->where('est_masque', false);

        if ($depuis > 0) {
            $messages = $requete->where('id', '>', $depuis)->orderBy('id')->get();
        } else {
            // Premier chargement : les 50 derniers, remis dans l'ordre.
            $messages = $requete->orderByDesc('id')->limit(50)->get()->reverse()->values();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'messages' => $messages->map(fn (ConversationMessage $m) => $this->formaterMessage($m, $user)),
                // Curseur à renvoyer au prochain sondage.
                'dernier_id' => $conversation->messages()->max('id') ?? 0,
            ],
        ]);
    }

    /** Poste un message dans le fil. */
    public function envoyer(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        if (! $conversation->autorise($user)) {
            return $this->refus();
        }

        $donnees = $request->validate([
            'contenu' => ['required', 'string', 'max:5000'],
        ]);

        $message = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'expediteur_id' => $user->id,
            'contenu' => trim($donnees['contenu']),
        ]);

        // L'expéditeur a forcément lu son propre message.
        $this->marquerLu($conversation, $user, $message->id);

        $message->load('expediteur:id,nom,prenom,role,avatar');

        return response()->json([
            'success' => true,
            'data' => $this->formaterMessage($message, $user),
        ], 201);
    }

    /** Positionne le curseur de lecture sur le dernier message du fil. */
    public function marquerCommeLu(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        if (! $conversation->autorise($user)) {
            return $this->refus();
        }

        $dernier = (int) ($conversation->messages()->max('id') ?? 0);
        $this->marquerLu($conversation, $user, $dernier);

        return response()->json([
            'success' => true,
            'data' => ['dernier_message_lu_id' => $dernier],
        ]);
    }

    private function marquerLu(Conversation $conversation, User $user, int $messageId): void
    {
        $lecture = ConversationLecture::firstOrNew([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
        ]);

        // Ne jamais reculer : un sondage tardif ne doit pas « dé-lire ».
        if ($messageId > (int) ($lecture->dernier_message_lu_id ?? 0)) {
            $lecture->dernier_message_lu_id = $messageId;
            $lecture->save();
        }
    }

    private function formater(Conversation $conversation, User $user): array
    {
        $dernier = $conversation->messages()
            ->where('est_masque', false)
            ->latest('id')
            ->first();

        return [
            'id' => $conversation->id,
            'type' => $conversation->type,
            'cours' => [
                'id' => $conversation->cours?->id,
                'titre' => $conversation->cours?->titre,
            ],
            'apprenant' => $conversation->apprenant === null ? null : [
                'id' => $conversation->apprenant->id,
                'nom' => trim($conversation->apprenant->prenom.' '.$conversation->apprenant->nom),
            ],
            'titre' => $conversation->estGroupe()
                ? ($conversation->cours?->titre ?? 'Cours')
                : trim(($conversation->apprenant?->prenom ?? '').' '.($conversation->apprenant?->nom ?? '')),
            'dernier_message' => $dernier?->contenu,
            'dernier_message_le' => optional($conversation->dernier_message_le)->toIso8601String(),
            'non_lus' => $conversation->nonLus($user),
        ];
    }

    private function formaterMessage(ConversationMessage $message, User $user): array
    {
        return [
            'id' => $message->id,
            'contenu' => $message->contenu,
            'envoye_le' => optional($message->created_at)->toIso8601String(),
            'est_de_moi' => (int) $message->expediteur_id === (int) $user->id,
            'expediteur' => [
                'id' => $message->expediteur?->id,
                'nom' => trim(($message->expediteur?->prenom ?? '').' '.($message->expediteur?->nom ?? '')),
                'role' => $message->expediteur?->role,
                'avatar' => $message->expediteur?->avatar,
            ],
        ];
    }

    private function refus(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => 'Vous n\'avez pas accès à cette conversation.',
        ], 403);
    }
}
