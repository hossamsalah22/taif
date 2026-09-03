<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use App\Http\Resources\User\SliderResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SliderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): AnonymousResourceCollection
    {
        $sliders = Slider::where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id', 'desc')
            ->get();

        return SliderResource::collection($sliders);
    }
}

