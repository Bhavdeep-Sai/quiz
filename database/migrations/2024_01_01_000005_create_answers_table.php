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
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            
            // Store answer in flexible JSON format
            // Supports: single value, array of values, numeric, text, etc.
            $table->json('user_answer')->nullable();
            
            // Question type at time of answering (audit trail)
            $table->enum('question_type', ['boolean', 'single_choice', 'multiple_choice', 'number', 'text']);
            
            // Scoring
            $table->decimal('score', 8, 2)->default(0);
            $table->boolean('is_correct')->default(false);
            
            // Feedback (optional)
            $table->text('feedback')->nullable();
            
            // Answer metadata
            $table->timestamp('answered_at')->nullable();
            $table->integer('time_spent_seconds')->default(0);
            
            $table->timestamps();
            
            // Indexes
            $table->index(['attempt_id', 'question_id']);
            $table->index(['attempt_id', 'is_correct']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
