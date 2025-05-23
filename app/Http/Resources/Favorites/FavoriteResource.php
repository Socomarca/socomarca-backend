<?php

namespace App\Http\Resources\Favorites;

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
        return [
            'id' => $this->id,
            'list_favorite' => $this->listFavorite,
            'product' => $this->product,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
