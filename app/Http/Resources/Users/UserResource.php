<?php

namespace App\Http\Resources\Users;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return
        [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'phone' => $this->phone,
            'rut' => $this->rut,
            'business_name' => $this->business_name,
            'is_active' => $this->is_active,
            'last_login' => $this->last_login,
            'password_changed_at' => $this->password_changed_at,
            'billing_addresses' => $this->addresses->map(function($address) {
                return [
                    'id' => $address->id,
                    'address_line1' => $address->address_line1,
                    'address_line2' => $address->address_line2,
                    'postal_code' => $address->postal_code,
                    'is_default' => $address->is_default,
                    'type' => $address->type,
                    'phone' => $address->phone,
                    'contact_name' => $address->contact_name,
                    'municipality' => [
                        'id' => $address->municipality->id,
                        'name' => $address->municipality->name,
                        'code' => $address->municipality->code,
                        'region' => [
                            'id' => $address->municipality->region->id,
                            'name' => $address->municipality->region->name,
                            'code' => $address->municipality->region->code,
                        ]
                    ]
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
