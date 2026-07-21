<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use App\Http\Resources\Global\AboutAppResource;
use App\Settings\PagesSettings;
use Illuminate\Http\Request;

class AboutAppController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $settings = app(PagesSettings::class);

        return $this->successResponse(__('retrieved_successfully'), [
            'settings' => AboutAppResource::make($settings),
        ]);
    }
}
