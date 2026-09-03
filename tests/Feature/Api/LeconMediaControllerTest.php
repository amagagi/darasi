<?php

namespace Tests\Feature\Api;

use App\Models\Cours;
use App\Models\Inscription;
use App\Models\Lecon;
use App\Models\Module;
use App\Models\Pole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Diffusion sécurisée des médias de leçon.
 *
 * Ces tests couvrent la garantie principale : un PDF ou une vidéo de cours
 * payant ne doit jamais être joignable sans URL signée valide.
 */
class LeconMediaControllerTest extends TestCase
{
    use RefreshDatabase;

    private Cours $cours;
    private Lecon $lecon;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Lecon::DISQUE_PRIVE);
        Storage::disk(Lecon::DISQUE_PRIVE)->put('lecons/pdfs/support.pdf', '%PDF-1.4 contenu de test');

        $pole = Pole::create(['nom' => 'IT', 'slug' => 'it-test', 'ordre' => 1]);

        $formateur = User::factory()->create(['role' => 'formateur']);

        $this->cours = Cours::create([
            'titre' => 'Cours payant',
            'pole_id' => $pole->id,
            'formateur_id' => $formateur->id,
            'prix' => 5000,
            'est_gratuit' => false,
            'statut' => 'publie',
        ]);

        $module = Module::create([
            'cours_id' => $this->cours->id,
            'titre' => 'Module 1',
            'ordre' => 1,
        ]);

        $this->lecon = Lecon::create([
            'module_id' => $module->id,
            'titre' => 'Support PDF',
            'type_contenu' => 'pdf',
            'url_pdf' => 'lecons/pdfs/support.pdf',
            'ordre' => 1,
        ]);
    }

    public function test_le_flux_est_refuse_sans_signature(): void
    {
        $this->getJson("/api/lecons/{$this->lecon->id}/stream/pdf")
            ->assertForbidden();
    }

    public function test_le_flux_est_refuse_si_la_signature_a_expire(): void
    {
        $url = URL::temporarySignedRoute(
            'lecons.media.stream',
            now()->subMinute(),
            ['lecon' => $this->lecon->id, 'type' => 'pdf'],
        );

        $this->get($url)->assertForbidden();
    }

    public function test_le_flux_signe_sert_le_fichier_en_inline(): void
    {
        $url = $this->lecon->urlMediaSignee('pdf');

        $reponse = $this->get($url);

        $reponse->assertOk();
        $reponse->assertHeader('Content-Type', 'application/pdf');
        // « inline » et non « attachment » : pas de téléchargement forcé.
        $this->assertStringContainsString(
            'inline',
            $reponse->headers->get('Content-Disposition'),
        );
    }

    public function test_le_flux_repond_en_206_a_une_requete_range(): void
    {
        $url = $this->lecon->urlMediaSignee('pdf');

        $reponse = $this->get($url, ['Range' => 'bytes=0-4']);

        // 206 : indispensable pour l'avance rapide vidéo sans tout télécharger.
        $reponse->assertStatus(206);
        $reponse->assertHeader('Accept-Ranges', 'bytes');
    }

    public function test_un_visiteur_non_inscrit_n_obtient_pas_d_url(): void
    {
        $intrus = User::factory()->create(['role' => 'apprenant']);

        $this->actingAs($intrus, 'sanctum')
            ->getJson("/api/lecons/{$this->lecon->id}/media")
            ->assertForbidden();
    }

    public function test_un_apprenant_inscrit_obtient_une_url_signee(): void
    {
        $apprenant = User::factory()->create(['role' => 'apprenant']);

        Inscription::create([
            'apprenant_id' => $apprenant->id,
            'cours_id' => $this->cours->id,
        ]);

        $reponse = $this->actingAs($apprenant, 'sanctum')
            ->getJson("/api/lecons/{$this->lecon->id}/media");

        $reponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.est_externe', false);

        $this->assertStringContainsString('signature=', $reponse->json('data.url'));
    }

    public function test_un_lien_externe_est_renvoye_sans_signature(): void
    {
        $this->lecon->update([
            'type_contenu' => 'video',
            'url_video' => 'https://www.youtube.com/watch?v=abc123',
        ]);

        $this->assertFalse($this->lecon->estMediaHeberge('video'));
        $this->assertSame(
            'https://www.youtube.com/watch?v=abc123',
            $this->lecon->urlMediaSignee('video'),
        );
    }

    /**
     * Les données seedées stockent `/storage/cours/x.pdf` tandis que Filament
     * enregistre `lecons/pdfs/x.pdf` : les deux doivent aboutir au même chemin.
     */
    public function test_les_chemins_herites_sont_normalises(): void
    {
        $this->lecon->url_pdf = '/storage/cours/ancien.pdf';
        $this->assertSame('cours/ancien.pdf', $this->lecon->cheminMediaRelatif('pdf'));

        $this->lecon->url_pdf = 'lecons/pdfs/nouveau.pdf';
        $this->assertSame('lecons/pdfs/nouveau.pdf', $this->lecon->cheminMediaRelatif('pdf'));
    }
}
