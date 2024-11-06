<?php

namespace App\Http\Controllers;

use App\Mail\ContactNotification;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        // Validation des données
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // Création d'un message de contact
        Contact::create($request->all());
        //Envoi de l'email de notification à l'administrateur
        $adminEmail='annamarone72@gmail.com';
        Mail::to($adminEmail)->send(new ContactNotification($request->all()));

        // Retourner une réponse JSON
        return response()->json(['message' => 'Votre message a été envoyé avec succès !'], 200);
    }
}
