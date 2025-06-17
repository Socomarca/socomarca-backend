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
        $product = $this->product;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'category' => $product->category ? [
                'id' => $product->category->id,
                'name' => $product->category->name,
            ] : null,
            'subcategory' => $product->subcategory ? [
                'id' => $product->subcategory->id,
                'name' => $product->subcategory->name,
            ] : null,
            'brand' => $product->brand ? [
                'id' => $product->brand->id,
                'name' => $product->brand->name,
            ] : null,
            'unit' => $product->prices->first()?->unit,
            'price' => $product->prices->first()?->price,
            'stock' => $product->prices->first()?->stock,
            'image' => $product->image,
            'sku' => $product->sku,
            'is_favorite' => true, // O la lógica que corresponda
        ];
    }
}
