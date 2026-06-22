<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
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
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->brandName('DARASI')
            ->brandLogo(asset('images/logo.png'))
            ->favicon(asset('images/favicon.ico'))
            ->brandLogoHeight('3rem')
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                return $builder
                    ->items([
                        NavigationItem::make('Tableau de bord')
                            ->url('/admin')
                            ->icon('heroicon-o-home'),
                    ])
                    ->groups([
                        NavigationGroup::make('Utilisateurs')
                            ->icon('heroicon-o-users')
                            ->items([
                                NavigationItem::make('Utilisateurs')->url('/admin/users'),
                            ]),
                        NavigationGroup::make('Cours & Contenus')
                            ->icon('heroicon-o-academic-cap')
                            ->items([
                                NavigationItem::make('Cours')->url('/admin/cours'),
                                NavigationItem::make('Catégories')->url('/admin/categories'),
                                NavigationItem::make('Niveaux')->url('/admin/niveaux'),
                                NavigationItem::make('Modules')->url('/admin/modules'),
                                NavigationItem::make('Leçons')->url('/admin/lecons'),
                            ]),
                        NavigationGroup::make('Inscriptions & Suivi')
                            ->icon('heroicon-o-document-text')
                            ->items([
                                NavigationItem::make('Inscriptions')->url('/admin/inscriptions'),
                            ]),
                        NavigationGroup::make('Finance & Abonnements')
                            ->icon('heroicon-o-currency-dollar')
                            ->items([
                                NavigationItem::make('Forfaits')->url('/admin/abonnement-types'),
                                NavigationItem::make('Abonnements actifs')->url('/admin/abonnement-souscrits'),
                                NavigationItem::make('Paiements')->url('/admin/paiements'),
                            ]),
                        NavigationGroup::make('Tests & Évaluations')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->items([
                                NavigationItem::make('Tests module')->url('/admin/tests'),
                                NavigationItem::make('Tests finaux')->url('/admin/test-finals'),
                                NavigationItem::make('Questions')->url('/admin/questions'),
                                NavigationItem::make('Règles tentatives')->url('/admin/config-tentatives'),
                            ]),
                        NavigationGroup::make('Demandes & Certificats')
                            ->icon('heroicon-o-document-check')
                            ->items([
                                NavigationItem::make('Certificats')->url('/admin/certificats'),
                                NavigationItem::make('Demandes formation')->url('/admin/demande-formations'),
                            ]),
                        NavigationGroup::make('Paramètres')
                            // ->icon('heroicon-o-cog-6-tooth')  ← SUPPRIMÉ
                            ->items([
                                NavigationItem::make('Contenus juridiques')
                                    ->url('/admin/contenus-juridiques')
                                    ->icon('heroicon-o-document-text'),
                            ]),
                    ]);
            })
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
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