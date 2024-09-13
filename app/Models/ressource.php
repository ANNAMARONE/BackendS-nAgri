<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ressource extends Model
{
    use HasFactory;
    protected $guarded = [];
    function CategorieRessouce(){
        return $this->belongsTo(categorieRessource::class);
    }
}
