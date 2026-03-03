<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FirstUserIsAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_registered_user_gets_admin_role(): void
    {
        $this->post('/register', [
            'name' => 'First User',
            'email' => 'first@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $first = User::where('email', 'first@example.com')->firstOrFail();
        $this->assertSame('admin', $first->role);
        $this->assertTrue($first->hasRole('global_admin'));
    }

    public function test_second_registered_user_gets_user_role(): void
    {
        User::factory()->create(['role' => 'admin']);

        $this->post('/register', [
            'name' => 'Second User',
            'email' => 'second@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $second = User::where('email', 'second@example.com')->firstOrFail();
        $this->assertSame('user', $second->role);
        $this->assertTrue($second->hasRole('user'));
    }
}
