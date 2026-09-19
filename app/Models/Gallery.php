<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Gallery",
    title: "Gallery",
    description: "Gallery item model with photo and category info",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "title", type: "string", example: "Interior view", nullable: true),
        new OA\Property(property: "category", type: "string", example: "Products", nullable: true),
        new OA\Property(property: "image", type: "string", example: "http://localhost:8000/storage/galleries/image.jpg"),
        new OA\Property(property: "status", description: "0=inactive, 1=active", type: "integer", example: 1),
        new OA\Property(property: "orderby", type: "integer", example: 1, nullable: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time")
    ]
)]
class Gallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'image',
        'status',
        'orderby',
    ];

    protected $casts = [
        'status' => 'integer',
        'orderby' => 'integer',
    ];

    protected $appends = ['image_url'];

    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : '';
    }

    protected static function booted()
    {
        static::deleting(function (Gallery $gallery) {
            if ($gallery->image && Storage::disk('public')->exists($gallery->image)) {
                Storage::disk('public')->delete($gallery->image);
            }
        });
    }
}
