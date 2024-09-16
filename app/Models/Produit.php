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
    function categorie(){
        return $this->belongsTo(categorieProduit::class);
    }
    
    public function paniers()
    {
        return $this->hasMany(Panier::class);
    }

}
