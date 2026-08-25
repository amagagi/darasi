<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Annonces\Pages\CreateAnnonce;
use App\Models\Annonce;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AnnonceUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_image_larger_than_5mo(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(CreateAnnonce::class)
            ->set('data.titre', 'Test annonce')
            ->set('data.image', UploadedFile::fake()->image('photo.jpg')->size(6000))
            ->call('create')
            ->assertHasFormErrors(['image']);
    }

    public function test_accepts_image_up_to_5mo(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(CreateAnnonce::class)
            ->set('data.titre', 'Test annonce')
            ->set('data.image', UploadedFile::fake()->image('photo.jpg')->size(4000))
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('annonces', ['titre' => 'Test annonce']);
    }

    /**
     * Régression : ->visibility('public') seul ne suffit pas, Filament utilise
     * config('filament.default_filesystem_disk') (= FILESYSTEM_DISK, "local" en
     * prod) tant que ->disk('public') n'est pas explicite — la racine du disque
     * "local" (storage/app/private depuis Laravel 11) n'est jamais servie par
     * nginx/le lien symbolique public/storage. Sans cette assertion, le champ
     * peut redevenir "silencieusement" mal routé sans faire échouer aucun test.
     */
    public function test_stores_image_on_the_public_disk(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(CreateAnnonce::class)
            ->set('data.titre', 'Test annonce disque public')
            ->set('data.image', UploadedFile::fake()->image('photo.jpg')->size(4000))
            ->call('create')
            ->assertHasNoFormErrors();

        $annonce = Annonce::where('titre', 'Test annonce disque public')->firstOrFail();

        Storage::disk('public')->assertExists($annonce->image);
    }
}
