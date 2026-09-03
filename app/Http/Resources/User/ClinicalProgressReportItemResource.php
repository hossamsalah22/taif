<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClinicalProgressReportItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();
        $title = $this['title'] ?? '';

        if (is_array($title)) {
            $title = $title[$locale] ?? ($title['ar'] ?? '');
        }

        return [
            'title' => (string) $title,
            'icon_url' => ! empty($this['icon']) ? asset('storage/'.$this['icon']) : null,
        ];
    }
}
