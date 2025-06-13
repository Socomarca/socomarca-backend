<?php

namespace App\Http\Resources\Users;

use App\Http\Resources\Addresses\AddressResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        self::withoutWrapping();

        return [
            'rut'=> $this->rut,
            'name' => $this->name,
            'business_name'=> $this->business_name,
            'email'=> $this->email,
            'phone'=> $this->phone,
            'is_active'=> $this->is_active,
            'billing_address' => $this->billing_address->toResource(AddressResource::class),
            'default_shipping_address' => $this->default_shipping_address->toResource(AddressResource::class),
        ];
    }
}
