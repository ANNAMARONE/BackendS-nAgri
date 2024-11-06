<?php

namespace App\Http\Controllers;

use App\Models\ressource;
use Illuminate\Http\Request;
use App\Models\categorieRessource;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreressourceRequest;
use App\Http\Requests\UpdateressourceRequest;

class RessourceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $ressources = Ressource::orderBy('created_at', 'desc')->paginate(10);
        return response()->json($ressources, 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Vérifiez si l'utilisateur est authentifié
        if (!$request->user()) {
            return response()->json(['error' => 'Veuillez vous connecter.'], 401);
        }
    
        // Validation des entrées
        $validator = Validator::make($request->all(), [
            'libelle' => 'required|string|max:255',
            'image' => 'required|mimes:jpeg,jpg,png|max:2048',
            'description' => 'required|string',
            'piéce_join' => 'required|file|mimes:pdf|max:2048',
            'categorie_ressource_id' => 'required|integer',
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
    
        // Création de la ressource
        $ressource = new Ressource();
        $ressource->fill($request->only(['libelle', 'description', 'categorie_ressource_id']));
    
        // Gestion du fichier PDF
        if ($request->hasFile('piéce_join')) {
            $file = $request->file('piéce_join');
            $filePath = $file->store('pdfs', 'public');
            $ressource->piéce_join = $filePath; 
        }
    
        // Gestion de l'image
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('images', $filename, 'public');
            $ressource->image = $path;
        }
    
        // Sauvegarde de la ressource
        $ressource->save();
    
        return response()->json([
            'message' => 'Article ajouté avec succès',
            'article' => $ressource
        ], 201);
    }
    

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $ressource = Ressource::find($id);
        
        if (!$ressource) {
            return response()->json(['message' => 'Ressource non trouvée.'], 404);
        }
    
        return response()->json($ressource, 200);
    }
    

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ressource $ressource)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Vérifiez si l'utilisateur est authentifié
        if (!$request->user()) {
            return response()->json(['error' => 'Veuillez vous connecter.'], 401);
        }
    
        // Trouvez la ressource par son ID ou renvoyez une erreur 404 si elle n'existe pas
        $ressource = Ressource::findOrFail($id);
    
        // Validation des données
        $validator = Validator::make($request->all(), [
            'libelle' => 'required|string|max:255',
            'image' => 'sometimes|nullable|mimes:jpeg,jpg,png|max:2048', 
            'description' => 'required|string',
            'piéce_join' => 'sometimes|nullable|file|mimes:pdf|max:2048',
            'categorie_ressource_id' => 'required|integer', 
        ]);
    
        // Si la validation échoue, retourner une réponse JSON avec les erreurs
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
    
        // Mettre à jour les champs de la ressource sauf les fichiers
        $ressource->fill($request->except('image', 'piéce_join'));
    
        // Gestion de l'image
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image si elle existe
            if ($ressource->image && File::exists(storage_path('app/public/images/' . $ressource->image))) {
                File::delete(storage_path('app/public/images/' . $ressource->image));
            }
    
            // Sauvegarder la nouvelle image
            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('images', $filename, 'public');
            $ressource->image = $path;
        }
    
        // Gestion du fichier PDF
        if ($request->hasFile('piéce_join')) {
            // Supprimer l'ancien PDF si nécessaire
            if ($ressource->piéce_join && File::exists(storage_path('app/public/pdfs/' . $ressource->piéce_join))) {
                File::delete(storage_path('app/public/pdfs/' . $ressource->piéce_join));
            }
    
            // Sauvegarder le nouveau PDF
            $file = $request->file('piéce_join');
            $filePath = $file->store('pdfs', 'public');
            $ressource->piéce_join = $filePath;
        }
    
        // Sauvegarder les modifications
        $ressource->save();
    
        return response()->json([
            'message' => 'Article mis à jour avec succès',
            'article' => $ressource
        ], 200);
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,$id)
    {
        if (!$request->user()) {
            return response()->json(['error' => 'Veuillez vous connecter.'], 401);
        }
        $ressource=ressource::findOrFail($id);
        if(!$ressource){
            return response()->json(['error'=> 'Ressouce non trouver'],404);
        } 
        $ressource->delete();
        return response()->json([
            'message' => 'Ressouce supprimer avec succé',
            'article' =>$ressource
        ], 200);  
    }
    public function RessourceCategorie($id, Request $request)
    {
        // Filtrer les ressources par catégorie avec pagination
        $ressources = Ressource::where('categorie_ressource_id', $id)->paginate(10); // 10 ressources par page
        
        // Vérifier si des ressources sont trouvées
        if ($ressources->isEmpty()) {
            return response()->json(['message' => 'Aucune ressource trouvée pour cette catégorie.'], 404);
        }
    
        return response()->json($ressources, 200);
    }
    
}
