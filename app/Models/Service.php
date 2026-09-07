<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Service",
    title: "Service",
    description: "Service model",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "title", type: "string", example: "Hair Cut"),
        new OA\Property(property: "slug", type: "string", example: "hair-cut"),
        new OA\Property(property: "description", type: "string", example: "Professional hair cutting", nullable: true),
        new OA\Property(property: "price", type: "number", format: "float", example: 500),
        new OA\Property(property: "duration", type: "integer", example: 30, nullable: true),
        new OA\Property(property: "image", type: "string", example: "http://localhost/storage/services/image.jpg"),
        new OA\Property(property: "status", description: "0=inactive, 1=active", type: "integer", example: 1),
        new OA\Property(property: "orderby", type: "integer", example: 1, nullable: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time")
    ]
)]
class Service extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'description',
        'price',
        'duration',
        'image',
        'status',
        'orderby',
    ];

    protected $casts = [
        'price'    => 'decimal:2',
        'status'   => 'integer',
        'duration' => 'integer',
        'orderby'  => 'integer',
    ];

    // Automatically return full URL for image
    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : '';
    }

    // Delete image file when model is deleted
    protected static function booted()
    {
        static::deleting(function (Service $service) {
            if ($service->image && Storage::disk('public')->exists($service->image)) {
                Storage::disk('public')->delete($service->image);
            }
        });
    }
}
