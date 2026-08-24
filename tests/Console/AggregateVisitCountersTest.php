<?php

namespace Tests\Console;

use App\Models\SiteVisit;
use App\Models\VisitCounter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AggregateVisitCountersTest extends TestCase
{
    use RefreshDatabase;

    public function test_recomputes_todays_visits_from_distinct_sessions(): void
    {
        // Trois visites journalisées mais seulement deux sessions distinctes.
        SiteVisit::factory()->create(['visited_at' => now(), 'session_id' => 'a']);
        SiteVisit::factory()->create(['visited_at' => now(), 'session_id' => 'a']);
        SiteVisit::factory()->create(['visited_at' => now(), 'session_id' => 'b']);

        $this->artisan('visits:aggregate', ['--days' => 1])->assertExitCode(0);

        $compteur = VisitCounter::where('date', today())->first();
        $this->assertNotNull($compteur);
        $this->assertSame(2, $compteur->today_visits);
        $this->assertSame(2, $compteur->total_visits);
    }

    public function test_corrects_a_drifted_counter(): void
    {
        SiteVisit::factory()->create(['visited_at' => now(), 'session_id' => 'a']);
        // Le compteur temps réel a raté un incrément (dérive volontaire ici).
        VisitCounter::create(['date' => today(), 'today_visits' => 0, 'total_visits' => 0]);

        $this->artisan('visits:aggregate', ['--days' => 1]);

        $compteur = VisitCounter::where('date', today())->first();
        $this->assertSame(1, $compteur->today_visits);
    }

    public function test_carries_the_running_total_forward_across_days(): void
    {
        SiteVisit::factory()->create(['visited_at' => today()->subDay()->addHours(10), 'session_id' => 'hier']);
        SiteVisit::factory()->create(['visited_at' => now(), 'session_id' => 'aujourdhui']);

        $this->artisan('visits:aggregate', ['--days' => 2]);

        $hier = VisitCounter::where('date', today()->subDay())->first();
        $aujourdhui = VisitCounter::where('date', today())->first();

        $this->assertSame(1, $hier->today_visits);
        $this->assertSame(1, $hier->total_visits);
        $this->assertSame(1, $aujourdhui->today_visits);
        $this->assertSame(2, $aujourdhui->total_visits);
    }
}
