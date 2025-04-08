export interface User {
  id: string;
  name: string;
  email: string;
  document?: string; // CPF or CNPJ
  cpf?: string;
  cnpj?: string;
  phone?: string;
  telefone?: string;
  domain?: string | null;
  dominio?: string | null;
  balance?: number;
  credito?: number;
  isAdmin?: boolean;
  createdAt?: string;
  data_cadastro?: string;
  accessLevel?: string;
  nivel_acesso?: string;
  token?: string; // Token de autenticação
}

export interface Transaction {
  id: string;
  userId: string;
  type: "deposit" | "withdrawal";
  amount: number;
  status: "pending" | "completed" | "failed";
  description: string;
  createdAt: string;
  updatedAt?: string;
  transactionCode?: string;
  pixCode?: string;
  qrCodeUrl?: string;
}

export interface CNPJData {
  cnpj: string;
  razaoSocial: string;
  nomeFantasia: string;
  situacao: string;
  dataSituacao: string;
  endereco: {
    logradouro: string;
    numero: string;
    complemento: string;
    cep: string;
    bairro: string;
    municipio: string;
    uf: string;
  };
  atividadePrincipal: {
    codigo: string;
    descricao: string;
  };
  capitalSocial: number;
  socios?: {
    nome: string;
    qualificacao: string;
    pais: string;
  }[];
}

export interface AuthContextType {
  user: User | null;
  isLoading: boolean;
  login: (email: string, password: string) => Promise<any>;
  register: (userData: RegisterData) => Promise<void>;
  logout: () => void;
  updateUser: (updatedUser: User) => void;
}

export interface RegisterData {
  name: string;
  email: string;
  document: string;
  phone: string;
  domain?: string;
  password: string;
}

export interface ApiToken {
  id: string;
  token: string;
  description: string | null;
  userId: string | null;
  user_id?: string | null; // Campo original do backend
  userName?: string | null; // Para exibir o nome do usuário associado
  createdAt: string;
  expiresAt: string | null;
  is_active: boolean;
  isActive: boolean; // Alias para is_active, para compatibilidade
}

export interface ConsultationLog {
  id: string;
  cnpj: string;
  domain: string;
  date: string;
  cost: number;
  userId?: string;
  userName?: string;
  document?: string;
  phone?: string;
}

export interface Payment {
  id: string;
  userId: string;
  userName?: string;
  amount: number;
  status: string;
  transactionCode: string;
  createdAt: string;
  updatedAt: string;
}
