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
        $query = Order::where('status', 'completed')
            ->whereBetween('created_at', [$this->start, $this->end]);

        // Si hay un cliente específico, agrupamos por fecha y cliente
        if ($this->client) {
            $query = $query->with('user')
                ->select(
                    DB::raw('DATE(created_at) as fecha'),
                    'user_id',
                    DB::raw('SUM(amount) as monto_total')
                )
                ->whereHas('user', function($q) {
                    $q->where('name', $this->client);
                })
                ->groupBy(DB::raw('DATE(created_at)'), 'user_id');

            // Filtros por monto total (después del GROUP BY)
            if ($this->totalMin !== null) {
                $query->havingRaw('SUM(amount) >= ?', [$this->totalMin]);
            }
            if ($this->totalMax !== null) {
                $query->havingRaw('SUM(amount) <= ?', [$this->totalMax]);
            }

            $query->orderBy('fecha')->orderBy('monto_total', 'desc');
            
            return $query->get()->map(function ($orderData) {
                return [
                    'Fecha' => $orderData->fecha,
                    'Cliente' => $orderData->user ? $orderData->user->name : 'N/A',
                    'Monto' => number_format($orderData->monto_total, 0, ',', '.'),
                ];
            });
        } else {
            // Si no hay cliente específico, agrupamos solo por fecha
            $query = $query->select(
                    DB::raw('DATE(created_at) as fecha'),
                    DB::raw('SUM(amount) as monto_total')
                )
                ->groupBy(DB::raw('DATE(created_at)'));

            // Filtros por monto total (después del GROUP BY)
            if ($this->totalMin !== null) {
                $query->havingRaw('SUM(amount) >= ?', [$this->totalMin]);
            }
            if ($this->totalMax !== null) {
                $query->havingRaw('SUM(amount) <= ?', [$this->totalMax]);
            }

            $query->orderBy('fecha');
            
            return $query->get()->map(function ($orderData) {
                return [
                    'Fecha' => $orderData->fecha,
                    'Cliente' => 'Todos los clientes',
                    'Monto' => number_format($orderData->monto_total, 0, ',', '.'),
                ];
            });
        }
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Cliente', 
            'Monto',
        ];
    }
}