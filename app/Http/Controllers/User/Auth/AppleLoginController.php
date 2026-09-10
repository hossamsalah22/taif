<?php

namespace App\Http\Controllers\User\Auth;

use App\Enums\UserStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\AppleLoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use Exception;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

class AppleLoginController extends Controller
{
    use ApiResponseTrait;

    /**
     * Handle user login with Apple token.
     */
    public function login(AppleLoginRequest $request)
    {
        try {
            /** @var AbstractProvider $driver */
            $driver = Socialite::driver('apple');
            $appleUser = $driver->stateless()->userFromToken($request->token);

            if (! $appleUser) {
                return $this->failedResponse(__('auth.apple_auth_failed'));
            }

            $user = User::withTrashed()->where('apple_id', $appleUser->getId())->first();

            if (! $user && $appleUser->getEmail()) {
                $user = User::withTrashed()->where('email', $appleUser->getEmail())->first();
            }

            if ($user) {
                $user->update([
                    'apple_id' => $appleUser->getId(),
                    'name' => $appleUser->getName() ?? $user->name ?? 'Apple User',
                    'status' => UserStatusEnum::ACCEPTED->value,
                    'is_active' => true,
                ]);
            } else {
                $user = User::create([
                    'apple_id' => $appleUser->getId(),
                    'email' => $appleUser->getEmail(),
                    'name' => $appleUser->getName() ?? 'Apple User',
                    'status' => UserStatusEnum::ACCEPTED->value,
                    'is_active' => true,
                ]);
            }

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
            return $this->failedResponse((__('auth.apple_auth_failed').': '.$e->getMessage()));
        }
    }
}
