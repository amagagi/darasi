<?php

namespace Tests\Feature\Api;

use App\Models\Conversation;
use App\Models\ConversationMessage;
use App\Models\Cours;
use App\Models\Inscription;
use App\Models\Pole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Messagerie formateur ↔ apprenants.
 *
 * L'enjeu principal est le cloisonnement : un apprenant ne doit voir que les
 * fils des cours auxquels il est inscrit, et jamais un fil privé qui ne le
 * concerne pas.
 */
class ConversationControllerTest extends TestCase
{
    use RefreshDatabase;

    private Cours $cours;
    private User $formateur;
    private User $inscrit;
    private User $intrus;

    protected function setUp(): void
    {
        parent::setUp();

        $pole = Pole::create(['nom' => 'IT', 'slug' => 'it-msg', 'ordre' => 1]);

        $this->formateur = User::factory()->create(['role' => 'formateur']);
        $this->inscrit = User::factory()->create(['role' => 'apprenant']);
        $this->intrus = User::factory()->create(['role' => 'apprenant']);

        $this->cours = Cours::create([
            'titre' => 'Cours de test',
            'pole_id' => $pole->id,
            'formateur_id' => $this->formateur->id,
            'est_gratuit' => true,
            'statut' => 'publie',
        ]);

        Inscription::create([
            'apprenant_id' => $this->inscrit->id,
            'cours_id' => $this->cours->id,
        ]);
    }

    public function test_un_non_inscrit_ne_voit_aucune_conversation(): void
    {
        Conversation::pourCours($this->cours);

        $this->actingAs($this->intrus, 'sanctum')
            ->getJson('/api/conversations')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_un_inscrit_voit_le_fil_de_groupe_de_son_cours(): void
    {
        Conversation::pourCours($this->cours);

        $this->actingAs($this->inscrit, 'sanctum')
            ->getJson('/api/conversations')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', Conversation::TYPE_GROUPE);
    }

    public function test_un_non_inscrit_ne_peut_pas_lire_les_messages(): void
    {
        $conversation = Conversation::pourCours($this->cours);

        $this->actingAs($this->intrus, 'sanctum')
            ->getJson("/api/conversations/{$conversation->id}/messages")
            ->assertForbidden();
    }

    public function test_un_non_inscrit_ne_peut_pas_ecrire(): void
    {
        $conversation = Conversation::pourCours($this->cours);

        $this->actingAs($this->intrus, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/messages", [
                'contenu' => 'Bonjour',
            ])
            ->assertForbidden();
    }

    public function test_un_apprenant_ne_voit_pas_le_fil_prive_d_un_autre(): void
    {
        $autreInscrit = User::factory()->create(['role' => 'apprenant']);
        Inscription::create([
            'apprenant_id' => $autreInscrit->id,
            'cours_id' => $this->cours->id,
        ]);

        $prive = Conversation::privee($this->cours, $autreInscrit->id);

        $this->actingAs($this->inscrit, 'sanctum')
            ->getJson("/api/conversations/{$prive->id}/messages")
            ->assertForbidden();
    }

    public function test_le_formateur_ouvre_un_fil_prive_avec_un_inscrit(): void
    {
        $this->actingAs($this->formateur, 'sanctum')
            ->postJson("/api/conversations/cours/{$this->cours->id}/prive", [
                'apprenant_id' => $this->inscrit->id,
            ])
            ->assertOk()
            ->assertJsonPath('data.type', Conversation::TYPE_PRIVE);
    }

    public function test_on_ne_peut_pas_ouvrir_un_fil_avec_un_non_inscrit(): void
    {
        $this->actingAs($this->formateur, 'sanctum')
            ->postJson("/api/conversations/cours/{$this->cours->id}/prive", [
                'apprenant_id' => $this->intrus->id,
            ])
            ->assertStatus(422);
    }

    /** Le curseur `since` est ce qui rend l'interrogation périodique viable. */
    public function test_le_curseur_since_ne_renvoie_que_les_nouveaux_messages(): void
    {
        $conversation = Conversation::pourCours($this->cours);

        $ancien = ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'expediteur_id' => $this->formateur->id,
            'contenu' => 'Premier',
        ]);

        ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'expediteur_id' => $this->formateur->id,
            'contenu' => 'Second',
        ]);

        $this->actingAs($this->inscrit, 'sanctum')
            ->getJson("/api/conversations/{$conversation->id}/messages?since={$ancien->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data.messages')
            ->assertJsonPath('data.messages.0.contenu', 'Second');
    }

    public function test_les_non_lus_se_remettent_a_zero_apres_marquage(): void
    {
        $conversation = Conversation::pourCours($this->cours);

        ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'expediteur_id' => $this->formateur->id,
            'contenu' => 'Avez-vous compris ?',
        ]);

        $this->actingAs($this->inscrit, 'sanctum')
            ->getJson('/api/conversations/non-lus')
            ->assertJsonPath('data.total', 1);

        $this->actingAs($this->inscrit, 'sanctum')
            ->postJson("/api/conversations/{$conversation->id}/lu")
            ->assertOk();

        $this->actingAs($this->inscrit, 'sanctum')
            ->getJson('/api/conversations/non-lus')
            ->assertJsonPath('data.total', 0);
    }

    public function test_un_message_masque_disparait_de_l_api(): void
    {
        $conversation = Conversation::pourCours($this->cours);

        ConversationMessage::create([
            'conversation_id' => $conversation->id,
            'expediteur_id' => $this->formateur->id,
            'contenu' => 'Message inapproprié',
            'est_masque' => true,
        ]);

        $this->actingAs($this->inscrit, 'sanctum')
            ->getJson("/api/conversations/{$conversation->id}/messages")
            ->assertOk()
            ->assertJsonCount(0, 'data.messages');
    }
}
