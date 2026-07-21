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
        Schema::table('assessments', function (Blueprint $table) {
            $table->integer('max_attempts')->default(1)->after('title');
        });

        Schema::table('children', function (Blueprint $table) {
            $table->boolean('override_assessment_lock')->default(false)->after('educational_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropColumn('max_attempts');
        });

        Schema::table('children', function (Blueprint $table) {
            $table->dropColumn('override_assessment_lock');
        });
    }
};
