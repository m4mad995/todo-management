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
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamp('completed_at')->nullable();
            $table->enum('matrix', ['do_first', 'schedule', 'delegate', 'drop'])->nullable();
            $table->string('title');
            
            // Status GTD & Eisenhower
            $table->enum('status', ['INBOX', 'DO', 'SCHEDULE', 'DELEGATE', 'DROP'])->default('INBOX');
            $table->integer('urgency_score')->nullable(); // 1 - 5
            $table->integer('impact_score')->nullable();  // 1 - 5
            
            // Habit Tracking
            $table->boolean('is_completed')->default(false);
            $table->boolean('is_habit')->default(false);
            $table->integer('streak_count')->default(0);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
