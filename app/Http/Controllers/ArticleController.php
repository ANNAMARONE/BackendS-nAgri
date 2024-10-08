<?php

namespace App\Http\Controllers;

use App\Models\article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\StorearticleRequest;
use App\Http\Requests\UpdatearticleRequest;
use Illuminate\Support\Facades\File;
class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        
        $articles = Article::orderBy("created_at","desc")->get();
        if ($articles->isEmpty()) {
            return response()->json(['message' => 'Aucune articles trouvée.'], 404);
        }
        return response()->json($articles,200);
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
    public function store(Request $request): JsonResponse
    {
        // Validation des données de la requête
        $validator = Validator::make($request->all(), [
            'libelle' => 'required|string|max:255',
            'image' => 'required|mimes:jpeg,jpg,png,webp|max:2048',
            'description' => 'required|string',
            'lien' => 'required|string|max:255',
            'statut' => 'required|string|min:1',
        ]);

        // Si la validation échoue, retourner une réponse JSON avec les erreurs
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        // Création d'une nouvelle instance de l'article
        $article = new Article();
        $article->fill($request->only(['libelle', 'description', 'date', 'lien', 'statut']));

        // Vérifier si un fichier image a été téléchargé
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('images', $filename, 'public');
            $article->image = $filename;
        }
        $article->save();
        return response()->json([
            'message' => 'Article ajouté avec succès',
            'article' => $article
        ], 201);
    
}

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $article=article::findOrFail($id);
        if(!$article){
            return response()->json(['message'=>'Article non trouvé'],404);
        }
        return response()->json([
            'message' => 'Article récupéré avec succès',
            'article' => $article
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(article $article)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $article = Article::findOrFail($id);
    
        // Validation des données de la requête
        $validator = Validator::make($request->all(), [
            'libelle' => 'required|string|max:255',
            'image' => 'sometimes|mimes:jpeg,jpg,png,webp|max:2048',
            'description' => 'required|string',
            'lien' => 'required|string|max:255',
            'statut' => 'required|string|min:1', 
        ]);
    
        // Si la validation échoue, retourner une réponse JSON avec les erreurs
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        $article->fill($request->except('image'));
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image si elle existe
            if ($article->image && File::exists(storage_path('app/public/images/' . $article->image))) {
                File::delete(storage_path('app/public/images/' . $article->image));
            }
    
            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('images', $filename, 'public');
            $article->image = $path;
        }
        $article->save(); 
        return response()->json([
            'message' => 'Article mis à jour avec succès',
            'article' => $article
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
      $article=article::findOrFail($id);

      if (!$article) {
        return response()->json(['error' => 'Événement non trouvé.'], 404);
    }
      $article->delete();

      return response()->json([
        'message' => 'Article supprimer avec succé',
        'article' => $article
    ], 200);
    }
}
