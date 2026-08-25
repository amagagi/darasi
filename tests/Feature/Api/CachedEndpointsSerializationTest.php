<?php

namespace Tests\Feature\Api;

use App\Models\Partner;
use App\Models\Platform;
use App\Models\SiteStatistic;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

/**
 * Les tests des contrôleurs individuels tournent avec CACHE_STORE=array
 * (phpunit.xml), qui garde les valeurs en mémoire PHP sans jamais les
 * sérialiser — ils n'auraient donc jamais pu détecter le bug de production
 * du 2026-08-25 : Cache::remember() mettait en cache une Collection Eloquent
 * (pas un tableau), et le pilote "database" (utilisé en prod), qui sérialise
 * réellement via serialize()/unserialize(), la renvoyait en
 * __PHP_Incomplete_Class au lieu d'une liste — un objet JSON `{}` au lieu
 * d'un tableau `[]`, faisant planter le `.map()` côté Flutter.
 *
 * Ce test force le pilote "database" pour reproduire exactement ce chemin,
 * y compris le vrai aller-retour de sérialisation sur une deuxième requête
 * (cache HIT).
 */
class CachedEndpointsSerializationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('cache.default', 'database');
        Cache::flush();
    }

    public function test_partners_survives_a_real_cache_round_trip(): void
    {
        Partner::factory()->create();

        $this->getJson('/api/partners')->assertOk()->assertJsonIsArray('data');
        // Deuxième appel = cache HIT, le seul qui exerce unserialize().
        $this->getJson('/api/partners')->assertOk()->assertJsonIsArray('data');
    }

    public function test_site_statistics_survives_a_real_cache_round_trip(): void
    {
        SiteStatistic::factory()->create();

        $this->getJson('/api/site-statistics')->assertOk()->assertJsonIsArray('data');
        $this->getJson('/api/site-statistics')->assertOk()->assertJsonIsArray('data');
    }

    public function test_testimonials_survives_a_real_cache_round_trip(): void
    {
        Testimonial::factory()->create();

        $this->getJson('/api/testimonials')->assertOk()->assertJsonIsArray('data');
        $this->getJson('/api/testimonials')->assertOk()->assertJsonIsArray('data');
    }

    public function test_platforms_survives_a_real_cache_round_trip(): void
    {
        Platform::factory()->create();

        $this->getJson('/api/platforms')->assertOk()->assertJsonIsArray('data');
        $this->getJson('/api/platforms')->assertOk()->assertJsonIsArray('data');
    }
}
