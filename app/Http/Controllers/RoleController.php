<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return response()->json($roles);
    }

    // creer un role dans mon api
    public function store(Request $request)
    {
        $role = Role::create(['name' => $request->name]);
        return response()->json([
           'message'=> 'Le role a bien été crée',
           'role'=> $role
        ]);
    }

    // supprimer un role dans mon api
    public function destroy($id)
    {
        $role = Role::find($id);
        
        // Liste des rôles qui ne doivent pas être supprimés
       $protectedRoles = ['admin', 'client', 'producteur'];

       if (in_array($role->name, $protectedRoles)) {
         return response()->json(['Le role ' . $role->name . ' ne peut pas être supprimé', $role],200);
       }
       $role->delete();
       return response()->json(['message'=> 'Le role a bien été supprimé'],200);
    
    }
    public function getPermissions($roleId)
    {
        $role = Role::findOrFail($roleId);
        $permissions = $role->permissions;
    
        // Ajoutez ce log pour vérifier ce que vous récupérez
        \Log::info('Permissions for role ID ' . $roleId . ': ', $permissions->toArray());
    
        return response()->json([
            'status' => true,
            'data' => $permissions,
        ]);
    }
    
    
    // modifier un role dans mon api
    public function update(Request $request, $id)
{
    // Validation des données
    $request->validate([
        'name' => 'required|string|max:255', // Ajustez la validation selon vos besoins
    ]);

    // Recherche du rôle
    $role = Role::find($id);
    
    // Vérification si le rôle existe
    if (!$role) {
        return response()->json([
            'message' => 'Le rôle spécifié n\'existe pas.'
        ], 404);
    }

    // Rôles protégés que l'on ne peut pas modifier
    $protectedRoles = ['admin', 'client', 'producteur'];
    
    if (in_array($role->name, $protectedRoles)) {
        return response()->json([
            'message' => 'Modification de ce rôle non autorisée.'
        ], 403);
    }

    // Mise à jour du nom du rôle
    $role->update(['name' => $request->name]);

    return response()->json([
        'message' => 'Le rôle a bien été modifié.',
        'role' => $role
    ], 200);
}


public function givePermissions(Request $request, $roleId)
{
    // Debug: voir le contenu de la requête
    \Log::info('Request Data:', $request->all());

    // Valider la requête pour s'assurer que les permissions sont fournies
    $validatedData = Validator::make($request->all(), [
        'permissionIds' => 'required|array',
        'permissionIds.*' => 'integer|exists:permissions,id', 
    ]);

    if ($validatedData->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation error',
            'data' => $validatedData->errors()
        ], 400);
    }

    $permissionIds = $validatedData->validated()['permissionIds'];

    try {
        // Trouver le rôle par ID
        $role = Role::findOrFail($roleId);

        // Attacher les permissions au rôle
        $role->permissions()->syncWithoutDetaching($permissionIds); // Utilisez le tableau

        return response()->json([
            'status' => true,
            'message' => 'Permissions successfully added'
        ], 200);
    } catch (\Exception $e) {
        // Log l'erreur et retourner une réponse d'erreur
        \Log::error('Error assigning permission: ' . $e->getMessage());

        return response()->json([
            'status' => false,
            'message' => 'An error occurred while assigning the permissions',
            'error' => $e->getMessage()
        ], 500);
    }
}



}
