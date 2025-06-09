<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{


    public function report(Request $request)
    {
        $start = $request->input('start') 
            ? date('Y-m-d', strtotime($request->input('start')))
            : now()->subMonths(12)->startOfMonth()->toDateString();

        $end = $request->input('end') 
            ? date('Y-m-d', strtotime($request->input('end')))
            : now()->endOfMonth()->toDateString();

        $type = $request->input('type', 'ventas'); // ventas, compradores, ingresos, transacciones, top-clientes

        $orders = \App\Models\Order::searchReport($start, $end, $type)->with('user')->get();

        if ($type === 'top-clientes') {
            $meses = $orders->pluck('mes')->unique()->sort()->values()->all();
            $clientes = $orders->pluck('user.name')->unique()->values()->all();

            // Para el gráfico: total de compras por cliente y mes
            $totales = [];
            foreach ($meses as $mes) {
                $comprasPorCliente = [];
                foreach ($clientes as $cliente) {
                    $total = $orders->where('mes', $mes)
                        ->where('user.name', $cliente)
                        ->sum('total_compras');
                    $comprasPorCliente[] = [
                        'cliente' => $cliente,
                        'total' => $total
                    ];
                }
                $totales[] = [
                    'mes' => $mes,
                    'comprasPorCliente' => $comprasPorCliente
                ];
            }

            // Detalle para tabla
            $detalleTabla = [];
            foreach ($orders as $order) {
                $detalleTabla[] = [
                    'id_cliente' => $order->user_id,
                    'cliente' => $order->user ? $order->user->name : null,
                    'monto' => $order->total_compras,
                    'fecha' => $order->mes,
                ];
            }

            $topClientesPorMes = [];
            foreach ($meses as $mes) {
                $compradoresMes = $orders->where('mes', $mes);
                $top = $compradoresMes->sortByDesc('total_compras')->first();
                if ($top) {
                    $topClientesPorMes[] = [
                        'mes' => $mes,
                        'id_cliente' => $top->user_id,
                        'cliente' => $top->user ? $top->user->name : null,
                        'cantidad_compras' => $top->cantidad_compras,
                        'total_compras' => $top->total_compras,
                    ];
                }
            }

            // Total global de compras de todos los clientes
            $total_compras_global = $orders->sum('total_compras');

            // Total de clientes distintos
            $total_clientes = $orders->pluck('user_id')->unique()->count();

            return response()->json([
                'meses' => $meses,
                'clientes' => $clientes,
                'totales' => $totales,
                'detalle_tabla' => $detalleTabla,
                'top_clientes_por_mes' => $topClientesPorMes,
                'total_clientes' => $total_clientes,
                'total_compras' => $total_compras_global,
            ]);
        }

        if ($type === 'transacciones') {
            $meses = $orders->pluck('mes')->all();
            $transacciones = $orders->pluck('transacciones_exitosas')->all();
            $totales = $orders->pluck('total_procesado')->all();

            // Formato para gráfico por meses
            $result = [];
            foreach ($orders as $order) {
                $result[] = [
                    'mes' => $order->mes,
                    'transacciones_exitosas' => (int)$order->transacciones_exitosas,
                    'total_procesado' => (float)$order->total_procesado,
                ];
            }

            // Detalle para tabla
            $detalleTabla = [];
            foreach ($orders as $order) {
                // Busca las órdenes originales para ese mes (ya que $orders es agrupado)
                $ordenesMes = Order::where('status', 'completed')
                    ->whereBetween('created_at', [$start, $end])
                    ->whereRaw("TO_CHAR(created_at, 'YYYY-MM') = ?", [$order->mes])
                    ->get();

                foreach ($ordenesMes as $o) {
                    $detalleTabla[] = [
                        'id' => $o->id,
                        'cliente' => $o->user ? $o->user->name : null,
                        'monto' => $o->amount,
                        'fecha' => $o->created_at->toDateString(),
                    ];
                }
            }

            // Totales generales
            $totalTransacciones = array_sum($transacciones);
            $totalMonto = array_sum($totales);

            return response()->json([
                'meses' => $meses,
                'transacciones_exitosas' => $transacciones,
                'total_procesado' => $totales,
                'detalle' => $result,
                'total_transacciones_exitosas' => $totalTransacciones,
                'total_monto_procesado' => $totalMonto,
                'detalle_tabla' => $detalleTabla, // <-- aquí el detalle para la tabla
            ]);
        }

        if ($type === 'top-productos') {
            // Obtén los productos relacionados para el detalle
            $productIds = $orders->pluck('product_id')->unique()->all();
            $products = \App\Models\Product::whereIn('id', $productIds)->get()->keyBy('id');

            $meses = $orders->pluck('mes')->unique()->sort()->values()->all();

            // Para el gráfico: producto más vendido por mes
            $topProductosPorMes = [];
            foreach ($meses as $mes) {
                $productosMes = $orders->where('mes', $mes);
                $top = $productosMes->sortByDesc('total_ventas')->first();
                if ($top) {
                    $producto = $products->get($top->product_id);
                    $topProductosPorMes[] = [
                        'mes' => $mes,
                        'id_producto' => $top->product_id,
                        'producto' => $producto ? $producto->name : null,
                        'total_ventas' => (int)$top->total_ventas,
                        'subtotal' => (float)$top->subtotal,
                        'margen' => 0, // Por ahora
                    ];
                }
            }

            // Detalle para tabla
            $detalleTabla = [];
            foreach ($orders as $order) {
                $producto = $products->get($order->product_id);
                $detalleTabla[] = [
                    'id_producto' => $order->product_id,
                    'producto' => $producto ? $producto->name : null,
                    'subtotal' => (float)$order->subtotal,
                    'margen' => 0, // Por ahora
                    'total_ventas' => (int)$order->total_ventas,
                    'mes' => $order->mes,
                ];
            }

            // Totales generales
            $total_productos = $orders->pluck('product_id')->unique()->count();
            $total_ventas = $orders->sum('total_ventas');

            return response()->json([
                'meses' => $meses,
                'top_productos_por_mes' => $topProductosPorMes,
                'detalle_tabla' => $detalleTabla,
                'total_productos' => $total_productos,
                'total_ventas' => $total_ventas,
            ]);
        }

        // Para ventas y compradores
        $meses = $orders->pluck('mes')->unique()->sort()->values()->all();
        $clientes = $orders->pluck('user.name')->unique()->values()->all();

        $totales = [];
        $totalCompradoresPorMes = [];

        foreach ($meses as $mes) {
            $ventasPorCliente = [];
            $totalMes = 0;
            $compradoresMes = $orders->where('mes', $mes)->pluck('user_id')->unique()->count();

            foreach ($clientes as $cliente) {
                $total = $orders->where('mes', $mes)
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
}
