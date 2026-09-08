import { MemoryRouter } from 'react-router-dom';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { setSession } from '@/auth/session';
import { CreateInvestmentPage } from '@/pages/CreateInvestmentPage';

describe('CreateInvestmentPage', () => {
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

  it('has no owner, status or balance inputs', () => {
    render(
      <MemoryRouter>
        <CreateInvestmentPage />
      </MemoryRouter>,
    );

    expect(document.querySelector('input[name="user_id"]')).toBeNull();
    expect(document.querySelector('input[name="status"]')).toBeNull();
    expect(document.querySelector('input[name="expected_balance"]')).toBeNull();
    expect(screen.getByLabelText(/valor/i)).toBeInTheDocument();
    expect(screen.getByLabelText(/data de criação/i)).toBeInTheDocument();
  });

  it('rejects zero amount without calling the API', async () => {
    const user = userEvent.setup();
    const fetchMock = vi.fn();
    vi.stubGlobal('fetch', fetchMock);

    render(
      <MemoryRouter>
        <CreateInvestmentPage />
      </MemoryRouter>,
    );

    await user.type(screen.getByLabelText(/valor/i), '0.00');
    await user.click(screen.getByRole('button', { name: /criar/i }));

    expect(await screen.findByRole('alert')).toHaveTextContent(/maior que 0\.00/i);
    expect(fetchMock).not.toHaveBeenCalled();
  });

  it('submits only amount and created_on', async () => {
    const user = userEvent.setup();
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(
        JSON.stringify({
          data: {
            id: 12,
            owner: { id: 1, name: 'A', email: 'a@b.com' },
            amount: '250.00',
            created_on: '2024-03-01',
            status: 'active',
            withdrawn_on: null,
            expected_balance: '250.00',
            gain: '0.00',
            tax: '0.00',
            net: '250.00',
          },
        }),
        { status: 201, headers: { 'Content-Type': 'application/json' } },
      ),
    );
    vi.stubGlobal('fetch', fetchMock);

    render(
      <MemoryRouter>
        <CreateInvestmentPage />
      </MemoryRouter>,
    );

    await user.clear(screen.getByLabelText(/valor/i));
    await user.type(screen.getByLabelText(/valor/i), '250.00');
    await user.clear(screen.getByLabelText(/data de criação/i));
    await user.type(screen.getByLabelText(/data de criação/i), '2024-03-01');
    await user.click(screen.getByRole('button', { name: /criar/i }));

    await waitFor(() => expect(fetchMock).toHaveBeenCalled());
    const [, init] = fetchMock.mock.calls[0] as [string, RequestInit];
    const body = JSON.parse(String(init.body)) as Record<string, unknown>;
    expect(Object.keys(body).sort()).toEqual(['amount', 'created_on']);
  });
});
