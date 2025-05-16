<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\DestroyRequest;
use App\Http\Requests\Users\ShowRequest;
use App\Http\Requests\Users\StoreRequest;
use App\Http\Requests\Users\UpdateRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $resources = User::all();

        return response()->json(['resources' => $resources]);
    }

    public function store(StoreRequest $storeRequest)
    {
        $data = $storeRequest->validated();

        $user = new User;

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->phone = $data['phone'];
        $user->rut = $data['rut'];
        $user->business_name = $data['business_name'];
        $user->is_active = $data['is_active'];

        $user->save();

        return response()->json(['message' => 'The user has been added']);
    }

    public function show(ShowRequest $showRequest, $id)
    {
        $showRequest->validated();

        if (!DB::table('addresses')->where('id', $id)->exists())
        {
            return response()->json(
            [
                'message' => 'User not found.',
            ], 404);
        }

        $resources = User::where('id', $id)->get();

        return response()->json(['resources' => $resources]);
    }

    public function update(UpdateRequest $updateRequest, $id)
    {
        $data = $updateRequest->validated();

        $user = User::find($id);

        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->phone = $data['phone'];
        $user->rut = $data['rut'];
        $user->business_name = $data['business_name'];
        $user->is_active = $data['is_active'];

        $user->save();

        return response()->json(['message' => 'The selected user has been updated']);
    }

    public function destroy(DestroyRequest $destroyRequest, $id)
    {
        $destroyRequest->validated();

        if (!DB::table('users')->where('id', $id)->exists())
        {
            return response()->json(
            [
                'message' => 'User not found.',
            ], 404);
        }

        $user = User::find($id);

        $user->delete();

        return response()->json(['message' => 'The selected user has been deleted']);
    }
}
