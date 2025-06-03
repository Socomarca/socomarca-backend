<?php

namespace App\Http\Resources\FavoritesList;

use App\Http\Resources\Favorites\WithoutFavoriteListCollection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FavoriteListResource extends JsonResource
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
            'user' => $this->user,
            'favorites' => new WithoutFavoriteListCollection($this->favorites),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
