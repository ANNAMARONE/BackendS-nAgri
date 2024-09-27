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
                         ->with('produits') // Inclure les informations du produit
                         ->get();

        // Retourner les Commandes sous forme de réponse JSON
        return response()->json($Commandes);
    }
    

    /**
     * Store a newly created resource in storage.
     */
    
// CommandeController.php
public function store(Request $request)
{
    // Valider les données de la requête
    $request->validate([
        'produits' => 'required|array',
        'produits.*.produit_id' => 'required|exists:produits,id', // Vérifier si le produit existe
        'produits.*.quantite' => 'required|integer|min:1', // Vérifier la quantité
    ]);

    // Obtenir l'utilisateur connecté
    $user = $request->user();
    $user_id = $user->id; 
    $montant_total = 0;

    // Initialiser un tableau pour stocker les produits à ajouter
    $produitsData = [];

    foreach ($request->produits as $produit) {
        // Obtenir le prix unitaire pour le produit
        $prix_unitaire = $this->getPrixUnitaire($produit['produit_id']);
        
        // Calculer le montant total
        $montant_total += $prix_unitaire * $produit['quantite'];

        // Ajouter les détails du produit au tableau
        $produitsData[] = [
            'produit_id' => $produit['produit_id'],
            'quantite' => $produit['quantite'],
            'prix_unitaire' => $prix_unitaire,
        ];
    }

    try {
        // Créer une nouvelle commande
        $commande = Commande::create([
            'user_id' => $user_id,
            'etat_commande' => 'en cours',
            'montant_total' => $montant_total,
            'produits' => json_encode($produitsData), // Enregistrement des produits
        ]);

        return response()->json($commande, 201);
        
    } catch (\Exception $e) {
        return response()->json(['message' => 'Erreur lors de la création de la commande : ' . $e->getMessage()], 500);
    }
}

private function getPrixUnitaire($produit_id)
{
    $produit = Produit::find($produit_id);
    return $produit ? $produit->prix : 0;
}


    
// Exemple de fonction pour ajouter un produit au Commande
public function ajouterProduitAuCommande(Request $request)
{
    
    $request->validate([
        'produit_id' => 'required|exists:produits,id',
        'quantite' => 'required|integer|min:1',
        'reference' => 'nullable|string|max:255'
    ]);

    $produitId = $request->input('produit_id');
    $quantite = $request->input('quantite');
    $reference = $request->input('reference') ?? 'REF-' . strtoupper(uniqid());

    \DB::beginTransaction();

    try {
        // Trouver le produit
        $produit = Produit::findOrFail($produitId);
        if (!$produit) {
            throw new \Exception('Produit non trouvé.');
        }

        // Vérifier la quantité disponible
        if ($produit->quantite < $quantite) {
            throw new \Exception('Quantité insuffisante.');
        }

        // Calculer le prix unitaire et le montant total pour ce produit
        $prixUnitaire = $produit->prix;
        $montantTotalProduit = $prixUnitaire * $quantite;

        // Récupérer ou créer le Commande en cours pour l'utilisateur
        $Commande = Commande::firstOrCreate(
            ['user_id' => $request->user()->id, 'etat_commande' => 'en cours'],
            ['reference' => 'REF-' . strtoupper(uniqid()), 'montant_total' => 0]
        );

        // Vérifier si le produit est déjà dans le Commande
        $existingProduct = $Commande->produits()->where('produit_id', $produitId)->first();

        if ($existingProduct) {
            // Mettre à jour la quantité et le prix si le produit est déjà dans le Commande
            $existingProduct->pivot->quantite += $quantite;
            $existingProduct->pivot->montant_total += $montantTotalProduit;
            $existingProduct->pivot->save();
        } else {
            // Ajouter le produit au Commande
            $Commande->produits()->attach($produitId, [
                'quantite' => $quantite,
                'prix_unitaire' => $prixUnitaire,
                'montant_total' => $montantTotalProduit,
                'reference' => $reference
            ]);
        }

        // Mettre à jour le montant total du Commande
        $Commande->montant_total += $montantTotalProduit;
        $Commande->save();

        \DB::commit();

        return response()->json(['success' => 'Produit ajouté au Commande avec succès.'], 200);
    } catch (\Exception $e) {
        \DB::rollBack();

        return response()->json(['error' => 'Une erreur est survenue lors de l\'ajout du produit au Commande : ' . $e->getMessage()], 500);
    }
}



//valider une commande
public function validerCommande(Request $request)
    {
        $Commande = Commande::where('user_id', $request->user()->id)
                        ->where('etat_commande', 'en cours')
                        ->first();

        if (!$Commande) {
            return response()->json(['error' => 'Commande non trouvé.'], 404);
        }

        \DB::beginTransaction();

        try {
            $Commande->validerCommande();
            \DB::commit();

            return response()->json(['success' => 'Commande validée avec succès.'], 200);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json(['error' => 'Une erreur est survenue lors de la validation de la commande : ' . $e->getMessage()], 500);
        }
    }


//Expredier une commande 

    public function expedierCommande(Request $request)
    {
        $Commande = Commande::where('user_id', $request->user()->id)
                        ->where('etat_commande', 'en cours')
                        ->first();

        if (!$Commande) {
            return response()->json(['error' => 'Commande non trouvée ou déjà expédiée.'], 404);
        }

        \DB::beginTransaction();

        try {
            $Commande->expedierCommande();
            \DB::commit();

            return response()->json(['success' => 'Commande expédiée avec succès.'], 200);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json(['error' => 'Une erreur est survenue lors de l\'expédition de la commande : ' . $e->getMessage()], 500);
        }
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
