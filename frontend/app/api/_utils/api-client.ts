/**
 * Laravel API Client
 * 
 * Centralized utility for making requests to Laravel backend.
 * All BFF routes use this to maintain consistency and DRY principles.
 */

import { NextResponse } from 'next/server';
import { ApiError, LaravelErrorResponse, LaravelSuccessResponse } from './types';

// ============================================================================
// Configuration
// ============================================================================

const LARAVEL_API_URL = process.env.LARAVEL_API_URL;

if (!LARAVEL_API_URL) {
  throw new Error('LARAVEL_API_URL environment variable is not defined');
}

// ============================================================================
// API Client Class
// ============================================================================

class LaravelApiClient {
  private baseUrl: string;

  constructor(baseUrl: string) {
    this.baseUrl = baseUrl.replace(/\/$/, ''); // Remove trailing slash
  }

  /**
   * Make a request to Laravel API
   * 
   * @param endpoint - Laravel endpoint (e.g., '/api/auth/login')
   * @param options - Fetch options (method, headers, body)
   * @returns Laravel response data
   * @throws ApiError with status code and message
   */
  async request<T = any>(
    endpoint: string,
    options: RequestInit = {}
  ): Promise<LaravelSuccessResponse<T>> {
    const url = `${this.baseUrl}${endpoint}`;

    try {
      const response = await fetch(url, {
        ...options,
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          ...options.headers,
        },
      });

      const data = await response.json();

      // Laravel returned an error response
      if (!response.ok) {
        const errorData = data as LaravelErrorResponse;
        throw new ApiError(
          response.status,
          errorData.message || 'An error occurred',
          errorData.errors
        );
      }

      // Success response
      return data as LaravelSuccessResponse<T>;
    } catch (error) {
      // Network error or JSON parse error
      if (error instanceof ApiError) {
        throw error;
      }

      // Unknown error
      throw new ApiError(
        500,
        error instanceof Error ? error.message : 'Internal server error'
      );
    }
  }

  /**
   * POST request helper
   */
  async post<T = any>(endpoint: string, body?: any, headers?: HeadersInit): Promise<LaravelSuccessResponse<T>> {
    return this.request<T>(endpoint, {
      method: 'POST',
      headers,
      body: body ? JSON.stringify(body) : undefined,
    });
  }

  /**
   * GET request helper
   */
  async get<T = any>(endpoint: string, headers?: HeadersInit): Promise<LaravelSuccessResponse<T>> {
    return this.request<T>(endpoint, {
      method: 'GET',
      headers,
    });
  }
}

// ============================================================================
// Singleton Instance
// ============================================================================

export const laravelApi = new LaravelApiClient(LARAVEL_API_URL);

// ============================================================================
// Error Response Handler
// ============================================================================

/**
 * Convert ApiError to NextResponse with proper status code
 * 
 * This ensures consistent error handling across all BFF routes.
 */
export function handleApiError(error: unknown): NextResponse {
  if (error instanceof ApiError) {
    return NextResponse.json(
      {
        status: 'error',
        message: error.message,
        errors: error.errors,
      },
      { status: error.status }
    );
  }

  // Unknown error - return 500
  return NextResponse.json(
    {
      status: 'error',
      message: error instanceof Error ? error.message : 'Internal server error',
    },
    { status: 500 }
  );
}

// ============================================================================
// Request Helper Functions
// ============================================================================

/**
 * Extract JSON body from Next.js Request
 * Handles parsing errors gracefully
 */
export async function getRequestBody<T = any>(request: Request): Promise<T> {
  try {
    return await request.json();
  } catch (error) {
    throw new ApiError(400, 'Invalid JSON in request body');
  }
}

/**
 * Extract Authorization header from request
 * Returns undefined if not present
 */
export function getAuthHeader(request: Request): string | undefined {
  return request.headers.get('Authorization') || undefined;
}

/**
 * Build headers object with optional Authorization
 */
export function buildHeaders(authToken?: string): HeadersInit {
  const headers: HeadersInit = {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  };

  if (authToken) {
    headers['Authorization'] = authToken;
  }

  return headers;
}