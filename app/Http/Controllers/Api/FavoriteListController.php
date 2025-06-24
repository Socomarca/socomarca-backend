<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FavoritesList\DestroyRequest;
use App\Http\Requests\FavoritesList\IndexRequest;
use App\Http\Requests\FavoritesList\ShowRequest;
use App\Http\Requests\FavoritesList\StoreRequest;
use App\Http\Requests\FavoritesList\UpdateRequest;
use App\Http\Resources\Favorites\FavoriteResource;
use App\Http\Resources\FavoritesList\FavoriteListCollection;
use App\Http\Resources\FavoritesList\FavoriteListResource;
use App\Models\FavoriteList;
use Illuminate\Support\Facades\Auth;

class FavoriteListController extends Controller
{
    public function index()
    {

        $userId = Auth::id();

        $favoritesList = FavoriteList::where('user_id', $userId)->get();

        $data = new FavoriteListCollection($favoritesList);

        return $data;
    }

    public function store(StoreRequest $storeRequest)
    {
        $data = $storeRequest->validated();

        $favoriteList = new FavoriteList;

        $favoriteList->name = $data['name'];
        $favoriteList->user_id = Auth::user()->id;

        $favoriteList->save();

        return response()->json(['message' => 'The favorites list has been added'], 201);
    }

    public function show(FavoriteList $favoriteList)
    {
        if ($favoriteList->user_id !== Auth::user()->id) {
            abort(403, 'Unauthorized action');
        }

        return $favoriteList->toResource(FavoriteListResource::class);
    }

    public function update(UpdateRequest $updateRequest, $id)
    {
        $data = $updateRequest->validated();

        $favoriteList = FavoriteList::where('id', $id)
        ->where('user_id', Auth::user()->id)
        ->first();

        if (!$favoriteList)
        {
            return response()->json(
            [
                'message' => 'Favorites list not found.',
            ], 404);
        }

        $favoriteList->name = $data['name'];


        $favoriteList->save();

        return response()->json(['message' => 'The selected favorites list has been updated']);
    }

    public function destroy(DestroyRequest $destroyRequest, $id)
    {
        $destroyRequest->validated();

        $favoriteList = FavoriteList::where('id', $id)
        ->where('user_id', Auth::user()->id)
        ->first();

        if (!$favoriteList)
        {
            return response()->json(
            [
                'message' => 'Favorites list not found.',
            ], 404);
        }

        $favoriteList->delete();

        return response()->json(['message' => 'The selected favorites list has been deleted']);
    }
}
