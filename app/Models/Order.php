<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subtotal',
        'amount',
        'status',
        'name',
        'rut',
        'email',
        'phone',
        'address',
        'region_id',
        'municipality_id',
        'billing_address',
        'billing_address_details'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
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

    public function scopeSearchReport($query, $start, $end, $type = 'sales')
    {
        $query->whereBetween('orders.created_at', [$start, $end]);

        if ($type === 'transactions') {
            // Solo órdenes exitosas (ajusta el estado según tu lógica)
            return $query->select(
                    DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"),
                    DB::raw('COUNT(*) as transactions'),
                    DB::raw('SUM(amount) as total')
                )
                ->where('status', 'completed')
                ->groupBy('month')
                ->orderBy('month');
        }

        if ($type === 'transactions-failed') {
            return $query->select(
                    DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"),
                    DB::raw('COUNT(*) as transactions_failed'),
                    DB::raw('SUM(amount) as total')
                )
                ->where('status', 'failed')
                ->groupBy('month')
                ->orderBy('month');
        }

        if ($type === 'sales') {
            return $query->select(
                    DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"),
                    'user_id',
                    DB::raw('SUM(amount) as total')
                )
                ->groupBy('month', 'user_id');
        }

        if ($type === 'revenue') {
            return $query->select(
                    DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"),
                    DB::raw('SUM(subtotal) as total_month')
                )
                ->groupBy('month')
                ->orderBy('month');
        }

        if ($type === 'top-customers') {
            return $query->select(
                    DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"),
                    'user_id',
                    DB::raw('SUM(amount) as total_purchases'),
                    DB::raw('COUNT(*) as quantity_purchases')
                )
                ->groupBy('month', 'user_id')
                ->orderBy('month')
                ->orderByDesc('total_purchases');
        }

        if ($type === 'top-products') {
            return $query->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->select(
                    DB::raw("TO_CHAR(orders.created_at, 'YYYY-MM') as month"),
                    'order_items.product_id',
                    DB::raw('SUM(order_items.quantity) as total_sales'),
                    DB::raw('SUM(order_items.price * order_items.quantity) as subtotal')
                )
                ->whereBetween('orders.created_at', [$start, $end]) 
                ->groupBy('month', 'order_items.product_id')
                ->orderBy('month')
                ->orderByDesc('total_sales');
        }

        if ($type === 'top-categories') {
            return $query
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->select(
                    DB::raw("TO_CHAR(orders.created_at, 'YYYY-MM') as month"),
                    'categories.id as category_id',
                    'categories.name as category',
                    DB::raw('SUM(order_items.quantity) as total_sales'),
                    DB::raw('SUM(order_items.price * order_items.quantity) as subtotal')
                )
                ->whereBetween('orders.created_at', [$start, $end])
                ->groupBy('month', 'categories.id', 'categories.name')
                ->orderBy('month')
                ->orderByDesc('total_sales');
        }

        // Por defecto, ventas por cliente y mes
        return $query->select(
                DB::raw("TO_CHAR(created_at, 'YYYY-MM') as month"),
                'user_id',
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('month', 'user_id');
    }
}
