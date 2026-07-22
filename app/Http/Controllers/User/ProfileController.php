<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;

class ProfileController extends Controller
{
    public function index()
    {
        return $this->successResponse(__('Retrieved Successfully'), UserResource::make(auth('sanctum')->user()));
    }
}
