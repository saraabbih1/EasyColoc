<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table): void {
            if (!Schema::hasColumn('payments', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('amount');
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('payments', 'paid_at')) {
            Schema::table('payments', function (Blueprint $table): void {
                $table->dropColumn('paid_at');
            });
        }
    }
};
