<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayPalController extends Controller
{
    public function createOrder(Request $request)
    {
        // Debug: Afficher toutes les données reçues
        // dd($request->all());
    
        // Instanciez le client PayPal
        $provider = new PayPalClient;
    
        // Configuration
        $config = config('paypal.sandbox');
    
        // Vérifiez que la configuration est correcte
        if (empty($config['client_id']) || empty($config['client_secret'])) {
            return response()->json(['message' => 'Configuration PayPal invalide. Assurez-vous que client_id et client_secret sont définis.'], 500);
        }
    
        // Vérifiez que le montant total est présent
        if (empty($request->montant_total)) {
            return response()->json(['message' => 'Le montant total est requis.'], 400);
        }
    
        // Convertir le montant total en chaîne de caractères
        $montantTotalString = (string) $request->montant_total;
    
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
                            "currency_code" => "USD",
                            "value" => $montantTotalString, // Utilisez la chaîne convertie ici
                        ]
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur lors de la création de la commande: ' . $e->getMessage()], 500);
        }
    
        return response()->json($order);
    }
    
    
    

    public function captureOrder(Request $request)
{
    $provider = new PayPalClient;
    $provider->setApiCredentials(config('paypal'));
    $provider->setAccessToken($provider->getAccessToken());

    $result = $provider->capturePaymentOrder($request->orderID);

    // Créer un enregistrement de paiement
    if ($result['status'] === 'COMPLETED') {
        Payment::create([
            'order_id' => $request->order_id, // Assurez-vous que l'ID de la commande est passé dans la requête
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
