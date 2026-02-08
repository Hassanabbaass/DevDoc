import { NextResponse } from 'next/server';
import { laravelApi, handleApiError, getRequestBody } from '../../_utils/api-client';
import type { LoginRequest, AuthResponse } from '../../_utils/types';

export async function POST(request: Request) {
  try {
    const body = await getRequestBody<LoginRequest>(request);
    const response = await laravelApi.post<AuthResponse>('/api/login', body);
    return NextResponse.json(response, { status: 200 });
  } catch (error) {
    return handleApiError(error);
  }
}
