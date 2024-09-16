<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorecommandeRequest;
use App\Http\Requests\UpdatecommandeRequest;
use App\Models\commande;

class CommandeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
    $commandes=commande::with("produits")->get();
    return response()->json($commandes);
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
    public function store(StorecommandeRequest $request)
    {
       $commande=commande::create([
        "user_id"=>$request->user_id,
        'total'=> $request->total,
        'statut'=>$request->statut,
       ]);
       foreach($request->produits as $produit){
        $commande->produits()->attach($produit['produit_id'],[
            'quantité'=>$produit['quantite'],
            'prix'=>$produit['prix'],
        ]);
       }
       return response()->json($commande,201);
    }

    /**
     * Display the specified resource.
     */
    public function show(commande $commande)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(commande $commande)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatecommandeRequest $request, commande $commande)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(commande $commande)
    {
        //
    }
}
