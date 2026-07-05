<?php

namespace App\Providers;

use AbdulmajeedJamaan\FilamentTranslatableTabs\TranslatableTabs;
use App\Models\Admin;
use App\Rules\PhoneNumberRule;
use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $locales = config('app.locales');
        JsonResource::withoutWrapping();

        Model::preventLazyLoading(! app()->isProduction());

        Gate::before(function (Admin $user, string $ability): ?bool {
            return $user->isSuperAdmin() ? true : null;
        });

        Validator::extend('phone_number', function ($attribute, $value, $parameters, $validator) {
            $phoneNumberRule = new PhoneNumberRule($parameters[0] ?? 'SA');

            return $phoneNumberRule->passes($attribute, $value);
        });

        LanguageSwitch::configureUsing(function (LanguageSwitch $switch) use ($locales) {
            $switch
                ->locales(array_keys($locales))
                ->labels($locales)
                ->flags([
                    'ar' => asset('flags/saudi-arabia.svg'),
                    'en' => asset('flags/usa.svg'),
                ])
                ->flagsOnly(true);
        });

        TranslatableTabs::configureUsing(function (TranslatableTabs $component) use ($locales) {
            $component
                ->localesLabels([
                    'ar' => __('ar'),
                    'en' => __('en'),
                ])
                ->locales(array_keys($locales));
        });

    }
}
