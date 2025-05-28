<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoriteList extends Model
{
    use HasFactory;

    protected $table = 'favorites_list';

    protected $fillable = [
        'name',
        'user_id',
    ];

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
