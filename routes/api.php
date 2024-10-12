<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OtpController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\PanierController;
use App\Http\Controllers\PayPalController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\PaytechController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\CinetPayController;
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\ControllerPayement;
use App\Http\Controllers\EvenementController;
use App\Http\Controllers\RessourceController;
use App\Http\Controllers\SmsTwilioController;
use App\Http\Controllers\ComentaireController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\CommentaireController;
use App\Http\Controllers\CategorieProduitController;
use App\Http\Controllers\CategorieRessourceController;

Route::group(['middleware'=>'api',
'prefix'=> 'auth',
],function($router){
    Route::post('/register',[AuthController::class,'register'])->name('register');
    Route::post('/login',[AuthController::class,'login'])->name('login');
    Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:api')->name('logout');
    Route::post('/refresh',[AuthController::class,'refresh'])->middleware('auth:api')->name('refresh');
    Route::get('/me',[AuthController::class,'me']);
    Route::post('/verifier-otp', [AuthController::class, 'verifyOtp']);
    

});
Route::get('payment/success/{commande}', [PaymentController::class, 'success'])->name('payment.success');
Route::get('payment/cancel/{commande}', [PaymentController::class, 'cancel'])->name('payment.cancel');

Route::get('/evenements',[EvenementController::class,'index']);
  
Route::get('/evenement/{id}', [EvenementController::class,'show']);
    //gestion article
Route::get('/articles',[ArticleController::class,'index'])->name('articles');
 Route::get('/article/{id}', [ArticleController::class,'show']);

Route::get('/afficher_produit',[ProduitController::class,'index']);
Route::get('/détail_produit/{id}', [ProduitController::class,'show']);
Route::get('/categories/{id}/produits', [ProduitController::class, 'produitParCategorie']);
Route::get('/CatégorieProduit', [CategorieProduitController::class,'index']);
Route::get('/catégorieRessouce', [CategorieRessourceController::class,'index']);

Route::get('/products/popular', [ProduitController::class, 'getPopularProducts']);
Route::get('/ressources',[RessourceController::class,'index']);
Route::get('/ressources/{id}', [RessourceController::class,'show']);
Route::get('/ressources/categorie/{id}', [RessourceController::class,'RessourceCategorie']);
Route::get('/forums',[ForumController::class,'index']);
Route::get('/forums/{id}/commentaires', [CommentaireController::class, 'index']);
Route::get('/forums/{id}', [ForumController::class, 'commentaireForum']);
Route::get('/forum/{id}', [ForumController::class, 'show']);
//Athentification
Route::middleware('auth.jwt')->group(function () {
  Route::post('/forums/{id}/commentaires', [CommentaireController::class, 'store']);
  Route::post('/commentaires/{id}/repondre', [CommentaireController::class, 'ReponseCommentaire']);
  Route::post('/produit/{id}/like', [ProduitController::class, 'likeProduct']);
  Route::post('/commentaires/{id}/like', [CommentaireController::class, 'addLike']);   
          Route::post('/ajout_forums', [ForumController::class,'store']);
         
  Route::middleware(['role:admin|producteur'])->group(function () {
    Route::get('/utilisateurs', [UserController::class, 'index']);
    Route::get('/utilisateurs/{id}', [UserController::class, 'show']);

   
     
        Route::post('/modifier_forums/{id}', [ForumController::class,'update']);
 
         //gestion evenement
 
    //gestion des produits
  Route::get('afficher_produitParUser',[ProduitController::class,'AfficheProduitParUser']);
  Route::post('/Ajouter_produits', [ProduitController::class,'store']);
  Route::post('/modifier_produit/{id}', [ProduitController::class,'update']);
  Route::delete('/supprimer_produit/{id}', [ProduitController::class,'destroy']);


  });
  
   // Routes accessibles uniquement aux clients
  Route::middleware(['role:client'])->group(function () {
     //payement
   
    
   
  
  });
    //gestion panier
    Route::post('/commander', [CommandeController::class, 'store']);
    Route::get('/commandes', [CommandeController::class, 'AfficherCommandes']);
    Route::post('/supprimer_commande/{id}', [CommandeController::class,'supprimerCommande']);
   
  // Routes accessibles uniquement aux producteurs
  Route::middleware(['role:producteur'])->group(function () {
    

 
  });
  
  // Routes accessibles uniquement aux administrateurs
  Route::middleware(['role:admin'])->group(function () {
    //gestion article
      Route::post('/modifier_Article/{id}', [ArticleController::class,'update']);
    Route::delete('supprimer_article/{id}', [ArticleController::class,'destroy']);
    Route::post('/articles', [ArticleController::class,'store']);
  //gestion ressource
      Route::post('/ajout_ressources', [RessourceController::class,'store']);
      Route::post('/modifier_Ressource/{id}', [RessourceController::class,'update']);
      Route::delete('supprimer_ressource/{id}', [RessourceController::class,'destroy']);
      //gestion evenement
      Route::post('/ajout_evenements', [EvenementController::class,'store']);
      Route::post('/modifier_Evenement/{id}', [EvenementController::class,'update']);
      Route::delete('supprimer_evenement/{id}', [EvenementController::class,'destroy']);
      //gestion utilisateur
    Route::get('/users', [AdminController::class, 'index']);
    Route::delete('/users/{id}', [AdminController::class, 'destroy']);
    Route::post('/users/{id}/role', [AdminController::class, 'changeRole']);
    Route::post('/users/{id}/activate', [AdminController::class, 'activate']);
    Route::post('/users/{id}/deactivate', [AdminController::class, 'deactivate']);
    Route::get('/users/{id}', [AdminController::class, 'show']);
    Route::delete('supprimer_forums/{id}', [ForumController::class,'destroy']);
     Route::get('/profil', [AdminController::class,'show']);
    
    
    // Roles
    Route::get('/roles', [RoleController::class, 'index']);
    Route::post('/ajout_roles', [RoleController::class, 'store']);
    Route::post('/roles/{id}', [RoleController::class, 'update']);
    Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
    Route::post('/roles/{id}/permission', [RoleController::class, 'givePermissions']);
    
    // Permissions
    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::post('/permissions', [PermissionController::class, 'store']);
    Route::post('/permissions/{id}', [PermissionController::class, 'update']);
    Route::delete('/permissions/{id}', [PermissionController::class, 'destroy']);
    //gestion catégorie de Produit
  Route::post('/Ajout_categorieProduit', [CategorieProduitController::class,'store']);
  
  //gestion catégorie de Ressource
  Route::post('/Ajout_CategorieRessource', [CategorieRessourceController::class,'store']);

  Route::post('/modifier_categorieProduit/{id}', [CategorieProduitController::class,'update']);
Route::delete('/supprimer_categorieProduit/{id}', [CategorieProduitController::class,'destroy']);
Route::get('/detail_categorieProduit/{id}', [CategorieProduitController::class,'show']);
Route::post('/modifier_categorieRessource/{id}', [CategorieRessourceController::class,'update']);
Route::delete('/supprimer_categorieRessource/{id}', [CategorieRessourceController::class,'destroy']);

  });
  
 
  Route::post('/user/profile', [AuthController::class, 'updateProfile']);
});

