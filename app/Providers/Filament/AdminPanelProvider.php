<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Dashboard as AdminDashboard;
use App\Filament\Widgets\AdminKpiOverview;
use App\Filament\Widgets\FulfillmentSnapshot;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->profile()
            ->brandName('Chess Puzzle Challenge')
            ->brandLogo(new HtmlString(<<<'HTML'
                <span class="cpc-brand-logo">
                    <span class="cpc-brand-pawn">&#9823;</span>
                    <span class="cpc-brand-text">
                        Chess Puzzle
                        <span class="cpc-brand-accent">Challenge</span>
                    </span>
                </span>
                HTML))
            ->darkModeBrandLogo(new HtmlString(<<<'HTML'
                <span class="cpc-brand-logo cpc-brand-logo-dark">
                    <span class="cpc-brand-pawn">&#9823;</span>
                    <span class="cpc-brand-text">
                        Chess Puzzle
                        <span class="cpc-brand-accent">Challenge</span>
                    </span>
                </span>
                HTML))
            ->favicon(asset('favicon.ico'))
            ->colors([
                'primary' => '#111111',
                'secondary' => '#F59E0B',
                'success' => Color::Green,
                'warning' => Color::Amber,
                'danger' => Color::Red,
                'info' => Color::Green,
                'gray' => Color::Slate,
            ])
            ->font('Inter')
            ->darkMode(true)
            ->sidebarCollapsibleOnDesktop(true)
            ->globalSearch(true)
            ->databaseNotifications(false)
            ->assets([
                Css::make('filament-puzzle-preview', Vite::asset('resources/css/filament-puzzle-preview.css')),
                Js::make('filament-puzzle-preview', Vite::asset('resources/js/filament-puzzle-preview.js'))->module(),
                Js::make('filament-editorjs', Vite::asset('resources/js/filament-editorjs.js'))->module(),
                Css::make('filament-admin-theme', asset('css/filament-admin-theme.css')),
                Css::make('filament-tailwind', Vite::asset('resources/css/filament-tailwind.css')),
            ])
            ->maxContentWidth(Width::Full)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                AdminDashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AdminKpiOverview::class,
                FulfillmentSnapshot::class,
                AccountWidget::class,
            ])
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
            ]);
    }
}
