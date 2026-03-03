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
        Schema::create('memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('colocation_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['active', 'left', 'cancelled'])->default('active');
            $table->timestamp('left_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'colocation_id']);
            $table->index(['user_id', 'status']);
            $table->index(['colocation_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('memberships');
    }
};
