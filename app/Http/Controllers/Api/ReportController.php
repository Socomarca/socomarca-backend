<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
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
            // Obtén los usuarios involucrados
            $userIds = $orders->pluck('user_id')->unique()->all();
            $users = \App\Models\User::whereIn('id', $userIds)->get()->keyBy('id');

            $meses = $orders->pluck('mes')->unique()->sort()->values()->all();

            $topClientes = [];
            $totalVentas = 0;

            foreach ($meses as $mes) {
                $clientesMes = $orders->where('mes', $mes);
                $top = $clientesMes->sortByDesc('total_compras')->first();
                if ($top) {
                    $usuario = $users->get($top->user_id);
                    $topClientes[] = [
                        'mes' => $mes,
                        'cliente' => $usuario ? $usuario->name : null,
                        'total' => (int)$top->total_compras,
                        'cantidad_compras' => (int)$top->cantidad_compras, 
                    ];
                    $totalVentas += $top->total_compras;
                }
            }

            return response()->json([
                'top-clientes' => $topClientes,
                'total_ventas' => $totalVentas
            ]);
        }

        if ($type === 'transacciones') {
            // $orders contiene el resultado del scope agrupado por mes
            $grafico = [];
            foreach ($orders as $order) {
                $grafico[] = [
                    'mes' => $order->mes,
                    'transacciones_exitosas' => (int)$order->transacciones_exitosas,
                    'total_procesado' => (float)$order->total_procesado,
                ];
            }

           

            return response()->json([
                'grafico' => $grafico,
                // 'detalle_tabla' => $detalleTabla, 
                
            ]);
        }

        if ($type === 'transacciones-fallidas') {
            $grafico = [];
            foreach ($orders as $order) {
                $grafico[] = [
                    'mes' => $order->mes,
                    'transacciones_fallidas' => (int)$order->transacciones_fallidas,
                    'total_fallido' => (float)$order->total_fallido,
                ];
            }

            return response()->json([
                'grafico' => $grafico,
            ]);
        }

        if ($type === 'top-productos') {
            // Obtén todos los productos involucrados
            $productIds = $orders->pluck('product_id')->unique()->all();
            $products = \App\Models\Product::whereIn('id', $productIds)->get()->keyBy('id');

            $meses = $orders->pluck('mes')->unique()->sort()->values()->all();

            $topProductos = [];
            foreach ($meses as $mes) {
                $productosMes = $orders->where('mes', $mes);
                $top = $productosMes->sortByDesc('total_ventas')->first();
                if ($top) {
                    $producto = $products->get($top->product_id);
                    $topProductos[] = [
                        'mes' => $mes,
                        'producto' => $producto ? $producto->name : null,
                        'total' => (int)$top->subtotal, 
                    ];
                }
            }

            return response()->json([
                'top-productos' => $topProductos
            ]);
        }

        if ($type === 'top-categorias') {
            $meses = $orders->pluck('mes')->unique()->sort()->values()->all();

            $topCategorias = [];
            foreach ($meses as $mes) {
                $categoriasMes = $orders->where('mes', $mes);
                $top = $categoriasMes->sortByDesc('total_ventas')->first();
                if ($top) {
                    $topCategorias[] = [
                        'mes' => $mes,
                        'categoria' => $top->categoria,
                        'total' => (int)$top->subtotal, // O usa total_ventas si prefieres cantidad
                    ];
                }
            }

            $totalVentas = collect($topCategorias)->sum('total');
            $promedioVentas = count($topCategorias) > 0 ? round($totalVentas / count($topCategorias), 0) : 0;

            return response()->json([
                'top-categorias' => $topCategorias,
                'total_ventas' => $totalVentas,
                'promedio_ventas' => $promedioVentas
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

    public function productsSalesList(Request $request)
    {
        $start = $request->input('start') 
            ? date('Y-m-d', strtotime($request->input('start')))
            : now()->subMonths(12)->startOfMonth()->toDateString();

        $end = $request->input('end') 
            ? date('Y-m-d', strtotime($request->input('end')))
            : now()->endOfMonth()->toDateString();

        $perPage = $request->input('per_page', 15);

        // Usa el scope con el tipo 'top-productos'
        $query = Order::searchReport($start, $end, 'top-productos');
        $ordersPaginated = $query->paginate($perPage);

        // productos relacionados para el detalle
        $productIds = $ordersPaginated->pluck('product_id')->unique()->all();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        // Detalle para tabla paginada
        $detalleTabla = [];
        foreach ($ordersPaginated as $order) {
            $producto = $products->get($order->product_id);
            $detalleTabla[] = [
                'id_producto' => $order->product_id,
                'producto' => $producto ? $producto->name : null,
                'subtotal' => (float)$order->subtotal,
                'margen' => 0,
                'total_ventas' => (int)$order->total_ventas,
                'mes' => $order->mes,
            ];
        }

        return response()->json([
            'detalle_tabla' => $detalleTabla,
            'pagination' => [
                'current_page' => $ordersPaginated->currentPage(),
                'last_page' => $ordersPaginated->lastPage(),
                'per_page' => $ordersPaginated->perPage(),
                'total' => $ordersPaginated->total(),
            ]
        ]);
    }

    public function transactionsList(Request $request)
    {
        $start = $request->input('start') 
            ? date('Y-m-d', strtotime($request->input('start')))
            : now()->subMonths(12)->startOfMonth()->toDateString();

        $end = $request->input('end') 
            ? date('Y-m-d', strtotime($request->input('end')))
            : now()->endOfMonth()->toDateString();

        $perPage = $request->input('per_page', 15);

        // Consulta directa, no agrupada
        $query = \App\Models\Order::with('user')
            ->where('status', 'completed')
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at');

        $ordersPaginated = $query->paginate($perPage);

        $detalleTabla = [];
        foreach ($ordersPaginated as $order) {
            $detalleTabla[] = [
                'id' => $order->id,
                'cliente' => $order->user ? $order->user->name : null,
                'monto' => $order->amount,
                'fecha' => $order->created_at ? $order->created_at->toDateString() : null,
                'estado' => $order->status,
            ];
        }

        return response()->json([
            'detalle_tabla' => $detalleTabla,
            'pagination' => [
                'current_page' => $ordersPaginated->currentPage(),
                'last_page' => $ordersPaginated->lastPage(),
                'per_page' => $ordersPaginated->perPage(),
                'total' => $ordersPaginated->total(),
            ]
        ]);
    }

    public function clientsList(Request $request)
    {
        $start = $request->input('start') 
        ? date('Y-m-d', strtotime($request->input('start')))
        : now()->subMonths(12)->startOfMonth()->toDateString();

    $end = $request->input('end') 
        ? date('Y-m-d', strtotime($request->input('end')))
        : now()->endOfMonth()->toDateString();

    $perPage = $request->input('per_page', 15);

    $query = \App\Models\Order::with('user')
        ->where('status', 'completed')
        ->whereBetween('created_at', [$start, $end])
        ->orderByDesc('created_at');

    $ordersPaginated = $query->paginate($perPage);

    $detalleTabla = [];
    foreach ($ordersPaginated as $order) {
        $detalleTabla[] = [
            'id' => $order->id,
            'cliente' => $order->user ? $order->user->name : null,
            'monto' => $order->amount,
            'fecha' => $order->created_at->toDateString(),
            'estado' => $order->status,
        ];
    }

    return response()->json([
        'detalle_tabla' => $detalleTabla,
        'pagination' => [
            'current_page' => $ordersPaginated->currentPage(),
            'last_page' => $ordersPaginated->lastPage(),
            'per_page' => $ordersPaginated->perPage(),
            'total' => $ordersPaginated->total(),
        ]
    ]);
    }

    public function failedTransactionsList(Request $request)
    {
        $start = $request->input('start') 
            ? date('Y-m-d', strtotime($request->input('start')))
            : now()->subMonths(12)->startOfMonth()->toDateString();

        $end = $request->input('end') 
            ? date('Y-m-d', strtotime($request->input('end')))
            : now()->endOfMonth()->toDateString();

        $perPage = $request->input('per_page', 15);

        $query = \App\Models\Order::with('user')
            ->where('status', 'failed')
            ->whereBetween('created_at', [$start, $end])
            ->orderByDesc('created_at');

        $ordersPaginated = $query->paginate($perPage);

        $detalleTabla = [];
        foreach ($ordersPaginated as $order) {
            $detalleTabla[] = [
                'id' => $order->id,
                'cliente' => $order->user ? $order->user->name : null,
                'monto' => $order->amount,
                'fecha' => $order->created_at ? $order->created_at->toDateString() : null,
                'estado' => $order->status,
            ];
        }

        return response()->json([
            'detalle_tabla' => $detalleTabla,
            'pagination' => [
                'current_page' => $ordersPaginated->currentPage(),
                'last_page' => $ordersPaginated->lastPage(),
                'per_page' => $ordersPaginated->perPage(),
                'total' => $ordersPaginated->total(),
            ]
        ]);
    }
}
