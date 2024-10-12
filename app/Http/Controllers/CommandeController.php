<?php

namespace App\Http\Controllers;
use App\Models\Payment;

use App\Models\Produit;
use App\Models\Commande;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreCommandeRequest;
use App\Http\Requests\UpdateCommandeRequest;
use \Illuminate\Validation\ValidationException;

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
    {
        try {
            // Valider les données de la requête
            $validatedData = $request->validate([
                'montant_total' => 'required|numeric',
                'produits' => 'required|array',
                'produits.*.produit_id' => 'required|exists:produits,id',
                'produits.*.quantite' => 'required|integer|min:1',
                'payment_method' => 'required|in:en_ligne,Paiement à la livraison',
            ]);
    
            // Récupérer l'utilisateur connecté
            $user = Auth::user();
    
            // Créer une nouvelle commande
            $commande = new Commande();
            $commande->user_id = $user->id;
            $commande->references = 'REF-' . strtoupper(uniqid()); 
            $commande->montant_total = 0; 
            $commande->status_de_commande = 'en_attente'; 
            $commande->save();
    
            // Variable pour calculer le montant total de la commande
            $montantTotal = 0;
    
            foreach ($validatedData['produits'] as $produitData) {
                // Récupérer le produit pour obtenir son prix unitaire
                $produit = Produit::findOrFail($produitData['produit_id']);
                $quantite = $produitData['quantite'];
                $montantProduit = $produit->prix * $quantite; 
    
                // Ajouter le produit à la commande avec la quantité et le montant
                $commande->produits()->attach($produit->id, [
                    'quantite' => $quantite,
                    'montant' => $montantProduit,
                ]);
    
                $montantTotal += $montantProduit;
            }
    
            // Mettre à jour le montant total de la commande après avoir ajouté tous les produits
            $commande->montant_total = $montantTotal;
            $commande->save();
    
            // Enregistrer la méthode de paiement
            $payment = new Payment();
            $payment->commande_id = $commande->id; 
            $payment->payment_method = $validatedData['payment_method']; 
            $payment->amount = $commande->montant_total; 
            $payment->payment_status = 'en_attente'; 
            $payment->save();
    
            // Gérer le paiement en ligne avec PayDunya
            if ($validatedData['payment_method'] === 'en_ligne') {
                // Créer une facture PayDunya
                $invoice = new \Paydunya\Checkout\CheckoutInvoice();
                $invoice->setCurrency('XOF'); // Définir la devise
                $invoice->setTotalAmount($montantTotal); // Montant total
                $invoice->setDescription('Paiement pour la commande ' . $commande->references); // Description
                $invoice->addCustomer($user->name, $user->email, $user->phone); // Informations sur le client
                
                // URL de retour après paiement
                $invoice->setReturnUrl(route('payment.success', ['commande' => $commande->id]));
                $invoice->setCancelUrl(route('payment.cancel', ['commande' => $commande->id]));
    
                // Créer l'invoice et obtenir le lien de paiement
                $paymentLink = $invoice->create(); // Si tout est correct, cela retournera l'URL de paiement
    
                if ($paymentLink) {
                    return response()->json([
                        'message' => 'Commande créée avec succès. Veuillez procéder au paiement.',
                        'payment_link' => $paymentLink, // URL de paiement
                        'commande' => $commande,
                    ], 201);
                } else {
                    return response()->json([
                        'message' => 'Erreur lors de la création de la facture de paiement.',
                    ], 500);
                }
            }
    
            return response()->json([
                'message' => 'Commande créée avec succès, paiement à la livraison.',
                'commande' => $commande,
            ], 201);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de la création de la commande',
                'error' => $e->getMessage(),
            ], 500);
        }
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
public function supprimerCommande(Request $request,$id){
   $Commande = Commande::findOrFail($id)->delete();
   return response()->json([
    'commande'=> $Commande,
   ],200);

}
}