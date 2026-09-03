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
        Schema::table('clinical_progress_reports', function (Blueprint $table) {
            $table->json('title')->change();
            $table->json('body')->nullable()->change();
            $table->json('smart_parental_advice')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clinical_progress_reports', function (Blueprint $table) {
            $table->text('title')->change();
            $table->text('body')->nullable()->change();
            $table->text('smart_parental_advice')->nullable()->change();
        });
    }
};
