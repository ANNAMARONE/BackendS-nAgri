<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request; // Correct import for Request
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            "name" => 'required|string|max:255',
            'profile' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'adresse' => 'required|string|max:255',
            'telephone' => 'required|unique:users,telephone|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
            "email" => 'required|email|unique:users,email|max:255',
            'password' => 'required|min:8',
            'secteur_id'=>'required'
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
    
        $user = new User;
        $user->name = $request->name;
        $user->profile = $request->profile;
        $user->adresse = $request->adresse;
        $user->telephone = $request->telephone;
        $user->email = $request->email;
        $user->password = bcrypt($request->password); 
        $user->secteur_id = $request->secteur_id;
    
        // Handle the profile image
        if ($request->hasFile('profile')) {
            $image = $request->file('profile');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('profiles', $filename, 'public');
            $user->profile = $filename;
        }
    
        $user->save();
    
        return response()->json($user, 201);
    }
    
    public function login() {
       $credentials=request([
        'email','password']);
        if(!$token=auth('api')->attempt($credentials)) {
            return response()->json(['error'=>'unauthorized'] ,401);
        }
        return $this->respondWithToken($token);
    }
    public function me(){
        return response()->json(auth('api')->user());
    }

    public function logout(){
        auth('api')->logout();
        return response()->json(['message'=>'successfully logged out']);
    }


    public function refresh(){
       return $this->respondWithToken(JWTAuth::refresh());
    }


    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token'=>$token,
            'token_type'=>'bearer',
            'expires_in'=>jwtAuth::factory()->getTTL()*60]);
    }
}
