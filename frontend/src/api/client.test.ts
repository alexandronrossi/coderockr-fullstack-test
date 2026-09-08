import { beforeEach, describe, expect, it, vi } from 'vitest';
import { apiRequest } from '@/api/client';
import { clearSession, setSession } from '@/auth/session';
import type { User } from '@/types/api';

const user: User = {
  id: 1,
  name: 'Owner',
  email: 'owner@example.com',
  role: 'owner',
};

describe('api client', () => {
  beforeEach(() => {
    sessionStorage.clear();
    vi.restoreAllMocks();
  });

  it('sends Authorization Bearer header when authenticated', async () => {
    setSession('secret-token', user);

    const fetchMock = vi.fn().mockResolvedValue(
      new Response(JSON.stringify({ ok: true }), {
        status: 200,
        headers: { 'Content-Type': 'application/json' },
      }),
    );
    vi.stubGlobal('fetch', fetchMock);

    await apiRequest('/investments');

    expect(fetchMock).toHaveBeenCalledOnce();
    const [, init] = fetchMock.mock.calls[0] as [string, RequestInit];
    const headers = new Headers(init.headers);
    expect(headers.get('Authorization')).toBe('Bearer secret-token');
  });

  it('clears session on 401', async () => {
    setSession('secret-token', user);

    vi.stubGlobal(
      'fetch',
      vi.fn().mockResolvedValue(
        new Response(JSON.stringify({ message: 'Unauthenticated' }), {
          status: 401,
          headers: { 'Content-Type': 'application/json' },
        }),
      ),
    );

    await expect(apiRequest('/investments')).rejects.toMatchObject({ status: 401 });
    expect(sessionStorage.getItem('coderockr.auth.token')).toBeNull();
  });
});
