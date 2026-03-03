<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('invitations')) {
            return;
        }

        Schema::table('invitations', function (Blueprint $table) {
            if (!Schema::hasColumn('invitations', 'invited_by')) {
                $table->foreignId('invited_by')
                    ->nullable()
                    ->after('colocation_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }
        });

        Schema::table('invitations', function (Blueprint $table) {
            $table->index('invited_by', 'invitations_invited_by_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('invitations') || !Schema::hasColumn('invitations', 'invited_by')) {
            return;
        }

        Schema::table('invitations', function (Blueprint $table) {
            $table->dropForeign(['invited_by']);
            $table->dropIndex('invitations_invited_by_idx');
            $table->dropColumn('invited_by');
        });
    }
};
