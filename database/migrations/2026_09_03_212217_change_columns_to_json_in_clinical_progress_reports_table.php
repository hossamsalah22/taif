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
        $reports = \Illuminate\Support\Facades\DB::table('clinical_progress_reports')->get();

        foreach ($reports as $report) {
            $update = [];
            
            // Check if title is not valid JSON
            json_decode($report->title);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $update['title'] = json_encode(['ar' => $report->title, 'en' => $report->title], JSON_UNESCAPED_UNICODE);
            }

            if ($report->body) {
                json_decode($report->body);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $update['body'] = json_encode(['ar' => $report->body, 'en' => $report->body], JSON_UNESCAPED_UNICODE);
                }
            }

            if ($report->smart_parental_advice) {
                json_decode($report->smart_parental_advice);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $update['smart_parental_advice'] = json_encode(['ar' => $report->smart_parental_advice, 'en' => $report->smart_parental_advice], JSON_UNESCAPED_UNICODE);
                }
            }

            if (!empty($update)) {
                \Illuminate\Support\Facades\DB::table('clinical_progress_reports')
                    ->where('id', $report->id)
                    ->update($update);
            }
        }

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
