<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreproduitRequest;
use App\Http\Requests\UpdateproduitRequest;
use App\Models\produit;

class ProduitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(StoreproduitRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(produit $produit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(produit $produit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateproduitRequest $request, produit $produit)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(produit $produit)
    {
        //
    }
}
