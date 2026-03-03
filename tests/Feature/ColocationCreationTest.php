<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ColocationCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_colocation_with_name_only(): void
    {
        $user = User::factory()->create([
            'is_banned' => false,
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->post(route('colocations.store'), [
            'name' => 'Coloc Sans Description',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('colocations', [
            'name' => 'Coloc Sans Description',
            'owner_id' => $user->id,
            'status' => 'active',
            'description' => null,
        ]);
    }

    public function test_authenticated_user_can_create_colocation_with_description(): void
    {
        $user = User::factory()->create([
            'is_banned' => false,
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->post(route('colocations.store'), [
            'name' => 'Coloc Avec Description',
            'description' => 'Appartement centre ville, charges partagees.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('colocations', [
            'name' => 'Coloc Avec Description',
            'owner_id' => $user->id,
            'status' => 'active',
            'description' => 'Appartement centre ville, charges partagees.',
        ]);
    }
}
