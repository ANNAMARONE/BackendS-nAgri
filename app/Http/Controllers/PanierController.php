<?php

namespace App\Http\Controllers;
use App\Models\Panier;
use App\Models\Produit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StorepanierRequest;
use App\Http\Requests\UpdatepanierRequest;

class PanierController extends Controller
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

        // Récupérer tous les paniers associés à cet utilisateur
        $paniers = Panier::where('user_id', $user->id)
                         ->where('etat_commande', 'en cours')
                         ->with('produit') // Inclure les informations du produit
                         ->get();

        // Retourner les paniers sous forme de réponse JSON
        return response()->json($paniers);
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Récupère l'utilisateur actuellement connecté
        $user = Auth::user();
    
        // Vérifie si l'utilisateur est authentifié
        if (!$user) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }
    
        // Crée un nouveau panier en utilisant l'ID de l'utilisateur connecté
        $panier = Panier::create([
            'user_id' => $user->id 
        ]);
    
        return response()->json($panier, 201);
    }
    
// Exemple de fonction pour ajouter un produit au panier
public function ajouterProduitAuPanier(Request $request)
{
    $request->validate([
        'produit_id' => 'required|exists:produits,id',
        'quantite' => 'required|integer|min:1'
    ]);

    $produitId = $request->input('produit_id');
    $quantite = $request->input('quantite');

    \DB::beginTransaction();

    try {
        // Trouver le produit
        $produit = Produit::find($produitId);
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

        // Récupérer ou créer le panier en cours pour l'utilisateur
        $panier = Panier::where('user_id', $request->user()->id)
                        ->where('etat_commande', 'en cours')
                        ->first();

        if (!$panier) {
            // Créer un nouveau panier si aucun n'existe
            $panier = Panier::create([
                'user_id' => $request->user()->id,
                'etat_commande' => 'en cours',
                'montant_total' => 0 
            ]);
        }

        // Vérifier si le produit est déjà dans le panier
        $existingProduct = Panier::where('user_id', $request->user()->id)
                                 ->where('produit_id', $produitId)
                                 ->where('etat_commande', 'en cours')
                                 ->first();

        if ($existingProduct) {
            // Mettre à jour la quantité et le prix si le produit est déjà dans le panier
            $existingProduct->quantite += $quantite;
            $existingProduct->prix_unitaire = $prixUnitaire;
            $existingProduct->montant_total += $montantTotalProduit;
            $existingProduct->save();
        } else {
            // Ajouter le produit au panier
            Panier::create([
                'user_id' => $request->user()->id,
                'produit_id' => $produitId,
                'quantite' => $quantite,
                'prix_unitaire' => $prixUnitaire,
                'etat_commande' => 'en cours',
                'montant_total' => $montantTotalProduit
            ]);
        }

        // Mettre à jour le montant total du panier
        $panier->montant_total += $montantTotalProduit;
        $panier->save();

        \DB::commit();

        return response()->json(['success' => 'Produit ajouté au panier avec succès.'], 200);
    } catch (\Exception $e) {
        \DB::rollBack();

        return response()->json(['error' => 'Une erreur est survenue lors de l\'ajout du produit au panier : ' . $e->getMessage()], 500);
    }
}

//calculer le montant totale des produit du panier
public function calculerMontantTotalProduit(Request $request){
    $userId=Auth::id();
    $panier=Panier::where('user_id', $userId)
   ->where('etat_commande', 'en cours')
   ->get();
   $montantTotal=$panier->sum('montant_total');
   return response()->json([
    'montant_total'=>$montantTotal
], 200);
}
public function validerTousLesPaniers(Request $request)
{
    if (!$request->user()) {
        return response()->json(['error' => 'Utilisateur non authentifié'], 401);
    }

    $userId = $request->user()->id;
    // Récupérer tous les paniers en cours de l'utilisateur
    $paniers = Panier::where('user_id', $userId)
                     ->where('etat_commande', 'en cours')
                     ->get();

    if ($paniers->isEmpty()) {
        return response()->json(['error' => 'Aucun panier en cours trouvé.'], 404);
    }

    foreach ($paniers as $panier) {
        $panier->etat_commande = 'validée';
        $panier->save();
    }

    return response()->json(['success' => 'Tous les paniers ont été validés avec succès.'], 200);
}


public function expedierPanier(Request $request){
    $userId=Auth::id();
    $panier=Panier::where("user_id", $userId)
    ->where('etat_commande', 'validée')
    ->get();
    if($panier->isEmpty()){
        return response()->json(['error'=> 'Aucun panier validé trouvé'],404);
    }
    foreach($panier as $item){
        $item->etat_commande= 'expédiée';
        $item->save();
    }
    return response()->json(['success'=> 'panier expédié avec succés.'], 200);
}
public function supprimerProduit(Request $request, $panierId, $produitId)
{
    if (!$request->user()) {
        return response()->json(['error' => 'Utilisateur non authentifié'], 401);
    }

    // Trouver le panier avec l'ID donné
    $panier = Panier::where('id', $panierId)
                    ->where('user_id', $request->user()->id)
                    ->where('etat_commande', 'en cours') 
                    ->first();

    if (!$panier) {
        return response()->json(['error' => 'Panier non trouvé.'], 404);
    }

    // Vérifier si le produit est dans le panier
    $produit = $panier->produit; 

    if (!$produit || $produit->id != $produitId) {
        return response()->json(['error' => 'Produit non trouvé dans le panier.'], 404);
    }

    // Supprimer le produit du panier
    $panier->delete(); 

    return response()->json(['success' => 'Produit supprimé du panier avec succès.'], 200);
}

   
public function update(Request $request, $panierId)
{
    $request->validate([
        'quantite' => 'required|integer',
    ]);

    $panier = Panier::where('id', $panierId)
                    ->where('user_id', Auth::id())
                    ->where('etat_commande', 'en cours')
                    ->first();

    if (!$panier) {
        return response()->json(['error' => 'Panier non trouvé.'], 404);
    }

    $panier->quantite = $request->input('quantite');
    $panier->montant_total = $panier->quantite * $panier->prix_unitaire;
    $panier->save();

    return response()->json(['success' => 'Panier mis à jour avec succès.'], 200);
}



}
