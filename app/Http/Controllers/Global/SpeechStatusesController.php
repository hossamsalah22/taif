<?php

namespace App\Http\Controllers\Global;

use App\Enums\SpeechStatusEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SpeechStatusesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return $this->successResponse(__('Data Retrieved Successfully'), SpeechStatusEnum::options(), 200);
    }
}
