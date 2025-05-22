<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListFavorite extends Model
{
    use HasFactory;

    protected $table = 'lists_favorites';
    
    protected $fillable = [
        'name',
        'user_id',
    ];

    // Relación con User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
