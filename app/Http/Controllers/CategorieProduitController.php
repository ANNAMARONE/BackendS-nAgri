<?php

namespace App\Http\Controllers;

use App\Models\categorieProduit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StorecategorieProduitRequest;
use App\Http\Requests\UpdatecategorieProduitRequest;
use Illuminate\Support\Facades\Storage;
class CategorieProduitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
       
        $catégorieProduit = CategorieProduit::all();
        if ( $catégorieProduit->isEmpty()) {
            return response()->json(['message' => 'Aucune catégorie trouvée.'], 404);
        }
        return response()->json($catégorieProduit,200);
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
        $validator = Validator::make($request->all(), [
            'libelle' => 'required|string|max:255',
            "image" => "required|mimes:jpeg,jpg,png|max:2048"
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        $categorie = new categorieProduit();
        $categorie->fill($request->only(['libelle', 'description', 'quantite', 'prix', 'statut','categorie_produit_id']));
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('images', $filename, 'public');
            $categorie->image = $path; 
    
        $categorie->save();
    
        return response()->json([
            'message' => 'Catégorie créée avec succès!',
            'article' => $categorie
        ], 200);
    
    }
    }

    /**
     * Display the specified resource.
     */
    public function show(categorieProduit $categorieProduit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(categorieProduit $categorieProduit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        // Récupérer la catégorie de produit spécifique
      $categorieProduit = CategorieProduit::find($id);
        // Valider les données entrantes
        $validator = Validator::make($request->all(), [
            'libelle' => 'required|string|max:255', 
            "image" => "required|mimes:jpeg,jpg,png|max:2048",
        ]);
    

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        
        // Vérifier si la catégorie existe
        if (!$categorieProduit) {
            return response()->json([
                'message' => 'Catégorie non trouvée.'
            ], 404);
        }
        $categorieProduit->fill($request->except('image'));
    
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image si elle existe
            if ($categorieProduit->image && Storage::disk('public')->exists('images/' . $categorieProduit->image)) {
                Storage::disk('public')->delete('images/' . $categorieProduit->image);
            }
            
            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('images', $filename, 'public');
            $categorieProduit->image = $filename;
        // Mettre à jour la catégorie avec les nouvelles données
        $categorieProduit->save();
        // Retourner une réponse JSON avec un message de succès
        return response()->json([
            'message' => 'Catégorie mise à jour avec succès.',
            'catégorie' => $categorieProduit
        ], 200);
    }
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        // Récupérer la catégorie de produit spécifique
        $categorieProduit = CategorieProduit::find($id);
    
        // Vérifier si la catégorie existe
        if (!$categorieProduit) {
            return response()->json([
                'message' => 'Catégorie non trouvée.'
            ], 404);
        }
    
        // Supprimer la catégorie
        $categorieProduit->delete();
    
        // Retourner une réponse JSON avec un message de succès
        return response()->json([
            'message' => 'Catégorie supprimée avec succès.',
            'catégorie' => $categorieProduit
        ], 200);
    }
    
}
