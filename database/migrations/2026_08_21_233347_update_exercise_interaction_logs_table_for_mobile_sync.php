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
        Schema::table('exercise_interaction_logs', function (Blueprint $table) {
            $table->dropColumn(['status', 'attempts', 'score']);
            $table->boolean('is_successful')->default(false)->after('learning_exercise_id');
            $table->integer('duration_seconds')->default(0)->after('is_successful');
            $table->integer('trials_count')->default(1)->after('duration_seconds');
            $table->string('interaction_type')->nullable()->after('trials_count');
            $table->json('metadata')->nullable()->after('interaction_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exercise_interaction_logs', function (Blueprint $table) {
            $table->dropColumn(['is_successful', 'duration_seconds', 'trials_count', 'interaction_type', 'metadata']);
            $table->string('status')->default('in_progress');
            $table->integer('attempts')->default(0);
            $table->integer('score')->nullable();
        });
    }
};
