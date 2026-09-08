import { beforeEach, describe, expect, it, vi } from 'vitest';
import { create, list, show, withdraw } from '@/api/investments';

describe('investments api', () => {
  beforeEach(() => {
    sessionStorage.clear();
    sessionStorage.setItem('coderockr.auth.token', 'tok');
    vi.restoreAllMocks();
  });

  it('list calls GET with page and per_page and never sends user_id', async () => {
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(
        JSON.stringify({
          data: [],
          meta: { current_page: 2, per_page: 10, total: 0, last_page: 1 },
        }),
        { status: 200, headers: { 'Content-Type': 'application/json' } },
      ),
    );
    vi.stubGlobal('fetch', fetchMock);

    await list(2, 10);

    expect(fetchMock).toHaveBeenCalledOnce();
    const [url] = fetchMock.mock.calls[0] as [string];
    expect(url).toContain('/investments?');
    expect(url).toContain('page=2');
    expect(url).toContain('per_page=10');
    expect(url).not.toContain('user_id');
  });

  it('create sends only amount and created_on and unwraps data', async () => {
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(
        JSON.stringify({
          data: {
            id: 9,
            owner: { id: 1, name: 'A', email: 'a@b.com' },
            amount: '100.00',
            created_on: '2024-01-01',
            status: 'active',
            withdrawn_on: null,
            expected_balance: '100.00',
            gain: '0.00',
            tax: '0.00',
            net: '100.00',
          },
        }),
        { status: 201, headers: { 'Content-Type': 'application/json' } },
      ),
    );
    vi.stubGlobal('fetch', fetchMock);

    const created = await create({ amount: '100.00', created_on: '2024-01-01' });

    const [, init] = fetchMock.mock.calls[0] as [string, RequestInit];
    const body = JSON.parse(String(init.body)) as Record<string, unknown>;
    expect(Object.keys(body).sort()).toEqual(['amount', 'created_on']);
    expect(body).not.toHaveProperty('user_id');
    expect(body).not.toHaveProperty('expected_balance');
    expect(created.id).toBe(9);
  });

  it('show fetches investment by id and unwraps data', async () => {
    const fetchMock = vi.fn().mockResolvedValue(
      new Response(
        JSON.stringify({
          data: {
            id: 3,
            owner: { id: 1, name: 'A', email: 'a@b.com' },
            amount: '1000.00',
            created_on: '2024-01-01',
            status: 'active',
            withdrawn_on: null,
            expected_balance: '1005.20',
            gain: '5.20',
            tax: '0.00',
            net: '1005.20',
          },
        }),
        { status: 200, headers: { 'Content-Type': 'application/json' } },
      ),
    );
    vi.stubGlobal('fetch', fetchMock);

    const result = await show(3);

    expect(fetchMock.mock.calls[0][0]).toContain('/investments/3');
    expect(result.expected_balance).toBe('1005.20');
    expect(result.gain).toBe('5.20');
  });

  it('withdraw sends only withdrawn_on and unwraps data', async () => {
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

    const result = await withdraw(3, { withdrawn_on: '2024-06-01' });

    const [, init] = fetchMock.mock.calls[0] as [string, RequestInit];
    const body = JSON.parse(String(init.body)) as Record<string, unknown>;
    expect(Object.keys(body)).toEqual(['withdrawn_on']);
    expect(result.status).toBe('withdrawn');
    expect(result.tax).toBe('1.17');
  });
});
