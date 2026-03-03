<?php

namespace Tests\Feature;

use App\Models\Colocation;
use App\Models\Invitation;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class InvitationEmailMatchTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_accept_invitation_for_another_email(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $guest = User::factory()->create([
            'email' => 'guest@example.com',
            'email_verified_at' => now(),
        ]);

        $colocation = Colocation::create([
            'name' => 'EasyColoc',
            'description' => null,
            'status' => 'active',
            'owner_id' => $owner->id,
        ]);

        Membership::create([
            'user_id' => $owner->id,
            'colocation_id' => $colocation->id,
            'status' => 'active',
        ]);

        $invitation = Invitation::create([
            'colocation_id' => $colocation->id,
            'email' => 'another@example.com',
            'token' => Str::random(32),
            'status' => 'pending',
            'invited_by' => $owner->id,
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->actingAs($guest)->get(route('invitations.accept.public', $invitation->token));

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseMissing('memberships', [
            'user_id' => $guest->id,
            'colocation_id' => $colocation->id,
            'status' => 'active',
        ]);
    }

    public function test_user_cannot_accept_invitation_when_already_has_active_colocation(): void
    {
        $ownerA = User::factory()->create(['email_verified_at' => now(), 'is_banned' => false]);
        $ownerB = User::factory()->create(['email_verified_at' => now(), 'is_banned' => false]);
        $guest = User::factory()->create([
            'email' => 'guest@example.com',
            'email_verified_at' => now(),
            'is_banned' => false,
        ]);

        $colocationA = Colocation::create([
            'name' => 'Coloc A',
            'description' => null,
            'status' => 'active',
            'owner_id' => $ownerA->id,
        ]);

        $colocationB = Colocation::create([
            'name' => 'Coloc B',
            'description' => null,
            'status' => 'active',
            'owner_id' => $ownerB->id,
        ]);

        Membership::create([
            'user_id' => $guest->id,
            'colocation_id' => $colocationA->id,
            'status' => 'active',
        ]);

        $invitation = Invitation::create([
            'colocation_id' => $colocationB->id,
            'email' => $guest->email,
            'token' => Str::random(32),
            'status' => 'pending',
            'invited_by' => $ownerB->id,
            'expires_at' => now()->addDays(7),
        ]);

        $response = $this->actingAs($guest)->get(route('invitations.accept.public', $invitation->token));

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseMissing('memberships', [
            'user_id' => $guest->id,
            'colocation_id' => $colocationB->id,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('memberships', [
            'user_id' => $guest->id,
            'colocation_id' => $colocationA->id,
            'status' => 'active',
        ]);
    }
}
