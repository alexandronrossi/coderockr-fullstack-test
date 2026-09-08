export type UserRole = 'admin' | 'owner';

export interface User {
  id: number;
  name: string;
  email: string;
  role: UserRole;
}

export interface InvestmentOwner {
  id: number;
  name: string;
  email: string;
}

export type InvestmentStatus = 'active' | 'withdrawn';

export interface Investment {
  id: number;
  owner: InvestmentOwner;
  amount: string;
  created_on: string;
  status: InvestmentStatus;
  withdrawn_on: string | null;
  expected_balance: string;
  gain: string;
  tax: string;
  net: string;
  rate?: string;
  complete_months?: number;
}

export interface InvestmentPageMeta {
  current_page: number;
  per_page: number;
  total: number;
  last_page?: number;
}

export interface InvestmentPage {
  data: Investment[];
  meta: InvestmentPageMeta;
}

export interface LoginCredentials {
  email: string;
  password: string;
}

export interface RegisterCredentials {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface LoginResponse {
  token: string;
  user: User;
}

export type AuthResponse = LoginResponse;

export interface CreateInvestmentInput {
  amount: string;
  created_on: string;
}

export interface WithdrawInput {
  withdrawn_on: string;
}
