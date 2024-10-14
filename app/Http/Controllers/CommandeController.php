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
use Paydunya\Checkout\CheckoutInvoice;
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
        $commande->payment_method = $validatedData['payment_method'];
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

        // Vérifiez ici que le montant total est correct
        if ($montantTotal < 200) {
            return response()->json([
                'message' => 'Le montant total doit être supérieur ou égal à 200 FCFA.',
                'montant_total' => $montantTotal // Ajoutez le montant total dans la réponse
            ], 400);
        }

        // Mettre à jour le montant total de la commande après avoir ajouté tous les produits
        $commande->montant_total = $montantTotal;
        $commande->save();

        // Enregistrer la méthode de paiement
        $payment = new Payment();
        $payment->commande_id = $commande->id;
        $payment->payment_method = $validatedData['payment_method'];
        $payment->total_amount = $commande->montant_total;
        $payment->payment_status = 'en_attente';
        $payment->save();

        // Gérer le paiement en ligne avec PayDunya
        if ($validatedData['payment_method'] === 'en_ligne') {
            // Configurer PayDunya
            \Paydunya\Setup::setMasterKey(config('paydunya.master_key'));
            \Paydunya\Setup::setPrivateKey(config('paydunya.private_key'));
            \Paydunya\Setup::setToken(config('paydunya.token'));
            \Paydunya\Setup::setPublicKey(config('paydunya.public_key'));
            \Paydunya\Setup::setMode(config('paydunya.mode'));

            // Créer une facture PayDunya
            
            $invoice = new CheckoutInvoice();
            $invoice->addItem("Commande " . $commande->references, 1, $montantTotal, $montantTotal, "Paiement pour la commande " . $commande->references);
            $invoice->setDescription('Paiement pour la commande ' . $commande->references);
            $invoice->setTotalAmount($montantTotal);
            \Paydunya\Checkout\Store::setName("SénAgri");
            // Ajouter les informations du client
            $invoice->addCustomData('customer_name', $user->name);
            $invoice->addCustomData('customer_email', $user->email);
            $invoice->addCustomData('customer_phone', $user->phone);
            $invoice->getReceiptUrl();

            // URL de retour après paiement
            $invoice->setReturnUrl(route('payment.success', ['commande' => $commande->id]));
            $invoice->setCancelUrl(route('payment.cancel', ['commande' => $commande->id]));

            // Créer l'invoice et obtenir le lien de paiement
            if ($invoice->create()) {
                $paymentLink = $invoice->getInvoiceUrl();

                // Mettez à jour le paiement avec le lien de transaction
                $payment->transaction_id = $invoice->getInvoiceUrl(); // Utilisez la méthode correcte ici
                $payment->save();

                return response()->json([
                    'message' => 'Commande créée avec succès. Veuillez procéder au paiement.',
                    'payment_link' => $paymentLink,
                    'commande' => $commande,
                ], 201);
            } else {
                // Afficher le montant total dans l'erreur
                return response()->json([
                    'message' => 'Erreur lors de la création de la facture de paiement.',
                    'error' => $invoice->response_text,
                    'montant_total' => $montantTotal // Ajoutez le montant total
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

    
    
    

    
    
    public function success(Request $request, $commandeId)
    {
        try {
            // Récupérer la commande
            $commande = Commande::findOrFail($commandeId);

            // Vérifier si le token de paiement est présent
            if (!$request->has('token')) {
                return response()->json(['message' => 'Token de paiement manquant.'], 400);
            }

            $token = $request->input('token');
            $invoice = new CheckoutInvoice();

            // Confirmer le paiement avec le token
            if ($invoice->confirm($token)) {
                // Mettre à jour le statut de la commande et du paiement
                if ($invoice->getStatus() === 'completed') {
                    $commande->status_de_commande = 'payé';
                    $commande->save();

                    // Mettre à jour le statut du paiement
                    $payment = Payment::where('commande_id', $commande->id)->first();
                    $payment->payment_status = 'completed';
                    $payment->save();

                    return response()->json([
                        'message' => 'Paiement réussi.',
                        'commande' => $commande,
                        'receipt_url' => $invoice->getReceiptUrl()
                    ], 200);
                }
            }

            // Si le paiement n'est pas confirmé ou est annulé
            return response()->json([
                'message' => 'Paiement non confirmé ou annulé.',
                'status' => $invoice->getStatus()
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors du traitement du paiement.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Gestion du retour d'annulation de paiement.
     */
    public function cancel(Request $request, $commandeId)
    {
        try {
            // Récupérer la commande
            $commande = Commande::findOrFail($commandeId);

            // Mettre à jour le statut de la commande et du paiement
            $commande->status_de_commande = 'annulé';
            $commande->save();

            $payment = Payment::where('commande_id', $commande->id)->first();
            $payment->payment_status = 'cancelled';
            $payment->save();

            return response()->json([
                'message' => 'Le paiement a été annulé.',
                'commande' => $commande
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors du traitement de l\'annulation.',
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