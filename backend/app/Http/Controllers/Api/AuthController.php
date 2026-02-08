<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    /**
     * Register a new user account
     */
    #[OA\Post(
        path: '/api/register',
        tags: ['Auth'],
        summary: 'Create account',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'password_confirmation'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                    new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                    new OA\Property(property: 'password_confirmation', type: 'string', example: 'password123'),
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: 'Account created successfully')]
    #[OA\Response(response: 422, description: 'Validation error')]
    public function register(RegisterRequest $request)
    {
        // Create the user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Generate JWT token
        $token = auth()->login($user);

        return response()->json([
            'status' => 'success',
            'message' => 'Account created successfully!',
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
            ]
        ], 201);
    }

    /**
     * Login user and return JWT token
     */
    #[OA\Post(
        path: '/api/login',
        tags: ['Auth'],
        summary: 'Login',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'john@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Login successful')]
    #[OA\Response(response: 401, description: 'Invalid credentials')]
    #[OA\Response(response: 422, description: 'Validation error')]
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials. Please check your email and password.',
            ], 401);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Welcome back!',
            'data' => [
                'user' => new UserResource(auth()->user()),
                'token' => $token,
            ]
        ], 200);
    }

    /**
     * Get authenticated user profile
     */
    #[OA\Get(
        path: '/api/auth/me',
        tags: ['Auth'],
        summary: 'Get current user',
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(response: 200, description: 'User profile retrieved')]
    #[OA\Response(response: 401, description: 'Unauthenticated')]
    public function me()
    {
        return response()->json([
            'status' => 'success',
            'data' => new UserResource(auth()->user()->load('teams')),
        ], 200);
    }
}