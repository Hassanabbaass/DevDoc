<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;

class HealthController extends Controller
{
    #[OA\Get(
        path: '/api/health',
        tags: ['Health'],
        summary: 'Health Check'
    )]
    #[OA\Response(
        response: 200,
        description: 'OK',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'status', type: 'string', example: 'ok')
            ]
        )
    )]
    public function index()
    {
        return response()->json(['status' => 'ok']);
    }
}