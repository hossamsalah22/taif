<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\Auth\LogoutRequest;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(LogoutRequest $request)
    {
        $data = $request->validated();
        $user = auth('user')->user();

        $user->currentAccessToken()->delete();
        if (! empty($data['device_token'])) {
            $user->firebaseTokens()->where('token', $data['device_token'])->delete();
        }

        return $this->successResponse(__('auth.logged_out'), [], 200);
    }
}
