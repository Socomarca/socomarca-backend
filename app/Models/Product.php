<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Product extends Model
{

    use HasFactory;

    protected $fillable = [
        'random_product_id',
        'name',
        'description',
        'category_id',
        'subcategory_id',
        'brand_id',
        'sku',
        'status',
        'price_id'
    ];

    /**
     * @var array
     * Allowed filters for the filter scope.
     */
    protected $allowedFilters = [
        [
            'field' => 'category_id',
            'operators' => ['=', '!=',],
        ],
        [
            'field' => 'subcategory_id',
            'operators' => ['=', '!=',],
        ],
        [
            'field' => 'brand_id',
            'operators' => ['=', '!=',],
        ],
        [
            'field' => 'is_favorite',
            'operators' => ['='],
        ],
        [
            'field' => 'name',
            'operators' => ['=', '!=', 'LIKE', 'ILIKE', 'NOT LIKE', 'fulltext'],
        ],
    ];

    /**
     * @var array
     * Allowed sorts for the filter scope.
     */
    protected $allowedSorts = [
        'name',
        'created_at',
        'updated_at',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function prices()
    {
        return $this->hasMany(Price::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function userFavorites($userId)
    {
        return $this->hasMany(Favorite::class)
            ->whereHas('favoriteList', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
    }

    public function toSearchableArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
        ];
    }

    /**
     * Scope a query to filter products based on given criteria
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, array $filters)
    {
        // Filtro de Precio 
        $priceFilter = $filters['price'];
        $query->whereHas('prices', function ($q) use ($priceFilter) {
            $q->where('price', '>=', $priceFilter['min'])
              ->where('price', '<=', $priceFilter['max'])
              ->where('is_active', true);
            
            // Opcional: filtrar por unidad si se envía
            if (isset($priceFilter['unit'])) {
                $q->where('unit', $priceFilter['unit']);
            }
        });

        // Filtro de Categoría
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Filtro de Subcategoría
        if (isset($filters['subcategory_id'])) {
            $query->where('subcategory_id', $filters['subcategory_id']);
        }

        // Filtro de Marca
        if (isset($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }
        
        // Filtro por Nombre (búsqueda parcial)
        if (isset($filters['name'])) {
            $searchTerm = $filters['name'];
            $query->where(function($q) use ($searchTerm) {
                $q->whereRaw('similarity(name, ?) > 0.3', [$searchTerm])
                ->orWhere('name', 'ILIKE', "%{$searchTerm}%");
            })
            ->orderByRaw('similarity(name, ?) DESC', [$searchTerm]);
        }

        // Filtro de Favoritos
        if (isset($filters['is_favorite']) && Auth::check()) {
            if ($filters['is_favorite'] === true) {
                $query->whereHas('favorites', function ($q) {
                    $q->whereHas('favoriteList', fn($subQ) => $subQ->where('user_id', Auth::id()));
                });
            } else {
                $query->whereDoesntHave('favorites', function ($q) {
                    $q->whereHas('favoriteList', fn($subQ) => $subQ->where('user_id', Auth::id()));
                });
            }
        }

        return $query;
    }
}