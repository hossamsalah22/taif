<?php

use App\Http\Controllers\Payment\MoyasarController;
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

Route::get('payments/moyasar/{payment}/return', [MoyasarController::class, 'return'])->name('payments.moyasar.return');

// Route::get('/privacy-policy', [WebPageController::class, 'privacyPolicy'])->name('privacy-policy');
// Route::get('/terms-and-conditions', [WebPageController::class, 'termsAndConditions'])->name('terms-and-conditions');
