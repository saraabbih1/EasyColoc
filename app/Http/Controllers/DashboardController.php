<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Colocation;
use App\Models\Expense;
use App\Models\Invitation;
use App\Models\Membership;
use App\Models\Settlement;
use App\Services\BalanceCalculatorService;
use Illuminate\Support\Facades\Auth;

/**
 * DashboardController - Contrôleur principal pour le tableau de bord EasyColoc
 * 
 * Ce contrôleur gère l'affichage du dashboard utilisateur avec :
 * - Vérification des middlewares (authentification et non-banni)
 * - Redirection automatique pour les admins globaux
 * - Récupération des données de colocation active
 * - Calcul des soldes et dettes
 * - Préparation des données pour la vue Blade
 */
class DashboardController extends Controller
{
    /**
     * Constructeur : Application des middlewares de sécurité
     * 
     * - auth : Vérifie que l'utilisateur est connecté
     * - not.banned : Vérifie que l'utilisateur n'est pas banni
     * - Redirection automatique si banni vers login avec message d'erreur
     */
    public function __construct()
    {
        // Application des middlewares pour la sécurité et l'authentification
        $this->middleware(['auth', 'not.banned']);
    }

    /**
     * Méthode principale du dashboard
     * 
     * Traitements effectués :
     * 1. Vérification si l'utilisateur est un admin global (redirection)
     * 2. Récupération de la colocation active de l'utilisateur
     * 3. Chargement des relations nécessaires (eager loading)
     * 4. Calcul des soldes et dettes via BalanceCalculatorService
     * 5. Préparation des données pour l'affichage
     * 
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        // Récupération de l'utilisateur connecté via l'authentification Laravel
        $user = Auth::user();
        
        // Redirection automatique pour les administrateurs globaux
        // L'admin accède au dashboard admin plutôt qu'au dashboard utilisateur
        if ($user->isGlobalAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        // Récupération du membership actif de l'utilisateur
        // Utilisation de la relation définie dans le modèle User
        $activeMembership = $user->activeMembership();
        $colocation = $activeMembership?->colocation;

        // Si l'utilisateur n'a pas de colocation active, affichage du dashboard vide
        // Vue dashboard-empty avec invitation à créer ou rejoindre une colocation
        if (!$colocation) {
            return view('dashboard-empty');
        }

        // Chargement optimisé des relations nécessaires (eager loading)
        // Évite les problèmes N+1 et améliore les performances
        $colocation->load([
            'activeMemberships.user',      // Membres actifs avec leurs infos
            'expenses.category',          // Dépenses avec leurs catégories
            'expenses.user',              // Dépenses avec le payeur
            'pendingSettlements.debtor',  // Dettes en attente (débiteurs)
            'pendingSettlements.creditor', // Dettes en attente (créditeurs)
            'categories'                  // Catégories de dépenses
        ]);

        // Récupération des 5 dépenses les plus récentes pour l'affichage rapide
        $recentExpenses = $colocation->expenses()
            ->with(['category', 'user'])
            ->orderBy('expense_date', 'desc')
            ->take(5)
            ->get();

        // Calcul des statistiques de base
        $totalExpenses = $colocation->expenses()->sum('amount');
        $memberCount = $colocation->activeMemberships()->count();

        // Utilisation du service métier pour le calcul des soldes
        // Pattern Service : séparation de la logique métier du contrôleur
        $balanceCalculator = new BalanceCalculatorService();
        $balances = $balanceCalculator->calculateBalances($colocation);

        // Extraction du solde de l'utilisateur connecté
        $userBalance = $balances[$user->id] ?? 0;

        // Récupération des dettes impliquant l'utilisateur connecté
        $pendingSettlements = $colocation->pendingSettlements()
            ->where(function ($query) use ($user) {
                $query->where('debtor_id', $user->id)
                      ->orWhere('creditor_id', $user->id);
            })
            ->with(['debtor', 'creditor'])
            ->get();

        // Récupération des invitations en attente pour la colocation
        $pendingInvitations = $colocation->pendingInvitations()->get();

        // Vérification si l'utilisateur est le propriétaire de la colocation
        // Utilisé pour afficher/masquer certaines fonctionnalités
        $isOwner = ((int) $colocation->owner_id === (int) $user->id);

        // Calcul des dépenses du mois en cours
        $monthlyExpenses = $colocation->expenses()
            ->whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');

        // Retour de la vue Blade avec toutes les données nécessaires
        // Utilisation de compact() pour passer les variables à la vue
        return view('dashboard', compact(
            'colocation',           // Colocation active de l'utilisateur
            'recentExpenses',      // 5 dernières dépenses
            'totalExpenses',       // Total des dépenses de la colocation
            'memberCount',         // Nombre de membres actifs
            'balances',            // Tableau des soldes de tous les membres
            'userBalance',         // Solde de l'utilisateur connecté
            'pendingSettlements',  // Dettes en attente de l'utilisateur
            'pendingInvitations',  // Invitations en attente
            'isOwner',             // True si l'utilisateur est propriétaire
            'monthlyExpenses'      // Total des dépenses du mois
        ));
    }
}
