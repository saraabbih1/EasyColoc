<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('model_has_roles')) {
            return;
        }

        $adminRole = Role::findOrCreate('global_admin', 'web');
        $userRole = Role::findOrCreate('user', 'web');

        User::query()->chunkById(100, function ($users) use ($adminRole, $userRole) {
            foreach ($users as $user) {
                $legacyRole = strtolower((string) ($user->role ?? 'user'));
                if ($legacyRole === 'admin' || $legacyRole === 'global_admin') {
                    $user->syncRoles([$adminRole]);
                } else {
                    $user->syncRoles([$userRole]);
                }
            }
        });
    }

    public function down(): void
    {
        // Keep role assignments in place on rollback.
    }
};
