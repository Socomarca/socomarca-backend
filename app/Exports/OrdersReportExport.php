<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OrdersReportExport implements FromCollection, WithHeadings
{
    protected $start;
    protected $end;
    protected $client;
    protected $totalMin;
    protected $totalMax;
    protected $type;

    public function __construct($start, $end, $client = null, $totalMin = null, $totalMax = null, $type = 'sales')
    {
        $this->start = $start;
        $this->end = $end;
        $this->client = $client;
        $this->totalMin = $totalMin;
        $this->totalMax = $totalMax;
        $this->type = $type;
    }

    public function collection()
    {
        $query = Order::with('user')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$this->start, $this->end]);

        if ($this->client) {
            $query->whereHas('user', function ($q) {
                $q->where('name', $this->client);
            });
        }
        if ($this->totalMin !== null) {
            $query->where('amount', '>=', $this->totalMin);
        }
        if ($this->totalMax !== null) {
            $query->where('amount', '<=', $this->totalMax);
        }

        $query->orderBy('created_at', 'desc');

        return $query->get()->map(function ($order) {
            return [
                'ID' => $order->id,
                'Cliente' => $order->user ? $order->user->name : 'N/A',
                'Monto' => number_format($order->amount, 0, ',', '.'),
                'Fecha' => $order->created_at ? $order->created_at->format('Y-m-d H:i') : '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Cliente',
            'Monto',
            'Fecha',
        ];
    }
}