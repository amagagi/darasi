<?php

namespace Tests\Feature\Api;

use App\Models\SiteStatistic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SiteStatisticControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget('site-statistics.active');
    }

    public function test_returns_only_active_statistics_ordered(): void
    {
        SiteStatistic::factory()->create(['label' => 'Second', 'display_order' => 20]);
        SiteStatistic::factory()->create(['label' => 'Premier', 'display_order' => 10]);
        SiteStatistic::factory()->inactive()->create(['label' => 'Masqué', 'display_order' => 5]);

        $response = $this->getJson('/api/site-statistics');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.label', 'Premier')
            ->assertJsonPath('data.1.label', 'Second');
    }

    public function test_value_is_returned_as_free_form_string(): void
    {
        SiteStatistic::factory()->create(['label' => 'Apprenants formés', 'value' => '1200+']);

        $response = $this->getJson('/api/site-statistics');

        $response->assertOk()->assertJsonPath('data.0.value', '1200+');
    }
}
