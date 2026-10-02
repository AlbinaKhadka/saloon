<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "ServiceCategory",
    title: "ServiceCategory",
    description: "ServiceCategory model",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "name", type: "string", example: "Hair Care"),
        new OA\Property(property: "slug", type: "string", example: "hair-care"),
        new OA\Property(property: "icon", type: "string", example: "http://localhost/storage/service_categories/icon.jpg", nullable: true),
        new OA\Property(property: "orderby", type: "integer", example: 1, nullable: true),
        new OA\Property(property: "status", type: "boolean", example: true),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time")
    ]
)]
class ServiceCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'orderby',
        'status',
    ];

    protected $casts = [
        'orderby' => 'integer',
        'status'  => 'boolean',
    ];

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'service_category_id');
    }

    protected static function booted(): void
    {
        static::creating(function (ServiceCategory $category) {
            if (! empty($category->name)) {
                $category->slug = static::generateUniqueSlug($category->name);
            }
        });

        static::updating(function (ServiceCategory $category) {
            if ($category->isDirty('name')) {
                $category->slug = static::generateUniqueSlug($category->name, $category->id);
            }
        });
    }

    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
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
