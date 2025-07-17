<?php

namespace App\Exports;

use App\Models\OrderItem;
use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TopProductsExport implements FromCollection, WithHeadings
{
    protected $start;
    protected $end;

    public function __construct($start = null, $end = null)
    {
        $this->start = $start ?? now()->subMonths(12)->startOfMonth()->toDateString();
        $this->end = $end ?? now()->endOfMonth()->toDateString();
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        // Trae los OrderItems con su Order y Product
        $items = OrderItem::with(['order', 'product'])
            ->whereHas('order', function ($q) {
                $q->where('status', 'completed')
                  ->whereBetween('created_at', [$this->start, $this->end]);
            })
            ->get();

        // Agrupa por mes
        $byMonth = $items->groupBy(function ($item) {
            return $item->order && $item->order->created_at
                ? $item->order->created_at->format('Y-m')
                : null;
        });

        // Para cada mes, encuentra el producto más vendido
        $topProducts = $byMonth->map(function ($itemsOfMonth, $month) {
            // Agrupa por producto
            $byProduct = $itemsOfMonth->groupBy('product_id');

            // Calcula ventas por producto
            $productSales = $byProduct->map(function ($items, $productId) use ($month) {
                $product = $items->first()->product;
                return [
                    'Producto' => $product ? $product->name : 'Desconocido',
                    'Mes' => $month,
                    'Cantidad vendida' => $items->sum('quantity'),
                    'Total ventas' => $items->sum(function ($item) {
                        return $item->price * $item->quantity;
                    }),
                ];
            });

            // Devuelve solo el producto más vendido de ese mes
            return $productSales->sortByDesc('Cantidad vendida')->first();
        })->filter();

        return $topProducts->values();
    }

    public function headings(): array
    {
        return [
            'Producto',
            'Mes',
            'Cantidad vendida',
            'Total ventas'
        ];
    }
}
