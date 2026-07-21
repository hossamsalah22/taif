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
        Schema::table('assessment_submissions', function (Blueprint $table) {
            $table->integer('assessment_version')->default(1)->after('assessment_id');
            $table->text('strengths')->nullable()->after('status');
            $table->text('improvements')->nullable()->after('strengths');
            $table->text('recommendations')->nullable()->after('improvements');
            $table->string('diagnosed_severity_level')->nullable()->after('recommendations');
            $table->timestamp('report_published_at')->nullable()->after('diagnosed_severity_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_submissions', function (Blueprint $table) {
            $table->dropColumn([
                'assessment_version',
                'strengths',
                'improvements',
                'recommendations',
                'diagnosed_severity_level',
                'report_published_at'
            ]);
        });
    }
};
