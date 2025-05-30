<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Price extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'price_list_id',
        'unit',
        'price',
        'valid_from',
        'valid_to',
        'is_active',
        'unit',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}