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
        Schema::create('quiz_audit_logs', function (Blueprint $table) {
            $table->id();
            
            // Track what changed
            $table->string('action'); // created, updated, deleted
            $table->string('model_type'); // Quiz, Question, etc.
            $table->unsignedBigInteger('model_id');
            
            // User/system info
            $table->string('user_identifier')->nullable();
            
            // Change data
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['model_type', 'model_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_audit_logs');
    }
};
