<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/login',
        operationId: 'login',
        description: 'Authenticate a user using session-based cookie authentication.',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'admin@example.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        example: 'password'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Login successful'
                        ),
                        new OA\Property(
                            property: 'user',
                            ref: '#/components/schemas/User'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid credentials'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        if (!Auth::attempt($request->validated())) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        // Prevent session fixation
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Login successful',
            'user' => new UserResource(Auth::user()),
        ], 200);
    }

    #[OA\Post(
        path: '/api/logout',
        operationId: 'logout',
        description: 'Log out the authenticated user and invalidate the session.',
        security: [['cookieAuth' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successfully logged out'
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    #[OA\Get(
        path: '/sanctum/csrf-cookie',
        operationId: 'csrfCookie',
        description: 'Get Sanctum CSRF cookie initialization for SPA/session authentication.',
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 204,
                description: 'CSRF cookie set successfully'
            ),
        ]
    )]
    public function csrfCookie(): JsonResponse
    {
        return response()->json(null, 204);
    }

    #[OA\Get(
        path: '/api/user',
        operationId: 'user',
        description: 'Get the currently authenticated user.',
        security: [['cookieAuth' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'User retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Login successful'
                        ),
                        new OA\Property(
                            property: 'user',
                            ref: '#/components/schemas/User'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Unauthenticated'
            ),
        ]
    )]
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Login successful',
            'user' => new UserResource($request->user()),
        ], 200);
    }
}
