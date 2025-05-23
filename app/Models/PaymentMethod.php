<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentMethod extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'active'];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}