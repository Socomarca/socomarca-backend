<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subtotal',
        'amount',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getAmountAttribute()
    {
        return round($this->attributes['amount'], 0);
    }

    public function getSubtotalAttribute()
    {
        return round($this->attributes['subtotal'], 0);
    }

}
