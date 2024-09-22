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
                         ->with('produits') // Inclure les informations du produit
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
        'quantite' => 'required|integer|min:1',
        'reference' => 'nullable|string|max:255'
    ]);

    $produitId = $request->input('produit_id');
    $quantite = $request->input('quantite');
    $reference = $request->input('reference') ?? 'REF-' . strtoupper(uniqid());

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
        $panier = Panier::firstOrCreate(
            ['user_id' => $request->user()->id, 'etat_commande' => 'en cours'],
            ['reference' => 'REF-' . strtoupper(uniqid()), 'montant_total' => 0]
        );

        // Vérifier si le produit est déjà dans le panier
        $existingProduct = $panier->produits()->where('produit_id', $produitId)->first();

        if ($existingProduct) {
            // Mettre à jour la quantité et le prix si le produit est déjà dans le panier
            $existingProduct->pivot->quantite += $quantite;
            $existingProduct->pivot->montant_total += $montantTotalProduit;
            $existingProduct->pivot->save();
        } else {
            // Ajouter le produit au panier
            $panier->produits()->attach($produitId, [
                'quantite' => $quantite,
                'prix_unitaire' => $prixUnitaire,
                'montant_total' => $montantTotalProduit,
                'reference' => $reference
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



//valider une commande
public function validerCommande(Request $request)
    {
        $panier = Panier::where('user_id', $request->user()->id)
                        ->where('etat_commande', 'en cours')
                        ->first();

        if (!$panier) {
            return response()->json(['error' => 'Panier non trouvé.'], 404);
        }

        \DB::beginTransaction();

        try {
            $panier->validerCommande();
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
        $panier = Panier::where('user_id', $request->user()->id)
                        ->where('etat_commande', 'en cours')
                        ->first();

        if (!$panier) {
            return response()->json(['error' => 'Commande non trouvée ou déjà expédiée.'], 404);
        }

        \DB::beginTransaction();

        try {
            $panier->expedierCommande();
            \DB::commit();

            return response()->json(['success' => 'Commande expédiée avec succès.'], 200);
        } catch (\Exception $e) {
            \DB::rollBack();

            return response()->json(['error' => 'Une erreur est survenue lors de l\'expédition de la commande : ' . $e->getMessage()], 500);
        }
    }


//supprimer un produit au panier
public function supprimerProduit(Request $request, $id)
{
    $panier = Panier::where('user_id', $request->user()->id)
                    ->where('etat_commande', 'en cours')
                    ->first();

    if (!$panier) {
        return response()->json(['error' => 'Panier non trouvé.'], 404);
    }

    \DB::beginTransaction();

    try {
        $panier->supprimerProduit($id);
        \DB::commit();

        return response()->json(['success' => 'Produit supprimé du panier avec succès.'], 200);
    } catch (\Exception $e) {
        \DB::rollBack();

        return response()->json(['error' => 'Une erreur est survenue lors de la suppression du produit du panier : ' . $e->getMessage()], 500);
    }
}

   //modifier un panier
   public function update(Request $request, $id)
   {
       $request->validate([
           'quantite' => 'required|integer',
       ]);
   
       $panier = Panier::where('user_id', $request->user()->id)
                       ->where('etat_commande', 'en cours')
                       ->first();
   
       if (!$panier) {
           return response()->json(['error' => 'Panier non trouvé.'], 404);
       }
   
       \DB::beginTransaction();
   
       try {
           // Appel de la méthode modifierProduit avec la nouvelle quantité
           $panier->modifierProduit($id, $request->input('quantite'));
           \DB::commit();
   
           return response()->json(['success' => 'Panier modifié avec succès.'], 200);
       } catch (\Exception $e) {
           \DB::rollBack();
   
           return response()->json(['error' => 'Une erreur est survenue lors de la modification du panier : ' . $e->getMessage()], 500);
       }
   }
   

public function afficherPanier(Request $request)
{
    $panier = Panier::where('user_id', $request->user()->id)
                    ->where('etat_commande', 'en cours')
                    ->first();

    if (!$panier) {
        return response()->json(['error' => 'Panier non trouvé.'], 404);
    }

    $montantTotal = $panier->calculerMontantTotal();

    return response()->json([
        'panier' => $panier,
        'montant_total' => $montantTotal
    ], 200);
}


}
