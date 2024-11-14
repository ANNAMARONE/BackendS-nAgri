<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomNotification extends Model
{
    use HasFactory;
    protected $table = 'notifications'; // Spécifiez le nom de la table

    protected $fillable = [
        'notifiable_id',
        'notifiable_type',
        'message',
        'is_read',
    ];

  
    public function notifiable()
    {
        return $this->morphTo();
    }
}
