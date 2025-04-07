import React, { createContext, useState, useContext, useEffect } from "react";
import { useToast } from "@/components/ui/use-toast";
import { AuthContextType, User, RegisterData } from "@/types";

// API URL - Using empty base URL for proxy
const API_URL = "";

// API functions
const apiLogin = async (email: string, password: string): Promise<User> => {
  const response = await fetch(`${API_URL}/api/login.php`, {
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
      const storedUser = localStorage.getItem("user");
      const storedToken = localStorage.getItem("token");

      if (storedUser && storedToken) {
        try {
          setUser(JSON.parse(storedUser));
          console.log("Usuário autenticado carregado do localStorage");
        } catch (error) {
          console.error("Erro ao carregar usuário do localStorage:", error);
          localStorage.removeItem("user");
          localStorage.removeItem("token");
        }
      } else {
        // For development, set a default token
        console.log("Definindo token de desenvolvimento");
        localStorage.setItem("token", "dev_token_user_1");
      }
      
      setIsLoading(false);
    };

    checkAuth();
  }, []);

  const login = async (email: string, password: string) => {
    setIsLoading(true);
    try {
      const userData = await apiLogin(email, password);
      setUser(userData);
      
      // Store user data and token in localStorage
      localStorage.setItem("user", JSON.stringify(userData));
      localStorage.setItem("token", userData.token || "dev_token_user_1");
      
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
