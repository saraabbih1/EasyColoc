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
            if (!Schema::hasColumn('invitations', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('status');
            }
        });

        Schema::table('invitations', function (Blueprint $table) {
            $table->index(
                ['colocation_id', 'email', 'status', 'expires_at'],
                'invitations_coloc_email_status_expires_idx'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('invitations')) {
            return;
        }

        Schema::table('invitations', function (Blueprint $table) {
            $table->dropIndex('invitations_coloc_email_status_expires_idx');
        });

        Schema::table('invitations', function (Blueprint $table) {
            if (Schema::hasColumn('invitations', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
        });
    }
};
