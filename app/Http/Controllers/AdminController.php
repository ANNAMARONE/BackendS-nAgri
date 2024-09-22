<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        // Charge les utilisateurs avec leurs rôles
        $users = User::with('roles')->get();
        
        // Retourne la réponse JSON personnalisée avec les utilisateurs et leurs rôles
        return response()->json($users);
    }
    
    // Trouver un utilisateur par son ID
    public function show($id)
    {
        // Trouver l'utilisateur avec ses rôles associés
        $user = User::with('roles')->find($id);
        
        // Vérifiez si l'utilisateur existe
        if (!$user) {
          return response()->json(['message'=> 'Utilisateur non trouvé',404]);
        }
        
        // Retourner la réponse JSON avec l'utilisateur et ses rôles
        return response()->json($user);
    }
    
    
    
        // Supprimer un utilisateur
        public function destroy($id)
        {
            $user = User::find($id);
            $user->delete();
           return response()->json(['message'=> 'utilisateur supprimer avec succés',200]);
        }
    
        // Changer le role d'un utilisateur avec assign role (coach ou entrepreneur)
        public function changeRole(Request $request, $id)
        {
            $user = User::find($id);
        
            if (!$user) {
                return response()->json(['message' => 'Utilisateur non trouvé'], 404);
            }
            if ($request->has('role')) {
                $user->role = $request->role;
                $user->save();
            }
        
            // Mettre à jour les rôles associés
            if ($request->has('roles')) {
               
                $user->syncRoles($request->roles);
            } else if ($request->has('role')) {
                // Ajouter un seul rôle
                $user->assignRole($request->role);
            }
        
            return response()->json(['message' => 'Rôle(s) mis à jour'], 200);
        }
        
    
        // Activer un utilisateur (en changeant la valeur de son status à 1)
        public function activate($id)
        {
            $user = User::find($id);
            $user->statut = 1;
            $user->save();
           return response()->json(['message'=> 'Utilisateur active',200]);
        }
    
       // Desactiver un utilisateur (en changeant la valeur de son status à 0)
        public function deactivate($id)
        {
            $user = User::find($id);
            $user->statut = 0;
            $user->save();
            return response()->json(['message'=> 'Utilisateur desactive',200]);
        } 
     
}
