<?php

namespace App\Http\Controllers;
use App\Models\Produit;

use App\Models\Commande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreCommandeRequest;
use App\Http\Requests\UpdateCommandeRequest;


class CommandeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Vérifie si l'utilisateur est authentifié
        if (!$request->user()) {
            return response()->json(['error' => 'Utilisateur non authentifié'], 401);
        }

        $user = $request->user();

        // Récupérer tous les Commandes associés à cet utilisateur
        $Commandes = Commande::where('user_id', $user->id)
                         ->where('etat_commande', 'en cours')
                         ->with('produits') 
                         ->get();

        // Retourner les Commandes sous forme de réponse JSON
        return response()->json($Commandes);
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    { $validatedData = $request->validate([
        'montant_total' => 'required|numeric',
        'produits' => 'required|array',
        'produits.*.produit_id' => 'required|exists:produits,id',
        'produits.*.quantite' => 'required|integer|min:1',
    ]);

    // Récupérer l'utilisateur connecté
    $user = Auth::user();

    // Créer la commande
    $commande = new Commande();
    $commande->user_id = $user->id; 
    $commande->montant_total = $validatedData['montant_total'];
    $commande->save();

    // Enregistrer les produits dans la commande
    foreach ($validatedData['produits'] as $produitData) {
        $commande->produits()->attach($produitData['produit_id'], ['quantite' => $produitData['quantite']]);
    }

    return response()->json([
        'message' => 'Commande créée avec succès',
        'commande' => $commande,
    ], 201);
}
public function AfficherCommandes()
{
    $user = Auth::user();
    
    // Récupérer les commandes de l'utilisateur
    $commandes = $user->commandes; 
    return response()->json($commandes);
}




//supprimer un produit au Commande

public function afficherCommande(Request $request)
{
    $Commande = Commande::where('user_id', $request->user()->id)
                    ->where('etat_commande', 'en cours')
                    ->first();

    if (!$Commande) {
        return response()->json(['error' => 'Commande non trouvé.'], 404);
    }

    $montantTotal = $Commande->calculerMontantTotal();

    return response()->json([
        'Commande' => $Commande,
        'montant_total' => $montantTotal
    ], 200);
}


}
