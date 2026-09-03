<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "Banner",
    title: "Banner",
    description: "Banner model",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "title", type: "string", example: "Summer Sale", nullable: true),
        new OA\Property(property: "slug", type: "string", example: "summer-sale", nullable: true),
        new OA\Property(property: "image", type: "string", example: "http://localhost/storage/banners/image.jpg"),
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
        'image',
        'status',
        'orderby',
    ];
}
