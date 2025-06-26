<?php

namespace App\Http\Resources\Products;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;

class ProductCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        
        return $this->collection->flatMap(function ($product) {
            
            $isFavorite = false;
            if (Auth::check()) {
                $isFavorite = $product->favorites()->whereHas('favoriteList', function ($q) {
                    $q->where('user_id', Auth::id());
                })->exists();
            }

            
            return $product->prices->map(function ($price) use ($product, $isFavorite) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => [
                        'id' => $product->category->id,
                        'name' => $product->category->name,
                    ],
                    'subcategory' => [
                        'id' => $product->subcategory->id,
                        'name' => $product->subcategory->name,
                    ],
                    'brand' => [
                        'id' => $product->brand->id,
                        'name' => $product->brand->name,
                    ],
                    'unit' => $price->unit,
                    'price' => (float) $price->price,
                    'stock' => isset($price->stock) ? (int) $price->stock : null,
                    'image' => $product->image ?? null,
                    'sku' => $product->sku ?? null,
                    'is_favorite' => $isFavorite,
                ];
            });
        })->values();
    }
}