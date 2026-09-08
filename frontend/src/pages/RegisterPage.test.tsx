import { MemoryRouter } from 'react-router-dom';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { getUser } from '@/auth/session';
import { RegisterPage } from '@/pages/RegisterPage';

describe('RegisterPage', () => {
  beforeEach(() => {
    sessionStorage.clear();
    vi.restoreAllMocks();
  });

  it('has no role field and enforces maxLength on auth inputs', () => {
    render(
      <MemoryRouter>
        <RegisterPage />
      </MemoryRouter>,
    );

    expect(document.querySelector('input[name="role"]')).toBeNull();
    expect(screen.getByLabelText(/^name$/i)).toHaveAttribute('maxLength', '255');
    expect(screen.getByLabelText(/^email$/i)).toHaveAttribute('maxLength', '255');
    expect(screen.getByLabelText(/^password$/i)).toHaveAttribute('maxLength', '72');
    expect(screen.getByLabelText(/confirm password/i)).toHaveAttribute('maxLength', '72');
  });

  it('registers with allowlisted fields only', async () => {
    const user = userEvent.setup();
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(
        JSON.stringify({
          token: 'tok-reg',
          user: { id: 3, name: 'New Owner', email: 'new@example.com', role: 'owner' },
        }),
        { status: 201, headers: { 'Content-Type': 'application/json' } },
      ),
    );
    vi.stubGlobal('fetch', fetchMock);

    render(
      <MemoryRouter>
        <RegisterPage />
      </MemoryRouter>,
    );

    await user.type(screen.getByLabelText(/^name$/i), 'New Owner');
    await user.type(screen.getByLabelText(/^email$/i), 'new@example.com');
    await user.type(screen.getByLabelText(/^password$/i), 'password1');
    await user.type(screen.getByLabelText(/confirm password/i), 'password1');
    await user.click(screen.getByRole('button', { name: /create account/i }));

    await waitFor(() => {
      expect(getUser()?.role).toBe('owner');
      expect(fetchMock).toHaveBeenCalled();
    });

    const [, init] = fetchMock.mock.calls[0] as [string, RequestInit];
    const body = JSON.parse(String(init.body)) as Record<string, unknown>;
    expect(Object.keys(body).sort()).toEqual([
      'email',
      'name',
      'password',
      'password_confirmation',
    ]);
    expect(body).not.toHaveProperty('role');
  });
});
