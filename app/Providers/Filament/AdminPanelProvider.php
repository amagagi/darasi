<?php

namespace App\Providers\Filament;

use App\Models\Annonce;
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
                        // Cette navigation est construite à la main : une
                        // ressource Filament n'apparaît QUE si elle est listée
                        // ici, la découverte automatique étant alors ignorée.
                        // Filament interdit qu'un groupe ET ses éléments portent
                        // une icône : l'icône reste sur le groupe, comme pour
                        // les autres groupes de ce menu.
                        NavigationGroup::make('Communication')
                            ->icon('heroicon-o-megaphone')
                            ->items([
                                NavigationItem::make('Actualités & alertes')
                                    ->url('/admin/annonces')
                                    ->isActiveWhen(fn (): bool => request()->is('admin/annonces*'))
                                    ->badge(fn (): ?string => self::compteAnnoncesActives()),
                            ]),
                        NavigationGroup::make('Paramètres')
                            // ->icon('heroicon-o-cog-6-tooth')  ← SUPPRIMÉ
                            ->items([
                                NavigationItem::make('Vision & Mission')
                                    ->url('/admin/contenus-site')
                                    ->icon('heroicon-o-sparkles')
                                    ->isActiveWhen(fn (): bool => request()->is('admin/contenus-site*')),
                                NavigationItem::make('Contenus juridiques')
                                    ->url('/admin/contenus-juridiques')
                                    ->icon('heroicon-o-document-text')
                                    ->isActiveWhen(fn (): bool => request()->is('admin/contenus-juridiques*')),
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

    /**
     * Nombre d'annonces actuellement visibles, pour la pastille du menu.
     *
     * Renvoie null en cas d'échec : cette requête est exécutée à chaque rendu
     * de la navigation, et une table absente (déploiement neuf, migrations pas
     * encore jouées) ne doit pas mettre tout le panel en erreur.
     */
    private static function compteAnnoncesActives(): ?string
    {
        try {
            $nombre = Annonce::query()->active()->count();
        } catch (\Throwable) {
            return null;
        }

        return $nombre > 0 ? (string) $nombre : null;
    }
}