<?php

namespace App\Http\Controllers;
use Log;
use App\Models\forum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\StoreforumRequest;
use App\Http\Requests\UpdateforumRequest;
use Illuminate\Support\Facades\Validator;

class ForumController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        
        $forums = Forum::with('user')->orderBy("created_at", "desc")->paginate(3);
        
        if ($forums->isEmpty()) {
            return response()->json(["error" => "Aucun forum trouvé"], 404);
        }
        
        return response()->json($forums, 200);
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
            "libelle" => "required|string|max:255",
            "description" => "required|string",
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()
            ], 422);
        }
    
        $forum = new Forum();
        $forum->fill($request->only(["libelle", "description"]));
        $forum->user_id = auth()->id();
        $forum->save();
    
        return response()->json([
            "success" => "Forum created successfully!"
        ], 201);
    }
    

    /**
     * Display the specified resource.
     */
    public function show(Request $request,$id)
    {
      
        $forum=forum::findOrfail($id);
        if(!$forum){
            return response()->json(["message"=>"forum non trouver"] ,404);
        }else{
            return response()->json([
                "message"=> "forum recuperer avec succé",
                "forum"=>$forum
                ] ,200);
        }  
      
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(forum $forum)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        if (!$request->user()) {
            return response()->json(['error' => 'Veuillez vous connecter'], 401);
        }
    
        $forum = Forum::findOrFail($id);
        $validator = Validator::make($request->all(), [
            "libelle" => "required|string|max:255",
            "description" => "required|string",
        ]);
    
        if ($validator->fails()) {
            return response()->json([
                "error" => $validator->errors()
            ], 422);
        }
    
        $forum->libelle = $request->input('libelle');
        $forum->description = $request->input('description');
        $forum->save();
    
        return response()->json([
            "message" => "Forum mis à jour avec succès",
            "forum" => $forum
        ], 200);
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,$id)
    {
        if(!$request->user()){
            return response()->json(['error'=>'veuillez vous connecter'] ,401);
        }
     $forum=forum::findOrFail($id);
     $forum->delete();
     return response()->json([
        "message"=> "forum supprimer avec succé",
        "forum"=>$forum
     ] ,200);

    }
    public function commentaireForum(Request $request, $id)
{
    // Charger le forum avec ses commentaires et les réponses à ces commentaires
    $forum = Forum::with(['commentaires.user', 'commentaires.replies.user', 'user'])->find($id);
    if (!$forum) {
        return response()->json(['message' => 'Forum non trouvé'], 404);
    }

    // Retourner le forum, ses commentaires et les réponses associées
    return response()->json([
        'forum' => $forum,
        'commentaires' => $forum->commentaires
    ], 200);
}
  
}
