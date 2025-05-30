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
        return
        [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'subcategory' => $this->subcategory,
            'brand' => $this->brand,
            'price_id' => $this->price_id,
            'price' => Price::where('id', $this->price_id)->where('is_active', 1)->pluck('price')->fisrt(),
            'sku' => $this->sku,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
