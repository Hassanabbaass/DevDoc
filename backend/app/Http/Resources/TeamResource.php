<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'invite_code' => $this->invite_code,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            
            // Include pivot data if available (user's role in team)
            'role' => $this->whenPivotLoaded('team_user', function () {
                return $this->pivot->role;
            }),
            
            // Include users if loaded
            'users' => UserResource::collection($this->whenLoaded('users')),
        ];
    }
}