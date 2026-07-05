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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // nullable only for google and apple login
            $table->string('phone')->nullable()->unique(); // nullable only for google and apple login
            $table->string('country_code', 2)->default('SA');
            $table->string('email')->nullable()->unique(); // nullable only for google and apple login
            $table->date('date_of_birth')->nullable(); // nullable only for google and apple login
            $table->string('gender')->nullable(); // nullable only for google and apple login
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->boolean('receive_notifications')->default(true);
            $table->string('otp')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->char('locale', 5)->default('ar');
            $table->string('google_id')->nullable();
            $table->string('apple_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
