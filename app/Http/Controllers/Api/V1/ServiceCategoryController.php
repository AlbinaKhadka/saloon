<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceCategoryRequest;
use App\Http\Requests\UpdateServiceCategoryRequest;
use App\Http\Resources\ServiceCategoryResource;
use App\Models\ServiceCategory;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class ServiceCategoryController extends Controller
{
    #[OA\Get(
        path: '/api/v1/service-categories',
        operationId: 'getServiceCategories',
        description: 'Get list of service categories',
        tags: ['Service Categories'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/ServiceCategory')
                        ),
                    ]
                )
            ),
        ]
    )]
    public function index(): JsonResponse
    {
        $categories = ServiceCategory::orderBy('orderby')->paginate(15);

        return ServiceCategoryResource::collection($categories)->response();
    }

    #[OA\Post(
        path: '/api/v1/service-categories',
        operationId: 'storeServiceCategory',
        description: 'Create a new service category',
        security: [['cookieAuth' => []]],
        tags: ['Service Categories'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Hair Styling'),
                    new OA\Property(property: 'icon', type: 'string', example: 'fa-cut', nullable: true),
                    new OA\Property(property: 'orderby', type: 'integer', example: 1, nullable: true),
                    new OA\Property(property: 'status', type: 'boolean', example: true, nullable: true),
                ]
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
        $category = ServiceCategory::create($request->validated());

        return (new ServiceCategoryResource($category))
            ->response()
            ->setStatusCode(201);
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

        return (new ServiceCategoryResource($serviceCategory))->response();
    }

    #[OA\Put(
        path: '/api/v1/service-categories/{service_category}',
        operationId: 'updateServiceCategory',
        description: 'Update a service category',
        security: [['cookieAuth' => []]],
        tags: ['Service Categories'],
        parameters: [
            new OA\Parameter(name: 'service_category', description: 'Service Category ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Hair Care & Styling'),
                    new OA\Property(property: 'icon', type: 'string', example: 'fa-scissors', nullable: true),
                    new OA\Property(property: 'orderby', type: 'integer', example: 2, nullable: true),
                    new OA\Property(property: 'status', type: 'boolean', example: true, nullable: true),
                ]
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
        $serviceCategory->update($request->validated());

        return (new ServiceCategoryResource($serviceCategory->fresh()))->response();
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
        $serviceCategory->delete();

        return response()->json(['message' => 'Service category deleted successfully.']);
    }
}
