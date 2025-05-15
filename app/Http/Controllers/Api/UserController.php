<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\DestroyUserRequest;
use App\Http\Requests\ShowUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
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

    public function store(StoreUserRequest $storeRequest)
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

    public function show(ShowUserRequest $showRequest, $id)
    {
        $showRequest->validated();

        if (!DB::table('users')->where('id', $id)->exists())
        {
            return response()->json(
            [
                'message' => 'The selected user in params is invalid.',
                'errors' => array(
                    'toll_company' => array('The selected user in params is invalid.'))
            ], 422);
        }

        $resources = User::where('id', $id)->get();

        return response()->json(['resources' => $resources]);
    }

    public function update(UpdateUserRequest $updateRequest, $id)
    {
        $data = $updateRequest->validated();

        if (!DB::table('users')->where('id', $id)->exists())
        {
            return response()->json(
            [
                'message' => 'The selected user in params is invalid.',
                'errors' => array(
                    'toll_company' => array('The selected user in params is invalid.'))
            ], 422);
        }

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

    public function destroy(DestroyUserRequest $destroyRequest, $id)
    {
        $destroyRequest->validated();

        if (!DB::table('users')->where('id', $id)->exists())
        {
            return response()->json(
            [
                'message' => 'The selected user in params is invalid.',
                'errors' => array(
                    'toll_company' => array('The selected user in params is invalid.'))
            ], 422);
        }

        $user = User::find($id);

        $user->delete();

        return response()->json(['message' => 'The selected user has been deleted']);
    }
}
