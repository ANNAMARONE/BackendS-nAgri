<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class commande extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function user(){
        return $this->belongsTo(User::class);
    }
    public function paiement(){
        return $this->hasMany(paiement::class);
    }
    public function produits()
    {
        return $this->belongsToMany(Produit::class, 'commande_produit')->withPivot('quantite', 'prix');
    }
}