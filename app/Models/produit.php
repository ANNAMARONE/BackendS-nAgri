<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class produit extends Model
{
    use HasFactory;
    protected $guarded = [];
    function user(){
        return $this->belongsTo(User::class);
    }
    function categorieProduit(){
        return $this->belongsTo(categorieProduit::class);
    }
    public function commande(){
        return $this->belongsTo(commande::class);
    }
}
