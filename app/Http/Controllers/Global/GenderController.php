<?php

namespace App\Http\Controllers\Global;

use App\Enums\GenderEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GenderController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return $this->successResponse(__('Data Retrieved Successfully'), GenderEnum::options(), 200);
    }
}
