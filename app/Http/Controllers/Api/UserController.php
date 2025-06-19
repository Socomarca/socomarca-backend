<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SearchUsersRequest;
use App\Http\Requests\Users\DestroyRequest;
use App\Http\Requests\Users\ShowRequest;
use App\Http\Requests\Users\StoreRequest;
use App\Http\Requests\Users\UpdateRequest;
use App\Http\Resources\Users\ProfileResource;
use App\Http\Resources\Users\UserCollection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index()
    {
        $perPage = request()->input('per_page', 20);
        $users = User::with('roles')->paginate($perPage);

        $data = new UserCollection($users);

        return $data;
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

        return response()->json(['message' => 'The user has been added'], 201);
    }

    public function show($id)
    {
        if (!User::find($id))
        {
            return response()->json(
            [
                'message' => 'User not found.',
            ], 404);
        }

        $users = User::with('billing_address')
            ->with('shipping_addresses')
            ->where('id', $id)
            ->get();

        $data = new UserCollection($users);

        return response()->json($data[0]);
    }

    public function update(UpdateRequest $updateRequest, $id)
    {
        $data = $updateRequest->validated();

        if (!User::find($id))
        {
            return response()->json(
            [
                'message' => 'User not found.',
            ], 404);
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

    public function destroy(DestroyRequest $destroyRequest, $id)
    {
        $destroyRequest->validated();

        if (!User::find($id))
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

    public function profile(Request $request)
    {
        $user = $request->user();
        return $user->toResource(ProfileResource::class);
        // return $user->toResource();
    }

    /**
     * Search users by filters
     * Requires manage-users permission
     *
     * @param Request $request
     *
     * @return UserCollection
     */
    public function search(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $filters = $request->input('filters', []);

        
        $roles = $request->input('roles', []);
        if (!empty($roles)) {
            if (count($roles) === 1) {
                $filters[] = [
                    'field' => 'role',
                    'operator' => '=',
                    'value' => $roles[0],
                ];
            } else {
                $filters[] = [
                    'field' => 'role',
                    'operator' => 'IN',
                    'value' => $roles,
                ];
            }
        }

        $sortField = $request->input('sort_field', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');

        $result = User::select("users.*")
            ->with('roles')
            ->filter($filters)
            ->orderBy($sortField, $sortDirection)
            ->paginate($perPage);

        return new \App\Http\Resources\Users\UserCollection($result);
    }

    public function searchUsers(Request $request)
    {
        $roles = $request->input('roles', []);
        $sortField = $request->input('sort_field', 'name');
        $sortDirection = $request->input('sort_direction', 'asc');
        $perPage = $request->input('per_page', 20);

        $result = [];

        foreach ($roles as $role) {
            $users = User::role($role)
                ->with('roles')
                ->orderBy($sortField, $sortDirection)
                ->paginate($perPage, ['*'], $role.'_page')
                ->items();

            $result[] = [
                'role' => $role,
                'users' => $users,
            ];
        }

        return response()->json($result);
    }
}
