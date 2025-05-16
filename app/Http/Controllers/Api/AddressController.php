<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Addresses\DestroyRequest;
use App\Http\Requests\Addresses\ShowRequest;
use App\Http\Requests\Addresses\StoreRequest;
use App\Http\Requests\Addresses\UpdateRequest;
use App\Http\Resources\AddressCollection;
use App\Models\Address;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = Address::all();

        $resources = new AddressCollection($addresses);

        return response()->json(['resources' => $resources]);
    }

    public function store(StoreRequest $storeRequest)
    {
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

        return response()->json(['message' => 'The address has been added']);
    }

    public function show(ShowRequest $showRequest, $id)
    {
        $showRequest->validated();

        if (!DB::table('addresses')->where('id', $id)->exists())
        {
            return response()->json(
            [
                'message' => 'Address not found.',
            ], 404);
        }

        $addresses = Address::where('id', $id)->get();

        $resources = new AddressCollection($addresses);

        return response()->json(['resources' => $resources]);
    }

    public function update(UpdateRequest $updateRequest, $id)
    {
        $data = $updateRequest->validated();

        $data['is_default'] === true &&
            DB::table('addresses')
                ->where('user_id', $data['user_id'])
                ->update(['is_default' => false]);

        $address = Address::find($id);

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

    public function destroy(DestroyRequest $destroyRequest, $id)
    {
        $destroyRequest->validated();

        if (!DB::table('addresses')->where('id', $id)->exists())
        {
            return response()->json(
            [
                'message' => 'Address not found.',
            ], 404);
        }

        $address = Address::find($id);

        $address->delete();

        return response()->json(['message' => 'The selected address has been deleted']);
    }
}
