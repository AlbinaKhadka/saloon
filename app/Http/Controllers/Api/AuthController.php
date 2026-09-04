<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\User;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/login',
        operationId: 'login',
        description: 'Login to get Access and Refresh tokens (Sanctum)',
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
            new OA\Response(response: 200, description: 'Successful login'),
            new OA\Response(response: 401, description: 'Invalid credentials')
        ]
    )]
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Optional: clear old tokens if you want single-device login
        // $user->tokens()->delete();

        $accessToken = $user->createToken('access_token', ['access-api'], now()->addMinutes(15))->plainTextToken;
        $refreshToken = $user->createToken('refresh_token', ['issue-access-token'], now()->addDays(7))->plainTextToken;

        return response()->json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => 900,
            'user' => $user
        ], 200);
    }

    #[OA\Post(
        path: '/api/refresh',
        operationId: 'refresh',
        description: 'Refresh the access token using a valid refresh token',
        tags: ['Authentication'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['refresh_token'],
                properties: [
                    new OA\Property(property: 'refresh_token', type: 'string', example: '1|def50200ca...'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Tokens refreshed successfully'),
            new OA\Response(response: 401, description: 'Invalid or expired refresh token')
        ]
    )]
    public function refresh(Request $request)
    {
        $request->validate(['refresh_token' => 'required|string']);

        $token = PersonalAccessToken::findToken($request->refresh_token);

        if (!$token || !$token->can('issue-access-token') || $token->expires_at->isPast()) {
            return response()->json(['message' => 'Invalid or expired refresh token'], 401);
        }

        $user = $token->tokenable;

        // Token Rotation: Delete the old refresh token
        $token->delete();

        // Issue new tokens
        $accessToken = $user->createToken('access_token', ['access-api'], now()->addMinutes(15))->plainTextToken;
        $refreshToken = $user->createToken('refresh_token', ['issue-access-token'], now()->addDays(7))->plainTextToken;

        return response()->json([
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'bearer',
            'expires_in' => 900,
            'user' => $user
        ], 200);
    }

    #[OA\Post(
        path: '/api/logout',
        operationId: 'logout',
        description: 'Logout (Revoke the current access token)',
        security: [['bearerAuth' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: 'Successfully logged out')
        ]
    )]
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    #[OA\Get(
        path: '/api/user',
        operationId: 'user',
        description: 'Get the authenticated user',
        security: [['bearerAuth' => []]],
        tags: ['Authentication'],
        responses: [
            new OA\Response(response: 200, description: 'User retrieved successfully')
        ]
    )]
    public function user(Request $request)
    {
        return response()->json($request->user(), 200);
    }
}
