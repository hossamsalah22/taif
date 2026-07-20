<?php

namespace App\Http\Controllers\User\Auth;

use App\Enums\VerificationTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\RegisterRequest;
use App\Models\User;
use App\Models\UserVerification;
use App\Notifications\SendUserOtpNotification;
use Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class RegisterController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::create($data);

        if ($request->hasFile('image')) {
            $user->addMedia($request->file('image'))->toMediaCollection('users');
        }
        $otp = app()->environment('production') ? random_int(1000, 9999) : 1234;
        $expires_at = Carbon::now()->addMinutes(10);
        $verification = UserVerification::create([
            'user_id' => $user->id,
            'type' => VerificationTypeEnum::REGISTRATION,
            'new_value' => $user->phone,
            'country_code' => $user->country_code,
            'otp' => Hash::make($otp),
            'expires_at' => $expires_at,
        ]);

        $user->notify(new SendUserOtpNotification($otp, $expires_at));

        return $this->successResponse(__('Code sent successfully, please enter it to verify your account'), [
            'phone' => $user->phone,
            'verification_id' => $verification->id,
        ]);
    }
}
