<?php

namespace Tests\Feature\Api;

use App\Models\Certificat;
use App\Models\Cours;
use App\Models\Inscription;
use App\Models\Pole;
use App\Models\Signataire;
use App\Models\TentativeTestFinal;
use App\Models\TestFinal;
use App\Models\User;
use App\Services\CertificatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Certificats : téléchargement sécurisé et signatures figées.
 *
 * Garanties couvertes : seul le titulaire obtient le PDF, un certificat
 * révoqué n'est plus téléchargeable, et un certificat délivré ne change pas
 * quand les signataires changent.
 */
class CertificatControllerTest extends TestCase
{
    use RefreshDatabase;

    private const CODE = 'CERT-TEST-000001';

    private User $apprenant;

    private Cours $cours;

    private Certificat $certificat;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Signataire::DISQUE);

        $pole = Pole::create(['nom' => 'IT', 'slug' => 'it-certificats', 'ordre' => 1]);
        $formateur = User::factory()->create(['role' => 'formateur']);

        $this->apprenant = User::factory()->create([
            'role' => 'apprenant',
            'prenom' => 'Aïcha',
            'nom' => 'Issoufou',
        ]);

        $this->cours = Cours::create([
            'titre' => 'Cybersécurité appliquée',
            'pole_id' => $pole->id,
            'formateur_id' => $formateur->id,
            'est_certifiant' => true,
            'prix' => 0,
            'est_gratuit' => true,
            'statut' => 'publie',
        ]);

        $inscription = Inscription::create([
            'apprenant_id' => $this->apprenant->id,
            'cours_id' => $this->cours->id,
        ]);

        $testFinal = TestFinal::create([
            'cours_id' => $this->cours->id,
            'titre' => 'Examen final',
            'note_minimale' => 70,
        ]);

        $tentative = TentativeTestFinal::create([
            'inscription_id' => $inscription->id,
            'test_final_id' => $testFinal->id,
            'note' => 16.5,
            'est_reussi' => true,
            'tentative_numero' => 1,
            'a_obtenu_certificat' => true,
            'date_obtention_certificat' => now(),
        ]);

        $this->certificat = Certificat::create([
            'inscription_id' => $inscription->id,
            'tentative_final_id' => $tentative->id,
            'code_verification' => self::CODE,
            'date_emission' => now(),
            'est_valide' => true,
        ]);
    }

    public function test_un_autre_apprenant_n_obtient_pas_le_lien(): void
    {
        $intrus = User::factory()->create(['role' => 'apprenant']);

        $this->actingAs($intrus, 'sanctum')
            ->getJson("/api/certificats/{$this->certificat->id}/pdf")
            ->assertForbidden();
    }

    public function test_le_titulaire_obtient_des_liens_signes(): void
    {
        $reponse = $this->actingAs($this->apprenant, 'sanctum')
            ->getJson("/api/certificats/{$this->certificat->id}/pdf");

        $reponse->assertOk()->assertJsonPath('success', true);

        $this->assertStringContainsString('/fichier/apercu', $reponse->json('data.url_apercu'));
        $this->assertStringContainsString('signature=', $reponse->json('data.url_apercu'));
        $this->assertStringContainsString('/fichier/telechargement', $reponse->json('data.url_telechargement'));
    }

    public function test_un_certificat_revoque_n_est_plus_telechargeable(): void
    {
        $this->certificat->update(['est_valide' => false, 'date_revocation' => now()]);

        $this->actingAs($this->apprenant, 'sanctum')
            ->getJson("/api/certificats/{$this->certificat->id}/pdf")
            ->assertForbidden();
    }

    public function test_le_fichier_est_refuse_sans_signature(): void
    {
        $this->get("/api/certificats/{$this->certificat->id}/fichier/apercu")
            ->assertForbidden();
    }

    public function test_le_lien_signe_sert_un_vrai_pdf(): void
    {
        $liens = $this->actingAs($this->apprenant, 'sanctum')
            ->getJson("/api/certificats/{$this->certificat->id}/pdf")
            ->json('data');

        $apercu = $this->get($liens['url_apercu']);
        $apercu->assertOk();
        $apercu->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF', $apercu->getContent());
        $this->assertStringContainsString('inline', $apercu->headers->get('Content-Disposition'));

        $telechargement = $this->get($liens['url_telechargement']);
        $telechargement->assertOk();
        $this->assertStringContainsString('attachment', $telechargement->headers->get('Content-Disposition'));
    }

    public function test_les_signatures_figees_ne_changent_plus(): void
    {
        $directeur = $this->creerSignataire('Dr Moussa Garba', 'Directeur général');
        $service = app(CertificatService::class);

        $this->assertTrue($service->figerSignatures($this->certificat));

        // Changement de directeur après l'émission, image d'origine supprimée.
        $cheminOriginal = $directeur->image_signature;
        $directeur->update(['nom' => 'Mme Fati Hama', 'fonction' => 'Directrice générale']);
        Storage::disk(Signataire::DISQUE)->delete($cheminOriginal);
        $this->creerSignataire('Nouveau signataire', 'Responsable pédagogique', 1);

        $this->assertFalse($service->figerSignatures($this->certificat), 'Déjà figé : rien ne doit changer.');

        $signatures = $this->certificat->signatures()->get();
        $this->assertCount(1, $signatures);
        $this->assertSame('Dr Moussa Garba', $signatures[0]->nom);
        $this->assertSame('Directeur général', $signatures[0]->fonction);

        // La copie figée survit à la suppression de l'image d'origine.
        $this->assertNotSame($cheminOriginal, $signatures[0]->image_signature);
        Storage::disk(Signataire::DISQUE)->assertExists($signatures[0]->image_signature);
    }

    public function test_sans_signataire_rien_n_est_fige(): void
    {
        $service = app(CertificatService::class);

        $this->assertFalse($service->figerSignatures($this->certificat));
        $this->assertSame(0, $this->certificat->signatures()->count());

        // Configurés plus tard, les signataires s'appliquent bien.
        $this->creerSignataire('Dr Moussa Garba', 'Directeur général');

        $this->assertTrue($service->figerSignatures($this->certificat));
        $this->assertSame(1, $this->certificat->signatures()->count());
    }

    public function test_les_signataires_du_cours_remplacent_ceux_par_defaut(): void
    {
        $this->creerSignataire('Signataire par défaut', 'Directeur général');
        $partenaire = $this->creerSignataire('Responsable partenaire', 'Partenaire', 0, estActif: false);
        $this->cours->signataires()->attach($partenaire->id);

        app(CertificatService::class)->figerSignatures($this->certificat);

        $this->assertSame(
            ['Responsable partenaire'],
            $this->certificat->signatures()->pluck('nom')->all(),
        );
    }

    public function test_au_plus_trois_signataires_sont_figes(): void
    {
        foreach (range(1, 4) as $ordre) {
            $this->creerSignataire("Signataire {$ordre}", 'Fonction', $ordre);
        }

        app(CertificatService::class)->figerSignatures($this->certificat);

        $this->assertSame(
            ['Signataire 1', 'Signataire 2', 'Signataire 3'],
            $this->certificat->signatures()->pluck('nom')->all(),
        );
    }

    public function test_actualiser_remplace_les_signatures_figees(): void
    {
        $ancien = $this->creerSignataire('Ancien directeur', 'Directeur général');
        $service = app(CertificatService::class);
        $service->figerSignatures($this->certificat);

        $ancien->update(['est_actif' => false]);
        $this->creerSignataire('Nouvelle directrice', 'Directrice générale');

        $this->assertTrue($service->actualiserSignatures($this->certificat));
        $this->assertSame(['Nouvelle directrice'], $this->certificat->signatures()->pluck('nom')->all());
    }

    public function test_actualiser_sans_signataire_conserve_les_signatures(): void
    {
        $directeur = $this->creerSignataire('Dr Moussa Garba', 'Directeur général');
        $service = app(CertificatService::class);
        $service->figerSignatures($this->certificat);

        $directeur->update(['est_actif' => false]);

        $this->assertFalse($service->actualiserSignatures($this->certificat));
        $this->assertSame(['Dr Moussa Garba'], $this->certificat->signatures()->pluck('nom')->all());
    }

    public function test_la_verification_publique_expose_les_signataires(): void
    {
        $this->creerSignataire('Dr Moussa Garba', 'Directeur général');
        app(CertificatService::class)->figerSignatures($this->certificat);

        $this->getJson('/api/certificats/verify/'.self::CODE)
            ->assertOk()
            ->assertJsonPath('data.valide', true)
            ->assertJsonPath('data.certificat.apprenant', 'Aïcha Issoufou')
            ->assertJsonPath('data.certificat.signataires.0.nom', 'Dr Moussa Garba')
            ->assertJsonPath('data.certificat.signataires.0.fonction', 'Directeur général');
    }

    public function test_la_liste_fournit_le_lien_de_verification(): void
    {
        config(['app.frontend_url' => 'https://darasihub.com']);

        $this->actingAs($this->apprenant, 'sanctum')
            ->getJson('/api/certificats')
            ->assertOk()
            ->assertJsonPath('data.0.url_verification', 'https://darasihub.com/#/verification/'.self::CODE);
    }

    private function creerSignataire(string $nom, string $fonction, int $ordre = 0, bool $estActif = true): Signataire
    {
        $chemin = 'signataires/'.md5($nom).'.png';
        // Contenu arbitraire : seule la copie du fichier est vérifiée ici.
        Storage::disk(Signataire::DISQUE)->put($chemin, "signature de {$nom}");

        return Signataire::create([
            'nom' => $nom,
            'fonction' => $fonction,
            'image_signature' => $chemin,
            'ordre' => $ordre,
            'est_actif' => $estActif,
        ]);
    }
}
