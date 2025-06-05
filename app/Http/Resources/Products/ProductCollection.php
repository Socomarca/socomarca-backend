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
            
            $filters = $request->input('filters', []);
            $min = null;
            $max = null;
            foreach ($filters as $filter) {
                if (isset($filter['field']) && $filter['field'] === 'price') {
                    $min = isset($filter['min']) ? (float)$filter['min'] : null;
                    $max = isset($filter['max']) ? (float)$filter['max'] : null;
                }
            }

            return $product->prices
                ->filter(function ($price) use ($min, $max) {
                    if ($min !== null && $price->price < $min) return false;
                    if ($max !== null && $price->price > $max) return false;
                    return true;
                })
                ->map(function ($price) use ($product) {
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
                        'is_favorite' => false, 
                    ];
                });
        })->values();
    }
}
