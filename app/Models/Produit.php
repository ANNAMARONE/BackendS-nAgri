<?php

namespace App\Models;

use App\Models\Panier;
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
      return $this->belongsTo(User::class, 'producteur_id'); // Assurez-vous que 'producteur_id' est la bonne clé étrangère
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
    
    

}
