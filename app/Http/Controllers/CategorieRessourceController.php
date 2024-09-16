<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorecategorieRessourceRequest;
use App\Http\Requests\UpdatecategorieRessourceRequest;
use App\Models\categorieRessource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class CategorieRessourceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categoriaRessource = categorieRessource::all();
        return response()->json($categoriaRessource,200);
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
        $validator=Validator::make($request->all(), [
        "libelle"=>"required|string"
        ]);
        if ($validator->fails()) {
            return response()->json([
                "error"=> $validator->errors(),
            ],422);
        }
        $categorieRessource = new categorieRessource();
        $categorieRessource->fill($request->only(['libelle']));
        $categorieRessource->save();
        return response()->json([
            'message' => 'catégorie ajouté avec succès',
            'article' => $categorieRessource
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(categorieRessource $categorieRessource)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(categorieRessource $categorieRessource)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatecategorieRessourceRequest $request, categorieRessource $categorieRessource)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(categorieRessource $categorieRessource)
    {
        //
    }
}
