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

    public function scopeSearchReport($query, $start, $end, $type = 'ventas')
    {
        $query->whereBetween('orders.created_at', [$start, $end]);

        if ($type === 'transacciones') {
            // Solo órdenes exitosas (ajusta el estado según tu lógica)
            return $query->select(
                    DB::raw("TO_CHAR(created_at, 'YYYY-MM') as mes"),
                    DB::raw('COUNT(*) as transacciones_exitosas'),
                    DB::raw('SUM(amount) as total_procesado')
                )
                ->where('status', 'completed')
                ->groupBy('mes')
                ->orderBy('mes');
        }

        if ($type === 'transacciones-fallidas') {
            return $query->select(
                    DB::raw("TO_CHAR(created_at, 'YYYY-MM') as mes"),
                    DB::raw('COUNT(*) as transacciones_fallidas'),
                    DB::raw('SUM(amount) as total_fallido')
                )
                ->where('status', 'failed')
                ->groupBy('mes')
                ->orderBy('mes');
        }

        if ($type === 'ventas') {
            return $query->select(
                    DB::raw("TO_CHAR(created_at, 'YYYY-MM') as mes"),
                    'user_id',
                    DB::raw('SUM(amount) as total')
                )
                ->groupBy('mes', 'user_id');
        }

        if ($type === 'ingresos') {
            return $query->select(
                    DB::raw("TO_CHAR(created_at, 'YYYY-MM') as mes"),
                    DB::raw('SUM(subtotal) as totalMes')
                )
                ->groupBy('mes')
                ->orderBy('mes');
        }

        if ($type === 'top-clientes') {
            return $query->select(
                    DB::raw("TO_CHAR(created_at, 'YYYY-MM') as mes"),
                    'user_id',
                    DB::raw('SUM(amount) as total_compras'),
                    DB::raw('COUNT(*) as cantidad_compras')
                )
                ->groupBy('mes', 'user_id')
                ->orderBy('mes')
                ->orderByDesc('total_compras');
        }

        if ($type === 'top-productos') {
            return $query->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->select(
                    DB::raw("TO_CHAR(orders.created_at, 'YYYY-MM') as mes"),
                    'order_items.product_id',
                    DB::raw('SUM(order_items.quantity) as total_ventas'),
                    DB::raw('SUM(order_items.price * order_items.quantity) as subtotal')
                )
                ->whereBetween('orders.created_at', [$start, $end]) 
                ->groupBy('mes', 'order_items.product_id')
                ->orderBy('mes')
                ->orderByDesc('total_ventas');
        }

        if ($type === 'top-categorias') {
            return $query
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('categories', 'products.category_id', '=', 'categories.id')
                ->select(
                    DB::raw("TO_CHAR(orders.created_at, 'YYYY-MM') as mes"),
                    'categories.id as categoria_id',
                    'categories.name as categoria',
                    DB::raw('SUM(order_items.quantity) as total_ventas'),
                    DB::raw('SUM(order_items.price * order_items.quantity) as subtotal')
                )
                ->whereBetween('orders.created_at', [$start, $end])
                ->groupBy('mes', 'categories.id', 'categories.name')
                ->orderBy('mes')
                ->orderByDesc('total_ventas');
        }

        // Por defecto, ventas por cliente y mes
        return $query->select(
                DB::raw("TO_CHAR(created_at, 'YYYY-MM') as mes"),
                'user_id',
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('mes', 'user_id');
    }
}
