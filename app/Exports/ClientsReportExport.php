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
    protected $regionCode;

    public function __construct($start, $end, $client = null, $totalMin = null, $totalMax = null, $regionCode = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->client = $client;
        $this->totalMin = $totalMin;
        $this->totalMax = $totalMax;
        $this->regionCode = $regionCode;
    }

    public function collection()
    {
        $query = Order::with('user')
            ->select('user_id', DB::raw('SUM(amount) as monto_total'))
            ->where('status', 'completed')
            ->whereBetween('created_at', [$this->start, $this->end])
            ->groupBy('user_id');

        // Filtro por cliente
        if ($this->client) {
            $query->whereHas('user', function($q) {
                $q->where('name', $this->client);
            });
        }

        // Filtro por código de región
        if ($this->regionCode) {
            // Obtener IDs de municipios que pertenecen a la región especificada
            $municipalityIds = Municipality::whereHas('region', function($q) {
                $q->where('code', $this->regionCode);
            })->pluck('id')->toArray();

            if (!empty($municipalityIds)) {
                $query->whereIn('order_meta->address->municipality_id', $municipalityIds);
            } else {
                // Si no hay municipios para esta región, no devolver ningún resultado
                $query->whereRaw('1 = 0');
            }
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
                'Monto Total' => number_format($clientData->monto_total, 0, ',', '.'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Cliente',
            'Monto Total',
        ];
    }
}