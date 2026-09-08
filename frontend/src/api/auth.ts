import { apiRequest } from '@/api/client';
import { clearSession, setSession } from '@/auth/session';
import type { LoginCredentials, LoginResponse } from '@/types/api';

export async function login(credentials: LoginCredentials): Promise<LoginResponse> {
  const body = {
    email: credentials.email,
    password: credentials.password,
  };

  const response = await apiRequest<LoginResponse>('/login', {
    method: 'POST',
    body,
    auth: false,
  });

  setSession(response.token, response.user);
  return response;
}

export async function logout(): Promise<void> {
  try {
    await apiRequest<void>('/logout', { method: 'POST' });
  } finally {
    clearSession();
  }
}
