/**
 * BFF Route: POST /api/teams
 * 
 * Proxies team creation requests to Laravel backend.
 * Creates a new team and assigns creator as owner.
 * Requires Authorization header with JWT token.
 */

import { NextResponse } from 'next/server';
import { laravelApi, handleApiError, getRequestBody, getAuthHeader, buildHeaders } from '../_utils/api-client';
import type { CreateTeamRequest, Team } from '../_utils/types';

export async function POST(request: Request) {
  try {
    // Extract Authorization header
    const authHeader = getAuthHeader(request);

    // Validate Authorization header presence
    if (!authHeader) {
      return NextResponse.json(
        {
          status: 'error',
          message: 'Authorization header is required',
        },
        { status: 401 }
      );
    }

    // Extract and validate request body
    const body = await getRequestBody<CreateTeamRequest>(request);

    // Validate required fields
    if (!body.name) {
      return NextResponse.json(
        {
          status: 'error',
          message: 'Team name is required',
          errors: {
            name: ['Please give your workspace a name.'],
          },
        },
        { status: 422 }
      );
    }

    // Forward request to Laravel with Authorization header
    const response = await laravelApi.post<Team>(
      '/api/teams',
      body,
      buildHeaders(authHeader)
    );

    // Return success response
    return NextResponse.json(response, { status: 201 });
  } catch (error) {
    // Handle and forward errors from Laravel
    return handleApiError(error);
  }
}