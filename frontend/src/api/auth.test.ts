import { beforeEach, describe, expect, it, vi } from 'vitest';
import { login } from '@/api/auth';

describe('auth api', () => {
  beforeEach(() => {
    sessionStorage.clear();
    vi.restoreAllMocks();
  });

  it('sends only email and password in the login body', async () => {
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(
        JSON.stringify({
          token: 'tok',
          user: { id: 1, name: 'A', email: 'a@b.com', role: 'owner' },
        }),
        { status: 200, headers: { 'Content-Type': 'application/json' } },
      ),
    );
    vi.stubGlobal('fetch', fetchMock);

    await login({ email: 'a@b.com', password: 'secret' });

    const [, init] = fetchMock.mock.calls[0] as [string, RequestInit];
    const body = JSON.parse(String(init.body)) as Record<string, unknown>;
    expect(Object.keys(body).sort()).toEqual(['email', 'password']);
    expect(body).not.toHaveProperty('role');
    expect(body).not.toHaveProperty('user_id');
  });
});
