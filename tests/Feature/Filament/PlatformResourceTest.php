<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Platforms\Pages\CreatePlatform;
use App\Filament\Resources\Platforms\Pages\EditPlatform;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_platform(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(CreatePlatform::class)
            ->set('data.name', 'Ma Plateforme')
            ->set('data.short_description', 'Une courte description.')
            ->set('data.url', 'https://exemple.com')
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('platforms', [
            'name' => 'Ma Plateforme',
            'slug' => 'ma-plateforme',
        ]);
    }

    public function test_slug_is_generated_uniquely_on_name_collision(): void
    {
        Platform::factory()->create(['name' => 'Ma Plateforme', 'slug' => 'ma-plateforme']);
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(CreatePlatform::class)
            ->set('data.name', 'Ma Plateforme')
            ->set('data.short_description', 'Une autre description.')
            ->set('data.url', 'https://exemple2.com')
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('platforms', ['slug' => 'ma-plateforme']);
        $this->assertDatabaseHas('platforms', ['slug' => 'ma-plateforme-2']);
    }

    public function test_admin_can_edit_a_platform(): void
    {
        $platform = Platform::factory()->create(['name' => 'Ancien Nom', 'is_active' => true]);
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(EditPlatform::class, ['record' => $platform->getRouteKey()])
            ->set('data.is_active', false)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('platforms', [
            'id' => $platform->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_a_platform(): void
    {
        $platform = Platform::factory()->create();
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(EditPlatform::class, ['record' => $platform->getRouteKey()])
            ->callAction('delete');

        $this->assertDatabaseMissing('platforms', ['id' => $platform->id]);
    }
}
