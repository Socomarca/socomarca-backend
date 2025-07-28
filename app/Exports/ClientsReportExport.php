<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\Municipality;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClientsReportExport implements FromCollection, WithHeadings
{
    protected $start;
    protected $end;
    protected $client;
    protected $totalMin;
    protected $totalMax;


    public function __construct($start, $end, $client = null, $totalMin = null, $totalMax = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->client = $client;
        $this->totalMin = $totalMin;
        $this->totalMax = $totalMax;

    }

    public function collection()
    {
        $query = Order::with('user')
            ->select('user_id', DB::raw('SUM(amount) as monto_total'), DB::raw('MAX(created_at) as ultima_compra'))
            ->where('status', 'completed')
            ->whereBetween('created_at', [$this->start, $this->end])
            ->groupBy('user_id');

        // Filtro por cliente
        if ($this->client) {
            $query->whereHas('user', function($q) {
                $q->where('name', $this->client);
            });
        }

        // Filtros por monto total (después del GROUP BY)
        if ($this->totalMin !== null) {
            $query->havingRaw('SUM(amount) >= ?', [$this->totalMin]);
        }
        if ($this->totalMax !== null) {
            $query->havingRaw('SUM(amount) <= ?', [$this->totalMax]);
        }

        $query->orderByDesc('monto_total');
        
        return $query->get()->map(function ($clientData) {
            return [
                'ID' => $clientData->user_id,
                'Cliente' => $clientData->user ? $clientData->user->name : 'N/A',
                'Monto Total' => $clientData->monto_total,
                'Fecha última compra' => $clientData->ultima_compra ? date('Y-m-d H:i', strtotime($clientData->ultima_compra)) : '',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Cliente',
            'Monto Total',
            'Fecha',
        ];
    }
}