<?php

namespace Database\Seeders;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
{
    // Create roles
    Role::create(['name' => 'global-admin']);
    Role::create(['name' => 'owner']);
    Role::create(['name' => 'member']);

    $admin = User::factory()->create([
        'name' => 'Admin',
        'email' => 'admin@easycoloc.com',
    ]);

    $admin->assignRole('global-admin');
}}