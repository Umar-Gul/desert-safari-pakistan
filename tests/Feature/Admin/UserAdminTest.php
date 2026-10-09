<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\Concerns\ActsAsStaff;
use Tests\TestCase;

class UserAdminTest extends TestCase
{
    use ActsAsStaff, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_only_super_admin_can_open_staff_users(): void
    {
        $this->actingAs($this->staff('super_admin'))->get('/admin/users')->assertOk();
        $this->actingAs($this->staff('admin'))->get('/admin/users')->assertForbidden();
        $this->actingAs($this->staff('editor'))->get('/admin/users/create')->assertForbidden();
    }

    public function test_super_admin_creates_a_staff_user_with_a_role(): void
    {
        Role::findOrCreate('editor', 'web');

        $this->actingAs($this->staff('super_admin'))->post('/admin/users', [
            'name' => 'New Editor',
            'email' => 'editor@example.com',
            'role' => 'editor',
            'password' => 'Str0ng!Pass#2026',
            'password_confirmation' => 'Str0ng!Pass#2026',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'editor@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('editor'));
        $this->assertNotSame('Str0ng!Pass#2026', $user->password);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_password_is_optional_when_editing(): void
    {
        Role::findOrCreate('support', 'web');
        $target = $this->staff('editor');
        $oldHash = $target->password;

        $this->actingAs($this->staff('super_admin'))->put("/admin/users/{$target->id}", [
            'name' => 'Renamed',
            'email' => $target->email,
            'role' => 'support',
        ])->assertSessionHasNoErrors();

        $target->refresh();

        $this->assertSame('Renamed', $target->name);
        $this->assertTrue($target->hasRole('support'));
        $this->assertSame($oldHash, $target->password);
    }

    public function test_a_super_admin_cannot_delete_or_demote_themselves(): void
    {
        Role::findOrCreate('admin', 'web');
        $me = $this->staff('super_admin');

        $this->actingAs($me)->delete("/admin/users/{$me->id}")->assertSessionHas('error');
        $this->assertNotNull(User::find($me->id));

        $this->actingAs($me)->put("/admin/users/{$me->id}", [
            'name' => $me->name,
            'email' => $me->email,
            'role' => 'admin',
        ])->assertSessionHas('error');

        $this->assertTrue($me->fresh()->hasRole('super_admin'));
    }

    public function test_another_staff_user_can_be_deleted(): void
    {
        $other = $this->staff('support');

        $this->actingAs($this->staff('super_admin'))->delete("/admin/users/{$other->id}")->assertRedirect(route('admin.users.index'));

        $this->assertNull(User::find($other->id));
    }
}