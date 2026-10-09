<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Setting::flushCache();
    }

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role, 'web');

        return User::factory()->create()->assignRole($role);
    }

    public function test_admin_can_view_and_update_general_settings(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->get('/admin/settings')->assertOk()->assertSee('Site name');

        $this->actingAs($admin)
            ->put('/admin/settings/general', ['site_name' => 'Thar Desert Safari', 'tagline' => 'Sand and stars'])
            ->assertRedirect(route('admin.settings.edit', 'general'))
            ->assertSessionHas('success');

        $this->assertSame('Thar Desert Safari', Setting::get('site_name'));
        $this->assertSame('Sand and stars', Setting::get('tagline'));
    }

    public function test_fields_missing_from_the_request_are_left_untouched(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->put('/admin/settings/general', ['site_name' => 'First', 'tagline' => 'Keep me']);
        $this->actingAs($admin)->put('/admin/settings/general', ['site_name' => 'Second']);

        $this->assertSame('Second', Setting::get('site_name'));
        $this->assertSame('Keep me', Setting::get('tagline'));
    }

    public function test_required_and_format_rules_are_enforced(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->put('/admin/settings/general', ['site_name' => ''])
            ->assertSessionHasErrors('site_name');

        $this->actingAs($admin)->put('/admin/settings/contact', ['email' => 'not-an-email', 'phone' => 'abc'])
            ->assertSessionHasErrors(['email', 'phone']);
    }

    public function test_home_intro_html_is_cleaned_on_save(): void
    {
        $admin = $this->userWithRole('admin');

        $this->actingAs($admin)->put('/admin/settings/home', [
            'home_intro_html' => '<p>Welcome</p><script>alert(1)</script>',
        ]);

        $saved = (string) Setting::get('home_intro_html');

        $this->assertStringContainsString('Welcome', $saved);
        $this->assertStringNotContainsString('<script', $saved);
    }

    public function test_editor_cannot_open_settings(): void
    {
        $this->actingAs($this->userWithRole('editor'))->get('/admin/settings')->assertForbidden();
    }

    public function test_unknown_group_returns_404(): void
    {
        $this->actingAs($this->userWithRole('admin'))->get('/admin/settings/nope')->assertNotFound();
    }
}