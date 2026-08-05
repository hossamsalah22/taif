<?php

namespace App\Providers\Filament;

use Althinect\FilamentSpatieRolesPermissions\FilamentSpatieRolesPermissionsPlugin;
use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Pages\RequestPasswordReset;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
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
            ->font('Tajawal')
            // ->collapsibleNavigationGroups(true)
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
                AccountWidget::class,
                FilamentInfoWidget::class,
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
                'panels::body.end',
                fn (): string => "
                    <script>
                        const navKey = 'filament.navigation.groups.collapsed';

                        function initAccordion() {
                            const groups = document.querySelectorAll('.fi-sidebar-group');

                            // Function to collapse a group
                            const collapseGroup = (group) => {
                                const button = group.querySelector('button[aria-expanded=\"true\"]');
                                if (button) button.click();
                            };

                            // Function to expand a group
                            const expandGroup = (group) => {
                                const button = group.querySelector('button[aria-expanded=\"false\"]');
                                if (button) button.click();
                            };

                            // 1. On Load: Handle expansion/collapse
                            groups.forEach(group => {
                                const currentPath = window.location.pathname;
                                const hasActive = group.querySelector('.fi-sidebar-item-active, .fi-active, .fi-sidebar-group-active');
                                const hasMatchedLink = Array.from(group.querySelectorAll('a')).some(a => a.pathname === currentPath);

                                if (hasActive || hasMatchedLink) {
                                    setTimeout(() => expandGroup(group), 10);
                                } else {
                                    collapseGroup(group);
                                }
                            });

                            // 2. Clear persistence to avoid browser fighting our code
                            localStorage.removeItem(navKey);

                            // 3. Listen for manual clicks to close others (Accordion behavior)
                            groups.forEach(group => {
                                const button = group.querySelector('button');
                                if (button && !button.hasAttribute('data-acc-init')) {
                                    button.setAttribute('data-acc-init', 'true');
                                    button.addEventListener('click', (e) => {
                                        // If we are opening this group
                                        setTimeout(() => {
                                            if (button.getAttribute('aria-expanded') === 'true') {
                                                groups.forEach(other => {
                                                    if (other !== group) collapseGroup(other);
                                                });
                                            }
                                        }, 10);
                                    });
                                }
                            });
                        }

                        function applyNoValidate() {
                            document.querySelectorAll('form').forEach(form => {
                                if (!form.hasAttribute('novalidate')) {
                                    form.setAttribute('novalidate', 'novalidate');
                                }
                            });
                        }

                        document.addEventListener('DOMContentLoaded', function() {
                            document.querySelectorAll('form').forEach(form => form.setAttribute('novalidate', 'novalidate'));
                            initAccordion();

                            // Watch for new forms or attribute changes to ensure novalidate is never removed by Livewire morphing
                            const observer = new MutationObserver((mutations) => {
                                applyNoValidate();
                            });
                            observer.observe(document.body, {
                                childList: true,
                                subtree: true,
                                attributes: true,
                                attributeFilter: ['novalidate']
                            });
                        });

                        document.addEventListener('livewire:navigated', function() {
                            applyNoValidate();
                            initAccordion();
                        });
                    </script>
                    <style>
                        :root { --user-avatar-url: url('".auth()->user()?->getFilamentAvatarUrl()."'); }
                        .fi-logo {
                            margin-inline-start: 4rem !important;
                        }

                        .fi-dropdown-list-item[href*='profile'] .fi-dropdown-list-item-icon,
                        .fi-dropdown-list-item[href*='profile'] svg {
                            display: none !important;
                        }
                        .fi-dropdown-list-item[href*='profile'] {
                            display: flex !important;
                            align-items: center !important;
                        }
                        .fi-dropdown-list-item[href*='profile']::before {
                            content: '';
                            display: block;
                            width: 24px;
                            height: 24px;
                            background-image: var(--user-avatar-url);
                            background-size: cover;
                            background-position: center;
                            border-radius: 50%;
                            margin-inline-end: 0.75rem;
                            flex-shrink: 0;
                        }

                        button[type='submit'][wire\\:click*='createAnother'],
                        button[type='button'][wire\\:click*='createAnother'] {
                            display: none !important;
                        }
                    </style>
                ",
            )

            ->resourceCreatePageRedirect('index')
            ->resourceEditPageRedirect('index');
    }
}
