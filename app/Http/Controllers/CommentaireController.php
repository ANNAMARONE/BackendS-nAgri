<?php

namespace App\Http\Controllers;

use App\Models\forum;
use App\Models\Commentaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CommentaireController extends Controller
{
 
    
    public function store(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        "description" => "required|string",
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Erreur de validation',
            'errors' => $validator->errors()
        ], 422);
    }

    $commentaires = Commentaire::create([
        "forum_id" => $id,
        "description" => $request->description,
        "user_id" => Auth::id(),
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Commentaire ajouté avec succès',
        'data' => $commentaires
    ], 201);
}

// Exemple de méthode pour ajouter un like à un commentaire
public function addLike($commentId)
{
    $comment = Commentaire::find($commentId);
    if (!$comment) {
        return response()->json(['message' => 'Comment not found'], 404);
    }

    $comment->likes += 1; 
    $comment->save();

    return response()->json(['message' => 'Like ajouté', 'likes' => $comment->likes]);
}

public function ReponseCommentaire(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        "description" => "required|string",
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Erreur de validation',
            'errors' => $validator->errors()
        ], 422);
    }

    $reply = Commentaire::create([
        "forum_id" => $request->forum_id, 
        "description" => $request->description,
        "user_id" => Auth::id(),
        "parent_id" => $id 
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Réponse ajoutée avec succès',
        'data' => $reply
    ], 201);
}


}
