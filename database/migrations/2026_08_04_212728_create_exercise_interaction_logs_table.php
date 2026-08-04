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
        Schema::create('exercise_interaction_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->foreignId('learning_exercise_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('in_progress'); // in_progress, completed
            $table->integer('attempts')->default(0);
            $table->integer('score')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exercise_interaction_logs');
    }
};
