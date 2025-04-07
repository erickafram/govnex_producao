import React, { createContext, useState, useContext, useEffect } from "react";
import { useToast } from "@/components/ui/use-toast";
import { User } from "@/types";
import { getApiUrl } from "@/config";

// Definindo as interfaces necessárias localmente
interface RegisterData {
  name: string;
  email: string;
  document: string;
  phone: string;
  domain?: string;
  password: string;
}

interface AuthContextType {
  user: User | null;
  isLoading: boolean;
  login: (email: string, password: string) => Promise<any>;
  register: (userData: RegisterData) => Promise<void>;
  logout: () => void;
  updateUser: (updatedUser: User) => void;
}

// API URL - Using empty base URL for proxy
const API_URL = "";

// API functions
const apiLogin = async (email: string, password: string): Promise<User> => {
  const response = await fetch(`${API_URL}/api/direct-login.php`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ email, password }),
  });

  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.error || "Falha ao fazer login");
  }

  return data.user;
};

const apiRegister = async (userData: RegisterData): Promise<User> => {
  const response = await fetch(`${API_URL}/api/register.php`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(userData),
  });

  const data = await response.json();

  if (!response.ok) {
    throw new Error(data.error || "Falha ao criar conta");
  }

  return data.user;
};

// Default state
const defaultAuthContext: AuthContextType = {
  user: null,
  isLoading: true,
  login: async () => { },
  register: async () => { },
  logout: () => { },
  updateUser: () => { },
};

// Create context
const AuthContext = createContext<AuthContextType>(defaultAuthContext);

// Provider component
export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const { toast } = useToast();

  // Check if user is already logged in
  useEffect(() => {
    const checkAuth = async () => {
      try {
        const storedUser = localStorage.getItem("user");
        const storedToken = localStorage.getItem("token");

        // Verificar se existe um usuário e token armazenados
        if (storedUser && storedToken) {
          try {
            // Tentar analisar o usuário armazenado
            const parsedUser = JSON.parse(storedUser);
            
            // Garantir que o usuário tenha todas as propriedades necessárias
            if (parsedUser && parsedUser.id) {
              console.log("Usuário autenticado carregado do localStorage");
              
              // Garantir que a propriedade isAdmin esteja correta
              parsedUser.isAdmin = parsedUser.isAdmin || parsedUser.accessLevel === "administrador";
              
              setUser(parsedUser);
              return; // Sair da função se o usuário foi carregado com sucesso
            }
          } catch (error) {
            console.error("Erro ao carregar usuário do localStorage:", error);
          }
        }
        
        // Se chegou aqui, significa que não há usuário válido ou token
        // Apenas definir o estado como não carregando
        console.log("Nenhum usuário autenticado encontrado");
        setUser(null);
      } catch (error) {
        console.error("Erro ao verificar autenticação:", error);
      } finally {
        setIsLoading(false);
      }
    };

    checkAuth();
  }, []);

  const login = async (email: string, password: string) => {
    setIsLoading(true);
    try {
      // Usar a API real para login
      const apiUrl = getApiUrl('direct-login.php');
      console.log('Tentando login na URL:', apiUrl);
      
      const response = await fetch(apiUrl, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ email, password }),
      });

      console.log('Resposta do servidor:', response.status);
      
      const data = await response.json();
      console.log('Dados recebidos:', data);

      if (!response.ok) {
        throw new Error(data.error || "Falha ao fazer login");
      }
      
      // Mapear os campos do backend para o formato esperado pelo frontend
      const userData = {
        ...data.user,
        // Garantir que as propriedades estejam no formato esperado pelo frontend
        id: data.user.id.toString(),
        name: data.user.nome || data.user.name,
        document: data.user.cpf || data.user.cnpj || data.user.document,
        phone: data.user.telefone || data.user.phone,
        domain: data.user.dominio || data.user.domain,
        dominio: data.user.dominio || data.user.domain,
        balance: parseFloat(data.user.credito || data.user.balance || 0),
        credito: parseFloat(data.user.credito || data.user.balance || 0),
        isAdmin: data.user.isAdmin || data.user.nivel_acesso === "administrador" || data.user.accessLevel === "administrador",
        nivel_acesso: data.user.nivel_acesso || data.user.accessLevel,
        accessLevel: data.user.nivel_acesso || data.user.accessLevel,
        createdAt: data.user.data_cadastro || data.user.createdAt
      };
      
      // Gerar um token simples (em produção, isso viria do backend)
      const token = data.token || `token_${userData.id}_${Date.now()}`;
      userData.token = token;
      
      console.log('Dados do usuário processados:', userData);
      
      setUser(userData);
      
      // Store user data and token in localStorage
      localStorage.setItem("user", JSON.stringify(userData));
      localStorage.setItem("token", token);
      
      toast({
        title: "Login realizado com sucesso",
        description: `Bem-vindo, ${userData.name || userData.email}!`,
      });
      
      return userData;
    } catch (error) {
      const errorMessage = error instanceof Error ? error.message : "Erro desconhecido";
      toast({
        title: "Falha ao fazer login",
        description: errorMessage,
        variant: "destructive",
      });
      throw error;
    } finally {
      setIsLoading(false);
    }
  };

  const register = async (userData: RegisterData) => {
    setIsLoading(true);
    try {
      const newUser = await apiRegister(userData);
      setUser(newUser);
      localStorage.setItem("user", JSON.stringify(newUser));
      
      // Armazenar o token de desenvolvimento para autenticação
      // Em um sistema de produção, o token viria do backend
      localStorage.setItem("token", "dev_token_user_1");
      
      toast({
        title: "Cadastro realizado com sucesso",
        description: `Bem-vindo, ${newUser.name}!`,
      });
    } catch (error) {
      toast({
        title: "Erro no cadastro",
        description: error instanceof Error ? error.message : "Falha ao criar conta",
        variant: "destructive",
      });
      throw error;
    } finally {
      setIsLoading(false);
    }
  };

  const logout = () => {
    setUser(null);
    localStorage.removeItem("user");
    localStorage.removeItem("token");
    toast({
      title: "Logout realizado",
      description: "Você foi desconectado com sucesso",
    });
  };

  const updateUser = (updatedUser: User) => {
    setUser(updatedUser);
    localStorage.setItem("user", JSON.stringify(updatedUser));
  };

  return (
    <AuthContext.Provider value={{ user, isLoading, login, register, logout, updateUser }}>
      {children}
    </AuthContext.Provider>
  );
};

// Custom hook to use auth context
export const useAuth = () => useContext(AuthContext);
