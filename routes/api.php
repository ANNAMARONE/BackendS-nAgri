<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ForumController;
use App\Http\Controllers\PanierController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\EvenementController;
use App\Http\Controllers\RessourceController;
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
    Route::post('/me',[AuthController::class,'me'])->middleware('auth:api')->name('me');
});
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

Route::get('afficher_produit',[ProduitController::class,'index']);
Route::get('/détail_produit/{id}', [ProduitController::class,'show']);
Route::get('/categories/{id}/produits', [ProduitController::class, 'produitParCategorie']);
Route::get('/CatégorieProduit', [CategorieProduitController::class,'index']);
Route::get('/catégorieRessouce', [CategorieRessourceController::class,'index']);


Route::middleware('auth:api')->group(function () {
//gestion des produits
Route::get('afficher_produitParUser',[ProduitController::class,'AfficheProduitParUser']);
Route::post('/Ajouter_produits', [ProduitController::class,'store']);
Route::post('/modifier_produit/{id}', [ProduitController::class,'update']);
Route::delete('/supprimer_produit/{id}', [ProduitController::class,'destroy']);

 
//gestion panier
Route::get('/paniers', [PanierController::class, 'index']);
Route::post('/ajouterProduitAuPanier', [PanierController::class, 'ajouterProduitAuPanier']);
Route::delete('/panier/{panierId}/produit/{produitId}', [PanierController::class, 'supprimerProduit']);
Route::put('/modifierPanier/{panierId}', [PanierController::class, 'update']);

    Route::get('/panier/montant-total', [PanierController::class,'calculerMontantTotalProduit']);
    Route::post('/panier/valider-tous', [PanierController::class, 'validerTousLesPaniers']);
    Route::post('/panier/expedier', [PanierController::class,'expedierPanier']);
    Route::get('/commandes', [PanierController::class, 'afficherCommandes']);

    //gestion article
Route::get('/article',[ArticleController::class,'index'])->name('articles');
Route::post('/articles', [ArticleController::class,'store']);
Route::get('/article_Détail/{id}', [ArticleController::class,'show']);
Route::post('/modifier_Article/{id}', [ArticleController::class,'update']);
Route::delete('supprimer_article/{id}', [ArticleController::class,'destroy']);

//gestion evenement
Route::get('/evenements',[EvenementController::class,'index'])->name('articles');
Route::post('/ajout_evenements', [EvenementController::class,'store']);
Route::get('/evenement_Détail/{id}', [EvenementController::class,'show']);
Route::post('/modifier_Evenement/{id}', [EvenementController::class,'update']);
Route::delete('supprimer_evenement/{id}', [EvenementController::class,'destroy']);
//gestion ressource
Route::get('/ressources',[RessourceController::class,'index']);
Route::post('/ajout_ressources', [RessourceController::class,'store']);
Route::get('/ressource_Détail/{id}', [RessourceController::class,'show']);
Route::post('/modifier_Ressource/{id}', [RessourceController::class,'update']);
Route::delete('supprimer_ressource/{id}', [RessourceController::class,'destroy']);
//gestion forum
Route::get('/forums',[ForumController::class,'index']);
Route::post('/ajout_forums', [ForumController::class,'store']);
Route::get('/forum_Détail/{id}', [ForumController::class,'show']);
Route::post('/modifier_forums/{id}', [ForumController::class,'update']);
Route::delete('supprimer_forums/{id}', [ForumController::class,'destroy']);
Route::get('commentaires/{id}', [ForumController::class,'commentaireForum']);
//gestion commentaire
Route::post('ajouter_commentaire/{forumId}', [CommentaireController::class,'store']);
//gestion catégorie de Produit
Route::post('/Ajout_categorieProduit', [CategorieProduitController::class,'store']);

//gestion catégorie de Ressource
Route::post('/Ajout_CategorieRessource', [CategorieRessourceController::class,'store']);

});

Route::get('/users', [AdminController::class, 'index']);
Route::delete('/users/{id}', [AdminController::class, 'destroy']);
Route::post('/users/{id}/role', [AdminController::class, 'changeRole']);
Route::post('/users/{id}/activate', [AdminController::class, 'activate']);
Route::post('/users/{id}/deactivate', [AdminController::class, 'deactivate']);
Route::get('/users/{id}', [AdminController::class, 'show']);

// Route::get('/profil', [AdminController::class,'show']);
Route::put('/profil/{id}', [AdminController::class, 'updateProfile']);

// Roles
Route::get('/roles', [RoleController::class, 'index']);
Route::post('/ajoutroles', [RoleController::class, 'store']);
Route::post('/roles/{id}', [RoleController::class, 'update']);
Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
Route::post('/roles/{id}/permission', [RoleController::class, 'givePermissions']);

// Permissions
Route::get('/permissions', [PermissionController::class, 'index']);
Route::post('/permissions', [PermissionController::class, 'store']);
Route::post('/permissions/{id}', [PermissionController::class, 'update']);
Route::delete('/permissions/{id}', [PermissionController::class, 'destroy']);
