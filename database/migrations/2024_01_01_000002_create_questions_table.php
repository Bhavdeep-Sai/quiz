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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            
            // Question type: boolean, single_choice, multiple_choice, number, text
            $table->enum('type', ['boolean', 'single_choice', 'multiple_choice', 'number', 'text']);
            
            // Question content
            $table->longText('question_text'); // HTML support
            $table->string('image_url')->nullable(); // Question image
            $table->string('video_url')->nullable(); // YouTube video URL
            
            // Scoring
            $table->integer('marks')->default(1);
            
            // Type-specific settings stored as JSON
            // For flexibility without schema changes
            $table->json('settings')->nullable();
            
            // Ordering within quiz
            $table->integer('sort_order')->default(0);
            
            // Soft delete for potential recovery
            $table->softDeletes();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['quiz_id', 'sort_order']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
