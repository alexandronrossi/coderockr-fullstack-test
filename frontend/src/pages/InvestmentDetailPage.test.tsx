import { MemoryRouter, Route, Routes } from 'react-router-dom';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { InvestmentDetailPage } from '@/pages/InvestmentDetailPage';

const activeInvestment = {
  id: 3,
  owner: { id: 1, name: 'Ana', email: 'ana@example.com' },
  amount: '1000.00',
  created_on: '2024-01-01',
  status: 'active',
  withdrawn_on: null,
  expected_balance: '1005.20',
  gain: '5.20',
  tax: '0.00',
  net: '1005.20',
};

describe('InvestmentDetailPage', () => {
  beforeEach(() => {
    vi.restoreAllMocks();
  });

  it('renders amount, expected_balance, gain and status from the mock', async () => {
    vi.stubGlobal(
      'fetch',
      vi.fn().mockResolvedValue(
        new Response(JSON.stringify({ data: activeInvestment }), {
          status: 200,
          headers: { 'Content-Type': 'application/json' },
        }),
      ),
    );

    render(
      <MemoryRouter initialEntries={['/investments/3']}>
        <Routes>
          <Route path="/investments/:id" element={<InvestmentDetailPage />} />
        </Routes>
      </MemoryRouter>,
    );

    expect(await screen.findByText('1000.00')).toBeInTheDocument();
    expect(screen.getByText('1005.20')).toBeInTheDocument();
    expect(screen.getByText('5.20')).toBeInTheDocument();
    expect(screen.getByText('active')).toBeInTheDocument();
    expect(screen.getByLabelText(/data do resgate/i)).toBeInTheDocument();
  });

  it('shows not-found on 404 without inventing data', async () => {
    vi.stubGlobal(
      'fetch',
      vi.fn().mockResolvedValue(
        new Response(JSON.stringify({ message: 'Not found' }), {
          status: 404,
          headers: { 'Content-Type': 'application/json' },
        }),
      ),
    );

    render(
      <MemoryRouter initialEntries={['/investments/99']}>
        <Routes>
          <Route path="/investments/:id" element={<InvestmentDetailPage />} />
        </Routes>
      </MemoryRouter>,
    );

    expect(await screen.findByText(/não encontrado/i)).toBeInTheDocument();
    expect(screen.queryByText('1005.20')).not.toBeInTheDocument();
  });

  it('after withdraw shows server tax and net without computing', async () => {
    const user = userEvent.setup();
    const fetchMock = vi
      .fn()
      .mockResolvedValueOnce(
        new Response(JSON.stringify({ data: activeInvestment }), {
          status: 200,
          headers: { 'Content-Type': 'application/json' },
        }),
      )
      .mockResolvedValueOnce(
        new Response(
          JSON.stringify({
            data: {
              ...activeInvestment,
              status: 'withdrawn',
              withdrawn_on: '2024-06-01',
              tax: '1.17',
              net: '1004.03',
            },
          }),
          { status: 200, headers: { 'Content-Type': 'application/json' } },
        ),
      );
    vi.stubGlobal('fetch', fetchMock);

    render(
      <MemoryRouter initialEntries={['/investments/3']}>
        <Routes>
          <Route path="/investments/:id" element={<InvestmentDetailPage />} />
        </Routes>
      </MemoryRouter>,
    );

    await screen.findByLabelText(/data do resgate/i);
    await user.clear(screen.getByLabelText(/data do resgate/i));
    await user.type(screen.getByLabelText(/data do resgate/i), '2024-06-01');
    await user.click(screen.getByRole('button', { name: /confirmar resgate/i }));

    await waitFor(() => {
      expect(screen.getByText('1.17')).toBeInTheDocument();
      expect(screen.getByText('1004.03')).toBeInTheDocument();
      expect(screen.getByText('withdrawn')).toBeInTheDocument();
    });

    const [, withdrawInit] = fetchMock.mock.calls[1] as [string, RequestInit];
    const body = JSON.parse(String(withdrawInit.body)) as Record<string, unknown>;
    expect(Object.keys(body)).toEqual(['withdrawn_on']);
  });
});
