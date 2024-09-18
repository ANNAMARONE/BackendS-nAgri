<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
 //lister les permissions dans mon api
 public function index()
 {
     $permissions = Permission::all();
     return response()->json($permissions);
 }

 // creer une permission dans mon api
 public function store(Request $request)
 {
     $permission = Permission::create(['name' => $request->name]);
     return response()->json([
        'message'=>'La permission a bien été crée',
        'permission'=>$permission
    ],200);
 }

 // modifier une permission dans mon api
 public function update(Request $request, $id)
 {
     $permission = Permission::find($id);
     $permission->name = $request->name;
     $permission->update();
    return response()->json([
        'message'=> 'La permission a bien été modifié',
        'permission'=>$permission
    ],200);
 }

 // supprimer une permission dans mon api
 public function destroy($id)
 {
     $permission = Permission::find($id);
     $permission->delete();
    return response()->json([
        'message'=> 'La permission a bien été supprimée',
        'permission'=>$permission
    ],200);
 }
}
