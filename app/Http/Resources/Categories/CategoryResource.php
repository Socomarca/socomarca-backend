<?php

namespace App\Http\Resources\Categories;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
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
            'code' => $this->code,
            'level' => $this->level,
            'key' => $this->key,
            'subcategories' => $this->subcategories,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
