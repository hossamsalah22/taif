<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use App\Http\Resources\Global\FaqResource;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $faqs = Faq::active()->orderBy('order', 'asc')->cursorPaginate(request('per_page', 15));

        return $this->successResponse(__('retrieved_successfully'), FaqResource::collection($faqs));
    }
}
