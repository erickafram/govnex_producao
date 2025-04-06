
import React, { createContext, useState, useContext, useEffect } from "react";
import { useToast } from "@/components/ui/use-toast";
import { AuthContextType, User, RegisterData } from "@/types";

// Mock API functions
const mockLogin = async (email: string, password: string): Promise<User> => {
  // Simulate API call delay
  await new Promise((resolve) => setTimeout(resolve, 1000));
  
  // Mock validation
  if (email === "admin@example.com" && password === "password") {
    return {
      id: "1",
      name: "Admin User",
      email: "admin@example.com",
      document: "12345678901234",
      phone: "11999999999",
      domain: null,
      balance: 1000,
      isAdmin: true,
      createdAt: new Date().toISOString(),
    };
  } else if (email === "user@example.com" && password === "password") {
    return {
      id: "2",
      name: "Regular User",
      email: "user@example.com",
      document: "12345678901",
      phone: "11988888888",
      domain: "example.com",
      balance: 250,
      isAdmin: false,
      createdAt: new Date().toISOString(),
    };
  }
  
  throw new Error("Credenciais inválidas");
};

const mockRegister = async (userData: RegisterData): Promise<User> => {
  // Simulate API call delay
  await new Promise((resolve) => setTimeout(resolve, 1000));
  
  // Mock registration
  return {
    id: "3",
    name: userData.name,
    email: userData.email,
    document: userData.document,
    phone: userData.phone,
    domain: userData.domain || null,
    balance: 0,
    isAdmin: false,
    createdAt: new Date().toISOString(),
  };
};

// Default state
const defaultAuthContext: AuthContextType = {
  user: null,
  isLoading: true,
  login: async () => {},
  register: async () => {},
  logout: () => {},
};

// Create context
const AuthContext = createContext<AuthContextType>(defaultAuthContext);

// Provider component
export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  const [user, setUser] = useState<User | null>(null);
  const [isLoading, setIsLoading] = useState(true);
  const { toast } = useToast();

  useEffect(() => {
    // Check for stored user on app load
    const storedUser = localStorage.getItem("user");
    if (storedUser) {
      try {
        setUser(JSON.parse(storedUser));
      } catch (error) {
        console.error("Failed to parse stored user:", error);
        localStorage.removeItem("user");
      }
    }
    setIsLoading(false);
  }, []);

  const login = async (email: string, password: string) => {
    setIsLoading(true);
    try {
      const userData = await mockLogin(email, password);
      setUser(userData);
      localStorage.setItem("user", JSON.stringify(userData));
      toast({
        title: "Login realizado com sucesso",
        description: `Bem-vindo, ${userData.name}!`,
      });
    } catch (error) {
      toast({
        title: "Erro de autenticação",
        description: error instanceof Error ? error.message : "Falha ao fazer login",
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
      const newUser = await mockRegister(userData);
      setUser(newUser);
      localStorage.setItem("user", JSON.stringify(newUser));
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
    toast({
      title: "Logout realizado",
      description: "Você foi desconectado com sucesso",
    });
  };

  return (
    <AuthContext.Provider value={{ user, isLoading, login, register, logout }}>
      {children}
    </AuthContext.Provider>
  );
};

// Custom hook to use auth context
export const useAuth = () => useContext(AuthContext);
