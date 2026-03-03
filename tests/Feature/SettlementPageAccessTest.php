<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Colocation;
use App\Models\Expense;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettlementPageAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_member_can_open_settlement_page(): void
    {
        $owner = User::factory()->create(['is_banned' => false]);
        $member = User::factory()->create(['is_banned' => false]);

        $colocation = Colocation::create([
            'name' => 'Coloc Test',
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
                'user_id' => $member->id,
                'colocation_id' => $colocation->id,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $category = Category::create([
            'name' => 'Courses',
            'color' => '#6366f1',
            'colocation_id' => $colocation->id,
        ]);

        Expense::create([
            'title' => 'Achat',
            'amount' => 100,
            'expense_date' => now()->toDateString(),
            'colocation_id' => $colocation->id,
            'user_id' => $owner->id,
            'category_id' => $category->id,
        ]);

        $this->actingAs($member)
            ->get(route('colocations.settlement.show', $colocation))
            ->assertOk();
    }
}
