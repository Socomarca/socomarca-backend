<?php

namespace App\Http\Resources\FavoritesList;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class FavoriteListCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return $this->collection->map(function ($favoriteList) {
            return [
                'id' => $favoriteList->id,
                'name' => $favoriteList->name,
                'user_id' => $favoriteList->user_id,
                
            ];
        })->all();
    }
}
