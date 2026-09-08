<?php

namespace App\Http\Resources\Global;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

class PageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        $about_us = $locale === 'ar' ? $this->about_us_ar : $this->about_us_en;
        $privacy_policy = $locale === 'ar' ? $this->privacy_policy_ar : $this->privacy_policy_en;
        $terms_and_conditions = $locale === 'ar' ? $this->terms_and_conditions_ar : $this->terms_and_conditions_en;

        $about_us_html = Str::markdown($about_us ?? '');
        $privacy_policy_html = Str::markdown($privacy_policy ?? '');
        $terms_and_conditions_html = Str::markdown($terms_and_conditions ?? '');

        $privacyPillars = collect($this->privacy_pillars ?? [])->map(fn ($p) => [
            'title' => $locale === 'ar' ? ($p['title_ar'] ?? '') : ($p['title_en'] ?? ''),
            'body'  => $locale === 'ar' ? ($p['body_ar'] ?? '') : ($p['body_en'] ?? ''),
        ])->values()->toArray();

        return [
            'about_us' => $about_us_html,
            'privacy_policy' => $privacy_policy_html,
            'terms_and_conditions' => $terms_and_conditions_html,
            'help_center' => [
                'slogan' => $locale === 'ar' ? ($this->help_center_slogan_ar ?? '') : ($this->help_center_slogan_en ?? ''),
                'privacy_pillars' => $privacyPillars,
            ],
        ];
    }
}
