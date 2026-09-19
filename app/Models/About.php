<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "About",
    title: "About",
    description: "About Us page content with images",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1),
        new OA\Property(property: "title", type: "string", example: "About Our Salon", nullable: true),
        new OA\Property(property: "slug", type: "string", example: "about-our-salon", nullable: true),
        new OA\Property(property: "description", type: "string", example: "We have been serving...", nullable: true),
        new OA\Property(
            property: "images",
            type: "array",
            items: new OA\Items(type: "string", example: "http://localhost/storage/abouts/image.jpg"),
            nullable: true
        ),
        new OA\Property(property: "created_at", type: "string", format: "date-time"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time"),
    ]
)]
class About extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
    ];
}
