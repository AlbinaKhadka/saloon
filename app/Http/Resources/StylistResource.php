<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StylistResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'slug'             => $this->slug,
            'designation'      => $this->designation,
            'bio'              => $this->bio,
            'photo'            => $this->photo ? asset('storage/' . $this->photo) : null,
            'phone'            => $this->phone,
            'email'            => $this->email,
            'experience_years' => $this->experience_years !== null ? (int) $this->experience_years : null,
            'social_links'     => $this->social_links,
            'status'           => (int) $this->status,
            'orderby'          => $this->orderby !== null ? (int) $this->orderby : null,
            'services'         => ServiceResource::collection($this->whenLoaded('services')),
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
        ];
    }
}
