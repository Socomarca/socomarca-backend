<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ListFavorite extends Model
{
    use HasFactory;
    protected $table = 'lists_favorites';

    protected $fillable = [
        'name',
        'user_id',
    ];

    public function favorites()
    {
        return $this->hasMany(Favorite::class, 'list_favorite_id');
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
