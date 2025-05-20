<?php

namespace App\Http\Resources\Products;

use App\Models\Brand;
use App\Models\Category;
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
        $id = $this->id;
        $categoryId = $this->category_id;
        $subcategoryId = $this->subcategory_id;
        $brandId = $this->brand_id;

        return
        [
            'id' => $id,
            'name' => $this->name,
            'description' => $this->description,
            'category' => Category::where('id', $categoryId)->first(),
            'subcategory' => Subcategory::where('id', $subcategoryId)->first(),
            'brand' => Brand::where('id', $brandId)->first(),
            'sku' => $this->sku,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
