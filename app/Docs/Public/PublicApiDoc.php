<?php

namespace App\Docs\Public;

use OpenApi\Attributes as OA;

#[OA\Info(
    title: "Public Website API (No Auth)",
    version: "1.0.0",
    description: "Public read-only endpoints for the Saloon website (Banners, Services, Service Categories, Galleries). No authorization required."
)]

#[OA\Server(
    url: "https://saloon-rx72.onrender.com",
    description: "Live Production Server (Render)"
)]

#[OA\Server(
    url: "http://127.0.0.1:8000",
    description: "Local Development Server"
)]

class PublicApiDoc
{
    // Banners (Public)

    #[OA\Get(
        path: '/api/v1/banners',
        operationId: 'getPublicBanners',
        description: 'Fetch list of banners for the website homepage/promotions (No auth required)',
        tags: ['Public Banners'],
        parameters: [
            new OA\Parameter(
                name: 'search',
                description: 'Search banners by title keyword',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'title',
                description: 'Filter banners by title keyword',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'status',
                description: 'Filter by status: 1 or "active", 0 or "inactive"',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'per_page',
                description: 'Number of items per page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Banner'))
            )
        ]
    )]
    public function getBanners() {}

    #[OA\Get(
        path: '/api/v1/banners/{banner}',
        operationId: 'getPublicBanner',
        description: 'Get details of a single banner by ID (No auth required)',
        tags: ['Public Banners'],
        parameters: [
            new OA\Parameter(name: 'banner', description: 'Banner ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Successful operation', content: new OA\JsonContent(ref: '#/components/schemas/Banner')),
            new OA\Response(response: 404, description: 'Banner not found')
        ]
    )]
    public function getBanner() {}

    // Service Categories (Public)

    #[OA\Get(
        path: '/api/v1/service-categories',
        operationId: 'getPublicServiceCategories',
        description: 'Fetch list of service categories for the website (No auth required)',
        tags: ['Public Service Categories'],
        parameters: [
            new OA\Parameter(
                name: 'search',
                description: 'Search categories by name or title keyword',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'title',
                description: 'Filter categories by title/name keyword',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'name',
                description: 'Filter categories by category name',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'per_page',
                description: 'Number of items per page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/ServiceCategory'))
            )
        ]
    )]
    public function getServiceCategories() {}

    #[OA\Get(
        path: '/api/v1/service-categories/{service_category}',
        operationId: 'getPublicServiceCategory',
        description: 'Get details of a single service category by ID (No auth required)',
        tags: ['Public Service Categories'],
        parameters: [
            new OA\Parameter(name: 'service_category', description: 'Service Category ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Successful operation', content: new OA\JsonContent(ref: '#/components/schemas/ServiceCategory')),
            new OA\Response(response: 404, description: 'Service Category not found')
        ]
    )]
    public function getServiceCategory() {}

    // Services (Public)

    #[OA\Get(
        path: '/api/v1/services',
        operationId: 'getPublicServices',
        description: 'Fetch list of services for the website with optional category and title filter (No auth required)',
        tags: ['Public Services'],
        parameters: [
            new OA\Parameter(
                name: 'search',
                description: 'Search services by title keyword',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'title',
                description: 'Filter services by title keyword',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'service_category_id',
                description: 'Filter services by service category ID',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            ),
            new OA\Parameter(
                name: 'per_page',
                description: 'Number of items per page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Service'))
            )
        ]
    )]
    public function getServices() {}

    #[OA\Get(
        path: '/api/v1/services/{service}',
        operationId: 'getPublicService',
        description: 'Get details of a single service by ID (No auth required)',
        tags: ['Public Services'],
        parameters: [
            new OA\Parameter(name: 'service', description: 'Service ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Successful operation', content: new OA\JsonContent(ref: '#/components/schemas/Service')),
            new OA\Response(response: 404, description: 'Service not found')
        ]
    )]
    public function getService() {}

    // Galleries (Public)

    #[OA\Get(
        path: '/api/v1/galleries',
        operationId: 'getPublicGalleries',
        description: 'Fetch list of gallery images for the website (No auth required)',
        tags: ['Public Galleries'],
        parameters: [
            new OA\Parameter(
                name: 'category',
                description: 'Filter gallery items by category name',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string')
            ),
            new OA\Parameter(
                name: 'per_page',
                description: 'Number of items per page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer')
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Gallery'))
            )
        ]
    )]
    public function getGalleries() {}

    #[OA\Get(
        path: '/api/v1/galleries/{gallery}',
        operationId: 'getPublicGallery',
        description: 'Get details of a single gallery item by ID (No auth required)',
        tags: ['Public Galleries'],
        parameters: [
            new OA\Parameter(name: 'gallery', description: 'Gallery ID', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))
        ],
        responses: [
            new OA\Response(response: 200, description: 'Successful operation', content: new OA\JsonContent(ref: '#/components/schemas/Gallery')),
            new OA\Response(response: 404, description: 'Gallery not found')
        ]
    )]
    public function getGallery() {}
}
