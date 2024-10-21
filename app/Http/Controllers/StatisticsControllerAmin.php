<?php

namespace App\Http\Controllers;

use App\Models\Commande;
use App\Models\User; // Ajout de l'importation du modèle User
use Illuminate\Http\Request;

class StatisticsControllerAmin extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse // Spécification du type de retour
    {
        $nombreUtilisateurs = User::count();
        $nombreVentes = Commande::count(); 
        $nombreProducteurs = User::where('role', 'producteur')->count();

        return response()->json([
            'utilisateurs' => $nombreUtilisateurs,
            'ventes' => $nombreVentes,
            'producteurs' => $nombreProducteurs,
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
