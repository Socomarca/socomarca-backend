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

        $type = $request->input('type', 'sales'); 

        if ($type === 'top-municipalities') {
            $topMunicipalities = \App\Models\Order::searchReport($start, $end, 'top-municipalities')->get();

            // Calcula los totales sumando los meses consultados
            $total_purchases = $topMunicipalities->sum(function($item) {
                return (int) $item->total_purchases;
            });
            $quantity = $topMunicipalities->sum(function($item) {
                return (int) $item->quantity;
            });

            return response()->json([
                'top_municipalities' => $topMunicipalities,
                'total_purchases' => $total_purchases,
                'quantity' => $quantity,
            ]);
        }

        // Solo para los tipos que usan Eloquent y relaciones:
        $orders = \App\Models\Order::searchReport($start, $end, $type)->with('user')->get();

        if ($type === 'top-customers') {
            $userIds = $orders->pluck('user_id')->unique()->all();
            $users = \App\Models\User::whereIn('id', $userIds)->get()->keyBy('id');

            $months = $orders->pluck('month')->unique()->sort()->values()->all();

            $topClients = [];
            $totalSales = 0;

            foreach ($months as $month) {
                $clientsMonth = $orders->where('month', $month);
                $top = $clientsMonth->sortByDesc('total_purchases')->first();
                if ($top) {
                    $user = $users->get($top->user_id);
                    $topClients[] = [
                        'month' => $month,
                        'customer' => $user ? $user->name : null,
                        'total_purchases' => (int)$top->total_purchases,
                        'quantity_purchases' => (int)$top->quantity_purchases, 
                    ];
                    $totalSales += $top->total_purchases;
                }
            }

            return response()->json([
                'top_customers' => $topClients,
                'total_sales' => $totalSales
            ]);
        }

        if ($type === 'transactions') {
            $chart = [];
            foreach ($orders as $order) {
                $chart[] = [
                    'month' => $order->month,
                    'transactions' => (int)$order->transactions,
                    'total' => (int)$order->total,
                ];
            }
            return response()->json([
                'chart' => $chart,
            ]);
        }

        if ($type === 'transactions-failed') {
            $chart = [];
            foreach ($orders as $order) {
                $chart[] = [
                    'month' => $order->month,
                    'failed_transactions' => (int)$order->transactions_failed,
                    'total_failed' => (float)$order->total,
                ];
            }
            return response()->json([
                'chart' => $chart,
            ]);
        }

        if ($type === 'top-products') {
            $productIds = $orders->pluck('product_id')->unique()->all();
            $products = \App\Models\Product::whereIn('id', $productIds)->get()->keyBy('id');

            $months = $orders->pluck('month')->unique()->sort()->values()->all();

            $topProducts = [];
            $total_sales = 0;
            foreach ($months as $month) {
                $productsMonth = $orders->where('month', $month);
                $top = $productsMonth->sortByDesc('total_sales')->first();
                if ($top) {
                    $product = $products->get($top->product_id);
                    $topProducts[] = [
                        'month' => $month,
                        'product' => $product ? $product->name : null,
                        'total' => (int)$top->subtotal, 
                    ];
                    $total_sales += (int)$top->subtotal;
                }
            }

            return response()->json([
                'top_products' => $topProducts,
                'total_sales' => $total_sales,
            ]);
        }

        if ($type === 'revenue') {
            $revenues = [];
            $total_revenue = 0;
            foreach ($orders as $order) {
                $revenues[] = [
                    'month' => $order->month,
                    'revenue' => (int)$order->total_month
                ];
                $total_revenue += (int)$order->total_month;
            }
            return response()->json([
                'revenues' => $revenues,
                'total_revenue' => $total_revenue,
            ]);
        }

        if ($type === 'top-categories') {
            $months = $orders->pluck('month')->unique()->sort()->values()->all();

            $topCategories = [];
            foreach ($months as $month) {
                $categoriesMonth = $orders->where('month', $month);
                $top = $categoriesMonth->sortByDesc('total_sales')->first();
                if ($top) {
                    $topCategories[] = [
                        'month' => $month,
                        'category' => $top->category,
                        'total' => (int)$top->subtotal,
                    ];
                }
            }

            $totalSales = collect($topCategories)->sum('total');
            $averageSales = count($topCategories) > 0 ? round($totalSales / count($topCategories), 0) : 0;

            return response()->json([
                'top_categories' => $topCategories,
                'total_sales' => $totalSales,
                'average_sales' => $averageSales
            ]);
        }

        // For sales and buyers
        $months = $orders->pluck('month')->unique()->sort()->values()->all();
        $clients = $orders->pluck('user.name')->unique()->values()->all();

        $totals = [];
        $totalBuyersPerMonth = [];

        foreach ($months as $month) {
            $salesByClient = [];
            $totalMonth = 0;
            $buyersMonth = $orders->where('month', $month)->pluck('user_id')->unique()->count();

            foreach ($clients as $client) {
                $total = $orders->where('month', $month)
                    ->where('user.name', $client)
                    ->sum('total');
                $salesByClient[] = [
                    'customer' => $client,
                    'total' => $total
                ];
                $totalMonth += $total;
            }
            $totals[] = [
                'month' => $month,
                'sales_by_customer' => $salesByClient,
                'total_month' => $totalMonth
            ];
            $totalBuyersPerMonth[] = [
                'month' => $month,
                'total_buyers' => $buyersMonth
            ];
        }

        return response()->json([
            'months' => $months,
            'customers' => $clients,
            'totals' => $totals,
            'total_buyers_per_month' => $totalBuyersPerMonth
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
        $query = Order::searchReport($start, $end, 'top-products');
        $ordersPaginated = $query->paginate($perPage);

        // productos relacionados para el detalle
        $productIds = $ordersPaginated->pluck('product_id')->unique()->all();
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        // Detalle para tabla paginada
        $detalleTabla = [];
        foreach ($ordersPaginated as $order) {
            $producto = $products->get($order->product_id);
            $detalleTabla[] = [
                'product_id' => $order->product_id,
                'product' => $producto ? $producto->name : null,
                'subtotal' => (float)$order->subtotal,
                'margen' => 0,
                'total_sales' => (int)$order->total_sales,
                'month' => $order->month,
            ];
        }

        return response()->json([
            'table_detail' => $detalleTabla,
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
                'customer' => $order->user ? $order->user->name : null,
                'amount' => $order->amount,
                'date' => $order->created_at ? $order->created_at->toDateString() : null,
                'status' => $order->status,
            ];
        }

        return response()->json([
            'table_detail' => $detalleTabla,
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
                'customer' => $order->user ? $order->user->name : null,
                'amount' => $order->amount,
                'date' => $order->created_at->toDateString(),
                'status' => $order->status,
            ];
        }

        return response()->json([
            'table_detail' => $detalleTabla,
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
                'client' => $order->user ? $order->user->name : null,
                'amount' => $order->amount,
                'date' => $order->created_at ? $order->created_at->toDateString() : null,
                'status' => $order->status,
            ];
        }

        return response()->json([
            'table_detail' => $detalleTabla,
            'pagination' => [
                'current_page' => $ordersPaginated->currentPage(),
                'last_page' => $ordersPaginated->lastPage(),
                'per_page' => $ordersPaginated->perPage(),
                'total' => $ordersPaginated->total(),
            ]
        ]);
    }
}
