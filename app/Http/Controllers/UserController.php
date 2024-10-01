<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Producteur;

class UserController extends Controller
{
    // Liste des utilisateurs avec le rôle 'producteur'
    public function index()
    {
        
        $utilisateurs = User::where('role', 'producteur')->get(); 
        return response()->json($utilisateurs);
    }

   
    public function show($id)
    {
        $utilisateur = User::findOrFail($id); 
        return response()->json($utilisateur);
    }
}

