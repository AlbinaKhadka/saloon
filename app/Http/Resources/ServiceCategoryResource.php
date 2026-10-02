<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ServiceCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $iconUrl = $this->icon;
        if ($this->icon && (str_starts_with($this->icon, 'service_categories/') || Storage::disk('public')->exists($this->icon))) {
            $iconUrl = asset('storage/' . $this->icon);
        }

        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'slug'       => $this->slug,
            'icon'       => $iconUrl,
            'orderby'    => $this->orderby,
            'status'     => (bool) $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'services'   => ServiceResource::collection($this->whenLoaded('services')),
        ];
    }
}
