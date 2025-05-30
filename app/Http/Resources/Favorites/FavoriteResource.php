<?php

namespace App\Http\Resources\Favorites;

use App\Models\FavoriteList;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $favoriteListId = $this->favorite_list_id;
        $productId = $this->product_id;

        return
        [
            'id' => $this->id,
            'favorite_list' => FavoriteList::select('id', 'name', 'created_at', 'updated_at')->where('id', $favoriteListId)->first(),
            'product' => Product::select('id', 'name', 'description', 'sku', 'status', 'created_at', 'updated_at')->where('id', $productId)->first(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
