<?php

namespace Tests\Feature\Api;

use App\Models\Platform;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PlatformControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget('platforms.active');
    }

    public function test_index_returns_only_active_platforms_ordered(): void
    {
        Platform::factory()->create(['name' => 'Seconde', 'display_order' => 20]);
        Platform::factory()->create(['name' => 'Première', 'display_order' => 10]);
        Platform::factory()->inactive()->create(['name' => 'Masquée', 'display_order' => 5]);

        $response = $this->getJson('/api/platforms');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Première')
            ->assertJsonPath('data.1.name', 'Seconde');
    }

    public function test_index_can_filter_by_category(): void
    {
        Platform::factory()->create(['name' => 'App Mobile', 'category' => 'mobile']);
        Platform::factory()->create(['name' => 'LMS', 'category' => 'e-learning']);

        $response = $this->getJson('/api/platforms?category=mobile');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'App Mobile');
    }

    public function test_show_returns_platform_with_description(): void
    {
        $platform = Platform::factory()->create([
            'slug' => 'ma-plateforme',
            'description' => '<p>Description complète</p>',
        ]);

        $response = $this->getJson("/api/platforms/{$platform->slug}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.slug', 'ma-plateforme')
            ->assertJsonPath('data.description', '<p>Description complète</p>');
    }

    public function test_show_returns_404_for_unknown_slug(): void
    {
        $response = $this->getJson('/api/platforms/inconnue');

        $response->assertStatus(404)->assertJsonPath('success', false);
    }

    public function test_show_returns_404_for_inactive_platform(): void
    {
        $platform = Platform::factory()->inactive()->create(['slug' => 'masquee']);

        $response = $this->getJson("/api/platforms/{$platform->slug}");

        $response->assertStatus(404);
    }
}
