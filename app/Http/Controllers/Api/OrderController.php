<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Orders\CreateFromCartRequest;
use App\Http\Requests\Orders\IndexRequest;
use App\Http\Requests\Orders\PayOrderRequest;
use App\Http\Resources\Orders\OrderCollection;
use App\Http\Resources\Orders\OrderResource;
use App\Http\Resources\Orders\PaymentResource;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Services\WebpayService;
use Illuminate\Support\Facades\Log;


use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Brand;
use App\Models\Price;


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
        //$this->createCart();
        $carts = CartItem::where('user_id', $data['user_id'])->get();

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
            $total = 0;
            foreach ($carts as $cart) {

                //TODO: Se debe obtener el precio de la unidad desde el carrito
                $price = $cart->product->prices->where('unit', '=', 'kg')->first();
                $total += $price->price * $cart->quantity;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cart->product_id,
                    'unit' => 'unidad',
                    'quantity' => $cart->quantity,
                    'price' => $price->price
                ]);
            }

            //Actualizar el subtotal de la orden
            $order->subtotal = $total;
            $order->amount = $total;
            $order->save();

            // Limpiar el carrito
            CartItem::where('user_id', $data['user_id'])->delete();

            DB::commit();

            return new OrderResource($order);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
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


    //NOTA: No eliminar este método, es para crear un carrito de prueba
    public function createCart(){
        $category = Category::factory()->create();
        $subcategory = Subcategory::factory()->create([
            'category_id' => $category->id
        ]);
        $brand = Brand::factory()->create();
    
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'subcategory_id' => $subcategory->id,
            'brand_id' => $brand->id
        ]);
    
        // Crear productos con sus precios
        $price1 = Price::factory()->create([
            'product_id' => $product->id,
            'price_list_id' => fake()->word(),
            'unit' => 'kg',
            'price' => 100,
            'valid_from' => now()->subDays(1),
            'valid_to' => null,
            'is_active' => true
        ]);
    
        CartItem::create([
            'user_id' => 12,
            'product_id' => $price1->product_id,
            'quantity' => 2,
            'price' => $price1->price
        ]);
    }
}
