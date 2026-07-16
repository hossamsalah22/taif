<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use App\Http\Resources\Global\PageResource;
use App\Settings\PagesSettings;
use Illuminate\Http\Request;

class PagesController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $settings = app(PagesSettings::class);

        return $this->successResponse(__('retrieved_successfully'), [
            'settings' => PageResource::make($settings),
        ]);
    }
}
