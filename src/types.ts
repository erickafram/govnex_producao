// Definição dos tipos usados no aplicativo

export interface User {
    id: string;
    name: string;
    email: string;
    balance: number;
    isAdmin?: boolean;
    domain?: string;
}

export interface Transaction {
    id: string;
    userId: string;
    type: 'deposit' | 'withdrawal';
    amount: number;
    status: 'pending' | 'completed' | 'failed' | 'cancelled' | 'pendente' | 'pago' | 'cancelado';
    description?: string;
    createdAt: string;
    updatedAt?: string;
    transactionCode?: string;
}

export interface ApiResponse<T> {
    success: boolean;
    error?: string;
    data?: T;
} 