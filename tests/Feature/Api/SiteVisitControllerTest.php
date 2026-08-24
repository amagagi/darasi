<?php

namespace Tests\Feature\Api;

use App\Models\ContenuSite;
use App\Models\VisitCounter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SiteVisitControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_requires_a_page_url(): void
    {
        $response = $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (Test Browser)'])
            ->postJson('/api/site-visits', []);

        $response->assertStatus(422)->assertJsonValidationErrors(['page_url']);
    }

    public function test_summary_returns_defaults_when_no_setting_row_exists(): void
    {
        $response = $this->getJson('/api/site-visits/summary');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.enabled', true)
            ->assertJsonPath('data.total_visits', 0);
    }

    public function test_summary_reflects_the_configured_setting(): void
    {
        VisitCounter::factory()->create(['date' => today(), 'total_visits' => 12345]);
        ContenuSite::create([
            'cle' => ContenuSite::CLE_COMPTEUR_VISITES,
            'titre' => 'Compteur',
            'contenu' => '{n} visites au total',
            'est_actif' => false,
        ]);

        $response = $this->getJson('/api/site-visits/summary');

        $response->assertOk()
            ->assertJsonPath('data.enabled', false)
            ->assertJsonPath('data.total_visits', 12345)
            ->assertJsonPath('data.text_template', '{n} visites au total');
    }
}
