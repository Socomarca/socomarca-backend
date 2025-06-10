<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
            'field' => 'name',
            'operators' => ['=', '!=', 'LIKE', 'NOT LIKE', 'fulltext'],
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

    public function orderDetails()
    {
        return $this->hasMany(OrderItem::class, 'product_id');
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
        foreach ($filters as $filter) {
            if (!isset($filter['field'])) continue;

            // Filtros especiales para precios
            if ($filter['field'] === 'price') {
                $query->whereHas('prices', function ($q) use ($filter) {
                    if (isset($filter['min'])) {
                        $q->where('price', '>=', $filter['min']);
                    }
                    if (isset($filter['max'])) {
                        $q->where('price', '<=', $filter['max']);
                    }
                    if (isset($filter['unit'])) {
                        $q->where('unit', $filter['unit']);
                    }
                    $q->where('is_active', true);
                });

                // Ordenar por precio si se solicita
                if (isset($filter['sort']) && in_array(strtolower($filter['sort']), ['asc', 'desc'])) {
                    $query->selectRaw('products.*, prices.price as current_price')
                        ->join('prices', function($join) {
                            $join->on('products.id', '=', 'prices.product_id')
                                ->where('prices.is_active', true);
                        })
                        ->orderBy('current_price', strtolower($filter['sort']));
                }
                continue;
            }

            // Ordenar por cantidad de ventas
            if ($filter['field'] === 'sales_quantity') {
                if (isset($filter['sort']) && in_array(strtolower($filter['sort']), ['asc', 'desc'])) {
                    $query->orderBy('sales_quantity', strtolower($filter['sort']));
                }
                continue;
            }

            $field = array_find($this->allowedFilters, function ($item) use ($filter) {
                return $item['field'] === $filter['field'];
            });

            if ($field !== null) {
                $value = $filter['value'];
                $operator = array_find($field['operators'], function ($item) use ($filter) {
                    return $item === $filter['operator'];
                });

                if ($operator !== null && $operator !== 'fulltext') {
                    $query->where($field['field'], $operator, $value);
                } elseif ($operator === 'fulltext') {
                    $query
                        ->selectRaw("similarity(products.{$field['field']}, ?) AS similarity_index", [$value])
                        ->whereRaw("products.{$field['field']} % ?", [$value])
                        ->orderBy("similarity_index", "DESC");
                }
            }
        }

        return $query;
    }
}
