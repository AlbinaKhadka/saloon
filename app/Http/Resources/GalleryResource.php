<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GalleryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'category' => $this->category,
            'image' => $this->image_url,
            'status' => (int) $this->status,
            'orderby' => $this->orderby,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
