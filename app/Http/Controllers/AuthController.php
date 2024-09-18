<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Models\Producteur;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request; // Correct import for Request

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
            'role' => 'required|string|in:admin,client,producteur',
            
        ]);
 // Validation conditionnelle pour les champs 'acteur' et 'region'
 $validator->sometimes('acteur', 'required|in:Agriculteurs,Jardiniers', function ($input) {
    return $input->role == 'producteur';
});

$validator->sometimes('region', 'required|in:Dakar,Diourbel,Fatick,Kaffrine,Kaolack,Kédougou,Kolda,Louga,Matam,Saint-Louis,Sédhiou,Tambacounda,Thiès,Ziguinchor', function ($input) {
    return $input->role == 'producteur';
});

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $filename = null;
        if ($request->hasFile('profile')) {
            $image = $request->file('profile');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('profiles', $filename, 'public');
        }

        $user = User::create([
            'name' => $request->name,
            'profile' => $filename,
            'adresse' => $request->adresse,
            'email' => $request->email,
            'telephone' => $request->telephone,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);

        if ($request->role == 'client') {
            $user->assignRole('client');
            Client::create([
                'user_id' => $user->id,
            ]);
        } elseif ($request->role == 'producteur') {
            $user->assignRole('producteur');
            Producteur::create([
                'user_id' => $user->id,
                'acteur' => $request->acteur,
                'region' => $request->region,
            ]);
        }
        elseif ($request->role === 'admin') {
            $user->assignRole('admin');
        }

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
