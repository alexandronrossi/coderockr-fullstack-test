import { describe, expect, it, beforeEach } from 'vitest';
import {
  clearSession,
  getToken,
  getUser,
  setSession,
} from '@/auth/session';
import type { User } from '@/types/api';

const user: User = {
  id: 1,
  name: 'Owner',
  email: 'owner@example.com',
  role: 'owner',
};

describe('session', () => {
  beforeEach(() => {
    sessionStorage.clear();
  });

  it('stores and reads token and user from sessionStorage', () => {
    setSession('tok-123', user);

    expect(getToken()).toBe('tok-123');
    expect(getUser()).toEqual(user);
  });

  it('clears token and user', () => {
    setSession('tok-123', user);
    clearSession();

    expect(getToken()).toBeNull();
    expect(getUser()).toBeNull();
  });
});
