<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('invitations')) {
            return;
        }

        Schema::table('invitations', function (Blueprint $table) {
            if (!Schema::hasColumn('invitations', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('status');
            }
        });

        if (DB::getDriverName() === 'mysql' && Schema::hasColumn('invitations', 'status')) {
            DB::statement("ALTER TABLE invitations MODIFY status ENUM('pending','accepted','refused','cancelled') NOT NULL DEFAULT 'pending'");
        }

        if (Schema::hasColumn('invitations', 'invited_by')) {
            if (DB::getDriverName() === 'mysql') {
                try {
                    DB::statement('ALTER TABLE invitations DROP FOREIGN KEY invitations_invited_by_foreign');
                } catch (\Throwable $e) {
                    // no-op
                }

                DB::statement('ALTER TABLE invitations MODIFY invited_by BIGINT UNSIGNED NULL');
                DB::statement('ALTER TABLE invitations ADD CONSTRAINT invitations_invited_by_foreign FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE SET NULL');
            }
        } else {
            Schema::table('invitations', function (Blueprint $table) {
                $table->foreignId('invited_by')
                    ->nullable()
                    ->after('colocation_id')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        Schema::table('invitations', function (Blueprint $table) {
            if (!Schema::hasIndex('invitations', 'invitations_coloc_email_status_idx')) {
                $table->index(['colocation_id', 'email', 'status'], 'invitations_coloc_email_status_idx');
            }
            if (!Schema::hasIndex('invitations', 'invitations_invited_by_idx')) {
                $table->index('invited_by', 'invitations_invited_by_idx');
            }
            if (!Schema::hasIndex('invitations', 'invitations_expires_at_idx')) {
                $table->index('expires_at', 'invitations_expires_at_idx');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('invitations')) {
            return;
        }

        Schema::table('invitations', function (Blueprint $table) {
            try {
                $table->dropIndex('invitations_coloc_email_status_idx');
            } catch (\Throwable $e) {
                // no-op
            }
            try {
                $table->dropIndex('invitations_invited_by_idx');
            } catch (\Throwable $e) {
                // no-op
            }
            try {
                $table->dropIndex('invitations_expires_at_idx');
            } catch (\Throwable $e) {
                // no-op
            }
        });
    }
};
