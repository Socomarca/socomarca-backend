<?php

namespace App\Http\Resources\SiteInfo;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SiteinfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if (in_array($this->key, ['terms', 'privacy_policy'])) {
            return [
                'content' => $this->content,
            ];
        }

        return [
            'key' => $this->key,
            'value' => $this->value,
            
        ];
    }
}
