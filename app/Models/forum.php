<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class forum extends Model
{
    use HasFactory;
    protected $guarded = [];
    public function comentaires(){
        return $this->hasMany(comentaire::class);
    }
    public function user(){
        return $this->belongsTo(User::class);
    }
}
