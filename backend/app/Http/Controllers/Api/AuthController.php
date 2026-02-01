<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    #[OA\Post(
        path: '/api/register',
        tags: ['Authentication'],
        summary: 'Register a new user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['name', 'email', 'password'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Hassan'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'hassan@example.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', example: 'password123'),
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: 'User created successfully')]
    #[OA\Response(response: 422, description: 'Validation Error')]
    public function register(Request $request)
    {
        // 1. Validate the input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // 2. Attempt to create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            return response()->json(['message' => 'User created', 'user' => $user], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Registration failed: ' . $e->getMessage()], 500);
        }
    }

    #[OA\Post(
        path: '/api/login',
        tags: ['Authentication'],
        summary: 'Login and get JWT token',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', example: 'hassan@example.com'),
                    new OA\Property(property: 'password', type: 'string', example: 'password123'),
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Successful login')]
    #[OA\Response(response: 401, description: 'Unauthorized')]
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $credentials = $request->only('email', 'password');

            if (!$token = auth()->attempt($credentials)) {
                return response()->json(['error' => 'Invalid email or password'], 401);
            }

            return response()->json([
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth()->factory()->getTTL() * 60
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Could not create token'], 500);
        }
    }
}