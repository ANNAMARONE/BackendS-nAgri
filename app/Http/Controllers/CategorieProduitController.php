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
    public function index()
    {
        $catégorieProduit = CategorieProduit::all();
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
    public function update(UpdatecategorieProduitRequest $request, categorieProduit $categorieProduit)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(categorieProduit $categorieProduit)
    {
        //
    }
}
