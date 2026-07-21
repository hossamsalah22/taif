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
        Schema::create('assessment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->integer('assessment_version')->default(1);
            $table->text('strengths')->nullable();
            $table->text('improvements')->nullable();
            $table->text('recommendations')->nullable();
            $table->string('diagnosed_severity_level')->nullable();
            $table->timestamp('report_published_at')->nullable();
            $table->string('status')->default('completed'); // could be in_progress, completed
            $table->text('specialist_notes')->nullable();
            $table->string('report_document_path')->nullable();
            $table->boolean('force_re_test')->default(false);
            $table->unsignedInteger('attempt_number')->default(1);
            $table->decimal('performance_accuracy', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_submissions');
    }
};
