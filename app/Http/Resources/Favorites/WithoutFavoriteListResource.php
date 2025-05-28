<?php

namespace App\Http\Resources\Favorites;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithoutFavoriteListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $productId = $this->product_id;

        return
        [
            'id' => $this->id,
            'product' => Product::select('id', 'name', 'description', 'sku', 'status', 'created_at', 'updated_at')->where('id', $productId)->first(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
