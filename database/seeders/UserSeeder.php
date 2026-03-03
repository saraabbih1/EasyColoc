<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user (first user becomes admin)
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@easycoloc.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'reputation' => 0,
            'is_banned' => false,
        ]);

        // Create regular users
        $users = [
            [
                'name' => 'Alice Martin',
                'email' => 'alice@easycoloc.test',
                'password' => Hash::make('password'),
                'role' => 'user',
                'reputation' => 5,
                'is_banned' => false,
            ],
            [
                'name' => 'Bob Dupont',
                'email' => 'bob@easycoloc.test',
                'password' => Hash::make('password'),
                'role' => 'user',
                'reputation' => 3,
                'is_banned' => false,
            ],
            [
                'name' => 'Carla Petit',
                'email' => 'carla@easycoloc.test',
                'password' => Hash::make('password'),
                'role' => 'user',
                'reputation' => 2,
                'is_banned' => false,
            ],
            [
                'name' => 'David Leroy',
                'email' => 'david@easycoloc.test',
                'password' => Hash::make('password'),
                'role' => 'user',
                'reputation' => 0,
                'is_banned' => false,
            ],
            [
                'name' => 'Eva Bernard',
                'email' => 'eva@easycoloc.test',
                'password' => Hash::make('password'),
                'role' => 'user',
                'reputation' => -1,
                'is_banned' => false,
            ],
            [
                'name' => 'Frank Moreau',
                'email' => 'frank@easycoloc.test',
                'password' => Hash::make('password'),
                'role' => 'user',
                'reputation' => 1,
                'is_banned' => false,
            ],
            [
                'name' => 'Banned User',
                'email' => 'banned@easycoloc.test',
                'password' => Hash::make('password'),
                'role' => 'user',
                'reputation' => -5,
                'is_banned' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        // Create additional random users
        User::factory()->count(20)->create();
    }
}
