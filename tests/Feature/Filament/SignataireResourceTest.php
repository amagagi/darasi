<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Cours\CoursResource;
use App\Filament\Resources\Signataires\Pages\CreateSignataire;
use App\Filament\Resources\Signataires\Pages\EditSignataire;
use App\Models\Certificat;
use App\Models\Cours;
use App\Models\Inscription;
use App\Models\Pole;
use App\Models\Signataire;
use App\Models\TentativeTestFinal;
use App\Models\TestFinal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Administration des signataires et des certificats.
 *
 * Les pages sont aussi chargées en entier : la navigation du panneau est
 * construite à la main, et une incohérence (icône sur un groupe ET sur l'un de
 * ses éléments) ne se révèle qu'au rendu Blade.
 */
class SignataireResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake(Signataire::DISQUE);
        $this->admin = User::factory()->admin()->create();
    }

    public function test_admin_peut_creer_un_signataire_avec_sa_signature(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CreateSignataire::class)
            ->set('data.nom', 'Dr Moussa Garba')
            ->set('data.fonction', 'Directeur général')
            // PNG réel de 1 × 1 pixel : valide pour la règle `image`, sans
            // passer par GD, absent ou instable sur certains postes Windows.
            ->set('data.image_signature', UploadedFile::fake()->createWithContent(
                'signature.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='),
            ))
            ->call('create')
            ->assertHasNoFormErrors();

        $signataire = Signataire::query()->where('nom', 'Dr Moussa Garba')->firstOrFail();

        $this->assertTrue($signataire->est_actif);
        $this->assertNotNull($signataire->image_signature);
        // Disque privé : la signature n'est jamais servie publiquement.
        Storage::disk(Signataire::DISQUE)->assertExists($signataire->image_signature);
    }

    public function test_nom_et_fonction_sont_obligatoires(): void
    {
        Livewire::actingAs($this->admin)
            ->test(CreateSignataire::class)
            ->call('create')
            ->assertHasFormErrors(['nom' => 'required', 'fonction' => 'required']);
    }

    public function test_admin_peut_modifier_un_signataire(): void
    {
        $signataire = Signataire::create(['nom' => 'Ancien directeur', 'fonction' => 'Directeur', 'est_actif' => true]);

        Livewire::actingAs($this->admin)
            ->test(EditSignataire::class, ['record' => $signataire->getRouteKey()])
            ->set('data.nom', 'Nouvelle directrice')
            ->set('data.est_actif', false)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('signataires', [
            'id' => $signataire->id,
            'nom' => 'Nouvelle directrice',
            'est_actif' => false,
        ]);
    }

    public function test_un_cours_certifiant_peut_avoir_ses_propres_signataires(): void
    {
        $cours = $this->creerCours();
        $partenaire = Signataire::create([
            'nom' => 'Responsable partenaire',
            'fonction' => 'Partenaire',
            'est_actif' => false,
        ]);

        Livewire::actingAs($this->admin)
            ->test(CoursResource::getPages()['edit']->getPage(), ['record' => $cours->getRouteKey()])
            ->set('data.signataires', [$partenaire->id])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(
            [$partenaire->id],
            $cours->signataires()->pluck('signataires.id')->all(),
        );
    }

    public function test_les_pages_d_administration_se_chargent(): void
    {
        $cours = $this->creerCours();
        $certificat = $this->creerCertificat($cours);
        Signataire::create(['nom' => 'Dr Moussa Garba', 'fonction' => 'Directeur général']);

        $this->actingAs($this->admin);

        $this->get('/admin/signataires')->assertOk()->assertSee('Dr Moussa Garba');
        $this->get('/admin/signataires/create')->assertOk();
        // Nouvelle action « PDF » (lien signé) et colonne des signataires.
        $this->get('/admin/certificats')->assertOk()->assertSee($certificat->code_verification);
        // Champ « Signataires du certificat » du formulaire de cours.
        $this->get("/admin/cours/{$cours->id}/edit")->assertOk();
    }

    private function creerCours(): Cours
    {
        $pole = Pole::create(['nom' => 'IT', 'slug' => 'it-signataires', 'ordre' => 1]);
        $formateur = User::factory()->create(['role' => 'formateur']);

        return Cours::create([
            'titre' => 'Cybersécurité appliquée',
            'pole_id' => $pole->id,
            'formateur_id' => $formateur->id,
            'est_certifiant' => true,
            'note_minimale_certificat' => 70,
            'prix' => 5000,
            'est_gratuit' => false,
            'statut' => 'publie',
        ]);
    }

    private function creerCertificat(Cours $cours): Certificat
    {
        $apprenant = User::factory()->create(['role' => 'apprenant']);
        $inscription = Inscription::create(['apprenant_id' => $apprenant->id, 'cours_id' => $cours->id]);
        $testFinal = TestFinal::create(['cours_id' => $cours->id, 'titre' => 'Examen final', 'note_minimale' => 70]);
        $tentative = TentativeTestFinal::create([
            'inscription_id' => $inscription->id,
            'test_final_id' => $testFinal->id,
            'note' => 15,
            'est_reussi' => true,
        ]);

        return Certificat::create([
            'inscription_id' => $inscription->id,
            'tentative_final_id' => $tentative->id,
            'code_verification' => 'CERT-ADMIN-000001',
            'date_emission' => now(),
            'est_valide' => true,
        ]);
    }
}
