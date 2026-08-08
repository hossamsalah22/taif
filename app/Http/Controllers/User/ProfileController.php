<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Http\Requests\User\UpdateProfileRequest;
use App\Models\UserVerification;
use App\Enums\VerificationTypeEnum;
use App\Notifications\SendUserOtpNotification;
use App\Traits\ApiResponseTrait;
use Arr;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function index()
    {
        return $this->successResponse(__('Retrieved Successfully'), UserResource::make(auth('user')->user()));
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = auth('user')->user();
        $data = $request->validated();

        $user->update(Arr::except($data, ['phone', 'country_code']));

        if (isset($data['phone']) && $data['phone'] !== $user->phone) {
            $otp = app()->environment('production') ? random_int(1000, 9999) : 1234;
            $expires_at = now()->addMinutes(5);

            $verification = UserVerification::create([
                'user_id' => $user->id,
                'type' => VerificationTypeEnum::PHONE_CHANGE,
                'new_value' => $data['phone'],
                'country_code' => $data['country_code'] ?? $user->country_code,
                'otp' => Hash::make($otp),
                'expires_at' => $expires_at,
            ]);

            $user->notify(new SendUserOtpNotification($otp, $expires_at));

            return $this->successResponse(__('Profile updated successfully, OTP sent for phone verification.'), [
                'user' => UserResource::make($user->fresh()),
                'requires_verification' => true,
                'verification_id' => $verification->id,
            ]);
        }

        return $this->successResponse(__('Profile updated successfully.'), [
            'user' => UserResource::make($user->fresh()),
            'requires_verification' => false,
        ]);
    }
}
