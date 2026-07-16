<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use App\Settings\GeneralSettings;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $settings = app(GeneralSettings::class);
        $locale = app()->getLocale();

        return $this->successResponse(__('retrieved_successfully'), [
            'settings' => [
                'cancellation_policy' => $locale === 'ar' ? $settings->cancellation_policy_ar : $settings->cancellation_policy_en,
                'refund_policy' => $locale === 'ar' ? $settings->refund_policy_ar : $settings->refund_policy_en,
                'phone' => $settings->phone,
                'whatsapp' => $settings->whatsapp,
                'email' => $settings->email,
                'app_store_link' => $settings->app_store_link,
                'play_store_link' => $settings->play_store_link,
                'tax' => (float) $settings->tax,
                'tax_type' => $settings->getTaxType(),
                'commission' => (float) $settings->commission,
                'commission_type' => $settings->getCommissionType(),
                'facebook' => $settings->facebook,
                'instagram' => $settings->instagram,
                'snapchat' => $settings->snapchat,
                'linkedin' => $settings->linkedin,
                'tiktok' => $settings->tiktok,
            ],
        ]);

    }
}
