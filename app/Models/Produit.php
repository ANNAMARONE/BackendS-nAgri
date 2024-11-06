<?php

namespace App\Models;

use App\Models\Panier;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produit extends Model
{
    use HasFactory;
    protected $guarded = [];
  
    function user(){
        return $this->belongsTo(User::class);
    }
  // Dans le modèle Produit
  public function producteur()
  {
      return $this->belongsTo(User::class, 'producteur_id'); 
  }

    
    function categorie(){
        return $this->belongsTo(categorieProduit::class);
    }
    
    public function commandes()
    {
        return $this->belongsToMany(Commande::class,'commande_produit')
                    ->withPivot('quantite', 'prix_unitaire', 'montant_total', 'reference')
                    ->withTimestamps();
    }
    
    public function decrementerQuantite($quantite)
    {
        $this->quantite -= $quantite;
        $this->save();
    }
// gestion stock des produit

// ajouter stock
public function ajouterStock($quantité){
    $this->quantite+=$quantité;
    $this->save();
}

// modifier l'stock

public function modifierStock($quantite)
{
    // Logique pour modifier le stock
    if ($quantite <= 0) {
        throw new Exception('La quantité doit être supérieure à zéro.');
    }

    if ($this->quantite < $quantite) {
        throw new Exception('Quantité insuffisante en stock.');
    }

    // Retirer la quantité du stock
    $this->quantite -= $quantite;
    $this->save();
}

}
