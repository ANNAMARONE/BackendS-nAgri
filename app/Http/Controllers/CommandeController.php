<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Payment;

use App\Models\Produit;
use App\Models\Commande;
use App\Mail\CommandeInfo;
use Illuminate\Http\Request;
use App\Mail\CommandeCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Paydunya\Checkout\CheckoutInvoice;
use App\Http\Requests\StoreCommandeRequest;
use App\Http\Requests\UpdateCommandeRequest;
use \Illuminate\Validation\ValidationException;

class CommandeController extends Controller

{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    try {
        $user = Auth::user();

        // Vérifier si l'utilisateur est connecté
        if (!$user) {
            return response()->json([
                'message' => 'Utilisateur non connecté.'
            ], 401); // Non autorisé
        }

        // Récupérer les commandes faites par l'utilisateur connecté avec les produits associés
        $commandes = Commande::where('user_id', $user->id)
            ->with('produits') // Assurez-vous que cette relation est bien définie dans le modèle
            ->get();

        if ($commandes->isEmpty()) {
            return response()->json([
                'message' => 'Aucune commande trouvée pour cet utilisateur.',
                'commandes' => []
            ], 200);
        }

        // Retourner les commandes avec les produits associés
        return response()->json([
            'message' => 'Liste de vos commandes',
            'commandes' => $commandes
        ], 200);
    } catch (\Exception $e) {
        // Log de l'erreur pour le débogage
        \Log::error('Erreur lors de la récupération des commandes: ' . $e->getMessage());

        return response()->json([
            'message' => 'Erreur lors de la récupération des commandes. Veuillez réessayer.'
        ], 500); // Erreur interne du serveur
    }
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
        

            // Vérifier si le stock est suffisant
            if ($produit->quantite < $quantite) {
                return response()->json(['message' => 'Quantité insuffisante pour le produit: ' . $produit->nom], 400);
            }

            // Décrémenter la quantité du produit
            $produit->decrementerQuantite($quantite);

              // Vérifier si la quantité est maintenant 0 et mettre à jour le statut
                if ($produit->quantite == 0) {
                    $produit->statut = 'en rupture';
                    $produit->save(); 
                }
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
                'montant_total' => $montantTotal 
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
        $this->sendCommandeInfo($commande->id);
        if ($commande->payment_method == "Paiement à la livraison") {
            Mail::to($user->email)->send(new CommandeCreated($commande, "Paiement à la livraison"));
        }
        
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

                // Mettez à jour le paiem   ent avec le lien de transaction
                $payment->transaction_id = $invoice->getInvoiceUrl(); 
                $payment->save();
                Mail::to($user->email)->send(new CommandeCreated($commande,$paymentLink));            
       
           
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
                    'montant_total' => $montantTotal 
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

public function handleCallback(Request $request) {
    // Traitez les données de la callback ici
    // Par exemple, vérifier l'état du paiement
    $data = $request->all();

    // Vérifiez si les données sont bien reçues
    Log::info('Callback reçu:', $data);

    return response()->json(['status' => 'success']);
}

public function sendCommandeInfo($commandeId)
{
    $commande = Commande::with('produits.user')->find($commandeId);

    if (!$commande) {
        return response()->json(['message' => 'Commande non trouvée.'], 404);
    }

    // Récupérer les utilisateurs uniques ayant ajouté les produits commandés
    $utilisateurs = $commande->produits->pluck('user_id')->unique();

    foreach ($utilisateurs as $userId) {
        // Récupérer l'objet utilisateur complet
        $user = User::find($userId);

        if (!$user) {
            continue; 
        }

        // Filtrer les produits pour l'utilisateur actuel
        $produitsUser = $commande->produits->filter(function($produit) use ($userId) {
            return $produit->user_id == $userId;
        });

        // Calculer le montant total pour les produits de cet utilisateur
        $montantTotal = $produitsUser->sum(function($produit) {
            return $produit->pivot->quantite * $produit->prix;
        });

        // Envoyer l'e-mail à l'utilisateur actuel
        Mail::to($user->email)->send(new CommandeInfo($commande, $produitsUser, $montantTotal));
    }
}
    

    
public function success(Request $request, $commandeId)
{
    try {
        $commande = Commande::findOrFail($commandeId);

        if (!$request->has('token')) {
            return response()->json(['message' => 'Token de paiement manquant.'], 400);
        }

        $token = $request->input('token');
        $invoice = new CheckoutInvoice();

        if ($invoice->confirm($token)) {
            if ($invoice->getStatus() === 'completed') {
                // Met à jour le statut de la commande et du paiement
                $commande->status_de_commande = 'payé';
                $commande->save();

                $payment = Payment::where('commande_id', $commande->id)->first();
                $payment->payment_status = 'completed';
                $payment->save();

                // Redirige l'utilisateur vers la page de la commande
                return redirect()->route('commande.show', ['commande' => $commande->id])
                    ->with('success', 'Paiement réussi et commande mise à jour.');
            } else {
                return response()->json(['message' => 'Le paiement n\'est pas encore confirmé.', 'status' => $invoice->getStatus()], 400);
            }
        }

        return response()->json(['message' => 'Paiement non confirmé ou annulé.', 'status' => $invoice->getStatus()], 400);
    } catch (\Exception $e) {
        return response()->json(['message' => 'Une erreur est survenue lors du traitement du paiement.', 'error' => $e->getMessage()], 500);
    }
}



    /**
     * Gestion du retour d'annulation de paiement.
     */
    public function cancel(Request $request, $commandeId)
    {
        try {
            // Retrieve the cancelled order
            $commande = Commande::findOrFail($commandeId);
    
            // Update the status of the order to cancelled
            $commande->status_de_commande = 'annulée';
            $commande->save();
    
            // Return a response indicating that the payment was cancelled
            return response()->json([
                'message' => 'Paiement annulé.',
                'commande' => $commande,
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de l\'annulation.',
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

public function destroy($id)
{
    // Vérifiez si la commande existe
    $commande = Commande::find($id);

    if (!$commande) {
        return response()->json(['message' => 'Commande non trouvée.'], 404);
    }

    // Vérifiez si l'utilisateur connecté est le propriétaire de la commande
    if ($commande->user_id !== Auth::id()) {
        return response()->json(['message' => 'Vous n\'êtes pas autorisé à supprimer cette commande.'], 403);
    }

    // Supprimez la commande
    $commande->delete();

    return response()->json(['message' => 'Commande supprimée avec succès.'], 200);
}


//supprimer un produit au Commande

public function AfficherMesCommande() {
    $user = Auth::user();

    // Récupérer les IDs des produits ajoutés par l'utilisateur connecté
    $ProduitUser = $user->produits->pluck('id');

    // Vérifier si l'utilisateur a des produits
    if ($ProduitUser->isEmpty()) {
        return response()->json([
            'message' => 'Aucune commande trouvée',
            'commandes' => []
        ]);
    }

    // Récupérer les commandes dont le statut est 'en_attente' et qui contiennent les produits de l'utilisateur
    $commandes = Commande::where('status_de_commande', 'en_cours') // Filtrer par statut
        ->whereHas('produits', function($query) use ($ProduitUser) {
            $query->whereIn('produit_id', $ProduitUser);
        })
        ->with('produits') // Charger les produits associés
        ->get();

    // Retourner les commandes sous forme de JSON
    return response()->json([
        'message' => 'Liste de mes commandes en attente',
        'commandes' => $commandes
    ]);
}

public function TraiterCommande(Request $request,$id){
$commande=Commande::find($id);
if(!$commande){
    return response()->json([
        'message'=>'commande non trouvée'
    ],404);
}
$request->validate([
    'statut'=>'required|in:invalide,en_attente,en_cours,expediee,livree'
]);
$commande->statut=$request->statut;
$commande->save();
return response()->json([
    'message'=>'le statut de la commande a été mis à jour avec succés.',
    'statut'=>$commande->statut
],200);
}
public function supprimerCommande($id){
$commande=Commande::find($id);
if(!$commande){
    return response()->json([
        'message'=>'Commande non trouvé.'
    ],404);
}
$commande->delete();
return response()->json([
    'message'=>'Commande supprimé avec succés.'
],200);
}
// permettre un client de modifier c'est commandes
public function updateStatus(Request $request, $id)
{
    Log::info("Tentative de mise à jour du statut de la commande ID: $id"); 
    $commande = Commande::find($id);

    $request->validate([
        'status_de_commande' => 'required|in:invalide,en_attente,en_cours,expediee,livree',
    ]);
    
    $commande->status_de_commande = $request->status_de_commande;
    $commande->save();

    return response()->json(['message' => 'Statut de la commande mis à jour avec succès']);
}

//historique des commande du producteur connecter
public function AfficherCommandesProduitsUser() {
    $user = Auth::user();

    // Récupérer les IDs des produits ajoutés par l'utilisateur connecté
    $produitUserIds = $user->produits->pluck('id');

    // Vérifier si l'utilisateur a des produits
    if ($produitUserIds->isEmpty()) {
        return response()->json([
            'message' => 'Aucune commande trouvée.',
            'commandes' => []
        ]);
    }

    // Récupérer toutes les commandes contenant les produits de l'utilisateur et inclure les informations du client
    $commandes = Commande::whereHas('produits', function($query) use ($produitUserIds) {
        $query->whereIn('produit_id', $produitUserIds);
    })
    ->with(['produits', 'user']) 
    ->get();

    // Formatage des commandes pour inclure les informations du client
    $commandesFormatees = $commandes->map(function($commande) {
        return [
            'id' => $commande->id,
            'status_de_commande' => $commande->status_de_commande,
            'date' => $commande->created_at->toDateString(),
            'client' => [
                'nom' => $commande->user->name, 
                'email' => $commande->user->email,
                'telephone' => $commande->user->telephone,
                'adresse'=>$commande->user->adresse
            ],
            'produits' => $commande->produits->map(function($produit) {
                return [
                    'id' => $produit->id,
                    'libelle' => $produit->libelle,
                    'prix' => $produit->prix,
                    'image' => $produit->image
                ];
            })
        ];
    });

    // Retourner les commandes sous forme de JSON
    return response()->json([
        'message' => 'Liste des commandes concernant vos produits',
        'commandes' => $commandesFormatees
    ]);
}

public function commandePourAdmin()
{
    $commandes = Commande::whereHas('produits') 
        ->with('produits',"user")
        ->get();

    return response()->json([
        'message' => 'Liste de toutes les commandes',
        'commandes' => $commandes
    ]);
}


}