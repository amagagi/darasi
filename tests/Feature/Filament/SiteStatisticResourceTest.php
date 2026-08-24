<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Statistics\Pages\CreateSiteStatistic;
use App\Filament\Resources\Statistics\Pages\EditSiteStatistic;
use App\Models\SiteStatistic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SiteStatisticResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_statistic(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(CreateSiteStatistic::class)
            ->set('data.label', 'Apprenants formés')
            ->set('data.value', '1200+')
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('site_statistics', [
            'label' => 'Apprenants formés',
            'value' => '1200+',
        ]);
    }

    public function test_admin_can_edit_a_statistic(): void
    {
        $statistic = SiteStatistic::factory()->create(['value' => '100+']);
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(EditSiteStatistic::class, ['record' => $statistic->getRouteKey()])
            ->set('data.value', '150+')
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('site_statistics', [
            'id' => $statistic->id,
            'value' => '150+',
        ]);
    }

    public function test_admin_can_delete_a_statistic(): void
    {
        $statistic = SiteStatistic::factory()->create();
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(EditSiteStatistic::class, ['record' => $statistic->getRouteKey()])
            ->callAction('delete');

        $this->assertDatabaseMissing('site_statistics', ['id' => $statistic->id]);
    }
}
