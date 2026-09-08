import { MemoryRouter } from 'react-router-dom';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { AppShell } from '@/components/AppShell';
import { getToken, setSession } from '@/auth/session';

describe('AppShell logout', () => {
  beforeEach(() => {
    sessionStorage.clear();
    vi.restoreAllMocks();
    setSession('tok', {
      id: 1,
      name: 'Owner',
      email: 'owner@example.com',
      role: 'owner',
    });
  });

  it('calls logout API, clears session and navigates to login', async () => {
    const user = userEvent.setup();
    const fetchMock = vi.fn().mockResolvedValue(new Response(null, { status: 204 }));
    vi.stubGlobal('fetch', fetchMock);

    render(
      <MemoryRouter initialEntries={['/investments']}>
        <AppShell>
          <div>Content</div>
        </AppShell>
      </MemoryRouter>,
    );

    expect(screen.getByText('Owner')).toBeInTheDocument();
    await user.click(screen.getByRole('button', { name: /sign out/i }));

    await waitFor(() => {
      expect(fetchMock).toHaveBeenCalled();
      expect(String(fetchMock.mock.calls[0][0])).toContain('/logout');
      expect(getToken()).toBeNull();
    });
  });

  it('hides the create nav link for admins', () => {
    setSession('tok', {
      id: 2,
      name: 'Admin',
      email: 'admin@example.com',
      role: 'admin',
    });

    render(
      <MemoryRouter initialEntries={['/investments']}>
        <AppShell>
          <div>Content</div>
        </AppShell>
      </MemoryRouter>,
    );

    expect(screen.getByRole('link', { name: /list/i })).toBeInTheDocument();
    expect(screen.queryByRole('link', { name: /^new$/i })).not.toBeInTheDocument();
  });
});
