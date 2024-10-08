<?php

namespace App\Http\Controllers;
use App\Models\User;

use App\Models\Client;
use App\Models\Producteur;
use App\services\SmsService;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Notifications\OTPNotification;
use Twilio\Rest\Client as TwilioClient;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
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
        // $this->sendSms($user->telephone);
        return response()->json($user, 201);
    }
    

    private function sendSms($telephone)
    {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_TOKEN');
        $twilio = new  TwilioClient($sid, $token);

        $message = "Votre compte est crée avec succé";

        $twilio->messages->create(
            '+221'.$telephone, 
            [
                'from' => env('TWILIO_PHONE_NUMBER'),
                'body' => $message,
            ]
        );
    }
    
    public function login()
    {
        $credentials = request(['email', 'password']);
    
        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json(['error' => 'unauthorized'], 401);
        }
    
        // Récupérer l'utilisateur connecté
        $user = auth('api')->user();
    
        
        return response()->json([
            'token' => $token,
            'user' => $user 
        ]);
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

  

    //=================================================
    public function updateProfile(Request $request)
    {
        // Récupérer l'utilisateur connecté
        $user = auth()->user();
        
        // Validation des données
        $validator = Validator::make($request->all(), [
            "name" => 'required|string|max:255',
            'profile' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'adresse' => 'required|string|max:255',
            'telephone' => 'required|unique:users,telephone,' . $user->id . '|regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
            "email" => 'required|email|unique:users,email,' . $user->id . '|max:255',
            'password' => 'nullable|min:8',
            'role' => 'string|in:admin,client,producteur',
        ]);
    
        $validator->sometimes('acteur', 'required|in:Agriculteurs,Jardiniers', function ($input) {
            return $input->role == 'producteur';
        });
    
        $validator->sometimes('region', 'required|in:Dakar,Diourbel,Fatick,Kaffrine,Kaolack,Kédougou,Kolda,Louga,Matam,Saint-Louis,Sédhiou,Tambacounda,Thiès,Ziguinchor', function ($input) {
            return $input->role == 'producteur';
        });
    
        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }
    
        // Mettre à jour les champs de l'utilisateur
        $user->name = $request->name;
        $user->email = $request->email;
        $user->adresse = $request->adresse;
        $user->telephone = $request->telephone;
        $user->role = $request->role;
        // Gestion de l'image de profil
        if ($request->hasFile('profile')) {
            if ($user->profile && File::exists(storage_path('app/public/profiles/' . $user->profile))) {
                File::delete(storage_path('app/public/profiles/' . $user->profile));
            }
            
            $image = $request->file('profile');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('profiles', $filename, 'public');
            $user->profile = $path;
        }
    
        // Mettre à jour le mot de passe si nécessaire
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }
    
        // Mettre à jour le rôle de l'utilisateur
        $user->role = $request->role;
        $user->save();
    
        // Gérer les rôles et données supplémentaires
        if ($request->role == 'client') {
            $user->assignRole('client');
            Client::updateOrCreate(['user_id' => $user->id]);
        } elseif ($request->role == 'producteur') {
            $user->assignRole('producteur');
            Producteur::updateOrCreate(
                ['user_id' => $user->id],
                ['acteur' => $request->acteur, 'region' => $request->region]
            );
        }
    
        return response()->json(['message' => 'Profil mis à jour avec succès', 'user' => $user], 200);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'user' => Auth::user()
        ]);
    }
}
