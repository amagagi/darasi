<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Partners\Pages\CreatePartner;
use App\Filament\Resources\Partners\Pages\EditPartner;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class PartnerResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_partner(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(CreatePartner::class)
            ->set('data.name', 'Université Test')
            ->set('data.logo_path', UploadedFile::fake()->image('logo.png')->size(500))
            ->set('data.website_url', 'https://universite-test.example')
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('partners', [
            'name' => 'Université Test',
            'website_url' => 'https://universite-test.example',
        ]);
    }

    public function test_name_must_be_unique(): void
    {
        Partner::factory()->create(['name' => 'Déjà Là']);
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(CreatePartner::class)
            ->set('data.name', 'Déjà Là')
            ->set('data.logo_path', UploadedFile::fake()->image('logo.png')->size(500))
            ->call('create')
            ->assertHasFormErrors(['name' => 'unique']);
    }

    public function test_admin_can_edit_a_partner(): void
    {
        $partner = Partner::factory()->create(['name' => 'Ancien Nom', 'is_active' => true]);
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(EditPartner::class, ['record' => $partner->getRouteKey()])
            ->set('data.name', 'Nouveau Nom')
            ->set('data.is_active', false)
            // Le harness de test ne pré-remplit pas data.logo_path depuis le
            // disque comme le ferait un vrai chargement de formulaire.
            ->set('data.logo_path', UploadedFile::fake()->image('logo.png')->size(500))
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('partners', [
            'id' => $partner->id,
            'name' => 'Nouveau Nom',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_a_partner(): void
    {
        $partner = Partner::factory()->create();
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(EditPartner::class, ['record' => $partner->getRouteKey()])
            ->callAction('delete');

        $this->assertDatabaseMissing('partners', ['id' => $partner->id]);
    }
}
