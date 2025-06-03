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
        $priceObj = Price::where('product_id', $product->id)->where('is_active', 1)->where('unit', $this->unit)->first();
        $price = $priceObj->price ?? 0;
        $unit = $priceObj->unit;
        $totalPrice = $price * $this->quantity;

        
        
    
        return [
            "id" => $this->id,
            "user_id" => $this->user_id,
            "product_id" => $this->product_id,
            "quantity" => $this->quantity,
            "price" => $price,
            "unit" => $unit,
            "subtotal" => $totalPrice,
            // "created_at" => $this->created_at,
            // "updated_at" => $this->updated_at,
        ];
    }
}
