<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = ['favorite_list_id', 'product_id', 'unit'];

    public function favoriteList()
    {
        return $this->belongsTo(FavoriteList::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
