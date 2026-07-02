<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\Cours;
use App\Models\Inscription;
use App\Models\Paiement;
use App\Models\DemandesFormation;
use App\Models\Certificat;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // Statistiques utilisateurs
        $totalUsers = User::count();
        $totalApprenants = User::where('role', 'apprenant')->count();
        $totalFormateurs = User::where('role', 'formateur')->count();
        $totalAdmins = User::where('role', 'admin')->count();
        
        // Formateurs en attente de validation
        $formateursEnAttente = User::where('role', 'formateur')
            ->whereNull('email_verified_at')
            ->count();
        
        // Statistiques cours
        $totalCours = Cours::count();
        $coursPublies = Cours::where('statut', 'publie')->count();
        $coursCertifiants = Cours::where('est_certifiant', true)->count();
        $coursGratuits = Cours::where('est_gratuit', true)->count();
        
        // Statistiques inscriptions
        $totalInscriptions = Inscription::count();
        $inscriptionsActives = Inscription::where('statut', 'actif')->count();
        $inscriptionsTerminees = Inscription::where('statut', 'termine')->count();
        
        // Taux de complétion moyen
        $progressionMoyenne = Inscription::avg('progression') ?? 0;
        
        // Statistiques financières
        $totalPaiements = Paiement::where('statut', 'paye')->sum('montant') ?? 0;
        $paiementsMois = Paiement::where('statut', 'paye')
            ->whereMonth('date_paiement', Carbon::now()->month)
            ->sum('montant') ?? 0;
        
        // Demandes de formation
        $demandesEnAttente = DemandesFormation::where('statut', 'en_attente')->count();
        
        // Certificats délivrés
        $certificatsDelivres = Certificat::where('est_valide', true)->count();
        
        return [
            // ========== SECTION UTILISATEURS ==========
            Stat::make('👥 Utilisateurs', $totalUsers)
                ->description("{$totalApprenants} apprenants, {$totalFormateurs} formateurs, {$totalAdmins} admins")
                ->color('success')
                ->icon('heroicon-o-users'),
            
            Stat::make('⏳ Formateurs à valider', $formateursEnAttente)
                ->description($formateursEnAttente > 0 ? 'En attente d\'activation' : 'Tous validés')
                ->color($formateursEnAttente > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-user-group'),
            
            // ========== SECTION COURS ==========
            Stat::make('📚 Cours', $totalCours)
                ->description("{$coursPublies} publiés, {$coursCertifiants} certifiants")
                ->color('primary')
                ->icon('heroicon-o-book-open'),
            
            Stat::make('🎓 Cours gratuits', $coursGratuits)
                ->description('Accessibles sans paiement')
                ->color('info')
                ->icon('heroicon-o-gift'),
            
            // ========== SECTION INSCRIPTIONS ==========
            Stat::make('📝 Inscriptions', $totalInscriptions)
                ->description("{$inscriptionsActives} actives, {$inscriptionsTerminees} terminées")
                ->color('warning')
                ->icon('heroicon-o-document-text'),
            
            Stat::make('📊 Progression moyenne', round($progressionMoyenne, 1) . '%')
                ->description('Taux de complétion moyen')
                ->color($progressionMoyenne >= 50 ? 'success' : 'danger')
                ->icon('heroicon-o-chart-bar'),
            
            // ========== SECTION FINANCES ==========
            Stat::make('💰 Chiffre d\'affaires', number_format($totalPaiements, 0, ',', ' ') . ' FCFA')
                ->description("Ce mois-ci : " . number_format($paiementsMois, 0, ',', ' ') . ' FCFA')
                ->color('success')
                ->icon('heroicon-o-currency-dollar'),
            
            // ========== SECTION DEMANDES ==========
            Stat::make('📋 Demandes formation', $demandesEnAttente)
                ->description($demandesEnAttente > 0 ? 'En attente de traitement' : 'Aucune demande en attente')
                ->color($demandesEnAttente > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-chat-bubble-left-right'),
            
            // ========== SECTION CERTIFICATS ==========
            Stat::make('🎓 Certificats', $certificatsDelivres)
                ->description('Certificats délivrés')
                ->color('info')
                ->icon('heroicon-o-document-check'),
        ];
    }
}