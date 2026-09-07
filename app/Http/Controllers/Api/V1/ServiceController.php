<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

class ServiceController extends Controller
{
    #[OA\Get(
        path: '/api/v1/services',
        operationId: 'getServices',
        description: 'Get list of all services',
        security: [['bearerAuth' => []]],
        tags: ['Services'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/Service')
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function index(): JsonResponse
    {
        $services = Service::orderBy('orderby')->get()->map(function ($service) {
            return $this->formatService($service);
        });

        return response()->json(['data' => $services]);
    }

    #[OA\Post(
        path: '/api/v1/services',
        operationId: 'storeService',
        description: 'Create a new service',
        security: [['bearerAuth' => []]],
        tags: ['Services'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['title', 'price', 'image', 'status'],
                    properties: [
                        new OA\Property(property: 'title', type: 'string', example: 'Hair Cut'),
                        new OA\Property(property: 'slug', type: 'string', example: 'hair-cut', nullable: true),
                        new OA\Property(property: 'description', type: 'string', example: 'Professional hair cutting', nullable: true),
                        new OA\Property(property: 'price', type: 'number', format: 'float', example: 500),
                        new OA\Property(property: 'duration', type: 'integer', example: 30, nullable: true),
                        new OA\Property(property: 'image', description: 'Service image file', type: 'string', format: 'binary'),
                        new OA\Property(property: 'status', description: '0 = inactive, 1 = active', type: 'integer', enum: [0, 1]),
                        new OA\Property(property: 'orderby', type: 'integer', example: 1, nullable: true),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Service created successfully', content: new OA\JsonContent(ref: '#/components/schemas/Service')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function store(StoreServiceRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('services', 'public');
            $data['image'] = $path;
        }

        if (empty($data['slug']) && ! empty($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $service = Service::create($data);

        return response()->json($this->formatService($service), 201);
    }

    #[OA\Get(
        path: '/api/v1/services/{service}',
        operationId: 'getService',
        description: 'Get a single service',
        security: [['bearerAuth' => []]],
        tags: ['Services'],
        parameters: [
            new OA\Parameter(name: 'service', description: 'Service ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Successful operation', content: new OA\JsonContent(ref: '#/components/schemas/Service')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Service not found'),
        ]
    )]
    public function show(Service $service): JsonResponse
    {
        return response()->json($this->formatService($service));
    }

    #[OA\Post(
        path: '/api/v1/services/{service}',
        operationId: 'updateService',
        description: 'Update a service. We use POST with _method=PUT to support multipart/form-data for image uploads in PHP.',
        security: [['bearerAuth' => []]],
        tags: ['Services'],
        parameters: [
            new OA\Parameter(name: 'service', description: 'Service ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['_method', 'status'],
                    properties: [
                        new OA\Property(property: '_method', description: 'Method spoofing for PUT', type: 'string', example: 'PUT'),
                        new OA\Property(property: 'title', type: 'string', nullable: true),
                        new OA\Property(property: 'slug', type: 'string', nullable: true),
                        new OA\Property(property: 'description', type: 'string', nullable: true),
                        new OA\Property(property: 'price', type: 'number', format: 'float', nullable: true),
                        new OA\Property(property: 'duration', type: 'integer', nullable: true),
                        new OA\Property(property: 'image', description: 'Service image file (optional on update)', type: 'string', format: 'binary', nullable: true),
                        new OA\Property(property: 'status', description: '0 = inactive, 1 = active', type: 'integer', enum: [0, 1]),
                        new OA\Property(property: 'orderby', type: 'integer', nullable: true),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Service updated successfully', content: new OA\JsonContent(ref: '#/components/schemas/Service')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Service not found'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function update(UpdateServiceRequest $request, Service $service): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            if ($service->image && Storage::disk('public')->exists($service->image)) {
                Storage::disk('public')->delete($service->image);
            }
            $data['image'] = $request->file('image')->store('services', 'public');
        }

        if (isset($data['title']) && empty($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        $service->update($data);

        return response()->json($this->formatService($service->fresh()));
    }

    #[OA\Delete(
        path: '/api/v1/services/{service}',
        operationId: 'deleteService',
        description: 'Delete a service',
        security: [['bearerAuth' => []]],
        tags: ['Services'],
        parameters: [
            new OA\Parameter(name: 'service', description: 'Service ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Service deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Service deleted successfully'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Service not found'),
        ]
    )]
    public function destroy(Service $service): JsonResponse
    {
        $service->delete();

        return response()->json(['message' => 'Service deleted successfully']);
    }

    private function formatService(Service $service): array
    {
        return [
            'id' => $service->id,
            'title' => $service->title,
            'slug' => $service->slug,
            'description' => $service->description,
            'price' => (float) $service->price,
            'duration' => $service->duration,
            'image' => $service->image_url,
            'status' => (int) $service->status,
            'orderby' => $service->orderby,
            'created_at' => $service->created_at,
            'updated_at' => $service->updated_at,
        ];
    }
}
