<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Commande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatisticsController extends Controller
{
    public function index(Request $request)
    {
        // Récupérer l'utilisateur connecté
        $user = $request->user();
    
        // Récupérer le nombre de commandes contenant les produits de l'utilisateur connecté
        $totalOrders = Commande::whereHas('produits', function ($query) use ($user) {
            $query->where('produits.user_id', $user->id);
        })->count(); 
    
        // Récupérer le nombre total de produits vendus
        $totalProductsSold = Commande::whereHas('produits', function ($query) use ($user) {
            $query->where('produits.user_id', $user->id);
        })->with(['produits' => function ($query) {
            $query->select('produits.id', 'produits.libelle')->withPivot('quantite');
        }])->get()->sum(function ($commande) {
            return $commande->produits->sum(function ($produit) {
                return $produit->pivot->quantite;
            });
        });
    
        // Nombre total de clients
        $totalClients = User::where("role", "client")->count();
    
        // Calculer le revenu total basé sur les produits vendus
        $totalRevenue = Commande::whereHas('produits', function ($query) use ($user) {
            $query->where('produits.user_id', $user->id);
        })->sum('montant_total');
        $totalRevenue = (float) $totalRevenue;
    
        return response()->json([
            'total_orders' => $totalOrders, 
            'total_products_sold' => $totalProductsSold,
            'total_clients' => $totalClients,
            'total_revenue' => $totalRevenue,
        ]);
    }
    
}
