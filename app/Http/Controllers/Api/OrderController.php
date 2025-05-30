<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\CreateFromCartRequest;
use App\Http\Requests\Orders\IndexRequest;
use App\Http\Requests\Orders\PayOrderRequest;
use App\Http\Resources\Orders\OrderCollection;
use App\Http\Resources\Orders\OrderResource;
use App\Http\Resources\Orders\PaymentResource;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use App\Services\WebpayService;

class OrderController extends Controller
{
    protected $webpayService;

    public function __construct(WebpayService $webpayService)
    {
        $this->webpayService = $webpayService;
    }

    public function index(IndexRequest $request)
    {
        $data = $request->validated();
        $orders = Order::where('user_id', $data['user_id'])->get();
        return new OrderCollection($orders);
    }

    public function createFromCart(CreateFromCartRequest $request)
    {
        $data = $request->validated();
        $carts = Cart::where('user_id', $data['user_id'])->get();

        if ($carts->isEmpty()) {
            return response()->json(['message' => 'El carrito está vacío'], 400);
        }

        try {
            DB::beginTransaction();

            // Calcular totales
            $subtotal = $carts->sum(function ($cart) {
                return $cart->price * $cart->quantity;
            });

            // Crear la orden
            $order = Order::create([
                'user_id' => $data['user_id'],
                'subtotal' => $subtotal,
                'amount' => $subtotal,
                'status' => 'pending'
            ]);

            // Crear los items de la orden
            foreach ($carts as $cart) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cart->product_id,
                    'unit' => 'unidad',
                    'quantity' => $cart->quantity,
                    'price' => $cart->price
                ]);
            }

            // Limpiar el carrito
            Cart::where('user_id', $data['user_id'])->delete();

            DB::commit();

            return new OrderResource($order);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear la orden: ' . $e->getMessage()], 500);
        }
    }

    public function payOrder(PayOrderRequest $request)
    {
        $data = $request->validated();
        $order = Order::find($data['order_id']);

        if (!$order) {
            return response()->json(['message' => 'Orden no encontrada'], 404);
        }

        if ($order->user_id !== $data['user_id']) {
            return response()->json(['message' => 'No tienes permiso para pagar esta orden'], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json(['message' => 'La orden no está pendiente de pago'], 400);
        }

        try {
            $paymentResponse = $this->webpayService->createTransaction($order);
            
            return new PaymentResource((object)[
                'order' => $order,
                'payment_url' => $paymentResponse['url'],
                'token' => $paymentResponse['token']
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al procesar el pago: ' . $e->getMessage(), 'order' => $order], 500);
        }
    }
}