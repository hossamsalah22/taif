<?php

namespace App\Http\Resources\Global;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Str;

class AboutAppResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $locale = app()->getLocale();

        $about_app_header = $locale === 'ar' ? $this->about_app_header_ar : $this->about_app_header_en;
        $about_app_sub_header = $locale === 'ar' ? $this->about_app_sub_header_ar : $this->about_app_sub_header_en;
        $help_center_slogan = $locale === 'ar' ? $this->help_center_slogan_ar : $this->help_center_slogan_en;

        $app_features_translated = [];
        $privacy_pillars_translated = [];

        foreach ($this->app_features as $feature) {
            $app_features_translated[] = [
                'title' => $locale === 'ar' ? $feature['title_ar'] : $feature['title_en'],
                'description' => $locale === 'ar' ? $feature['description_ar'] : $feature['description_en'],
            ];
        }

        foreach ($this->privacy_pillars as $pillar) {
            $privacy_pillars_translated[] = [
                'title' => $locale === 'ar' ? $pillar['title_ar'] : $pillar['title_en'],
                'body' => $locale === 'ar' ? $pillar['body_ar'] : $pillar['body_en'],
            ];
        }

        $about_app_header_html = Str::markdown($about_app_header ?? '');
        $about_app_sub_header_html = Str::markdown($about_app_sub_header ?? '');
        $help_center_slogan_html = Str::markdown($help_center_slogan ?? '');

        return [
            'about_app_header' => $about_app_header_html,
            'about_app_sub_header' => $about_app_sub_header_html,
            'help_center_slogan' => $help_center_slogan_html,
            'app_features' => $app_features_translated,
            'privacy_pillars' => $privacy_pillars_translated,
        ];
    }
}
