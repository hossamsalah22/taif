<?php

use App\Http\Controllers\AssessmentReportController;
use App\Http\Controllers\Global\AutismLevelController;
use App\Http\Controllers\Global\GenderController;
use App\Http\Controllers\Global\SpeechStatusesController;
use App\Http\Controllers\User\AssessmentController;
use App\Http\Controllers\User\Auth\LoginController;
use App\Http\Controllers\User\Auth\OtpController;
use App\Http\Controllers\User\Auth\RegisterController;
use App\Http\Controllers\User\ChildController;
use Illuminate\Support\Facades\Route;

Route::prefix('user')->group(function () {
    Route::post('register', RegisterController::class);
    Route::post('confirm-otp', [OtpController::class, 'confirmOtp']);
    Route::post('send-otp', [OtpController::class, 'sendOtp']);
    Route::post('login', LoginController::class);

    Route::middleware('auth:user')->group(function () {
        Route::apiResource('children', ChildController::class);

        Route::prefix('children/{child}/assessments')->group(function () {
            Route::get('registration', [AssessmentController::class, 'registrationTest']);
            Route::post('submit', [AssessmentController::class, 'submitTest']);
            Route::get('{submission}/report', [AssessmentReportController::class, 'show']);
            Route::get('{submission}/report/download', [AssessmentReportController::class, 'download'])->name('api.reports.download');
        });

    });
});

Route::prefix('global')->group(function () {
    Route::get('autism-levels', AutismLevelController::class);
    Route::get('genders', GenderController::class);
    Route::get('speech-statuses', SpeechStatusesController::class);
});
