<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Favorites\DestroyRequest;
use App\Http\Requests\Favorites\IndexRequest;
use App\Http\Requests\Favorites\StoreRequest;
use App\Http\Resources\Favorites\FavoriteCollection;
use App\Models\Favorite;
use App\Models\FavoriteList;

class FavoriteController extends Controller
{
    public function index(IndexRequest $indexRequest)
    {
        $data = $indexRequest->validated();

        $userId = $data['user_id'];
        $favoriteListId = $data['favorite_list_id'];

        $favoritesList = FavoriteList::where('user_id', $userId)->where('id', $favoriteListId)->exists();

        if(!$favoritesList)
        {
            return response()->json(
            [
                'message' => 'Favorite product not found.',
            ], 404);
        }

        $favorites = Favorite::where('favorite_list_id', $favoriteListId)->get();

        $data = new FavoriteCollection($favorites);

        return $data;
    }

    public function store(StoreRequest $storeRequest)
    {
        $data = $storeRequest->validated();

        $favorite = new favorite;

        $favorite->favorite_list_id = $data['favorite_list_id'];
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
                'message' => 'Favorite product not found.',
            ], 404);
        }

        $favorite->delete();

        return response()->json(['message' => 'The selected favorite product has been deleted']);
    }
}
