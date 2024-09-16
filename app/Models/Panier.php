<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Panier extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function produit()
    {
        return $this->belongsTo(Produit::class, 'produit_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
