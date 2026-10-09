<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Stylist",
    title: "Stylist",
    description: "Stylist model",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "name", type: "string", example: "Jane Doe"),
        new OA\Property(property: "slug", type: "string", example: "jane-doe", nullable: true),
        new OA\Property(property: "designation", type: "string", example: "Senior Hair Stylist", nullable: true),
        new OA\Property(property: "bio", type: "string", example: "Specialist in modern haircuts and coloring", nullable: true),
        new OA\Property(property: "photo", type: "string", example: "http://localhost:8000/storage/stylists/jane.jpg", nullable: true),
        new OA\Property(property: "phone", type: "string", example: "+1234567890", nullable: true),
        new OA\Property(property: "email", type: "string", example: "jane@example.com", nullable: true),
        new OA\Property(property: "experience_years", type: "integer", example: 5, nullable: true),
        new OA\Property(
            property: "social_links",
            type: "object",
            nullable: true,
            example: ["instagram" => "https://instagram.com/jane", "facebook" => "https://facebook.com/jane"]
        ),
        new OA\Property(property: "status", description: "0=hidden, 1=active", type: "integer", example: 1),
        new OA\Property(property: "orderby", type: "integer", example: 1, nullable: true),
        new OA\Property(
            property: "services",
            type: "array",
            items: new OA\Items(ref: "#/components/schemas/Service"),
            nullable: true
        ),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time")
    ]
)]
class Stylist extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'designation',
        'bio',
        'photo',
        'phone',
        'email',
        'experience_years',
        'social_links',
        'status',
        'orderby',
    ];

    protected $casts = [
        'social_links'     => 'array',
        'experience_years' => 'integer',
        'status'           => 'integer',
        'orderby'          => 'integer',
    ];

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'stylist_service')->withTimestamps();
    }

    protected static function booted(): void
    {
        static::creating(function (Stylist $stylist) {
            if (! empty($stylist->name) && empty($stylist->slug)) {
                $stylist->slug = static::generateUniqueSlug($stylist->name);
            } elseif (! empty($stylist->slug)) {
                $stylist->slug = static::generateUniqueSlug($stylist->slug);
            }
        });

        static::updating(function (Stylist $stylist) {
            if ($stylist->isDirty('name') && empty($stylist->slug)) {
                $stylist->slug = static::generateUniqueSlug($stylist->name, $stylist->id);
            } elseif ($stylist->isDirty('slug') && ! empty($stylist->slug)) {
                $stylist->slug = static::generateUniqueSlug($stylist->slug, $stylist->id);
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
