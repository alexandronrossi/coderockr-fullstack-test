import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { WithdrawForm } from '@/components/WithdrawForm';

describe('WithdrawForm', () => {
  beforeEach(() => {
    vi.restoreAllMocks();
  });

  it('submits only withdrawn_on and has no tax field', async () => {
    const user = userEvent.setup();
    const onSuccess = vi.fn();
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(
        JSON.stringify({
          data: {
            id: 3,
            owner: { id: 1, name: 'A', email: 'a@b.com' },
            amount: '1000.00',
            created_on: '2024-01-01',
            status: 'withdrawn',
            withdrawn_on: '2024-06-01',
            expected_balance: '1005.20',
            gain: '5.20',
            tax: '1.17',
            net: '1004.03',
          },
        }),
        { status: 200, headers: { 'Content-Type': 'application/json' } },
      ),
    );
    vi.stubGlobal('fetch', fetchMock);
    sessionStorage.setItem('coderockr.auth.token', 'tok');

    render(
      <WithdrawForm investmentId={3} createdOn="2024-01-01" onSuccess={onSuccess} />,
    );

    expect(document.querySelector('input[name="tax"]')).toBeNull();

    await user.clear(screen.getByLabelText(/withdrawal date/i));
    await user.type(screen.getByLabelText(/withdrawal date/i), '2024-06-01');
    await user.click(screen.getByRole('button', { name: /confirm withdrawal/i }));

    await vi.waitFor(() => expect(onSuccess).toHaveBeenCalled());
    const [, init] = fetchMock.mock.calls[0] as [string, RequestInit];
    const body = JSON.parse(String(init.body)) as Record<string, unknown>;
    expect(Object.keys(body)).toEqual(['withdrawn_on']);
  });
});
