<?php

namespace App\Http\Controllers\User\Auth;

use App\Enums\VerificationTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\ConfirmOtpRequest;
use App\Http\Requests\User\Auth\SendOtpRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserVerification;
use App\Notifications\SendUserOtpNotification;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Hash;

class OtpController extends Controller
{
    use ApiResponseTrait;

    public function confirmOtp(ConfirmOtpRequest $request)
    {
        $verification = UserVerification::query()
            ->where('id', $request->verification_id)
            ->first();

        if (
            ! $verification ||
            $verification->verified_at !== null ||
            ! Hash::check($request->otp, $verification->otp) ||
            $verification->expires_at->isPast()
        ) {
            return $this->failedResponse(__('auth.otp_wrong_or_expired'), 422);
        }
        $user = $verification->user;

        switch ($verification->type) {
            case VerificationTypeEnum::LOGIN:
                break;
            case VerificationTypeEnum::REGISTRATION:
                $user->update([
                    'is_verified' => true,
                ]);
                $user->tokens()->delete();
                break;
            case VerificationTypeEnum::PHONE_CHANGE:
                $user->update([
                    'phone' => $verification->new_value,
                    'country_code' => $verification->country_code,
                ]);
                break;
            case VerificationTypeEnum::EMAIL_CHANGE:
                $user->update([
                    'email' => $verification->new_value,
                ]);
                break;
            case VerificationTypeEnum::DELETE_ACCOUNT:
                $user->tokens()->delete();
                $user->firebaseTokens()->delete();
                $user->delete();
                break;
        }

        if (isset($request->device_token)) {
            $user->firebaseTokens()->updateOrCreate(
                ['token' => $request->device_token],
                ['device_type' => $request->device_type ?? null]
            );
        }

        return $this->successResponse(
            __('auth.code_verified_successfully'),
            [
                'user' => ($verification->type !== VerificationTypeEnum::DELETE_ACCOUNT ? UserResource::make($user->fresh()) : null),
                'token' => ($verification->type !== VerificationTypeEnum::DELETE_ACCOUNT ? $user->createToken('user_token')->plainTextToken : null),
            ]
        );
    }

    public function sendOtp(SendOtpRequest $request)
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
            'type' => VerificationTypeEnum::REGISTRATION,
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
