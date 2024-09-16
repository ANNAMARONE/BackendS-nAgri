<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PanierController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ProduitController;
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
//gestion article
Route::get('/article',[ArticleController::class,'index'])->name('articles');
Route::post('/articles', [ArticleController::class,'store']);
Route::get('/article_Détail/{id}', [ArticleController::class,'show']);
Route::post('/modifier_Article/{id}', [ArticleController::class,'update']);
Route::delete('supprimer_article/{id}', [ArticleController::class,'destroy']);

//gestion catégorie de Produit
Route::post('/Ajout_categorieProduit', [CategorieProduitController::class,'store']);
Route::get('/CatégorieProduit', [CategorieProduitController::class,'index']);
//gestion catégorie de Ressource
Route::post('/Ajout_CategorieRessource', [CategorieRessourceController::class,'store']);
Route::get('/catégorieRessouce', [CategorieRessourceController::class,'index']);
//gestion des produits
Route::get('afficher_produit',[ProduitController::class,'index'])->name('');
Route::post('/Ajouter_produits', [ProduitController::class,'store'])->middleware('auth:api');
Route::post('/modifier_produit/{id}', [ProduitController::class,'update'])->middleware('auth:api');
Route::get('/détail_produit/{id}', [ProduitController::class,'show'])->middleware('auth:api');
Route::delete('/supprimer_produit/{id}', [ProduitController::class,'destroy'])->middleware('auth:api');
Route::get('/categories/{id}/produits', [ProduitController::class, 'produitParCategorie']);
 
//gestion panier
Route::get('/paniers', [PanierController::class, 'index'])->middleware('auth:api');
Route::post('/ajouterProduitAuPanier', [PanierController::class, 'ajouterProduitAuPanier'])->middleware('auth:api');
Route::delete('/panier/{panierId}/produit/{produitId}', [PanierController::class, 'supprimerProduit'])->middleware('auth:api');
Route::put('/modifierPanier/{panierId}', [PanierController::class, 'update'])->middleware('auth:api');

Route::middleware('auth:api')->group(function () {
    Route::get('/panier/montant-total', [PanierController::class,'calculerMontantTotalProduit']);
    Route::post('/panier/valider-tous', [PanierController::class, 'validerTousLesPaniers']);
    Route::post('/panier/expedier', [PanierController::class,'expedierPanier']);
});
