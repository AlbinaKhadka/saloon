<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Banner",
    title: "Banner",
    description: "Banner model",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "title", type: "string", example: "Summer Sale", nullable: true),
        new OA\Property(property: "slug", type: "string", example: "summer-sale", nullable: true),
        new OA\Property(property: "description", type: "string", example: "Get 50% discount on all hair styling services", nullable: true),
        new OA\Property(property: "image", type: "string", example: "http://localhost/storage/banners/image.jpg"),
        new OA\Property(property: "url", type: "string", example: "https://example.com/promo", nullable: true),
        new OA\Property(property: "status", description: "0=inactive, 1=active", type: "integer", example: 1),
        new OA\Property(property: "orderby", type: "integer", example: 1, nullable: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time")
    ]
)]
class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'image',
        'url',
        'status',
        'orderby',
    ];

    protected static function booted(): void
    {
        static::creating(function (Banner $banner) {
            if (! empty($banner->title)) {
                $banner->slug = static::generateUniqueSlug($banner->title);
            } else {
                $banner->slug = null;
            }
        });

        static::updating(function (Banner $banner) {
            if ($banner->isDirty('title')) {
                $banner->slug = ! empty($banner->title)
                    ? static::generateUniqueSlug($banner->title, $banner->id)
                    : null;
            }
        });
    }

    public static function generateUniqueSlug(?string $title, ?int $ignoreId = null): ?string
    {
        if (empty($title)) {
            return null;
        }

        $baseSlug = Str::slug($title);

        if (empty($baseSlug)) {
            return null;
        }

        $slug = $baseSlug;
        $count = 2;

        while (
            static::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}
