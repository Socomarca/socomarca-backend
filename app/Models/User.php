<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'rut',
        'business_name',
        'is_active',
        'last_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Retorna solo la dirección de facturación
     *
     * @return [type]
     */
    public function billing_address()
    {
        return $this->hasOne(Address::class)->where('type', 'billing');
    }

    /**
     * Retorna las direcciones de envío
     *
     * @return [type]
     */
    public function shipping_addresses()
    {
        return $this->hasMany(Address::class)
            ->where('type', 'shipping');
    }

    public function default_shipping_address()
    {
        return $this->hasOne(Address::class)
            ->where('type', 'shipping')
            ->where('is_default', 1);
    }

    public function favoritesList()
    {
        return $this->hasMany(FavoriteList::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
}
