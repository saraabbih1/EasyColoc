<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NavigationAndAdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_sees_expected_navbar_links(): void
    {
        Role::findOrCreate('user', 'web');
        $user = User::factory()->create(['is_banned' => false]);
        $user->assignRole('user');

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertSee('Colocations');
        $response->assertSee('Profile');
        $response->assertSee('Logout');
        $response->assertDontSee('Admin');
    }

    public function test_global_admin_can_access_admin_and_normal_user_cannot(): void
    {
        Role::findOrCreate('global_admin', 'web');
        Role::findOrCreate('user', 'web');

        $admin = User::factory()->create(['is_banned' => false]);
        $admin->assignRole('global_admin');

        $user = User::factory()->create(['is_banned' => false]);
        $user->assignRole('user');

        $this->actingAs($admin)->get('/admin')->assertRedirect(route('admin.dashboard'));
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();

        $this->actingAs($user)->get('/admin/dashboard')->assertForbidden();
    }
}
