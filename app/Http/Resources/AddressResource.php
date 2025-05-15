<?php

namespace App\Http\Resources;

use App\Models\Municipality;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $id = $this->id;
        $userId = $this->user_id;
        $municipalityId = $this->municipality_id;

        return
        [
            'id' => $id,
            'address_line1' => $this->address_line1,
            'address_line2' => $this->address_line2,
            'postal_code' => $this->postal_code,
            'is_default' => $this->is_default,
            'type' => $this->type,
            'phone' => $this->phone,
            'contact_name' => $this->contact_name,
            'user' => User::where('id', $userId)->first(),
            'municipality' => Municipality::where('id', $municipalityId)->first(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
