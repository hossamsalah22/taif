<?php

namespace App\Http\Controllers\User\Auth;

use App\Enums\VerificationTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\LoginRequest;
use App\Models\User;
use App\Models\UserVerification;
use App\Notifications\SendUserOtpNotification;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $user = User::where('phone', $request->phone)->first();
        if (! $user) {
            return $this->failedResponse(__('auth.user_not_found'), 404);
        }
        if (! $user->is_active) {
            return $this->failedResponse(__('auth.user_not_active'), 403);
        }
        $otp = app()->environment('production') ? random_int(1000, 9999) : 1234;
        $expires_at = now()->addMinutes(5);

        $verification = UserVerification::create([
            'user_id' => $user->id,
            'type' => VerificationTypeEnum::LOGIN,
            'new_value' => $user->phone,
            'country_code' => $user->country_code,
            'otp' => Hash::make($otp),
            'expires_at' => $expires_at,
        ]);

        $user->notify(new SendUserOtpNotification($otp, $expires_at));

        return $this->successResponse(__('notification.code_sent_successfully'), [
            'phone' => $user->phone,
            'verification_id' => $verification->id,
        ]);
    }
}
