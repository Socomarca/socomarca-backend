<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListsFavorites\DestroyRequest;
use App\Http\Requests\ListsFavorites\IndexRequest;
use App\Http\Requests\ListsFavorites\ShowRequest;
use App\Http\Requests\ListsFavorites\StoreRequest;
use App\Http\Requests\ListsFavorites\UpdateRequest;
use App\Http\Resources\ListsFavorites\ListFavoriteCollection;
use Illuminate\Http\Request;
use App\Models\ListFavorite;

class ListFavoriteController extends Controller
{
    public function index(IndexRequest $indexRequest)
    {
        $data = $indexRequest->validated();

        $userId = $data['user_id'];

        $listsFavorites = ListFavorite::where('user_id', $userId)->get();
        $data = new ListFavoriteCollection($listsFavorites);
        return $data;

    }

    public function store(StoreRequest $storeRequest)
    {
        $data = $storeRequest->validated();

        $listFavorite = new ListFavorite;

        $listFavorite->name = $data['name'];
        $listFavorite->user_id = $data['user_id'];

        $listFavorite->save();

        return response()->json(['message' => 'The list has been added'], 201);
    }

    public function show(ShowRequest $showRequest, $id)
    {
        $showRequest->validated();

        if (!ListFavorite::find($id))
        {
            return response()->json(
            [
                'message' => 'List not found.',
            ], 404);
        }

        $listsFavorites = ListFavorite::where('id', $id)->get();

        $data = new ListFavoriteCollection($listsFavorites);

        return response()->json($data[0]);
    }

    public function update(UpdateRequest $updateRequest, $id)
    {
        $data = $updateRequest->validated();

        $listFavorite = ListFavorite::find($id);

        $listFavorite->name = $data['name'];
        $listFavorite->user_id = $data['user_id'];

        $listFavorite->save();

        return response()->json(['message' => 'The List has been updated']);
    }

    public function destroy(DestroyRequest $destroyRequest, $id)
    {
        $destroyRequest->validated();

        $listFavorite = ListFavorite::find($id);

        if (!$listFavorite)
        {
            return response()->json(
            [
                'message' => 'List not found.',
            ], 404);
        }

        
        $listFavorite->delete();

        return response()->json(['message' => 'List deleted.']);
    }
}
