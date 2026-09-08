import type { User } from '@/types/api';

/** UX helper only — API Policy still enforces create for owners. */
export function canCreateInvestments(user: User | null | undefined): boolean {
  return user?.role === 'owner';
}
