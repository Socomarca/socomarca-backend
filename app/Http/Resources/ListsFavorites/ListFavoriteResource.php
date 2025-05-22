<?php

namespace App\Http\Resources\ListsFavorites;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListFavoriteResource extends JsonResource
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
            'name' => $this->name,
            'user' => $this->user,
            'favorites' =>$this->favorites,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
