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
        
        $result = User::select("users.*")
            ->with('roles')
            ->filter($filters)
            ->paginate($perPage);

        $data = new UserCollection($result);

        return $data;
    }

        public function searchUsers(SearchUsersRequest $request)
    {
        // Obtén los roles a filtrar (o todos si no se especifica)
        $roles = $request->input('roles', ['admin', 'superadmin', 'supervisor', 'editor', 'cliente']);
        if (is_string($roles)) {
            $roles = explode(',', $roles);
        }

        $result = [];

        foreach ($roles as $roleName) {
            $users = User::role($roleName)
                ->when($request->filled('name'), function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->input('name') . '%');
                })
                ->when($request->filled('email'), function ($q) use ($request) {
                    $q->where('email', 'like', '%' . $request->input('email') . '%');
                })
                ->orderBy(
                    $request->input('sort_field', 'name'),
                    $request->input('sort_direction', 'asc')
                )
                ->get(['id', 'name', 'email', 'created_at']);

            $result[] = [
                'role' => $roleName,
                'users' => $users,
            ];
        }

        return response()->json($result);
    }
}
