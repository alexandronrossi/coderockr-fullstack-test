import { MemoryRouter } from 'react-router-dom';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
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
};

describe('InvestmentsListPage', () => {
  beforeEach(() => {
    vi.restoreAllMocks();
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
    expect(screen.getByLabelText(/paginação/i)).toBeInTheDocument();
  });

  it('shows empty state when there are no investments', async () => {
    vi.stubGlobal(
      'fetch',
      vi.fn().mockResolvedValue(
        new Response(
          JSON.stringify({
            data: [],
            meta: { current_page: 1, per_page: 15, total: 0, last_page: 1 },
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

    expect(await screen.findByText(/nenhum investimento ainda/i)).toBeInTheDocument();
    expect(screen.getByRole('link', { name: /criar o primeiro/i })).toBeInTheDocument();
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
    await user.click(screen.getByRole('button', { name: /próxima/i }));

    await waitFor(() => {
      expect(fetchMock.mock.calls.length).toBeGreaterThanOrEqual(2);
      expect(String(fetchMock.mock.calls[1][0])).toContain('page=2');
    });
  });
});
