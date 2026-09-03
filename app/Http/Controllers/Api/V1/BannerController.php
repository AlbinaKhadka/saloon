<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBannerRequest;
use App\Http\Requests\UpdateBannerRequest;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class BannerController extends Controller
{
    #[OA\Get(
        path: '/api/v1/banners',
        operationId: 'getBanners',
        description: 'Get list of all banners',
        security: [['bearerAuth' => []]],
        tags: ['Banners'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Banner'))
            ),
            new OA\Response(response: 401, description: 'Unauthenticated')
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        $banners = Banner::orderBy('orderby', 'asc')->get();
        return BannerResource::collection($banners);
    }

    #[OA\Post(
        path: '/api/v1/banners',
        operationId: 'storeBanner',
        description: 'Create a new banner',
        security: [['bearerAuth' => []]],
        tags: ['Banners'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['image', 'status'],
                    properties: [
                        new OA\Property(property: 'title', type: 'string', nullable: true),
                        new OA\Property(property: 'slug', type: 'string', nullable: true),
                        new OA\Property(property: 'image', description: 'Banner image file', type: 'string', format: 'binary'),
                        new OA\Property(property: 'status', description: '0 = inactive, 1 = active', type: 'integer', enum: [0, 1]),
                        new OA\Property(property: 'orderby', type: 'integer', nullable: true),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Banner created successfully', content: new OA\JsonContent(ref: '#/components/schemas/Banner')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation Error')
        ]
    )]
    public function store(StoreBannerRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner = Banner::create($validated);

        return response()->json(new BannerResource($banner), 201);
    }

    #[OA\Get(
        path: '/api/v1/banners/{banner}',
        operationId: 'getBanner',
        description: 'Get a single banner',
        security: [['bearerAuth' => []]],
        tags: ['Banners'],
        parameters: [
            new OA\Parameter(name: 'banner', description: 'Banner ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Successful operation', content: new OA\JsonContent(ref: '#/components/schemas/Banner')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Banner not found')
        ]
    )]
    public function show(Banner $banner): JsonResponse
    {
        return response()->json(new BannerResource($banner), 200);
    }

    #[OA\Post(
        path: '/api/v1/banners/{banner}',
        operationId: 'updateBanner',
        description: 'Update a banner. We use POST with _method=PUT to support multipart/form-data for image uploads in PHP.',
        security: [['bearerAuth' => []]],
        tags: ['Banners'],
        parameters: [
            new OA\Parameter(name: 'banner', description: 'Banner ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
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
                        new OA\Property(property: 'slug', type: 'string', nullable: true),
                        new OA\Property(property: 'image', description: 'Banner image file (optional on update)', type: 'string', format: 'binary', nullable: true),
                        new OA\Property(property: 'status', description: '0 = inactive, 1 = active', type: 'integer', enum: [0, 1]),
                        new OA\Property(property: 'orderby', type: 'integer', nullable: true),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Banner updated successfully', content: new OA\JsonContent(ref: '#/components/schemas/Banner')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Banner not found'),
            new OA\Response(response: 422, description: 'Validation Error')
        ]
    )]
    public function update(UpdateBannerRequest $request, Banner $banner): JsonResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('image')) {
            if ($banner->image) {
                Storage::disk('public')->delete($banner->image);
            }
            $validated['image'] = $request->file('image')->store('banners', 'public');
        }

        $banner->update($validated);

        return response()->json(new BannerResource($banner), 200);
    }

    #[OA\Delete(
        path: '/api/v1/banners/{banner}',
        operationId: 'deleteBanner',
        description: 'Delete a banner',
        security: [['bearerAuth' => []]],
        tags: ['Banners'],
        parameters: [
            new OA\Parameter(name: 'banner', description: 'Banner ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Banner deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Banner not found')
        ]
    )]
    public function destroy(Banner $banner): JsonResponse
    {
        if ($banner->image) {
            Storage::disk('public')->delete($banner->image);
        }
        
        $banner->delete();

        return response()->json(['message' => 'Banner deleted successfully'], 200);
    }
}
