<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // Total compradores x mes
    public function buyersPerMonth(Request $request)
    {
        $start = $request->input('start') 
            ? date('Y-m-d', strtotime($request->input('start')))
            : now()->subMonths(12)->startOfMonth()->toDateString();

        $end = $request->input('end') 
            ? date('Y-m-d', strtotime($request->input('end')))
            : now()->endOfMonth()->toDateString();

        $buyers = Order::select(
                DB::raw("TO_CHAR(created_at, 'YYYY-MM') as mes"),
                'user_id',
                DB::raw('SUM(amount) as total')
            )
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('mes', 'user_id')
            ->with('user')
            ->get();

        $meses = $buyers->pluck('mes')->unique()->sort()->values()->all();
        $clientes = $buyers->pluck('user.name')->unique()->values()->all();

        $totales = [];
        $totalCompradoresPorMes = [];

        foreach ($meses as $mes) {
            $ventasPorCliente = [];
            $totalMes = 0;
            $compradoresMes = $buyers->where('mes', $mes)->pluck('user_id')->unique()->count();

            foreach ($clientes as $cliente) {
                $total = $buyers->where('mes', $mes)
                    ->where('user.name', $cliente)
                    ->sum('total');
                $ventasPorCliente[] = [
                    'cliente' => $cliente,
                    'total' => $total
                ];
                $totalMes += $total;
            }
            $totales[] = [
                'mes' => $mes,
                'ventasPorCliente' => $ventasPorCliente,
                'totalMes' => $totalMes
            ];
            $totalCompradoresPorMes[] = [
                'mes' => $mes,
                'totalCompradores' => $compradoresMes
            ];
        }

        return response()->json([
            'meses' => $meses,
            'clientes' => $clientes,
            'totales' => $totales,
            'totalCompradoresPorMes' => $totalCompradoresPorMes
        ]);
    }

    // Total ingresos x mes
    public function incomePerMonth(Request $request)
    {
        $start = $request->input('start') 
            ? date('Y-m-d', strtotime($request->input('start')))
            : now()->subMonths(12)->startOfMonth()->toDateString();

        $end = $request->input('end') 
            ? date('Y-m-d', strtotime($request->input('end')))
            : now()->endOfMonth()->toDateString();

        $ingresos = Order::select(
                DB::raw("TO_CHAR(created_at, 'YYYY-MM') as mes"),
                DB::raw('SUM(subtotal) as totalMes')
            )
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();

        $meses = $ingresos->pluck('mes')->all();
        $totales = $ingresos->pluck('totalMes')->all();

        return response()->json([
            'meses' => $meses,
            'totales' => $totales
        ]);
    }
}
