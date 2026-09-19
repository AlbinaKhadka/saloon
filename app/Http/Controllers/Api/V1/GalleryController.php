<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGalleryRequest;
use App\Http\Requests\UpdateGalleryRequest;
use App\Http\Resources\GalleryResource;
use App\Models\Gallery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class GalleryController extends Controller
{
    #[OA\Get(
        path: '/api/v1/galleries',
        operationId: 'getGalleries',
        description: 'Get list of gallery items with optional category filtering',
        security: [['bearerAuth' => []]],
        tags: ['Gallery'],
        parameters: [
            new OA\Parameter(
                name: 'category',
                description: 'Filter photos by category (e.g. Products, From Farm, Recipes, Lifestyle)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Gallery'))
            ),
            new OA\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Gallery::query();

        if ($request->filled('category') && strtolower($request->category) !== 'all') {
            $query->where('category', $request->category);
        }

        $galleries = $query->orderBy('orderby', 'asc')->orderBy('id', 'desc')->get();

        return GalleryResource::collection($galleries);
    }

    #[OA\Post(
        path: '/api/v1/galleries',
        operationId: 'storeGallery',
        description: 'Create gallery item(s). Supports single image ("image") or batch upload of multiple images ("images[]").',
        security: [['bearerAuth' => []]],
        tags: ['Gallery'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['status'],
                    properties: [
                        new OA\Property(property: 'title', type: 'string', nullable: true),
                        new OA\Property(property: 'category', type: 'string', example: 'Products', nullable: true),
                        new OA\Property(property: 'image', description: 'Single gallery image file', type: 'string', format: 'binary', nullable: true),
                        new OA\Property(
                            property: 'images',
                            description: 'Batch array of image files to upload multiple images at once',
                            type: 'array',
                            items: new OA\Items(type: 'string', format: 'binary'),
                            nullable: true
                        ),
                        new OA\Property(property: 'status', description: '0 = inactive, 1 = active', type: 'integer', enum: [0, 1], example: 1),
                        new OA\Property(property: 'orderby', type: 'integer', example: 1, nullable: true),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Gallery item(s) created successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation Error')
        ]
    )]
    public function store(StoreGalleryRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $created = [];

        // Handle batch multi-image upload
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $file) {
                $path = $file->store('galleries', 'public');
                $gallery = Gallery::create([
                    'title' => $validated['title'] ?? null,
                    'category' => $validated['category'] ?? null,
                    'image' => $path,
                    'status' => $validated['status'] ?? 1,
                    'orderby' => isset($validated['orderby']) ? ($validated['orderby'] + $index) : null,
                ]);
                $created[] = new GalleryResource($gallery);
            }
            return response()->json([
                'message' => count($created) . ' gallery photos uploaded successfully',
                'data' => $created,
            ], 201);
        }

        // Handle single image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('galleries', 'public');
        }

        $gallery = Gallery::create($validated);

        return response()->json(new GalleryResource($gallery), 201);
    }

    #[OA\Get(
        path: '/api/v1/galleries/{gallery}',
        operationId: 'getGallery',
        description: 'Get a single gallery photo item',
        security: [['bearerAuth' => []]],
        tags: ['Gallery'],
        parameters: [
            new OA\Parameter(name: 'gallery', description: 'Gallery ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Successful operation', content: new OA\JsonContent(ref: '#/components/schemas/Gallery')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Gallery item not found')
        ]
    )]
    public function show(Gallery $gallery): JsonResponse
    {
        return response()->json(new GalleryResource($gallery), 200);
    }

    #[OA\Post(
        path: '/api/v1/galleries/{gallery}',
        operationId: 'updateGallery',
        description: 'Update a gallery item. We use POST with _method=PUT to support multipart/form-data for image upload in PHP.',
        security: [['bearerAuth' => []]],
        tags: ['Gallery'],
        parameters: [
            new OA\Parameter(name: 'gallery', description: 'Gallery ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['status', '_method'],
                    properties: [
                        new OA\Property(property: '_method', description: 'Method spoofing for PUT', type: 'string', example: 'PUT'),
                        new OA\Property(property: 'title', type: 'string', nullable: true),
                        new OA\Property(property: 'category', type: 'string', nullable: true),
                        new OA\Property(property: 'image', description: 'Gallery image file (optional on update)', type: 'string', format: 'binary', nullable: true),
                        new OA\Property(property: 'status', description: '0 = inactive, 1 = active', type: 'integer', enum: [0, 1]),
                        new OA\Property(property: 'orderby', type: 'integer', nullable: true),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Gallery item updated successfully', content: new OA\JsonContent(ref: '#/components/schemas/Gallery')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Gallery item not found'),
            new OA\Response(response: 422, description: 'Validation Error')
        ]
    )]
    public function update(UpdateGalleryRequest $request, Gallery $gallery): JsonResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            if ($gallery->image && Storage::disk('public')->exists($gallery->image)) {
                Storage::disk('public')->delete($gallery->image);
            }
            $validated['image'] = $request->file('image')->store('galleries', 'public');
        }

        $gallery->update($validated);

        return response()->json(new GalleryResource($gallery), 200);
    }

    #[OA\Delete(
        path: '/api/v1/galleries/{gallery}',
        operationId: 'deleteGallery',
        description: 'Delete a gallery item',
        security: [['bearerAuth' => []]],
        tags: ['Gallery'],
        parameters: [
            new OA\Parameter(name: 'gallery', description: 'Gallery ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Gallery item deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Gallery item not found')
        ]
    )]
    public function destroy(Gallery $gallery): JsonResponse
    {
        $gallery->delete();

        return response()->json(['message' => 'Gallery item deleted successfully'], 200);
    }
}
