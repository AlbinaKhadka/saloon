<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'service_category_id' => $this->service_category_id,
            'title'               => $this->title,
            'slug'                => $this->slug,
            'description'         => $this->description,
            'price'               => (float) $this->price,
            'duration'            => $this->duration,
            'image'               => $this->image_url,
            'status'              => (int) $this->status,
            'orderby'             => $this->orderby,
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,
            'category'            => new ServiceCategoryResource($this->whenLoaded('category')),
        ];
    }
}
