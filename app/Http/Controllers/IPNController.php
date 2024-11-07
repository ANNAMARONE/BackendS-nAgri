<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;

class IPNController extends Controller
{
    public function handleIPN(Request $request)
    {
        // Enregistrer la notification pour débogage
        Log::info('Notification IPN reçue : ', $request->all());
    
        // Récupérer les données de la notification
        $data = $request->all();
    
        // Vérifier que la notification contient les informations nécessaires
        if (isset($data['status']) && $data['status'] == 'completed') {
            // Créer ou mettre à jour le paiement
            Payment::updateOrCreate(
                ['transaction_id' => $data['transaction_id']], 
                [
                    'payment_status' => 'completed', 
                    'total_amount' => $data['amount'], 
                ]
            );
        }
    
        // Répondre avec un code HTTP 200
        return response()->json(['message' => 'IPN reçu et traité avec succès'], 200);
    }
    
}
