<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Annonces\Pages\CreateAnnonce;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
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
}
