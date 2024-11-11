<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\User; // Ajout de l'importation du modèle User
use Illuminate\Http\Request;

class StatisticsControllerAmin extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        // Nombre d'utilisateurs
        $nombreUtilisateurs = User::count();
    
        // Nombre de ventes (commandes)
        $nombreVentes = Commande::count(); 
    
        // Nombre de producteurs
        $nombreProducteurs = User::where('role', 'producteur')->count();
    
        // Calcul du revenu total de toutes les commandes
        $totalRevenue = Commande::sum('montant_total'); // Somme de la colonne montant_total de toutes les commandes
        
        // Assurez-vous que $totalRevenue est bien un float
        $totalRevenue = (float) $totalRevenue;
    
        return response()->json([
            'utilisateurs' => $nombreUtilisateurs,
            'ventes' => $nombreVentes,
            'producteurs' => $nombreProducteurs,
            'total_revenue' => $totalRevenue,
        ]);
    }
    
    public function evolutionMontantCommandes()
    {
       
        $commandes = Commande::selectRaw('SUM(Montant_total) as total, MONTH(created_at) as mois, YEAR(created_at) as annee')
            ->groupBy('mois', 'annee')
            ->orderBy('annee')
            ->orderBy('mois')
            ->get();
    
        $montants = [];
        $labels = [];
    
        foreach ($commandes as $commande) {
            $montants[] = $commande->total;
            $labels[] = \Carbon\Carbon::create()->month($commande->mois)->format('F') . ' ' . $commande->annee;
        }
    
       
        return response()->json([
            'montants' => $montants,
            'labels' => $labels
        ]);
    }
    
}
