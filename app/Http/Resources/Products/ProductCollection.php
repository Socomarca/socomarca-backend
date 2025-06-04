<?php

namespace App\Http\Resources\Products;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProductCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request)
    {
        return $this->collection->flatMap(function ($product) use ($request) {
            return $product->prices->map(function ($price) use ($product, $request) {
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
                    'price' => isset($price->price) ? (int) $price->price : null,
                    'stock' => isset($price->stock) ? (int) $price->stock : null,
                    'image' => $product->image ?? null,
                    'sku' => $product->sku ?? null,
                    'is_favorite' => false, 
                ];
            });
        })->values();
    }
}
