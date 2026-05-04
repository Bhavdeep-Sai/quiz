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
        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            
            // User identifier (can be UUID, email, or custom identifier)
            $table->string('user_identifier')->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            
            // Timestamps
            $table->timestamp('started_at');
            $table->timestamp('submitted_at')->nullable();
            
            // Timer feature (bonus)
            $table->integer('time_spent_seconds')->default(0);
            
            // Scoring
            $table->decimal('total_score', 8, 2)->default(0);
            $table->integer('total_marks')->default(0);
            $table->boolean('is_passed')->default(false);
            
            // Status
            $table->enum('status', ['in_progress', 'submitted', 'evaluated'])->default('in_progress');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['quiz_id', 'created_at']);
            $table->index('status');
            $table->index('user_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};
