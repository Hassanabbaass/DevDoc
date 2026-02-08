/**
 * BFF Route: POST /api/teams/join
 * 
 * Proxies team join requests to Laravel backend.
 * Joins a team using an invite code.
 * Requires Authorization header with JWT token.
 */

import { NextResponse } from 'next/server';
import { laravelApi, handleApiError, getRequestBody, getAuthHeader, buildHeaders } from '../../_utils/api-client';
import type { JoinTeamRequest, Team } from '../../_utils/types';

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
    const body = await getRequestBody<JoinTeamRequest>(request);

    // Validate required fields
    if (!body.invite_code) {
      return NextResponse.json(
        {
          status: 'error',
          message: 'Invite code is required',
          errors: {
            invite_code: ['Please provide an invite code.'],
          },
        },
        { status: 422 }
      );
    }

    // Forward request to Laravel with Authorization header
    const response = await laravelApi.post<Team>(
      '/api/teams/join',
      body,
      buildHeaders(authHeader)
    );

    // Return success response
    return NextResponse.json(response, { status: 200 });
  } catch (error) {
    // Handle and forward errors from Laravel
    return handleApiError(error);
  }
}