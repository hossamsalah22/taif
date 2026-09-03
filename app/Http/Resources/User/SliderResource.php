<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SliderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $enLarge = $this->getFirstMediaUrl('slider_en_large');
        $enSmall = $this->getFirstMediaUrl('slider_en_small');
        $arLarge = $this->getFirstMediaUrl('slider_ar_large');
        $arSmall = $this->getFirstMediaUrl('slider_ar_small');

        // Fallback for English Small -> English Large -> Arabic Small -> Arabic Large
        if (!$enSmall) $enSmall = $enLarge;
        if (!$enSmall) $enSmall = $arSmall;
        if (!$enSmall) $enSmall = $arLarge;

        // Fallback for English Large -> English Small -> Arabic Large -> Arabic Small
        if (!$enLarge) $enLarge = $enSmall;
        if (!$enLarge) $enLarge = $arLarge;
        if (!$enLarge) $enLarge = $arSmall;

        // Fallback for Arabic Small -> Arabic Large -> English Small -> English Large
        if (!$arSmall) $arSmall = $arLarge;
        if (!$arSmall) $arSmall = $enSmall;
        if (!$arSmall) $arSmall = $enLarge;

        // Fallback for Arabic Large -> Arabic Small -> English Large -> English Small
        if (!$arLarge) $arLarge = $arSmall;
        if (!$arLarge) $arLarge = $enLarge;
        if (!$arLarge) $arLarge = $enSmall;

        return [
            'id' => $this->id,
            'sort_order' => $this->sort_order,
            'image_en_large' => $enLarge ?: null,
            'image_en_small' => $enSmall ?: null,
            'image_ar_large' => $arLarge ?: null,
            'image_ar_small' => $arSmall ?: null,
        ];
    }
}

