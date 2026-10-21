<?php

namespace App\Providers\Filament;

use Althinect\FilamentSpatieRolesPermissions\FilamentSpatieRolesPermissionsPlugin;
use App\Enums\NavigationGroupEnum;
use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\RequestPasswordReset;
use App\Filament\Widgets\DashboardKpiOverview;
use App\Filament\Widgets\PerformanceLineChart;
use App\Filament\Widgets\RevenueBreakdownOverview;
use App\Filament\Widgets\SkillMappingPieChart;
use App\Http\Middleware\AuthenticateAdminSocketToken;
use App\Models\Admin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Actions\CreateAction;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->passwordReset(RequestPasswordReset::class)
            ->authPasswordBroker('admins')
            ->authGuard('web')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => (auth('web')->user() instanceof Admin
                    ? '<meta name="admin-socket-token" content="'.e(AuthenticateAdminSocketToken::tokenFor(auth('web')->user())).'">'
                    : '').Blade::render("@vite('resources/js/app.js')"),
            )
            ->font('Tajawal')
            ->navigationGroups(NavigationGroupEnum::navigationGroups())
            ->collapsibleNavigationGroups()
            ->login()
            ->profile(EditProfile::class)
            ->colors([
                'primary' => Color::Amber,
            ])
            ->favicon(Storage::url('tayf.png'))
            ->brandLogo(Storage::url('tayf.png'))
            ->brandLogoHeight('4.5rem')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                DashboardKpiOverview::class,
                RevenueBreakdownOverview::class,
                PerformanceLineChart::class,
                SkillMappingPieChart::class,
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('5s')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                SpatieTranslatablePlugin::make()->defaultLocales(config('app.locales')),
                FilamentSpatieRolesPermissionsPlugin::make(),
                FilamentShieldPlugin::make(),
            ])
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => view('filament.admin.body-end')->render(),
            )
            ->bootUsing(function (): void {
                // Every create screen returns to the list after saving, so "Create & create another" is not offered.
                CreateRecord::disableCreateAnother();
                CreateAction::configureUsing(fn (CreateAction $action): CreateAction => $action->createAnother(false));
            })
            ->resourceCreatePageRedirect('index')
            ->resourceEditPageRedirect('index');
    }
}
