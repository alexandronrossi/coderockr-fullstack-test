import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { render, screen } from '@testing-library/react';
import { beforeEach, describe, expect, it } from 'vitest';
import { RequireAuth } from '@/auth/RequireAuth';
import { setSession } from '@/auth/session';

describe('RequireAuth', () => {
  beforeEach(() => {
    sessionStorage.clear();
  });

  it('redirects to login when there is no token', () => {
    render(
      <MemoryRouter initialEntries={['/investments']}>
        <Routes>
          <Route path="/login" element={<div>Login page</div>} />
          <Route
            path="/investments"
            element={
              <RequireAuth>
                <div>Protected</div>
              </RequireAuth>
            }
          />
        </Routes>
      </MemoryRouter>,
    );

    expect(screen.getByText('Login page')).toBeInTheDocument();
    expect(screen.queryByText('Protected')).not.toBeInTheDocument();
  });

  it('renders children when token exists', () => {
    setSession('tok', {
      id: 1,
      name: 'Owner',
      email: 'owner@example.com',
      role: 'owner',
    });

    render(
      <MemoryRouter initialEntries={['/investments']}>
        <Routes>
          <Route path="/login" element={<div>Login page</div>} />
          <Route
            path="/investments"
            element={
              <RequireAuth>
                <div>Protected</div>
              </RequireAuth>
            }
          />
        </Routes>
      </MemoryRouter>,
    );

    expect(screen.getByText('Protected')).toBeInTheDocument();
  });
});
