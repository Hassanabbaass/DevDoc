/**
 * BFF Route: GET /api/auth/me
 * 
 * Proxies authenticated user profile requests to Laravel backend.
 * Returns user data with their teams.
 * Requires Authorization header with JWT token.
 */

import { NextResponse } from 'next/server';
import { laravelApi, handleApiError, getAuthHeader, buildHeaders } from '../../_utils/api-client';
import type { User } from '../../_utils/types';

export async function GET(request: Request) {
  try {
    // Extract Authorization header from request
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

    // Forward request to Laravel with Authorization header
    const response = await laravelApi.get<User>(
      '/api/auth/me',
      buildHeaders(authHeader)
    );

    // Return success response
    return NextResponse.json(response, { status: 200 });
  } catch (error) {
    // Handle and forward errors from Laravel
    return handleApiError(error);
  }
}