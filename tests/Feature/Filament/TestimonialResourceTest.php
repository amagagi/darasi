<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Testimonials\Pages\CreateTestimonial;
use App\Filament\Resources\Testimonials\Pages\EditTestimonial;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TestimonialResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_testimonial(): void
    {
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(CreateTestimonial::class)
            ->set('data.author_name', 'Aïcha Moussa')
            ->set('data.content', 'Un très bon parcours.')
            ->set('data.rating', 5)
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('testimonials', [
            'author_name' => 'Aïcha Moussa',
            'rating' => 5,
        ]);
    }

    public function test_admin_can_edit_a_testimonial(): void
    {
        $testimonial = Testimonial::factory()->create(['is_active' => true]);
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(EditTestimonial::class, ['record' => $testimonial->getRouteKey()])
            ->set('data.is_active', false)
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('testimonials', [
            'id' => $testimonial->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_a_testimonial(): void
    {
        $testimonial = Testimonial::factory()->create();
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)
            ->test(EditTestimonial::class, ['record' => $testimonial->getRouteKey()])
            ->callAction('delete');

        $this->assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
    }
}
