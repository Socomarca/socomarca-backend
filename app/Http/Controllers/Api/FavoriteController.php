<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Favorites\DestroyRequest;
use App\Http\Requests\Favorites\IndexRequest;
use App\Http\Requests\Favorites\StoreRequest;
use App\Http\Resources\Favorites\FavoriteCollection;
use App\Http\Resources\FavoritesList\FavoriteListResource;
use App\Models\Favorite;
use App\Models\FavoriteList;
use Illuminate\Support\Facades\Auth;

class FavoriteController extends Controller
{
    public function index(){
       
        $userId = Auth::user()->id;
        $lists = FavoriteList::with([
            'favorites.product.category',
            'favorites.product.subcategory'
        ])->where('user_id', $userId)->get();

        return FavoriteListResource::collection($lists);
    }

    public function store(StoreRequest $storeRequest)
    {
        $data = $storeRequest->validated();

        // Verifica que la lista pertenezca al usuario autenticado
        $favoriteList = FavoriteList::where('id', $data['favorite_list_id'])
            ->where('user_id', Auth::user()->id)
            ->first();

        if (!$favoriteList) {
            return response()->json(['message' => 'Favorite list not found or does not belong to the user.'], 404);
        }

        // Evita duplicados
        $exists = Favorite::where('favorite_list_id', $data['favorite_list_id'])
            ->where('product_id', $data['product_id'])
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Product already in favorites.'], 409);
        }

        $favorite = new Favorite;
        $favorite->favorite_list_id = $data['favorite_list_id'];
        $favorite->product_id = $data['product_id'];
        $favorite->save();

        return response()->json(['message' => 'The favorite product has been added'], 201);
    }

    public function destroy(DestroyRequest $destroyRequest, $id)
    {
        $destroyRequest->validated();

        $favorite = Favorite::where('id', $id)
        ->whereHas('favoriteList', function ($q) {
            $q->where('user_id', Auth::user()->id);
        })
        ->first();

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
