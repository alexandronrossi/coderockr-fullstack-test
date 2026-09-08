import { MemoryRouter } from 'react-router-dom';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { getToken, getUser } from '@/auth/session';
import { LoginPage } from '@/pages/LoginPage';

describe('LoginPage', () => {
  beforeEach(() => {
    sessionStorage.clear();
    vi.restoreAllMocks();
  });

  it('shows a generic message on 401 and does not expose a role field', async () => {
    const user = userEvent.setup();
    vi.stubGlobal(
      'fetch',
      vi.fn().mockResolvedValue(
        new Response(JSON.stringify({ message: 'Unauthenticated' }), {
          status: 401,
          headers: { 'Content-Type': 'application/json' },
        }),
      ),
    );

    render(
      <MemoryRouter>
        <LoginPage />
      </MemoryRouter>,
    );

    expect(screen.queryByLabelText(/role/i)).not.toBeInTheDocument();
    expect(document.querySelector('input[name="role"]')).toBeNull();
    expect(screen.getByLabelText(/^email$/i)).toHaveAttribute('maxLength', '255');
    expect(screen.getByLabelText(/^password$/i)).toHaveAttribute('maxLength', '72');

    await user.type(screen.getByLabelText(/^email$/i), 'a@b.com');
    await user.type(screen.getByLabelText(/^password$/i), 'wrong');
    await user.click(screen.getByRole('button', { name: /continue/i }));

    expect(await screen.findByRole('alert')).toHaveTextContent(/could not sign in/i);
  });

  it('stores session on successful login', async () => {
    const user = userEvent.setup();
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(
        JSON.stringify({
          token: 'tok-ok',
          user: { id: 1, name: 'Owner', email: 'owner@example.com', role: 'owner' },
        }),
        { status: 200, headers: { 'Content-Type': 'application/json' } },
      ),
    );
    vi.stubGlobal('fetch', fetchMock);

    render(
      <MemoryRouter>
        <LoginPage />
      </MemoryRouter>,
    );

    await user.type(screen.getByLabelText(/^email$/i), 'owner@example.com');
    await user.type(screen.getByLabelText(/^password$/i), 'secret');
    await user.click(screen.getByRole('button', { name: /continue/i }));

    await waitFor(() => {
      expect(getToken()).toBe('tok-ok');
      expect(getUser()?.email).toBe('owner@example.com');
    });

    const [, init] = fetchMock.mock.calls[0] as [string, RequestInit];
    const body = JSON.parse(String(init.body)) as Record<string, unknown>;
    expect(Object.keys(body).sort()).toEqual(['email', 'password']);
  });
});
