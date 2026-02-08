import { NextResponse } from 'next/server';
import { laravelApi, handleApiError, getRequestBody } from '../../_utils/api-client';
import type { RegisterRequest, AuthResponse } from '../../_utils/types';

export async function POST(request: Request) {
  try {
    const body = await getRequestBody<RegisterRequest>(request);
    const response = await laravelApi.post<AuthResponse>('/api/register', body);
    return NextResponse.json(response, { status: 201 });
  } catch (error) {
    return handleApiError(error);
  }
}
