<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_access_dashboard_after_registration(): void
    {
        $this->post('/register', [
            'name' => 'First Admin',
            'email' => 'first-admin@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);
        $this->post('/logout');

        $response = $this->post('/register', [
            'name' => 'Demo User',
            'email' => 'demo@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        $this->get('/dashboard')->assertOk();
    }

    public function test_user_can_access_dashboard_after_login(): void
    {
        $user = User::factory()->create([
            'is_banned' => false,
            'role' => 'user',
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->get('/dashboard')->assertOk();
    }
}
