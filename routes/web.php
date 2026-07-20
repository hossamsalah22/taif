<?php

use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\WebPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin/login');
});

// Language switching
Route::get('set-language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ar'])) {
        session(['locale' => $locale]);
    }

    return redirect()->back();
})->name('set.language');

// Route::get('delete-account', [UserController::class, 'showDeleteForm'])->name('account.delete-form');
// Route::post('delete-account/request-code', [UserController::class, 'requestDeleteCode'])->name('account.request-delete-code');
// Route::delete('delete-account', [UserController::class, 'destroy'])->name('account.destroy');

// Route::get('/handle-payment/success', [TelrController::class, 'onSuccess'])->name('telr.success');
// Route::get('/handle-payment/declined', [TelrController::class, 'onDeclined'])->name('telr.declined');
// Route::get('/handle-payment/cancel', [TelrController::class, 'onCancel'])->name('telr.cancel');

// Route::get('/privacy-policy', [WebPageController::class, 'privacyPolicy'])->name('privacy-policy');
// Route::get('/terms-and-conditions', [WebPageController::class, 'termsAndConditions'])->name('terms-and-conditions');
