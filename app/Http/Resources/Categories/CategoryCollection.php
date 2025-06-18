<?php

namespace App\Http\Resources\Categories;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CategoryCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->collection->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
                'code' => $category->code,
                'level' => $category->level,
                'key' => $category->key,
                'subcategories_count' => $category->subcategories_count ?? ($category->subcategories ? $category->subcategories->count() : 0),
                'products_count' => $category->products_count ?? ($category->products ? $category->products->count() : 0),
                'created_at' => $category->created_at,
                'updated_at' => $category->updated_at,
            ];
        })->toArray();
    }
}
