<?php

namespace Tests\Feature\Api;

use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class TestimonialControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::forget('testimonials.active');
    }

    public function test_returns_only_active_testimonials_ordered(): void
    {
        Testimonial::factory()->create(['author_name' => 'Second', 'display_order' => 20]);
        Testimonial::factory()->create(['author_name' => 'Premier', 'display_order' => 10]);
        Testimonial::factory()->inactive()->create(['author_name' => 'Masqué', 'display_order' => 5]);

        $response = $this->getJson('/api/testimonials');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.author_name', 'Premier')
            ->assertJsonPath('data.1.author_name', 'Second');
    }

    public function test_photo_is_null_when_not_set(): void
    {
        Testimonial::factory()->create(['photo_path' => null]);

        $response = $this->getJson('/api/testimonials');

        $response->assertOk()->assertJsonPath('data.0.photo', null);
    }
}
