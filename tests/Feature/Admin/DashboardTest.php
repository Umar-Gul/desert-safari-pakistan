<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role, 'web');

        return User::factory()->create()->assignRole($role);
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_non_staff_users_are_forbidden(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();
    }

    public function test_admin_sees_booking_cards_and_upcoming_tours(): void
    {
        $booking = Booking::factory()->confirmed()->create([
            'tour_date' => now()->addDays(2)->toDateString(),
        ]);

        $this->actingAs($this->userWithRole('admin'))
            ->get('/admin')
            ->assertOk()
            ->assertSee('Bookings this month')
            ->assertSee('Revenue this month')
            ->assertSee($booking->reference);
    }

    public function test_editor_does_not_see_booking_or_revenue_numbers(): void
    {
        $this->actingAs($this->userWithRole('editor'))
            ->get('/admin')
            ->assertOk()
            ->assertDontSee('Bookings this month')
            ->assertDontSee('Revenue this month');
    }

    public function test_staff_login_redirect_lands_on_the_admin_dashboard(): void
    {
        $this->actingAs($this->userWithRole('admin'))
            ->get('/dashboard')
            ->assertRedirect(route('admin.dashboard'));
    }
}