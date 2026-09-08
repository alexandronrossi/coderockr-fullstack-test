import { apiRequest } from '@/api/client';
import type {
  CreateInvestmentInput,
  Investment,
  InvestmentPage,
  WithdrawInput,
} from '@/types/api';

export async function list(page = 1, perPage = 15): Promise<InvestmentPage> {
  const params = new URLSearchParams({
    page: String(page),
    per_page: String(perPage),
  });

  return apiRequest<InvestmentPage>(`/investments?${params.toString()}`);
}

export async function create(input: CreateInvestmentInput): Promise<Investment> {
  const body = {
    amount: input.amount,
    created_on: input.created_on,
  };

  return apiRequest<Investment>('/investments', {
    method: 'POST',
    body,
  });
}

export async function show(id: number): Promise<Investment> {
  return apiRequest<Investment>(`/investments/${id}`);
}

export async function withdraw(id: number, input: WithdrawInput): Promise<Investment> {
  const body = {
    withdrawn_on: input.withdrawn_on,
  };

  return apiRequest<Investment>(`/investments/${id}/withdraw`, {
    method: 'POST',
    body,
  });
}
