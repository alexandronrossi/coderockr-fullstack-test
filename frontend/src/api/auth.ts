import { apiRequest } from '@/api/client';
import { clearSession, setSession } from '@/auth/session';
import type { AuthResponse, LoginCredentials, RegisterCredentials } from '@/types/api';

export async function login(credentials: LoginCredentials): Promise<AuthResponse> {
  const body = {
    email: credentials.email,
    password: credentials.password,
  };

  const response = await apiRequest<AuthResponse>('/login', {
    method: 'POST',
    body,
    auth: false,
  });

  setSession(response.token, response.user);
  return response;
}

export async function register(credentials: RegisterCredentials): Promise<AuthResponse> {
  const body = {
    name: credentials.name,
    email: credentials.email,
    password: credentials.password,
    password_confirmation: credentials.password_confirmation,
  };

  const response = await apiRequest<AuthResponse>('/register', {
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
