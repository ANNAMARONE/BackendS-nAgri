<?php

namespace App\Http\Controllers;

use App\Models\categorieProduit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StorecategorieProduitRequest;
use App\Http\Requests\UpdatecategorieProduitRequest;

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
        // Créer une nouvelle instance de CategorieProduit avec les données validées
        $validator = Validator::make($request->all(), [
            "libelle"=> "required|string",
        ]);
    
        // Retourner une réponse JSON avec le statut 201 pour indiquer la création réussie
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        $categorieProduit= new CategorieProduit();

        $categorieProduit->fill($request->only(['libelle']));

        $categorieProduit->save();
        return response()->json([
            'message' => 'catégorie ajouté avec succès',
            'article' => $categorieProduit
        ], 201);
    
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
        // Valider les données entrantes
        $validatedData = $request->validate([
            'libelle' => 'required|string|max:255', 
        ]);
    
        // Récupérer la catégorie de produit spécifique
        $categorieProduit = CategorieProduit::find($id);
    
        // Vérifier si la catégorie existe
        if (!$categorieProduit) {
            return response()->json([
                'message' => 'Catégorie non trouvée.'
            ], 404);
        }
    
        // Mettre à jour la catégorie avec les nouvelles données
        $categorieProduit->update($validatedData);
    
        // Retourner une réponse JSON avec un message de succès
        return response()->json([
            'message' => 'Catégorie mise à jour avec succès.',
            'catégorie' => $categorieProduit
        ], 200);
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
