<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Producteur extends Authenticatable
{
    use HasFactory;

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
 
    public function produits()
    {
        
        return $this->hasMany(Produit::class, 'producteur_id');
    }
}
