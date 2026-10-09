<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStylistRequest;
use App\Http\Requests\UpdateStylistRequest;
use App\Http\Resources\StylistResource;
use App\Models\Stylist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

class StylistController extends Controller
{
    #[OA\Get(
        path: '/api/v1/stylists',
        operationId: 'getStylists',
        description: 'Get list of active stylists with optional search and pagination.',
        tags: ['Stylists'],
        parameters: [
            new OA\Parameter(name: 'search', description: 'Search stylists by name or designation keyword', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'name', description: 'Filter stylists by name keyword', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'status', description: 'Filter by status: 1 or "active", 0 or "hidden"', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', description: 'Number of items per page. If omitted, loads all matching active stylists.', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Stylist List'),
                        new OA\Property(
                            property: 'data',
                            type: 'object',
                            properties: [
                                new OA\Property(
                                    property: 'items',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/Stylist')
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
        $query = Stylist::with('services');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('designation', 'like', "%{$search}%");
            });
        } elseif ($request->filled('name')) {
            $name = $request->name;
            $query->where('name', 'like', "%{$name}%");
        }

        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $status = strtolower((string) $request->status);
            if ($status === 'active' || $status === '1') {
                $query->where('status', 1);
            } elseif ($status === 'hidden' || $status === '0') {
                $query->where('status', 0);
            }
        } else {
            // Default public listing only shows active stylists
            $query->where('status', 1);
        }

        $query->orderBy('orderby', 'asc')->orderBy('id', 'desc');

        if ($request->filled('per_page') && is_numeric($request->per_page) && (int) $request->per_page > 0) {
            $perPage = (int) $request->per_page;
            $stylists = $query->paginate($perPage);

            return response()->json([
                'message' => 'Stylist List',
                'data' => [
                    'items' => StylistResource::collection($stylists->items()),
                    'page' => $stylists->currentPage(),
                    'total_page' => $stylists->lastPage(),
                    'total_items' => $stylists->total(),
                ],
                'success' => true,
            ], 200);
        }

        $stylists = $query->get();

        return response()->json([
            'message' => 'Stylist List',
            'data' => [
                'items' => StylistResource::collection($stylists),
                'total_items' => $stylists->count(),
            ],
            'success' => true,
        ], 200);
    }

    #[OA\Get(
        path: '/api/v1/stylists/{stylist}',
        operationId: 'getStylist',
        description: 'Get details of a single active stylist by ID',
        tags: ['Stylists'],
        parameters: [
            new OA\Parameter(name: 'stylist', description: 'Stylist ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Successful operation', content: new OA\JsonContent(ref: '#/components/schemas/Stylist')),
            new OA\Response(response: 404, description: 'Stylist not found or inactive'),
        ]
    )]
    public function show(Stylist $stylist): JsonResponse
    {
        if ($stylist->status !== 1) {
            return response()->json(['message' => 'Stylist not found'], 404);
        }

        $stylist->load('services');

        return response()->json(new StylistResource($stylist), 200);
    }

    #[OA\Post(
        path: '/api/v1/stylists',
        operationId: 'storeStylist',
        description: 'Create a new stylist with optional photo upload and assigned services',
        security: [['cookieAuth' => []]],
        tags: ['Stylists'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['name', 'status'],
                    properties: [
                        new OA\Property(property: 'name', type: 'string', example: 'Jane Doe'),
                        new OA\Property(property: 'slug', type: 'string', nullable: true, example: 'jane-doe'),
                        new OA\Property(property: 'designation', type: 'string', nullable: true, example: 'Senior Hair Stylist'),
                        new OA\Property(property: 'bio', type: 'string', nullable: true, example: 'Expert hair stylist with 5 years experience'),
                        new OA\Property(property: 'photo', description: 'Stylist photo file', type: 'string', format: 'binary', nullable: true),
                        new OA\Property(property: 'phone', type: 'string', nullable: true, example: '+1234567890'),
                        new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true, example: 'jane@example.com'),
                        new OA\Property(property: 'experience_years', type: 'integer', nullable: true, example: 5),
                        new OA\Property(
                            property: 'social_links',
                            type: 'object',
                            nullable: true,
                            example: ["instagram" => "https://instagram.com/jane"]
                        ),
                        new OA\Property(property: 'status', description: '0 = hidden, 1 = active', type: 'integer', enum: [0, 1]),
                        new OA\Property(property: 'orderby', type: 'integer', nullable: true, example: 1),
                        new OA\Property(
                            property: 'service_ids',
                            description: 'Array of service IDs to assign to this stylist',
                            type: 'array',
                            items: new OA\Items(type: 'integer'),
                            nullable: true
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 201, description: 'Stylist created successfully', content: new OA\JsonContent(ref: '#/components/schemas/Stylist')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation Error')
        ]
    )]
    public function store(StoreStylistRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('stylists', 'public');
        }

        $stylist = DB::transaction(function () use ($validated, $request) {
            $stylist = Stylist::create($validated);

            if ($request->has('service_ids')) {
                $stylist->services()->sync($request->input('service_ids', []));
            }

            return $stylist;
        });

        $stylist->load('services');

        return response()->json(new StylistResource($stylist), 201);
    }

    #[OA\Post(
        path: '/api/v1/stylists/{stylist}',
        operationId: 'updateStylist',
        description: 'Update a stylist. We use POST with _method=PUT to support multipart/form-data for image uploads in PHP.',
        security: [['cookieAuth' => []]],
        tags: ['Stylists'],
        parameters: [
            new OA\Parameter(name: 'stylist', description: 'Stylist ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['status', '_method'],
                    properties: [
                        new OA\Property(property: '_method', description: 'Method spoofing for PUT', type: 'string', example: 'PUT'),
                        new OA\Property(property: 'name', type: 'string', nullable: true),
                        new OA\Property(property: 'slug', type: 'string', nullable: true),
                        new OA\Property(property: 'designation', type: 'string', nullable: true),
                        new OA\Property(property: 'bio', type: 'string', nullable: true),
                        new OA\Property(property: 'photo', description: 'Stylist photo file (optional on update)', type: 'string', format: 'binary', nullable: true),
                        new OA\Property(property: 'phone', type: 'string', nullable: true),
                        new OA\Property(property: 'email', type: 'string', format: 'email', nullable: true),
                        new OA\Property(property: 'experience_years', type: 'integer', nullable: true),
                        new OA\Property(
                            property: 'social_links',
                            type: 'object',
                            nullable: true
                        ),
                        new OA\Property(property: 'status', description: '0 = hidden, 1 = active', type: 'integer', enum: [0, 1]),
                        new OA\Property(property: 'orderby', type: 'integer', nullable: true),
                        new OA\Property(
                            property: 'service_ids',
                            description: 'Array of service IDs to assign (omit to preserve existing, pass empty array [] to clear)',
                            type: 'array',
                            items: new OA\Items(type: 'integer'),
                            nullable: true
                        ),
                    ]
                )
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Stylist updated successfully', content: new OA\JsonContent(ref: '#/components/schemas/Stylist')),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Stylist not found'),
            new OA\Response(response: 422, description: 'Validation Error')
        ]
    )]
    public function update(UpdateStylistRequest $request, Stylist $stylist): JsonResponse
    {
        $validated = $request->validated();
        $oldPhoto = null;

        if ($request->hasFile('photo')) {
            $oldPhoto = $stylist->photo;
            $validated['photo'] = $request->file('photo')->store('stylists', 'public');
        }

        DB::transaction(function () use ($stylist, $validated, $request) {
            $stylist->update($validated);

            if ($request->has('service_ids')) {
                $stylist->services()->sync($request->input('service_ids', []));
            }
        });

        if ($oldPhoto && Storage::disk('public')->exists($oldPhoto)) {
            Storage::disk('public')->delete($oldPhoto);
        }

        $stylist->load('services');

        return response()->json(new StylistResource($stylist), 200);
    }

    #[OA\Delete(
        path: '/api/v1/stylists/{stylist}',
        operationId: 'deleteStylist',
        description: 'Delete a stylist and remove associated photo',
        security: [['cookieAuth' => []]],
        tags: ['Stylists'],
        parameters: [
            new OA\Parameter(name: 'stylist', description: 'Stylist ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Stylist deleted successfully'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 404, description: 'Stylist not found')
        ]
    )]
    public function destroy(Stylist $stylist): JsonResponse
    {
        if ($stylist->photo && Storage::disk('public')->exists($stylist->photo)) {
            Storage::disk('public')->delete($stylist->photo);
        }

        $stylist->delete();

        return response()->json(['message' => 'Stylist deleted successfully'], 200);
    }
}
