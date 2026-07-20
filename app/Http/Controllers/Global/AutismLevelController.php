<?php

namespace App\Http\Controllers\Global;

use App\Enums\AutismLevelEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AutismLevelController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return $this->successResponse(__('Data Retrieved Successfully'), AutismLevelEnum::options(), 200);
    }
}
