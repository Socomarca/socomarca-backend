<?php

namespace App\Http\Resources\Products;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Price;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Obtiene el precio activo más reciente
        $activePrice = $this->prices()
            ->where('is_active', true)
            ->orderByDesc('valid_from')
            ->first();

        $isFavorite = false;

        $userId = 1;

        $isFavorite = $this->favorites()
            ->whereHas('favoriteList', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->exists();

        return
        [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'subcategory' => $this->subcategory,
            'brand' => $this->brand,
            'price_id' => $this->price_id,
            'price' => $activePrice,
            'sku' => $this->sku,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_favorite' => $isFavorite,
        ];
    }
}
