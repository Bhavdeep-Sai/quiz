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
        Schema::create('result_breakdowns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('attempts')->cascadeOnDelete();
            
            // Category/Section breakdown
            $table->string('category')->nullable();
            
            // Aggregate metrics
            $table->integer('total_questions')->default(0);
            $table->integer('correct_answers')->default(0);
            $table->decimal('percentage', 5, 2)->default(0);
            $table->decimal('total_score', 8, 2)->default(0);
            
            // Performance metrics
            $table->integer('avg_time_per_question')->default(0);
            
            // Results
            $table->string('performance_level')->nullable(); // excellent, good, average, poor
            
            $table->timestamps();
            
            // Indexes
            $table->index(['attempt_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('result_breakdowns');
    }
};
