<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Addresses\PatchRequest;
use App\Http\Requests\Addresses\StoreRequest;
use App\Http\Requests\Addresses\UpdateRequest;
use App\Http\Resources\Addresses\AddressCollection;
use App\Models\Address;
use App\Models\Municipality;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $addresses = null;

        if ($user->can('see-all-addresses')) {
            $addresses = Address::all();
        } elseif ($user->can('see-own-addresses')) {
            $addresses = Address::where('user_id', $user->id)->get();
        } else {
            abort(403, 'Forbidden');
        }

        $data = new AddressCollection($addresses);

        return $data;
    }

    public function store(StoreRequest $storeRequest)
    {
        $user = $storeRequest->user();

        if (!$user->can('store-address')) {
            return abort(403, 'Forbidden');
        }

        $data = $storeRequest->validated();

        $data['is_default'] === true &&
            DB::table('addresses')
                ->where('user_id', $user->id)
                ->update(['is_default' => false]);

        $address = new Address;

        $address->address_line1 = $data['address_line1'];
        $address->address_line2 = $data['address_line2'];
        $address->postal_code = $data['postal_code'];
        $address->is_default = $data['is_default'];
        $address->type = $data['type'];
        $address->phone = $data['phone'];
        $address->contact_name = $data['contact_name'];
        $address->user_id = $user->id;
        $address->municipality_id = $data['municipality_id'];
        $address->alias = $data['alias'];

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
        $user = $updateRequest->user();

        if (!Gate::authorize('update', $address)) {
            return abort(403, 'Forbidden');
        }

        $data = $updateRequest->validated();

        $data['is_default'] === true &&
            DB::table('addresses')
                ->where('user_id',$user->id)
                ->update(['is_default' => false]);

        $address->address_line1 = $data['address_line1'];
        $address->address_line2 = $data['address_line2'];
        $address->postal_code = $data['postal_code'];
        $address->is_default = $data['is_default'];
        $address->type = $data['type'];
        $address->contact_name = $data['contact_name'];
        $address->user_id = $user->id;
        $address->municipality_id = $data['municipality_id'];
        $address->alias = $data['alias'];
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

    
    /**
     * Get all regions
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function regions()
    {
        
        return Region::select('id', 'name')->orderBy('id','ASC')->get();
    }

    /**
     * Get municipalities by region ID
     *
     * @param \Illuminate\Http\Request $request
     * @param int|null $regionId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function municipalities(Request $request,$regionId = null)
    {
        if ($regionId !== null) {
            $request->merge(['region_id' => $regionId]);
        }
        $validated = $request->validate([
            'region_id' => 'nullable|integer|exists:regions,id',
        ],
        [
            'region_id.exists' => 'error',
            'region_id.integer' => 'error',
        ]);

        $query = Municipality::select('id', 'name', 'status');
        if ($regionId) {
            $query->where('region_id', $regionId);
        }
        return $query->orderBy('name')->get();
    }

    /**
     * Patch an existing address
     *
     * @param PatchRequest $request
     * @param Address $address
     * @return \Illuminate\Http\JsonResponse
     */
    public function patch(PatchRequest $request, Address $address)
    {
        $user = $request->user();

        if (!Gate::authorize('update', $address)) {
            return abort(403, 'Forbidden');
        }

        $data = $request->validated();

        // Si se quiere marcar como default
        if (array_key_exists('is_default', $data) && $data['is_default'] === true) {
            $user->addresses()->update(['is_default' => false]);
            $address->is_default = true;
        }

        $address->fill($data);
        $address->save();

        return response()->json(['message' => 'The address has been updated']);
    }
}
