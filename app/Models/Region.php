<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'status'];

    public function municipalities()
    {
        return $this->hasMany(Municipality::class);
    }
}