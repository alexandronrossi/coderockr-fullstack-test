import { describe, expect, it } from 'vitest';
import { canCreateInvestments } from '@/auth/permissions';

describe('canCreateInvestments', () => {
  it('allows owners only', () => {
    expect(
      canCreateInvestments({
        id: 1,
        name: 'Owner',
        email: 'owner@example.com',
        role: 'owner',
      }),
    ).toBe(true);

    expect(
      canCreateInvestments({
        id: 2,
        name: 'Admin',
        email: 'admin@example.com',
        role: 'admin',
      }),
    ).toBe(false);

    expect(canCreateInvestments(null)).toBe(false);
  });
});
