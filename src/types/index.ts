export interface User {
  id: string;
  name: string;
  email: string;
  document: string; // CPF or CNPJ
  phone?: string;
  domain?: string;
  balance: number;
  isAdmin: boolean;
  createdAt: string;
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
  login: (email: string, password: string) => Promise<void>;
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
  userName?: string | null; // Para exibir o nome do usuário associado
  createdAt: string;
  expiresAt: string | null;
  is_active: boolean;
  isActive: boolean; // Alias para is_active, para compatibilidade
}

export interface ConsultationLog {
  id: string;
  cnpjConsultado: string;
  dominioOrigem: string;
  dataConsulta: string;
  custo: number;
}

export interface Payment {
  id: string;
  userId: string;
  valor: number;
  status: "pendente" | "pago" | "cancelado";
  codigoTransacao: string;
  dataCriacao: string;
  dataAtualizacao: string;
}
