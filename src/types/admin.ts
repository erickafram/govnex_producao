// Interfaces para o painel administrativo

export interface AdminStats {
  totalUsers: number;
  totalBalance: number;
  totalPayments: number;
  totalPaymentsValue: number;
  totalConsultas: number;
  totalConsultasValue: number;
}

export interface RecentUser {
  id: string;
  name: string;
  email: string;
  accessLevel: string;
  balance: number;
  createdAt: string;
}

export interface RecentPayment {
  id: string;
  userId: string;
  userName: string;
  amount: number;
  status: string;
  transactionCode: string;
  createdAt: string;
  updatedAt: string;
}

export interface RecentConsulta {
  id: string;
  cnpj: string;
  domain: string;
  date: string;
  cost: number;
  userId?: string;
  userName?: string;
}

export interface RecentData {
  users: RecentUser[];
  payments: RecentPayment[];
  consultas: RecentConsulta[];
}

export interface PaginationData {
  total: number;
  page: number;
  limit: number;
  totalPages: number;
}
