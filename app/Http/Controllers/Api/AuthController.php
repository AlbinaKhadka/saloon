<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'admin@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Login successful'),
                        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Request is not from a stateful domain'),
            new OA\Response(response: 401, description: 'Invalid credentials'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->validated())) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user),
        ], 200);
    }

    #[OA\Post(
        path: '/api/logout',
        operationId: 'logout',
        description: 'Log out the authenticated user and invalidate token or session.',
        security: [['cookieAuth' => []], ['bearerAuth' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: 'Successfully logged out'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user && method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }

    #[OA\Get(
        path: '/sanctum/csrf-cookie',
        operationId: 'csrfCookie',
        description: 'Get Sanctum CSRF cookie initialization for SPA/session authentication.',
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 204, description: 'CSRF cookie set successfully'),
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
                        new OA\Property(property: 'message', type: 'string', example: 'Login successful'),
                        new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Login successful',
            'user' => new UserResource($request->user()),
        ], 200);
    }

    #[OA\Post(
        path: '/api/v1/change-password',
        operationId: 'changePassword',
        description: 'Change current user password.',
        security: [['cookieAuth' => []]],
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['current_password', 'new_password', 'new_password_confirmation'],
                properties: [
                    new OA\Property(property: 'current_password', type: 'string', format: 'password', example: 'password'),
                    new OA\Property(property: 'new_password', type: 'string', format: 'password', example: 'newpassword123'),
                    new OA\Property(property: 'new_password_confirmation', type: 'string', format: 'password', example: 'newpassword123'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Password changed successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'message', type: 'string', example: 'Password changed successfully'),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthenticated'),
            new OA\Response(response: 422, description: 'Validation Error / Current Password Mismatch'),
        ]
    )]
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password does not match',
                'errors' => [
                    'current_password' => ['The provided current password is incorrect.'],
                ],
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'message' => 'Password changed successfully',
        ], 200);
    }
}
