<?php

namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Colocation;
use App\Models\Membership;
use App\Models\Category;
use App\Models\Expense;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
  public function run(): void
    {
       // Truncate tables first
Expense::truncate();
Category::truncate();
Membership::truncate();
Colocation::truncate();
User::truncate();

// Create Admin User
$admin = User::create([
    'name' => 'Admin',
    'email' => 'admin@easycoloc.com',
    'password' => bcrypt('12345678'),
    'role' => 'admin',
    'reputation' => 0,
    'is_banned' => 0,
]);

// Create Colocation
$coloc = Colocation::create([
    'name' => 'Coloc Test',
    'owner_id' => $admin->id,
]);

// Create Membership
Membership::create([
    'user_id' => $admin->id,
    'colocation_id' => $coloc->id,
    'status' => 'active',
]);

// Create Category
$category = Category::create([
    'name' => 'Courses',
]);

// Create Expense
Expense::create([
    'title' => 'Supermarché',
    'amount' => 100.50,
    'expense_date' => now(),
    'user_id' => $admin->id,
    'colocation_id' => $coloc->id,
    'category_id' => $category->id,
]);}}