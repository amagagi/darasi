<?php

namespace Tests\Feature\Middleware;

use App\Models\SiteVisit;
use App\Models\VisitCounter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackSiteVisitTest extends TestCase
{
    use RefreshDatabase;

    public function test_records_a_visit_and_increments_todays_counter(): void
    {
        $response = $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (Test Browser)'])
            ->postJson('/api/site-visits', [
                'page_url' => '/welcome',
                'session_id' => 'session-abc',
            ]);

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('site_visits', [
            'page_url' => '/welcome',
            'session_id' => 'session-abc',
        ]);

        $compteur = VisitCounter::where('date', today())->first();
        $this->assertNotNull($compteur);
        $this->assertSame(1, $compteur->today_visits);
        $this->assertSame(1, $compteur->total_visits);
    }

    public function test_ignores_requests_from_known_bots(): void
    {
        $response = $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'])
            ->postJson('/api/site-visits', ['page_url' => '/welcome']);

        // Le middleware court-circuite avant même la validation/le contrôleur.
        $response->assertNoContent();

        $this->assertDatabaseCount('site_visits', 0);
        $this->assertDatabaseCount('visit_counters', 0);
    }

    public function test_ignores_requests_with_no_user_agent(): void
    {
        $response = $this->withHeaders(['User-Agent' => ''])
            ->postJson('/api/site-visits', ['page_url' => '/welcome']);

        $response->assertNoContent();
        $this->assertDatabaseCount('site_visits', 0);
    }

    public function test_a_second_request_within_30_minutes_from_the_same_session_does_not_increment_the_counter(): void
    {
        $headers = ['User-Agent' => 'Mozilla/5.0 (Test Browser)'];

        $this->withHeaders($headers)->postJson('/api/site-visits', [
            'page_url' => '/welcome',
            'session_id' => 'session-abc',
        ]);
        $this->withHeaders($headers)->postJson('/api/site-visits', [
            'page_url' => '/plateformes',
            'session_id' => 'session-abc',
        ]);

        // Comptée une seule fois...
        $compteur = VisitCounter::where('date', today())->first();
        $this->assertSame(1, $compteur->today_visits);

        // ...mais chaque vue de page est bien journalisée (classement des
        // pages les plus vues).
        $this->assertDatabaseCount('site_visits', 2);
        $this->assertDatabaseHas('site_visits', ['page_url' => '/plateformes']);
    }

    public function test_a_request_from_a_different_session_increments_the_counter_again(): void
    {
        $headers = ['User-Agent' => 'Mozilla/5.0 (Test Browser)'];

        $this->withHeaders($headers)->postJson('/api/site-visits', [
            'page_url' => '/welcome',
            'session_id' => 'session-un',
        ]);
        $this->withHeaders($headers)->postJson('/api/site-visits', [
            'page_url' => '/welcome',
            'session_id' => 'session-deux',
        ]);

        $compteur = VisitCounter::where('date', today())->first();
        $this->assertSame(2, $compteur->today_visits);
    }

    public function test_dedup_falls_back_to_ip_hash_when_no_session_id_is_provided(): void
    {
        $headers = ['User-Agent' => 'Mozilla/5.0 (Test Browser)', 'REMOTE_ADDR' => '203.0.113.5'];

        $this->withHeaders($headers)->postJson('/api/site-visits', ['page_url' => '/welcome']);
        $this->withHeaders($headers)->postJson('/api/site-visits', ['page_url' => '/welcome']);

        $compteur = VisitCounter::where('date', today())->first();
        $this->assertSame(1, $compteur->today_visits);
        $this->assertDatabaseCount('site_visits', 2);
    }

    public function test_never_stores_the_raw_ip_address(): void
    {
        $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (Test Browser)', 'REMOTE_ADDR' => '203.0.113.9'])
            ->postJson('/api/site-visits', ['page_url' => '/welcome']);

        $visite = SiteVisit::first();
        $this->assertNotNull($visite);
        $this->assertNotSame('203.0.113.9', $visite->ip_hash);
        $this->assertSame(64, strlen($visite->ip_hash));
    }
}
