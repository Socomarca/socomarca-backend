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
            //'favorites' => new WithoutFavoriteListCollection($this->favorites),
            'favorites' => \App\Http\Resources\Favorites\FavoriteResource::collection($this->favorites),

        ];
    }
}
