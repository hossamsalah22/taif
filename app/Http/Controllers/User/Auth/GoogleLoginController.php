<?php

namespace App\Http\Controllers\User\Auth;

use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\GoogleLoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Exception;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

class GoogleLoginController extends Controller
{
    use ApiResponseTrait;

    /**
     * Handle user login with Google token.
     */
    public function __invoke(GoogleLoginRequest $request)
    {
        try {
            /** @var AbstractProvider $driver */
            $driver = Socialite::driver('google');
            $googleUser = $driver->stateless()->userFromToken($request->token);

            if (! $googleUser) {
                return $this->failedResponse(__('auth.google_auth_failed'));
            }

            $user = User::withTrashed()->updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    'status' => UserStatusEnum::ACCEPTED->value,
                    'is_active' => true,
                ]
            );

            if ($user->trashed() || $user->status === UserStatusEnum::REJECTED) {
                return $this->failedResponse(__('auth.deleted_user'));
            }

            if ($user->status === UserStatusEnum::REJECTED) {
                return $this->failedResponse(__('auth.blocked_user'));
            }

            if (isset($request->device_token)) {
                $user->firebaseTokens()->updateOrCreate(
                    ['token' => $request->device_token],
                    ['device_type' => $request->os_type ?? 'ios']
                );
            }

            return $this->successResponse(__('auth.logged_in_successfully'), [
                'user' => UserResource::make($user),
                'token' => $user->createToken('user-token')->plainTextToken,
            ]);
        } catch (Exception $e) {
            return $this->failedResponse(__('auth.google_auth_failed').': '.$e->getMessage());
        }
    }
}
