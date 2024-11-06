<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayPalController extends Controller
{
    public function createOrder(Request $request)
    {
        // Assurez-vous que commande_id et montant_total sont passés dans la requête
        $commandeId = $request->input('commande_id');
        $montantTotalString = (string) $request->input('montant_total');
    
        $provider = new PayPalClient;
    
        // Configuration
        $config = config('paypal.sandbox');
    
        // Vérifiez que la configuration est correcte
        if (empty($config['client_id']) || empty($config['client_secret'])) {
            return response()->json(['message' => 'Configuration PayPal invalide. Assurez-vous que client_id et client_secret sont définis.'], 500);
        }
    
        // Obtenez un token d'accès
        try {
            $accessToken = $provider->getAccessToken();
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de l\'obtention du token d\'accès: ' . $e->getMessage()], 500);
        }
    
        if (!$accessToken) {
            return response()->json(['message' => 'Impossible d\'obtenir un token d\'accès'], 500);
        }
    
        // Créer une commande
        try {
            $order = $provider->createOrder([
                "intent" => "CAPTURE",
                "purchase_units" => [
                    [
                        "amount" => [
                            "currency_code" => "EUR", // Changer USD à XOF
                            "value" => $montantTotalString,
                        ],
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la création de la commande: ' . $e->getMessage()], 500);
        }
    
        // Sauvegarder l'ID de commande PayPal pour un usage futur
        // (Vous pouvez mettre à jour la commande ici si nécessaire)
    
        return response()->json($order);
    }
    
    
    
    

    public function captureOrder(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->setAccessToken($provider->getAccessToken());
    
        // Capturer le paiement
        $result = $provider->capturePaymentOrder($request->orderID);
    
        // Vérifiez le statut de la commande
        if ($result['status'] === 'COMPLETED') {
            // Créer un enregistrement de paiement
            Payment::create([
                'commande_id' => $request->commande_id, // Assurez-vous que l'ID de la commande est passé dans la requête
                'payment_method' => 'PayPal',
                'amount' => $result['purchase_units'][0]['payments']['captures'][0]['amount']['value'],
                'currency' => $result['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'],
                'payment_status' => $result['status'],
                'transaction_id' => $result['purchase_units'][0]['payments']['captures'][0]['id'],
            ]);
        }
    
        return response()->json($result);
    }
    

}
