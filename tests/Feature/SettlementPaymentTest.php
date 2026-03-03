<?php

namespace Tests\Feature;

use App\Models\Colocation;
use App\Models\Membership;
use App\Models\Settlement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettlementPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_mark_as_paid_updates_settlement_and_records_payment(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $debtor = User::factory()->create(['email_verified_at' => now()]);
        $creditor = User::factory()->create(['email_verified_at' => now()]);

        $colocation = Colocation::create([
            'name' => 'Maison',
            'description' => null,
            'status' => 'active',
            'owner_id' => $owner->id,
        ]);

        Membership::insert([
            [
                'user_id' => $owner->id,
                'colocation_id' => $colocation->id,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $debtor->id,
                'colocation_id' => $colocation->id,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $creditor->id,
                'colocation_id' => $colocation->id,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $settlement = Settlement::create([
            'colocation_id' => $colocation->id,
            'debtor_id' => $debtor->id,
            'creditor_id' => $creditor->id,
            'amount' => 100,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($debtor)->post(route('settlements.mark-as-paid', [$colocation, $settlement]));

        $response->assertRedirect();
        $this->assertDatabaseHas('settlements', [
            'id' => $settlement->id,
            'status' => 'paid',
        ]);
        $this->assertDatabaseHas('payments', [
            'from_user_id' => $debtor->id,
            'to_user_id' => $creditor->id,
            'colocation_id' => $colocation->id,
            'amount' => 100,
        ]);
        $this->assertDatabaseMissing('settlements', [
            'id' => $settlement->id,
            'status' => 'pending',
        ]);
    }
}
