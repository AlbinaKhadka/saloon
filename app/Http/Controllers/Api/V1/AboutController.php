<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAboutRequest;
use App\Http\Requests\UpdateAboutRequest;
use App\Http\Resources\AboutResource;
use App\Models\About;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class AboutController extends Controller
{
    #[OA\Get(
        path: '/api/v1/abouts',
        operationId: 'getAbouts',
        description: 'Get list of all About Us records',
        security: [['bearerAuth' => []]],
        tags: ['About'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/About'))
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(): AnonymousResourceCollection
    {
        return AboutResource::collection(About::latest()->get());
    }

    #[OA\Post(
        path: '/api/v1/abouts',
        operationId: 'storeAbout',
        description: 'Create a new About Us record with one or more images',
        security: [['bearerAuth' => []]],
        tags: ['About'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['images'],
                    properties: [
                        new OA\Property(property: 'title', type: 'string', nullable: true),
                        new OA\Property(property: 'slug', type: 'string', nullable: true),
                        new OA\Property(property: 'description', type: 'string', nullable: true),
                        new OA\Property(
                            property: 'images',
                            description: 'One or more image files',
                            type: 'array',
                            items: new OA\Items(type: 'string', format: 'binary')
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'About record created successfully', content: new OA\JsonContent(ref: '#/components/schemas/About')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
   public function store(StoreAboutRequest $request): JsonResponse
{
    $paths = [];

    foreach ($request->file('images', []) as $file) {
        $paths[] = $file->store('abouts', 'public');
    }

    $about = About::create([
        'title'       => $request->title,
        'slug'        => $request->slug,
        'description' => $request->description,
        'images'      => $paths,
    ]);

    return response()->json(new AboutResource($about), 201);
}

public function update(UpdateAboutRequest $request, About $about): JsonResponse
{
    $currentImages = $about->images ?? [];

    // Remove selected images
    if ($request->filled('remove_images')) {
        foreach ($request->remove_images as $item) {
            $path = str_replace(asset('storage/'), '', $item);
            $path = ltrim($path, '/');

            if (in_array($path, $currentImages)) {
                Storage::disk('public')->delete($path);
                $currentImages = array_values(array_diff($currentImages, [$path]));
            }
        }
    }

    // Append new images
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $file) {
            $currentImages[] = $file->store('abouts', 'public');
        }
    }

    $about->update([
        'title'       => $request->title ?? $about->title,
        'slug'        => $request->slug ?? $about->slug,
        'description' => $request->description ?? $about->description,
        'images'      => $currentImages,
    ]);

    return response()->json(new AboutResource($about), 200);
}
    public function destroy(About $about): JsonResponse
    {
        foreach ($about->images ?? [] as $path) {
            Storage::disk('public')->delete($path);
        }

        $about->delete();

        return response()->json(['message' => 'About record deleted successfully'], 200);
    }
}
