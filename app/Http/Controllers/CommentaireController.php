<?php

namespace App\Http\Controllers;

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

}
