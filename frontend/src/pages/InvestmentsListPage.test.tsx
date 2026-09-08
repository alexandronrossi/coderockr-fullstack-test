import { MemoryRouter } from 'react-router-dom';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { setSession } from '@/auth/session';
import { InvestmentsListPage } from '@/pages/InvestmentsListPage';

const pagePayload = {
  data: [
    {
      id: 1,
      owner: { id: 2, name: 'Maria', email: 'maria@example.com' },
      amount: '1000.00',
      created_on: '2024-01-15',
      status: 'active',
      withdrawn_on: null,
      expected_balance: '1005.20',
      gain: '5.20',
      tax: '0.00',
      net: '1005.20',
    },
  ],
  meta: { current_page: 1, per_page: 15, total: 16, last_page: 2 },
  summary: { total_balance: '16083.20' },
};

describe('InvestmentsListPage', () => {
  beforeEach(() => {
    vi.restoreAllMocks();
    sessionStorage.clear();
    setSession('tok', {
      id: 1,
      name: 'Owner',
      email: 'owner@example.com',
      role: 'owner',
    });
  });

  it('renders owner, date, amount, expected_balance and status from the API', async () => {
    vi.stubGlobal(
      'fetch',
      vi.fn().mockResolvedValue(
        new Response(JSON.stringify(pagePayload), {
          status: 200,
          headers: { 'Content-Type': 'application/json' },
        }),
      ),
    );

    render(
      <MemoryRouter>
        <InvestmentsListPage />
      </MemoryRouter>,
    );

    expect(await screen.findByText('Maria')).toBeInTheDocument();
    expect(screen.getByText('2024-01-15')).toBeInTheDocument();
    expect(screen.getByText('1000.00')).toBeInTheDocument();
    expect(screen.getByText('1005.20')).toBeInTheDocument();
    expect(screen.getByText('active')).toBeInTheDocument();
    expect(screen.getByText('Total balance')).toBeInTheDocument();
    expect(screen.getByText('16083.20')).toBeInTheDocument();
    expect(screen.getByLabelText(/pagination/i)).toBeInTheDocument();
  });

  it('shows empty state when there are no investments', async () => {
    vi.stubGlobal(
      'fetch',
      vi.fn().mockResolvedValue(
        new Response(
          JSON.stringify({
            data: [],
            meta: { current_page: 1, per_page: 15, total: 0, last_page: 1 },
            summary: { total_balance: '0.00' },
          }),
          { status: 200, headers: { 'Content-Type': 'application/json' } },
        ),
      ),
    );

    render(
      <MemoryRouter>
        <InvestmentsListPage />
      </MemoryRouter>,
    );

    expect(await screen.findByText(/no investments yet/i)).toBeInTheDocument();
    expect(screen.getByText('Total balance')).toBeInTheDocument();
    expect(screen.getByText('0.00')).toBeInTheDocument();
    expect(screen.getByRole('link', { name: /create the first one/i })).toBeInTheDocument();
  });

  it('requests the next page from the API when pagination is used', async () => {
    const user = userEvent.setup();
    const fetchMock = vi
      .fn()
      .mockResolvedValueOnce(
        new Response(JSON.stringify(pagePayload), {
          status: 200,
          headers: { 'Content-Type': 'application/json' },
        }),
      )
      .mockResolvedValueOnce(
        new Response(
          JSON.stringify({
            ...pagePayload,
            meta: { ...pagePayload.meta, current_page: 2 },
          }),
          { status: 200, headers: { 'Content-Type': 'application/json' } },
        ),
      );
    vi.stubGlobal('fetch', fetchMock);

    render(
      <MemoryRouter>
        <InvestmentsListPage />
      </MemoryRouter>,
    );

    await screen.findByText('Maria');
    await user.click(screen.getByRole('button', { name: /^next$/i }));

    await waitFor(() => {
      expect(fetchMock.mock.calls.length).toBeGreaterThanOrEqual(2);
      expect(String(fetchMock.mock.calls[1][0])).toContain('page=2');
    });
  });

  it('hides create actions for admins', async () => {
    setSession('tok', {
      id: 2,
      name: 'Admin',
      email: 'admin@example.com',
      role: 'admin',
    });

    vi.stubGlobal(
      'fetch',
      vi.fn().mockResolvedValue(
        new Response(JSON.stringify(pagePayload), {
          status: 200,
          headers: { 'Content-Type': 'application/json' },
        }),
      ),
    );

    render(
      <MemoryRouter>
        <InvestmentsListPage />
      </MemoryRouter>,
    );

    expect(await screen.findByText('Maria')).toBeInTheDocument();
    expect(screen.queryByRole('link', { name: /new investment/i })).not.toBeInTheDocument();
  });
});
