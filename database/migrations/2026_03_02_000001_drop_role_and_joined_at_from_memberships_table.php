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
        Schema::table('memberships', function (Blueprint $table) {
            if (Schema::hasColumn('memberships', 'role')) {
                $table->dropColumn('role');
            }

            if (Schema::hasColumn('memberships', 'joined_at')) {
                $table->dropColumn('joined_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('memberships', function (Blueprint $table) {
            if (!Schema::hasColumn('memberships', 'role')) {
                $table->string('role')->default('member');
            }

            if (!Schema::hasColumn('memberships', 'joined_at')) {
                $table->timestamp('joined_at')->nullable();
            }
        });
    }
};
