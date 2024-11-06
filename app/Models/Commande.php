<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Commande extends Model
{
    use HasFactory;

    protected $guarded = [];
  
    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'commande_produit')
                    ->withPivot('quantite', 'montant')
                    ->withTimestamps();
    }
    
    public function calculerMontantTotal()
    {
        $total = 0;

        foreach ($this->produits as $produit) {
            $total += $produit->pivot->montant_total;
        }

        return $total;
    }
  

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    
}
