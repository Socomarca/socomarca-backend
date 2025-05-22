<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Favorites\DestroyRequest;
use Illuminate\Http\Request;
use App\Http\Requests\Favorites\IndexRequest;
use App\Http\Requests\Favorites\StoreRequest;
use App\Http\Resources\Favorites\FavoriteCollection;
use App\Models\Favorite;
use App\Models\ListFavorite;

class FavoriteController extends Controller
{
    public function index(IndexRequest $indexRequest)
    {
        $data = $indexRequest->validated();

        $userId = $data['user_id'];
        $favoriteId = $data['list_favorite_id'];

        $listsfavorites = ListFavorite::where('user_id', $userId)->where('id', $favoriteId)->exists();

        if(!$listsfavorites){
            return response()->json(
            [
                'message' => 'Favorites not found.',
            ], 404);
        }

        $favorites = Favorite::where('list_favorite_id', $favoriteId)->get();
        $data = new FavoriteCollection($favorites);
        return $data;
        // return FavoriteResource::collection($favorites);
    }

    public function store(StoreRequest $storeRequest)
    {
        $data = $storeRequest->validated();

        $favorite = new favorite;

        $favorite->list_favorite_id = $data['list_favorite_id'];
        $favorite->product_id = $data['product_id'];

        $favorite->save();

        return response()->json(['message' => 'The favorite product has been added'], 201);
    }

    public function destroy(DestroyRequest $destroyRequest, $id)
    {
        $destroyRequest->validated();

        $favorite = Favorite::find($id);
        if (!$favorite)
        {
            return response()->json(
            [
                'message' => 'Favorite not found.',
            ], 404);
        }

        $favorite->delete();

        return response()->json(['message' => 'Favorite deleted.']);
    }
}
