<?php

namespace App\Services;

use App\Models\Certificat;
use App\Models\CertificatSignature;
use App\Models\Cours;
use App\Models\Signataire;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DocumentPdf;
use chillerlan\QRCode\Common\EccLevel;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use finfo;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

/**
 * Production du certificat PDF et gestion de ses signatures.
 *
 * Les signatures sont FIGÉES sur le certificat : nom, fonction et image sont
 * recopiés au moment de l'émission. Remplacer le directeur ou sa signature
 * dans l'administration ne modifie donc jamais un certificat déjà délivré —
 * un document officiel ne doit pas changer après coup.
 */
class CertificatService
{
    /**
     * Copies figées des images, nommées par empreinte de leur contenu : une
     * même signature n'est stockée qu'une fois, quel que soit le nombre de
     * certificats qui la portent.
     */
    private const DOSSIER_SIGNATURES_FIGEES = 'certificats/signatures';

    /** Formats que dompdf sait imprimer. */
    private const TYPES_IMAGE_IMPRIMABLES = ['image/png', 'image/jpeg'];

    /**
     * Signataires applicables à un cours : ceux choisis pour ce cours s'il en
     * a, sinon les signataires par défaut.
     *
     * Les signataires propres ne sont PAS filtrés sur `est_actif` : ce drapeau
     * signifie « par défaut », et un responsable de partenaire choisi pour un
     * seul cours n'a pas vocation à signer tous les autres.
     */
    public function signatairesPour(Cours $cours): Collection
    {
        $propres = $cours->signataires()->get();

        $signataires = $propres->isNotEmpty()
            ? $propres
            : Signataire::query()->actifs()->get();

        return $signataires->take(Signataire::MAXIMUM_PAR_CERTIFICAT)->values();
    }

    /**
     * Fige les signatures du certificat s'il n'en porte pas encore.
     *
     * Sans aucun signataire configuré, rien n'est figé : le certificat recevra
     * les signatures dès qu'elles seront définies, au lieu de rester à jamais
     * vierge.
     *
     * @return bool vrai si cet appel a figé des signatures.
     */
    public function figerSignatures(Certificat $certificat): bool
    {
        $figees = DB::transaction(function () use ($certificat): bool {
            // Verrou : deux téléchargements simultanés d'un certificat encore
            // vierge ne doivent pas figer deux jeux de signatures.
            $verrouille = Certificat::query()
                ->with('inscription.cours')
                ->lockForUpdate()
                ->find($certificat->id);

            if ($verrouille === null || $verrouille->signatures()->exists()) {
                return false;
            }

            $cours = $verrouille->inscription?->cours;
            $signataires = $cours !== null ? $this->signatairesPour($cours) : collect();

            if ($signataires->isEmpty()) {
                return false;
            }

            foreach ($signataires as $position => $signataire) {
                $verrouille->signatures()->create([
                    'signataire_id' => $signataire->id,
                    'nom' => $signataire->nom,
                    'fonction' => $signataire->fonction,
                    'image_signature' => $this->copierImageFigee($signataire->image_signature),
                    'ordre' => $position,
                ]);
            }

            return true;
        });

        $certificat->unsetRelation('signatures');

        return $figees;
    }

    /**
     * Remplace les signatures figées par les signataires actuels du cours.
     *
     * Réservé à l'administration, pour corriger une erreur : un certificat
     * délivré n'a normalement pas à changer.
     */
    public function actualiserSignatures(Certificat $certificat): bool
    {
        $cours = $certificat->inscription?->cours;

        // Rien pour les remplacer : on garde les signatures en place plutôt
        // que de rendre le certificat vierge.
        if ($cours === null || $this->signatairesPour($cours)->isEmpty()) {
            return false;
        }

        return DB::transaction(function () use ($certificat): bool {
            Certificat::query()->lockForUpdate()->find($certificat->id);

            CertificatSignature::query()
                ->where('certificat_id', $certificat->id)
                ->delete();

            return $this->figerSignatures($certificat);
        });
    }

    /**
     * Construit le PDF du certificat, en figeant ses signatures au passage
     * pour les certificats émis avant l'existence des signataires.
     */
    public function genererPdf(Certificat $certificat): DocumentPdf
    {
        $this->figerSignatures($certificat);

        $certificat->load([
            'inscription.cours',
            'inscription.apprenant',
            'tentativeFinal',
            'signatures',
        ]);

        $apprenant = $certificat->inscription->apprenant;
        $urlVerification = $this->urlVerification($certificat);
        $disque = Storage::disk(Signataire::DISQUE);

        return Pdf::loadView('certificats.pdf', [
            'code' => $certificat->code_verification,
            'estValide' => (bool) $certificat->est_valide,
            'titulaire' => trim("{$apprenant->prenom} {$apprenant->nom}"),
            'cours' => $certificat->inscription->cours->titre,
            'note' => $this->noteSurVingt($certificat->tentativeFinal?->note),
            'dateEmission' => $certificat->date_emission->locale('fr')->isoFormat('D MMMM YYYY'),
            'signatures' => $certificat->signatures
                ->map(fn (CertificatSignature $signature) => [
                    'nom' => $signature->nom,
                    'fonction' => $signature->fonction,
                    'image' => $this->imageDuDisque($disque, $signature->image_signature),
                ])
                ->all(),
            'urlVerification' => $urlVerification,
            'qrCode' => $this->qrCode($urlVerification),
            'logo' => $this->logo(),
        ])
            ->setPaper('a4', 'landscape')
            // Sans sous-ensemble, dompdf embarque les polices DejaVu entières :
            // plus d'1 Mo pour une page de texte.
            ->setOption('isFontSubsettingEnabled', true);
    }

    public function nomFichier(Certificat $certificat): string
    {
        return 'certificat-darasi-'.Str::slug($certificat->code_verification).'.pdf';
    }

    /** Page publique où un tiers confirme l'authenticité du certificat. */
    public function urlVerification(Certificat $certificat): string
    {
        $front = rtrim((string) config('app.frontend_url', config('app.url')), '/');

        // Le front Flutter route par fragment (#) : sans lui, le lien
        // ouvrirait la page d'accueil au lieu de la page de vérification.
        return $front.'/#/verification/'.rawurlencode($certificat->code_verification);
    }

    /**
     * Copie l'image dans le dossier des signatures figées et renvoie le chemin
     * de la copie. Supprimer ou remplacer l'image du signataire ne touche donc
     * pas les certificats déjà émis.
     */
    private function copierImageFigee(?string $chemin): ?string
    {
        if (blank($chemin)) {
            return null;
        }

        $disque = Storage::disk(Signataire::DISQUE);

        if (! $disque->exists($chemin)) {
            return null;
        }

        $contenu = $disque->get($chemin);
        $extension = strtolower(pathinfo($chemin, PATHINFO_EXTENSION)) ?: 'png';
        $copie = self::DOSSIER_SIGNATURES_FIGEES.'/'.hash('sha256', $contenu).'.'.$extension;

        if (! $disque->exists($copie)) {
            $disque->put($copie, $contenu);
        }

        return $copie;
    }

    /** 16.5 → « 16,5 », 20.00 → « 20 ». */
    private function noteSurVingt(mixed $note): ?string
    {
        if ($note === null || $note === '') {
            return null;
        }

        $texte = number_format((float) $note, 2, ',', ' ');

        return rtrim(rtrim($texte, '0'), ',');
    }

    private function qrCode(string $contenu): ?string
    {
        // chillerlan/php-qrcode arrive avec Filament (double authentification)
        // et non en dépendance directe. S'il disparaissait, le certificat
        // resterait vérifiable par le code et l'URL imprimés en clair.
        if (! class_exists(QRCode::class)) {
            return null;
        }

        try {
            return (new QRCode(new QROptions([
                'outputType' => QROutputInterface::MARKUP_SVG,
                'outputBase64' => true,
                'eccLevel' => EccLevel::M,
                'addQuietzone' => false,
                'drawLightModules' => false,
            ])))->render($contenu);
        } catch (Throwable $e) {
            Log::warning('QR code du certificat non généré', ['erreur' => $e->getMessage()]);

            return null;
        }
    }

    private function imageDuDisque(Filesystem $disque, ?string $chemin): ?string
    {
        if (blank($chemin) || ! $disque->exists($chemin)) {
            return null;
        }

        return $this->dataUri($disque->get($chemin), $chemin);
    }

    private function logo(): ?string
    {
        $chemin = public_path('images/logo.png');

        return is_file($chemin)
            ? $this->dataUri((string) file_get_contents($chemin), $chemin)
            : null;
    }

    /**
     * Image embarquée dans le PDF, ou null si dompdf ne peut pas l'imprimer.
     *
     * Embarquée plutôt que référencée : dompdf n'a pas accès au réseau
     * (`enable_remote` désactivé) et le disque des signatures est privé.
     */
    private function dataUri(?string $contenu, string $chemin): ?string
    {
        if ($contenu === null || $contenu === '') {
            return null;
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->buffer($contenu) ?: '';

        if (! in_array($mime, self::TYPES_IMAGE_IMPRIMABLES, true)) {
            return null;
        }

        // dompdf a besoin de GD pour un PNG à canal alpha — le cas de toute
        // signature détourée. Sans GD, le téléchargement échouerait : mieux
        // vaut omettre l'image, et le signaler. L'image Docker embarque GD.
        if ($mime === 'image/png' && ! extension_loaded('gd')) {
            Log::error('Extension GD absente : image PNG omise du certificat', [
                'fichier' => basename($chemin),
            ]);

            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($contenu);
    }
}
