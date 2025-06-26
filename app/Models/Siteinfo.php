<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Siteinfo extends Model
{
    protected $table = 'siteinfo';

    protected $fillable = [
        'key',
        'value',
        'content',
    ];

    protected $casts = [
        'value' => 'array',
    ];
}