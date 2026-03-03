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
        if (!Schema::hasTable('memberships')) {
            return;
        }

        Schema::table('memberships', function (Blueprint $table) {
            if (!Schema::hasColumn('memberships', 'status')) {
                $table->string('status')->default('active');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('memberships')) {
            return;
        }

        Schema::table('memberships', function (Blueprint $table) {
            if (Schema::hasColumn('memberships', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
