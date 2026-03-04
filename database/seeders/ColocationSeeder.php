<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Colocation;
use App\Models\Membership;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Invitation;
use Carbon\Carbon;

class ColocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get users for seeding
        $alice = \App\Models\User::where('email', 'alice@easycoloc.test')->first();
        $bob = \App\Models\User::where('email', 'bob@easycoloc.test')->first();
        $carla = \App\Models\User::where('email', 'carla@easycoloc.test')->first();
        $david = \App\Models\User::where('email', 'david@easycoloc.test')->first();
        $eva = \App\Models\User::where('email', 'eva@easycoloc.test')->first();
        $frank = \App\Models\User::where('email', 'frank@easycoloc.test')->first();

        // Create first colocation (Alice as owner)
        $colocation1 = Colocation::create([
            'name' => 'Appartement Paris 15',
            'description' => 'Super appartement de 3 pièces près du métro',
            'status' => 'active',
        ]);

        // Add Alice as owner
        Membership::create([
            'user_id' => $alice->id,
            'colocation_id' => $colocation1->id,
            'role' => 'owner',
            'status' => 'active',
            'joined_at' => Carbon::now()->subMonths(6),
        ]);

        // Add Bob and Carla as members
        Membership::create([
            'user_id' => $bob->id,
            'colocation_id' => $colocation1->id,
            'role' => 'member',
            'status' => 'active',
            'joined_at' => Carbon::now()->subMonths(5),
        ]);

        Membership::create([
            'user_id' => $carla->id,
            'colocation_id' => $colocation1->id,
            'role' => 'member',
            'status' => 'active',
            'joined_at' => Carbon::now()->subMonths(4),
        ]);

        // Create categories for first colocation
        $categories1 = [
            ['name' => 'Courses alimentaires'],
            ['name' => 'Loyer et charges'],
            ['name' => 'Ã‰lectricitÃ©'],
            ['name' => 'Internet'],
            ['name' => 'Sorties et restaurants'],
            ['name' => 'Produits mÃ©nagers'],
        ];

        foreach ($categories1 as $categoryData) {
            Category::create([
                'name' => $categoryData['name'],
                'colocation_id' => $colocation1->id,
            ]);
        }

        // Create expenses for first colocation
        $coursesCategory = Category::where('colocation_id', $colocation1->id)->where('name', 'Courses alimentaires')->first();
        $loyerCategory = Category::where('colocation_id', $colocation1->id)->where('name', 'Loyer et charges')->first();
        $electricityCategory = Category::where('colocation_id', $colocation1->id)->where('name', 'Électricité')->first();
        $internetCategory = Category::where('colocation_id', $colocation1->id)->where('name', 'Internet')->first();
        $sortiesCategory = Category::where('colocation_id', $colocation1->id)->where('name', 'Sorties et restaurants')->first();

        // Monthly rent (Alice pays, split among 3)
        for ($i = 1; $i <= 6; $i++) {
            Expense::create([
                'title' => 'Loyer juillet ' . (2024 + $i - 1),
                'amount' => 1800.00,
                'expense_date' => Carbon::now()->subMonths(6 - $i + 1)->startOfMonth(),
                'colocation_id' => $colocation1->id,
                'user_id' => $alice->id,
                'category_id' => $loyerCategory->id,
            ]);
        }

        // Various expenses
        Expense::create([
            'title' => 'Courses Supermarché',
            'amount' => 145.50,
            'expense_date' => Carbon::now()->subDays(5),
            'colocation_id' => $colocation1->id,
            'user_id' => $bob->id,
            'category_id' => $coursesCategory->id,
        ]);

        Expense::create([
            'title' => 'Facture Électricité',
            'amount' => 89.00,
            'expense_date' => Carbon::now()->subDays(10),
            'colocation_id' => $colocation1->id,
            'user_id' => $alice->id,
            'category_id' => $electricityCategory->id,
        ]);

        Expense::create([
            'title' => 'Abonnement Internet',
            'amount' => 29.99,
            'expense_date' => Carbon::now()->subDays(15),
            'colocation_id' => $colocation1->id,
            'user_id' => $carla->id,
            'category_id' => $internetCategory->id,
        ]);

        Expense::create([
            'title' => 'Restaurant Pizza',
            'amount' => 67.50,
            'expense_date' => Carbon::now()->subDays(20),
            'colocation_id' => $colocation1->id,
            'user_id' => $carla->id,
            'category_id' => $sortiesCategory->id,
        ]);

        // Create pending invitation for David
        Invitation::create([
            'email' => $david->email,
            'token' => 'test-invitation-token-1',
            'colocation_id' => $colocation1->id,
            'invited_by' => $alice->id,
            'status' => 'pending',
            'expires_at' => Carbon::now()->addDays(5),
        ]);

        // Create second colocation (David as owner)
        $colocation2 = Colocation::create([
            'name' => 'Colocation Lyon',
            'description' => 'Maison avec jardin en périphérie de Lyon',
            'status' => 'active',
        ]);

        Membership::create([
            'user_id' => $david->id,
            'colocation_id' => $colocation2->id,
            'role' => 'owner',
            'status' => 'active',
            'joined_at' => Carbon::now()->subMonths(2),
        ]);

        Membership::create([
            'user_id' => $eva->id,
            'colocation_id' => $colocation2->id,
            'role' => 'member',
            'status' => 'active',
            'joined_at' => Carbon::now()->subMonths(1),
        ]);

        // Create categories for second colocation
        $categories2 = [
            ['name' => 'Nourriture'],
            ['name' => 'Factures'],
            ['name' => 'Jardin'],
        ];

        foreach ($categories2 as $categoryData) {
            Category::create([
                'name' => $categoryData['name'],
                'colocation_id' => $colocation2->id,
            ]);
        }

        // Create some expenses for second colocation
        $nourritureCategory = Category::where('colocation_id', $colocation2->id)->where('name', 'Nourriture')->first();
        $facturesCategory = Category::where('colocation_id', $colocation2->id)->where('name', 'Factures')->first();

        Expense::create([
            'title' => 'Courses hebdomadaires',
            'amount' => 89.90,
            'expense_date' => Carbon::now()->subDays(3),
            'colocation_id' => $colocation2->id,
            'user_id' => $david->id,
            'category_id' => $nourritureCategory->id,
        ]);

        Expense::create([
            'title' => 'Eau et gaz',
            'amount' => 120.00,
            'expense_date' => Carbon::now()->subDays(12),
            'colocation_id' => $colocation2->id,
            'user_id' => $eva->id,
            'category_id' => $facturesCategory->id,
        ]);

        // Create cancelled colocation (Frank)
        $colocation3 = Colocation::create([
            'name' => 'Ancienne colocation Marseille',
            'description' => 'Studio en centre-ville',
            'status' => 'cancelled',
        ]);

        Membership::create([
            'user_id' => $frank->id,
            'colocation_id' => $colocation3->id,
            'role' => 'owner',
            'status' => 'cancelled',
            'joined_at' => Carbon::now()->subMonths(8),
            'left_at' => Carbon::now()->subMonths(2),
        ]);
    }
}
