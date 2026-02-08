<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\JoinTeamRequest;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use OpenApi\Attributes as OA;

class TeamController extends Controller
{
    /**
     * Create a new team and assign creator as owner
     */
    #[OA\Post(
        path: '/api/teams',
        tags: ['Teams'],
        summary: 'Create team',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['name'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Engineering Team'),
                ]
            )
        )
    )]
    #[OA\Response(response: 201, description: 'Team created successfully')]
    #[OA\Response(response: 422, description: 'Validation error')]
    #[OA\Response(response: 401, description: 'Unauthenticated')]
    public function store(CreateTeamRequest $request)
    {
        // Use database transaction to ensure atomicity
        $team = DB::transaction(function () use ($request) {
            // Create the team
            $team = Team::create([
                'name' => $request->name,
            ]);

            // Attach the creator as owner
            $team->users()->attach(auth()->id(), ['role' => 'owner']);

            return $team;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Workspace created successfully!',
            'data' => new TeamResource($team),
        ], 201);
    }

    /**
     * Join a team using an invite code
     */
    #[OA\Post(
        path: '/api/teams/join',
        tags: ['Teams'],
        summary: 'Join via invite code',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                required: ['invite_code'],
                properties: [
                    new OA\Property(property: 'invite_code', type: 'string', example: 'ABC123XYZ'),
                ]
            )
        )
    )]
    #[OA\Response(response: 200, description: 'Successfully joined team')]
    #[OA\Response(response: 404, description: 'Invalid invite code')]
    #[OA\Response(response: 422, description: 'Validation error')]
    #[OA\Response(response: 401, description: 'Unauthenticated')]
    public function join(JoinTeamRequest $request)
    {
        $team = Team::where('invite_code', $request->invite_code)->firstOrFail();

        // Check if user is already a member
        if ($team->users()->where('user_id', auth()->id())->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => "You're already a member of {$team->name}.",
            ], 422);
        }

        // Add user as member
        $team->users()->attach(auth()->id(), ['role' => 'member']);

        return response()->json([
            'status' => 'success',
            'message' => "You've joined {$team->name}!",
            'data' => new TeamResource($team),
        ], 200);
    }
}