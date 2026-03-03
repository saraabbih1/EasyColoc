<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Colocation;
use App\Models\Expense;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\ViewErrorBag;
use Tests\TestCase;

class ExpenseAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_owner_can_see_delete_button_and_delete_expense(): void
    {
        $owner = User::factory()->create(['is_banned' => false]);
        $payer = User::factory()->create(['is_banned' => false]);

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
                'user_id' => $payer->id,
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

        $expense = Expense::create([
            'title' => 'Achat supermarche',
            'amount' => 120,
            'expense_date' => now()->toDateString(),
            'user_id' => $payer->id,
            'colocation_id' => $colocation->id,
            'category_id' => $category->id,
        ]);

        $this->actingAs($owner);
        $this->view('expenses.index', [
            'colocation' => $colocation,
            'expenses' => collect([$expense]),
            'categories' => collect([$category]),
            'filteredExpenses' => collect([$expense]),
            'monthlyStats' => collect(),
            'categoryStats' => collect(),
            'monthFilter' => null,
            'categoryFilter' => null,
            'errors' => new ViewErrorBag(),
        ])->assertSee(route('expenses.destroy', [$colocation, $expense]), false);

        $this->actingAs($owner)
            ->delete(route('expenses.destroy', [$colocation, $expense]))
            ->assertRedirect(route('expenses.index', $colocation));

        $this->assertDatabaseMissing('expenses', ['id' => $expense->id]);
    }

    public function test_non_owner_member_cannot_see_delete_button_and_cannot_delete_other_users_expense(): void
    {
        $owner = User::factory()->create(['is_banned' => false]);
        $payer = User::factory()->create(['is_banned' => false]);
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
                'user_id' => $payer->id,
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

        $expense = Expense::create([
            'title' => 'Achat supermarche',
            'amount' => 120,
            'expense_date' => now()->toDateString(),
            'user_id' => $payer->id,
            'colocation_id' => $colocation->id,
            'category_id' => $category->id,
        ]);

        $this->actingAs($member);
        $this->view('expenses.index', [
            'colocation' => $colocation,
            'expenses' => collect([$expense]),
            'categories' => collect([$category]),
            'filteredExpenses' => collect([$expense]),
            'monthlyStats' => collect(),
            'categoryStats' => collect(),
            'monthFilter' => null,
            'categoryFilter' => null,
            'errors' => new ViewErrorBag(),
        ])->assertDontSee(route('expenses.destroy', [$colocation, $expense]), false);

        $this->actingAs($member)
            ->delete(route('expenses.destroy', [$colocation, $expense]))
            ->assertForbidden();

        $this->assertDatabaseHas('expenses', ['id' => $expense->id]);
    }
}
