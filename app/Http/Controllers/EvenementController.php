<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreevenementRequest;
use App\Http\Requests\UpdateevenementRequest;
use App\Models\evenement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
class EvenementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!$request->user()) {
            return response()->json(['error' => 'Veuillez vous connecter.'], 401);
        }
        $evenement=evenement::orderBy("created_at","desc")->paginate(10);

        if ($evenement->isEmpty()) {
            return response()->json(['message' => 'Aucune evenement trouvée.'], 404);
        } 

        return response()->json($evenement,200);
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
       if (!$request->user()) {
            return response()->json(['error' => 'Veuillez vous connecter.'], 401);
        }
        $validator = Validator::make($request->all(), [
            "libelle"=>'required|string|max:255',
            "image"=>'required|mimes:jpeg,jpg,png|max:2048',
            "description"=>'required|string',
            "lien"=>'required|string|max:255',
            "date"=>'required|date|after_or_equal:today',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors'=>$validator->errors()
            ],422);
        }
        $evenement=new evenement();
        $evenement->fill($request->only(['libelle','image', 'description', 'lien','date']));

           // Vérifier si un fichier image a été téléchargé
           if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('images', $filename, 'public');
            $evenement->image = $filename;
        }
        if(!$evenement){
            return response()->json(['message'=>'Evenement non trouvé'],404);
           } 
        $evenement->save();
        return response()->json([
            'message' => 'Article ajouté avec succès',
            'article' =>$evenement
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request,$id)
    {
        if (!$request->user()) {
            return response()->json(['error' => 'Veuillez vous connecter.'], 401);
        }
       $evenement=evenement::findOrFail($id);
       if(!$evenement){
        return response()->json(['message'=>'Evenement non trouvé'],404);
       } 
       return response()->json([
        'message'=> 'Evenement recuperer avec succés',
        'evenement'=>$evenement
       ],200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(evenement $evenement)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (!$request->user()) {
            return response()->json(['error' => 'Veuillez vous connecter.'], 401);
        }
         $evenement=evenement::findOrFail($id);
        $validator = Validator::make($request->all(), [
            "libelle"=>'required|string|max:255',
            "image"=>'required|mimes:jpeg,jpg,png|max:2048',
            "description"=>'required|string',
            "lien"=>'required|string|max:255',
            "date"=>'required|date|after_or_equal:today', 
        ]);
          // Si la validation échoue, retourner une réponse JSON avec les erreurs
          if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        $evenement->fill($request->except('image'));
        if ($request->hasFile('image')) {
            // Supprimer l'ancienne image si elle existe
            if ($evenement->image && File::exists(storage_path('app/public/images/' . $evenement->image))) {
                File::delete(storage_path('app/public/images/' . $evenement->image));
            }
    
            $image = $request->file('image');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('images', $filename, 'public');
            $evenement->image = $filename;
        }
      
        $evenement->save(); 
        
        return response()->json([
            'message' => 'Article mis à jour avec succès',
            'article' => $evenement
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
         $evenement=evenement::findOrFail($id);

         if (!$evenement) {
            return response()->json(['error' => 'Événement non trouvé.'], 404);
        }
       $evenement->delete();

      return response()->json([
        'message' => 'Article supprimer avec succé',
        'article' =>  $evenement
    ], 200);
    }
}
