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
            'favorite_list' => FavoriteList::select('id', 'name')->where('id', $favoriteListId)->first(),
            'product' => Product::select('id', 'name', 'description', 'sku', 'status')->where('id', $productId)->first(),
            
        ];
    }
}
