<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class categorieRessource extends Model
{
    use HasFactory;
    protected $guarded = [];
    function ressouces(){
        return $this->hasMany(ressource::class);
    }
}
