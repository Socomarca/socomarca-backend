<?php

namespace App\Http\Resources\CartItems;

use App\Http\Resources\Products\ProductResource;
use App\Models\Price;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $product = Product::where('id', $this->product_id)->first();
        $priceObj = Price::where('id', $product->price_id)->where('is_active', 1)->first();
        $price = $priceObj->price;
        $totalPrice = $price * $this->quantity;

        return [
            "id" => $this->id,
            "user" => $this->user,
            "product" => $this->product,
            "quantity" => $this->quantity,
            "price" => $price,
            "subtotal" => $totalPrice,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
