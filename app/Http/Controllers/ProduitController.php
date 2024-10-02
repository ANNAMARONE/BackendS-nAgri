<?php

namespace App\Http\Controllers;

use Log;
use App\Models\article;

use App\Models\Produit;
use Illuminate\Http\Request;
use App\Models\categorieProduit;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StoreproduitRequest;
use App\Http\Requests\UpdateproduitRequest;

class ProduitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
      
        $produits = Produit::paginate(10);
        if ($produits->isEmpty()) {
            return response()->json(['message' => 'Aucune produit trouvée.'], 404);
        }
        return response()->json($produits,200);
    }

    /**
     * Show the form for creating a new resource.
     */
public function AfficheProduitParUser(Request $request){
    if (!$request->user()) {
        return response()->json(['error' => 'Veuillez vous connecter.'], 401);
    }
    $user = auth()->user();
    $produits = Produit::where('user_id', $user->id)->paginate(10);
    if ($produits->isEmpty()) {
        return response()->json(['message' => 'Aucune produit trouvée.'], 404);
    }
    return response()->json($produits,200);   
}

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
            "libelle" => "required|string|max:255",
            "image" => "required|mimes:jpeg,jpg,png|max:2048",
            "description" => "required|string",
            "quantite" => "required|integer",
            "prix" => "required|integer",
            "statut" => "required|string",
            "categorie_produit_id"=> "required|integer",
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
    
        $produit = new produit();
        $produit->fill($request->only(['libelle', 'description', 'quantite', 'prix', 'statut','categorie_produit_id']));
        $produit->user_id = auth()->id();
    
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('images', $filename, 'public');
            $produit->image = $path; 
    
        $produit->save();
    
        return response()->json([
            'message' => 'Article ajouté avec succès',
            'article' => $produit
        ], 200);
    }
    
    }    
  

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
      $produit=produit::findOrFail($id);
      if(!$produit){
        return response()->json(['message'=>'produit non trouvé'],404);
      }else{
        return response()->json([
            'message'=> 'produit recupéré avec succés',
            'produit'=> $produit
          ],200);
      }
     
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
    public function update(Request $request, $id)
    {
        $produit = Produit::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            "libelle" => "required|string|max:255",
            "image" => "nullable|mimes:jpeg,jpg,png|max:2048", 
            "description" => "required|string",
            "quantite" => "required|integer",
            "prix" => "required|integer",
            "statut" => "required|string",
            "categorie_produit_id" => "required|integer",    
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        
        $produit->fill($request->except('image'));
    
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image si elle existe
            if ($produit->image && Storage::disk('public')->exists('images/' . $produit->image)) {
                Storage::disk('public')->delete('images/' . $produit->image);
            }
            
            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('images', $filename, 'public');
            $produit->image = $path;
        }
        
        $produit->save();
        
        return response()->json([
            'message' => 'Produit mis à jour avec succès',
            'produit' => $produit
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
    $produit=Produit::findOrFail($id) ;
    $produit->delete();
    return response()->json([
        'message'=> 'Produit supprimer avec succé',
        'produit'=> $produit
    ] ,200);
    }
    public function produitParCategorie($id){
        $categorie=categorieProduit::with('produits')->findOrFail($id);
        if(!$categorie){
            return response()->json(['message'=>'catégorie non trouvée'] ,404);
        }
        // Vérifiez les produits associés
    $produits = $categorie->produits;

    // Ajoutez des logs pour déboguer
       \Log::info('Produits associés :', $produits->toArray());

    return response()->json($produits);
       
    }
    public function likeProduct($id)
{
    $product = Produit::findOrFail($id);
    $product->increment('likes'); // Incrémente la colonne 'likes'
    
    return response()->json([
        'message' => 'Produit aimé avec succès!',
        'likes' => $product->likes
    ]);
}

}
