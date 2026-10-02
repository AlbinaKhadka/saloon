<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceCategoryRequest;
use App\Http\Requests\UpdateServiceCategoryRequest;
use App\Http\Resources\ServiceCategoryResource;
use App\Models\ServiceCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class ServiceCategoryController extends Controller
{
    #[OA\Get(
        path: '/api/v1/service-categories',
        operationId: 'getServiceCategories',
        description: 'Get list of service categories with optional status filtering and optional pagination.',
        tags: ['Service Categories'],
        parameters: [
            new OA\Parameter(
                name: 'status',
                description: 'Filter by status: 1/true or "active", 0/false or "inactive"',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'per_page',
                description: 'Number of items per page. If omitted, loads all matching service categories.',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Service Category List'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'items',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/ServiceCategory')
                                ),
                                new OA\Property(property: 'page', type: 'integer', example: 1, nullable: true),
                                new OA\Property(property: 'total_page', type: 'integer', example: 1, nullable: true),
                                new OA\Property(property: 'total_items', type: 'integer', example: 5)
                            ]
                        ),
                        new OA\Property(property: 'success', type: 'boolean', example: true)
                    ]
                )
            ),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = ServiceCategory::query();

        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $status = strtolower((string) $request->status);
            if ($status === 'active' || $status === '1' || $status === 'true') {
                $query->where('status', true);
            } elseif ($status === 'inactive' || $status === '0' || $status === 'false') {
                $query->where('status', false);
            }
        }

        $query->orderBy('orderby', 'asc')->orderBy('id', 'desc');

        if ($request->filled('per_page') && is_numeric($request->per_page) && (int) $request->per_page > 0) {
            $perPage = (int) $request->per_page;
            $categories = $query->paginate($perPage);

            return response()->json([
                'message' => 'Service Category List',
                'data' => [
                    'items' => ServiceCategoryResource::collection($categories->items()),
                    'page' => $categories->currentPage(),
                    'total_page' => $categories->lastPage(),
                    'total_items' => $categories->total(),
                ],
                'success' => true,
            ], 200);
        }

        $categories = $query->get();

        return response()->json([
            'message' => 'Service Category List',
            'data' => [
                'items' => ServiceCategoryResource::collection($categories),
                'total_items' => $categories->count(),
            ],
            'success' => true,
        ], 200);
    }

    #[OA\Post(
        path: '/api/v1/service-categories',
        operationId: 'storeServiceCategory',
        description: 'Create a new service category (slug is auto-generated from name)',
        security: [['cookieAuth' => []]],
        tags: ['Service Categories'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['name'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Hair Styling'),
                        new OA\Property(property: 'icon', description: 'Category icon string (class/URL) or image file upload', type: 'string', format: 'binary', nullable: true),
                        new OA\Property(property: 'orderby', type: 'integer', example: 1, nullable: true),
                        new OA\Property(property: 'status', type: 'boolean', example: true, nullable: true),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Category created successfully', content: new OA\JsonContent(ref: '#/components/schemas/ServiceCategory')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function store(StoreServiceCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('icon')) {
            $data['icon'] = $request->file('icon')->store('service_categories', 'public');
        }

        $category = ServiceCategory::create($data);

        return response()->json(new ServiceCategoryResource($category), 201);
    }

    #[OA\Get(
        path: '/api/v1/service-categories/{service_category}',
        operationId: 'getServiceCategory',
        description: 'Get a single service category with its services',
        tags: ['Service Categories'],
        parameters: [
            new OA\Parameter(name: 'service_category', description: 'Service Category ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Successful operation', content: new OA\JsonContent(ref: '#/components/schemas/ServiceCategory')),
            new OA\Response(response: 404, description: 'Category not found'),
        ]
    )]
    public function show(ServiceCategory $serviceCategory): JsonResponse
    {
        $serviceCategory->load('services');

        return response()->json(new ServiceCategoryResource($serviceCategory), 200);
    }

    #[OA\Post(
        path: '/api/v1/service-categories/{service_category}',
        operationId: 'updateServiceCategory',
        description: 'Update a service category. Uses POST with _method=PUT for file uploads.',
        security: [['cookieAuth' => []]],
        tags: ['Service Categories'],
        parameters: [
            new OA\Parameter(name: 'service_category', description: 'Service Category ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['_method'],
                    properties: [
                        new OA\Property(property: '_method', description: 'Method spoofing for PUT', type: 'string', example: 'PUT'),
                        new OA\Property(property: 'name', type: 'string', example: 'Hair Care & Styling', nullable: true),
                        new OA\Property(property: 'icon', description: 'Category icon string (class/URL) or image file upload', type: 'string', format: 'binary', nullable: true),
                        new OA\Property(property: 'orderby', type: 'integer', example: 2, nullable: true),
                        new OA\Property(property: 'status', type: 'boolean', example: true, nullable: true),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Category updated successfully', content: new OA\JsonContent(ref: '#/components/schemas/ServiceCategory')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Category not found'),
            new OA\Response(response: 422, description: 'Validation Error'),
        ]
    )]
    public function update(UpdateServiceCategoryRequest $request, ServiceCategory $serviceCategory): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('icon')) {
            if ($serviceCategory->icon && Storage::disk('public')->exists($serviceCategory->icon)) {
                Storage::disk('public')->delete($serviceCategory->icon);
            }
            $data['icon'] = $request->file('icon')->store('service_categories', 'public');
        }

        $serviceCategory->update($data);

        return response()->json(new ServiceCategoryResource($serviceCategory->fresh()), 200);
    }

    #[OA\Delete(
        path: '/api/v1/service-categories/{service_category}',
        operationId: 'deleteServiceCategory',
        description: 'Delete a service category (hard delete)',
        security: [['cookieAuth' => []]],
        tags: ['Service Categories'],
        parameters: [
            new OA\Parameter(name: 'service_category', description: 'Service Category ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Category deleted successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Service category deleted successfully.'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Category not found'),
        ]
    )]
    public function destroy(ServiceCategory $serviceCategory): JsonResponse
    {
        if ($serviceCategory->icon && Storage::disk('public')->exists($serviceCategory->icon)) {
            Storage::disk('public')->delete($serviceCategory->icon);
        }

        $serviceCategory->delete();

        return response()->json(['message' => 'Service category deleted successfully.']);
    }
}
