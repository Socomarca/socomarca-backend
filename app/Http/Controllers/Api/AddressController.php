<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Addresses\StoreRequest;
use App\Http\Requests\Addresses\UpdateRequest;
use App\Http\Resources\Addresses\AddressCollection;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $addresses = null;

        if (Gate::authorize('viewAny', Address::class)) {
            $addresses = Address::all();
        } elseif (Gate::authorize('view', Address::class)) {
            $addresses = Address::where('user_id', $user->id)->get();
        } else {
            return abort(403, 'Forbidden');
        }

        $data = new AddressCollection($addresses);

        return $data;
    }

    public function store(StoreRequest $storeRequest)
    {
        if (!Gate::authorize('create', Address::class)) {
            return abort(403, 'Forbidden');
        }

        $data = $storeRequest->validated();

        $data['is_default'] === true &&
            DB::table('addresses')
                ->where('user_id', $data['user_id'])
                ->update(['is_default' => false]);

        $address = new Address;

        $address->address_line1 = $data['address_line1'];
        $address->address_line2 = $data['address_line2'];
        $address->postal_code = $data['postal_code'];
        $address->is_default = $data['is_default'];
        $address->type = $data['type'];
        $address->contact_name = $data['contact_name'];

        $address->user_id = $data['user_id'];
        $address->municipality_id = $data['municipality_id'];

        $address->save();

        return response()->json(['message' => 'The address has been added'], 201);
    }

    public function show(Address $address)
    {
        if (!Gate::authorize('view', $address)) {
            return abort(403, 'Forbidden');
        }

        $data = new AddressCollection([$address]);

        return response()->json($data[0]);
    }

    public function update(UpdateRequest $updateRequest, Address $address)
    {
        if (!Gate::authorize('update', $address)) {
            return abort(403, 'Forbidden');
        }

        $data = $updateRequest->validated();

        $data['is_default'] === true &&
            DB::table('addresses')
                ->where('user_id', $data['user_id'])
                ->update(['is_default' => false]);

        $address->address_line1 = $data['address_line1'];
        $address->address_line2 = $data['address_line2'];
        $address->postal_code = $data['postal_code'];
        $address->is_default = $data['is_default'];
        $address->type = $data['type'];
        $address->contact_name = $data['contact_name'];

        $address->user_id = $data['user_id'];
        $address->municipality_id = $data['municipality_id'];

        $address->save();

        return response()->json(['message' => 'The selected address has been updated']);

    }

    public function destroy(Address $address)
    {
        if (!Gate::authorize('delete', $address)) {
            return abort(403, 'Forbidden');
        }

        $address->delete();

        return response()->json(['message' => 'The selected address has been deleted']);
    }
}
