/**
 * Shared TypeScript Types for BFF Layer
 * 
 * These types ensure type safety across all BFF routes
 * and match the Laravel API response structure.
 */

// ============================================================================
// Laravel API Response Types
// ============================================================================

export interface LaravelSuccessResponse<T = any> {
  status: 'success';
  message?: string;
  data: T;
}

export interface LaravelErrorResponse {
  status: 'error';
  message: string;
  errors?: Record<string, string[]>; // Validation errors
}

export type LaravelResponse<T = any> = LaravelSuccessResponse<T> | LaravelErrorResponse;

// ============================================================================
// User & Auth Types
// ============================================================================

export interface User {
  id: number;
  name: string;
  email: string;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
  teams?: Team[];
}

export interface AuthResponse {
  user: User;
  token: string;
}

// ============================================================================
// Team Types
// ============================================================================

export interface Team {
  id: number;
  name: string;
  invite_code: string;
  created_at: string;
  updated_at: string;
  role?: 'owner' | 'admin' | 'member'; // User's role in the team
  users?: User[];
}

// ============================================================================
// Request Body Types
// ============================================================================

export interface RegisterRequest {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface CreateTeamRequest {
  name: string;
}

export interface JoinTeamRequest {
  invite_code: string;
}

// ============================================================================
// API Error Types
// ============================================================================

export class ApiError extends Error {
  constructor(
    public status: number,
    public message: string,
    public errors?: Record<string, string[]>
  ) {
    super(message);
    this.name = 'ApiError';
  }
}