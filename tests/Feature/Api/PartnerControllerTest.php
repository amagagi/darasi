<?php

namespace Tests\Feature\Api;

use App\Models\Partner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PartnerControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Le cache array de test survit entre les méthodes du même process :
        // on repart propre à chaque test pour ne pas lire les données d'un
        // test précédent.
        Cache::forget('partners.active');
    }

    public function test_returns_only_active_partners_ordered(): void
    {
        Partner::factory()->create(['name' => 'Troisième', 'display_order' => 30]);
        Partner::factory()->create(['name' => 'Premier', 'display_order' => 10]);
        Partner::factory()->inactive()->create(['name' => 'Masqué', 'display_order' => 5]);
        Partner::factory()->create(['name' => 'Deuxième', 'display_order' => 20]);

        $response = $this->getJson('/api/partners');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.name', 'Premier')
            ->assertJsonPath('data.1.name', 'Deuxième')
            ->assertJsonPath('data.2.name', 'Troisième');
    }

    public function test_returns_empty_list_when_no_active_partner(): void
    {
        Partner::factory()->inactive()->create();

        $response = $this->getJson('/api/partners');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(0, 'data');
    }
}
