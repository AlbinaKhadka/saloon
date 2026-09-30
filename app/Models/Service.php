<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Service",
    title: "Service",
    description: "Service model",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "service_category_id", type: "integer", example: 1),
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
        'service_category_id',
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
        'service_category_id' => 'integer',
        'price'               => 'decimal:2',
        'status'              => 'integer',
        'duration'            => 'integer',
        'orderby'             => 'integer',
    ];

    protected $appends = ['image_url'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : '';
    }

    protected static function booted(): void
    {
        static::creating(function (Service $service) {
            if (empty($service->slug) && ! empty($service->title)) {
                $service->slug = static::generateCategoryScopedSlug($service->title, $service->service_category_id);
            }
        });

        static::updating(function (Service $service) {
            if ($service->isDirty('title') && ! $service->isDirty('slug')) {
                $service->slug = static::generateCategoryScopedSlug(
                    $service->title,
                    $service->service_category_id,
                    $service->id
                );
            }
        });

        static::deleting(function (Service $service) {
            if ($service->image && Storage::disk('public')->exists($service->image)) {
                Storage::disk('public')->delete($service->image);
            }
        });
    }

    public static function generateCategoryScopedSlug(string $title, int $categoryId, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $count = 2;

        while (
            static::where('service_category_id', $categoryId)
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$count}";
            $count++;
        }

        return $slug;
    }
}
