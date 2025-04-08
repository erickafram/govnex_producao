import React, { useState, useEffect } from "react";
import { useNavigate, Link } from "react-router-dom";
import { useAuth } from "@/context/AuthContext";
import Layout from "@/components/Layout";
import { User, Transaction } from "@/types";
import { AdminStats, RecentData, PaginationData, RecentConsulta, RecentPayment } from "@/types/admin";
import { API_URL, CONFIG } from "@/config";
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter } from "@/components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { 
  Search, 
  Download, 
  ChevronLeft, 
  ChevronRight, 
  User as UserIcon, 
  Check, 
  X, 
  Users, 
  Key, 
  Settings, 
  DollarSign, 
  BarChart4, 
  FileText,
  Loader2
} from "lucide-react";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Separator } from "@/components/ui/separator";
import { useToast } from "@/hooks/use-toast";

// Admin quick actions

// Admin quick actions
const adminActions = [
  {
    title: "Gerenciar Usuários",
    description: "Administre contas, domínios e créditos",
    icon: Users,
    href: "/gerenciar-usuarios",
    color: "bg-blue-500",
  },
  {
    title: "Gerenciar Tokens",
    description: "Crie e gerencie tokens da API",
    icon: Key,
    href: "/gerenciar-tokens",
    color: "bg-purple-500",
  },
  {
    title: "Relatórios",
    description: "Visualize estatísticas e dados",
    icon: BarChart4,
    href: "#",
    color: "bg-green-500",
  },
  {
    title: "Configurações",
    description: "Opções do sistema e da conta",
    icon: Settings,
    href: "#",
    color: "bg-amber-500",
  },
];

const Admin = () => {
  const { user } = useAuth();
  const navigate = useNavigate();
  const { toast } = useToast();
  
  // State for data
  const [users, setUsers] = useState<User[]>([]);
  const [payments, setPayments] = useState<RecentPayment[]>([]);
  const [consultas, setConsultas] = useState<RecentConsulta[]>([]);
  const [stats, setStats] = useState<AdminStats | null>(null);
  const [recentData, setRecentData] = useState<RecentData | null>(null);
  
  // UI state
  const [searchTerm, setSearchTerm] = useState("");
  const [currentPage, setCurrentPage] = useState(1);
  const [itemsPerPage] = useState(10);
  const [activeTab, setActiveTab] = useState("overview");
  const [isLoading, setIsLoading] = useState(true);
  const [pagination, setPagination] = useState<PaginationData | null>(null);

  // Fetch admin stats
  const fetchAdminStats = async () => {
    try {
      setIsLoading(true);
      const token = localStorage.getItem("token");
      
      if (!token) {
        throw new Error("Token não encontrado");
      }
      
      const response = await fetch("/api/admin_stats.php", {
        headers: {
          "Authorization": `Bearer ${token}`
        }
      });
      
      if (!response.ok) {
        throw new Error("Erro ao buscar estatísticas");
      }
      
      const data = await response.json();
      
      if (data.success) {
        setStats(data.stats);
        setRecentData(data.recentData);
      } else {
        throw new Error(data.error || "Erro ao buscar estatísticas");
      }
    } catch (error) {
      console.error("Erro ao buscar estatísticas:", error);
      toast({
        title: "Erro",
        description: error instanceof Error ? error.message : "Não foi possível carregar as estatísticas",
        variant: "destructive"
      });
    } finally {
      setIsLoading(false);
    }
  };

  // Fetch users data
  const fetchUsers = async (page = 1) => {
    try {
      setIsLoading(true);
      const token = localStorage.getItem("token");
      
      if (!token) {
        throw new Error("Token não encontrado");
      }
      
      const response = await fetch(`/api/admin_users.php?page=${page}&limit=${itemsPerPage}`, {
        headers: {
          "Authorization": `Bearer ${token}`
        }
      });
      
      if (!response.ok) {
        throw new Error("Erro ao buscar usuários");
      }
      
      const data = await response.json();
      
      if (data.success) {
        setUsers(data.users);
        setPagination(data.pagination);
      } else {
        throw new Error(data.error || "Erro ao buscar usuários");
      }
    } catch (error) {
      console.error("Erro ao buscar usuários:", error);
      toast({
        title: "Erro",
        description: error instanceof Error ? error.message : "Não foi possível carregar a lista de usuários",
        variant: "destructive"
      });
    } finally {
      setIsLoading(false);
    }
  };

  // Fetch payments data
  const fetchPayments = async (page = 1) => {
    try {
      setIsLoading(true);
      const token = localStorage.getItem("token");
      
      if (!token) {
        throw new Error("Token não encontrado");
      }
      
      const response = await fetch(`/api/admin_payments.php?page=${page}&limit=${itemsPerPage}`, {
        headers: {
          "Authorization": `Bearer ${token}`
        }
      });
      
      if (!response.ok) {
        throw new Error("Erro ao buscar pagamentos");
      }
      
      const data = await response.json();
      
      if (data.success) {
        setPayments(data.payments);
        setPagination(data.pagination);
      } else {
        throw new Error(data.error || "Erro ao buscar pagamentos");
      }
    } catch (error) {
      console.error("Erro ao buscar pagamentos:", error);
      toast({
        title: "Erro",
        description: error instanceof Error ? error.message : "Não foi possível carregar a lista de pagamentos",
        variant: "destructive"
      });
    } finally {
      setIsLoading(false);
    }
  };

  // Fetch consultas data
  const fetchConsultas = async (page = 1) => {
    try {
      setIsLoading(true);
      const token = localStorage.getItem("token");
      
      if (!token) {
        throw new Error("Token não encontrado");
      }
      
      const response = await fetch(`/api/admin_consultas.php?page=${page}&limit=${itemsPerPage}`, {
        headers: {
          "Authorization": `Bearer ${token}`
        }
      });
      
      if (!response.ok) {
        throw new Error("Erro ao buscar consultas");
      }
      
      const data = await response.json();
      
      if (data.success) {
        setConsultas(data.consultas);
        setPagination(data.pagination);
      } else {
        throw new Error(data.error || "Erro ao buscar consultas");
      }
    } catch (error) {
      console.error("Erro ao buscar consultas:", error);
      toast({
        title: "Erro",
        description: error instanceof Error ? error.message : "Não foi possível carregar a lista de consultas",
        variant: "destructive"
      });
    } finally {
      setIsLoading(false);
    }
  };

  // Load data based on active tab
  useEffect(() => {
    if (user && (user.isAdmin || user.accessLevel === 'administrador' || user.nivel_acesso === 'administrador')) {
      if (activeTab === "overview") {
        fetchAdminStats();
      } else if (activeTab === "users") {
        fetchUsers(currentPage);
      } else if (activeTab === "payments") {
        fetchPayments(currentPage);
      } else if (activeTab === "consultas") {
        fetchConsultas(currentPage);
      }
    }
  }, [user, activeTab, currentPage]);

  // Redirect if not admin
  useEffect(() => {
    if (!user) {
      navigate("/login");
      return;
    }
    
    // Verificar se o usuário é administrador
    const isAdmin = user.isAdmin || user.accessLevel === "administrador" || user.nivel_acesso === "administrador";
    if (!isAdmin) {
      toast({
        title: "Acesso negado",
        description: "Você não tem permissão para acessar esta página",
        variant: "destructive"
      });
      navigate("/dashboard");
    }
  }, [user, navigate, toast]);

  if (!user || !(user.isAdmin || user.accessLevel === "administrador" || user.nivel_acesso === "administrador")) {
    return null; // or a loading spinner
  }

  // Format date
  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString("pt-BR", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
      hour: "2-digit",
      minute: "2-digit",
    });
  };

  // Handle tab change
  const handleTabChange = (value: string) => {
    setActiveTab(value);
    setCurrentPage(1); // Reset to first page when changing tabs
  };

  // Handle page change
  const handlePageChange = (newPage: number) => {
    if (newPage > 0 && newPage <= pagination?.totalPages) {
      setCurrentPage(newPage);
    }
  };

  // Get user name by ID
  const getUserNameById = (userId: string) => {
    const user = users.find(u => u.id === userId);
    return user ? user.name : "Usuário Desconhecido";
  };

  return (
    <Layout>
      <div className="space-y-6">
        <div className="flex justify-between items-center mb-8">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Painel Administrativo</h1>
            <p className="text-muted-foreground">
              Gerencie usuários, transações e dados do sistema
            </p>
          </div>
          <Button variant="outline" className="gap-2">
            <Download className="h-4 w-4" />
            Exportar Dados
          </Button>
        </div>

        {/* Admin Quick Stats */}
        <div className="grid gap-4 md:grid-cols-4 mb-8">
          <Card>
            <CardContent className="p-6">
              <div className="flex flex-col space-y-2">
                <div className="flex items-center justify-between">
                  <h3 className="text-sm font-medium text-muted-foreground">Total de Usuários</h3>
                  <UserIcon className="h-4 w-4 text-blue-500" />
                </div>
                <p className="text-2xl font-bold">{stats?.totalUsers}</p>
                <p className="text-xs text-muted-foreground">
                  {stats?.totalUsersWithDomain} com domínio vinculado
                </p>
              </div>
            </CardContent>
          </Card>
          
          <Card>
            <CardContent className="p-6">
              <div className="flex flex-col space-y-2">
                <div className="flex items-center justify-between">
                  <h3 className="text-sm font-medium text-muted-foreground">Saldo Total</h3>
                  <DollarSign className="h-4 w-4 text-green-500" />
                </div>
                <p className="text-2xl font-bold">R$ {stats?.totalBalance.toFixed(2)}</p>
                <p className="text-xs text-muted-foreground">
                  Créditos disponíveis no sistema
                </p>
              </div>
            </CardContent>
          </Card>
          
          <Card>
            <CardContent className="p-6">
              <div className="flex flex-col space-y-2">
                <div className="flex items-center justify-between">
                  <h3 className="text-sm font-medium text-muted-foreground">Pagamentos</h3>
                  <FileText className="h-4 w-4 text-amber-500" />
                </div>
                <p className="text-2xl font-bold">R$ {stats?.totalPayments.toFixed(2)}</p>
                <p className="text-xs text-muted-foreground">
                  {stats?.totalPaymentsCount} transações concluídas
                </p>
              </div>
            </CardContent>
          </Card>
          
          <Card>
            <CardContent className="p-6">
              <div className="flex flex-col space-y-2">
                <div className="flex items-center justify-between">
                  <h3 className="text-sm font-medium text-muted-foreground">Consultas</h3>
                  <Search className="h-4 w-4 text-purple-500" />
                </div>
                <p className="text-2xl font-bold">R$ {stats?.totalConsultas.toFixed(2)}</p>
                <p className="text-xs text-muted-foreground">
                  {stats?.totalConsultasCount} consultas realizadas
                </p>
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Admin Quick Actions */}
        <div className="grid gap-4 md:grid-cols-4 mb-8">
          {adminActions.map((action, index) => (
            <Card key={index} className="hover:shadow-md transition-all">
              <Link to={action.href}>
                <CardContent className="p-6">
                  <div className="flex items-start space-x-4">
                    <div className={`${action.color} text-white p-3 rounded-lg`}>
                      <action.icon className="h-6 w-6" />
                    </div>
                    <div>
                      <h3 className="font-semibold">{action.title}</h3>
                      <p className="text-sm text-muted-foreground">{action.description}</p>
                    </div>
                  </div>
                </CardContent>
              </Link>
            </Card>
          ))}
        </div>

        <div className="flex mb-4">
          <div className="relative flex-1 max-w-sm">
            <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
            <Input
              placeholder="Buscar..."
              className="pl-8"
              value={searchTerm}
              onChange={(e) => {
                setSearchTerm(e.target.value);
                setCurrentPage(1); // Reset to first page on search
              }}
            />
          </div>
        </div>

        <Tabs defaultValue="overview">
          <TabsList className="grid grid-cols-4 w-full max-w-md mb-4">
            <TabsTrigger value="overview">Visão Geral</TabsTrigger>
            <TabsTrigger value="users">Usuários</TabsTrigger>
            <TabsTrigger value="payments">Pagamentos</TabsTrigger>
            <TabsTrigger value="consultas">Consultas</TabsTrigger>
          </TabsList>
          
          <TabsContent value="overview" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>Visão Geral do Sistema</CardTitle>
                <CardDescription>
                  Estatísticas e dados do sistema
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div className="p-4">
                  <h3 className="font-medium">Estatísticas</h3>
                  <div className="grid gap-4 md:grid-cols-2">
                    <div>
                      <p className="text-sm text-muted-foreground">Total de usuários:</p>
                      <p className="text-2xl font-bold">{stats?.totalUsers}</p>
                    </div>
                    <div>
                      <p className="text-sm text-muted-foreground">Total de pagamentos:</p>
                      <p className="text-2xl font-bold">R$ {stats?.totalPayments.toFixed(2)}</p>
                    </div>
                    <div>
                      <p className="text-sm text-muted-foreground">Total de consultas:</p>
                      <p className="text-2xl font-bold">R$ {stats?.totalConsultas.toFixed(2)}</p>
                    </div>
                    <div>
                      <p className="text-sm text-muted-foreground">Saldo total:</p>
                      <p className="text-2xl font-bold">R$ {stats?.totalBalance.toFixed(2)}</p>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </TabsContent>
          
          <TabsContent value="users" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>Usuários do Sistema</CardTitle>
                <CardDescription>
                  Total de {users.length} usuários cadastrados
                </CardDescription>
              </CardHeader>
              <CardContent>
                <ScrollArea className="h-[400px] rounded-md border">
                  <div className="p-4">
                    {users.length === 0 ? (
                      <div className="text-center py-8 text-muted-foreground">
                        Nenhum usuário encontrado
                      </div>
                    ) : (
                      <div className="space-y-4">
                        {users.map((u) => (
                          <div key={u.id} className="p-4 border rounded-lg">
                            <div className="flex items-start justify-between">
                              <div className="flex items-center gap-3">
                                <div className="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                                  <UserIcon className="h-5 w-5 text-primary" />
                                </div>
                                <div>
                                  <div className="flex items-center gap-2">
                                    <h3 className="font-medium">{u.name}</h3>
                                    {u.isAdmin && (
                                      <Badge variant="outline" className="bg-blue-100 text-blue-800">
                                        Admin
                                      </Badge>
                                    )}
                                  </div>
                                  <p className="text-sm text-muted-foreground">{u.email}</p>
                                </div>
                              </div>
                              <div className="text-right">
                                <p className="font-medium">R$ {u.balance.toFixed(2)}</p>
                                <p className="text-xs text-muted-foreground">
                                  Desde {formatDate(u.createdAt)}
                                </p>
                              </div>
                            </div>
                            <div className="mt-3 pt-3 border-t flex justify-between items-center">
                              <div className="text-sm text-muted-foreground">
                                <p>Documento: {u.document}</p>
                                <p>Telefone: {u.phone}</p>
                                {u.domain && <p>Domínio: {u.domain}</p>}
                              </div>
                              <div className="flex gap-2">
                                <Button 
                                  variant="outline" 
                                  size="sm"
                                  onClick={() => navigate("/gerenciar-usuarios")}
                                >
                                  Editar
                                </Button>
                                <Button variant="outline" size="sm" className="text-red-600 hover:text-red-700">
                                  Bloquear
                                </Button>
                              </div>
                            </div>
                          </div>
                        ))}
                      </div>
                    )}
                  </div>
                </ScrollArea>
              </CardContent>
              <CardFooter className="flex justify-center">
                <Button 
                  variant="outline" 
                  onClick={() => navigate("/gerenciar-usuarios")}
                  className="gap-2"
                >
                  <Users className="h-4 w-4" />
                  Ver Todos os Usuários
                </Button>
              </CardFooter>
            </Card>
          </TabsContent>
          
          <TabsContent value="payments" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>Histórico de Pagamentos</CardTitle>
                <CardDescription>
                  Total de {payments.length} pagamentos registrados
                </CardDescription>
              </CardHeader>
              <CardContent>
                <ScrollArea className="h-[400px] rounded-md border">
                  <div className="p-4">
                    {payments.length === 0 ? (
                      <div className="text-center py-8 text-muted-foreground">
                        Nenhum pagamento encontrado
                      </div>
                    ) : (
                      <div className="space-y-4">
                        {payments.map((payment) => (
                          <div key={payment.id} className="p-4 border rounded-lg">
                            <div className="flex items-start justify-between">
                              <div>
                                <h3 className="font-medium">
                                  {payment.description}
                                </h3>
                                <p className="text-sm text-muted-foreground">
                                  Usuário: {getUserNameById(payment.userId)}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                  ID: {payment.id}
                                </p>
                              </div>
                              <div className="text-right">
                                <p className={`font-semibold ${
                                  payment.type === 'deposit' ? 'text-green-600' : 'text-red-600'
                                }`}>
                                  {payment.type === 'deposit' ? '+' : '-'} 
                                  R$ {payment.amount.toFixed(2)}
                                </p>
                                <Badge className={`
                                  ${payment.status === 'completed' ? 'bg-green-100 text-green-800' : 
                                    payment.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                    'bg-red-100 text-red-800'}
                                `}>
                                  {payment.status === 'completed' ? (
                                    <Check className="h-3 w-3 mr-1" />
                                  ) : payment.status === 'pending' ? (
                                    <svg className="h-3 w-3 mr-1" viewBox="0 0 24 24" fill="none">
                                      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                  ) : (
                                    <X className="h-3 w-3 mr-1" />
                                  )}
                                  <span className="capitalize">
                                    {payment.status === 'completed' ? 'Concluído' : 
                                    payment.status === 'pending' ? 'Pendente' : 
                                    payment.status === 'failed' ? 'Falhou' : 'Expirado'}
                                  </span>
                                </Badge>
                                <p className="text-xs text-muted-foreground mt-1">
                                  {formatDate(payment.createdAt)}
                                </p>
                              </div>
                            </div>
                          </div>
                        ))}
                      </div>
                    )}
                  </div>
                </ScrollArea>
              </CardContent>
            </Card>
          </TabsContent>
          
          <TabsContent value="consultas" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>Histórico de Consultas</CardTitle>
                <CardDescription>
                  Total de {consultas.length} consultas registradas
                </CardDescription>
              </CardHeader>
              <CardContent>
                <ScrollArea className="h-[400px] rounded-md border">
                  <div className="p-4">
                    {consultas.length === 0 ? (
                      <div className="text-center py-8 text-muted-foreground">
                        Nenhuma consulta encontrada
                      </div>
                    ) : (
                      <div className="space-y-2">
                        {consultas.map((consulta) => (
                          <div key={consulta.id} className="p-3 border rounded-lg bg-gray-50">
                            <div className="flex justify-between items-start">
                              <div>
                                <p className="font-medium">
                                  <Badge variant="outline" className="mr-2 capitalize">
                                    {consulta.action}
                                  </Badge>
                                  {consulta.details}
                                </p>
                                <p className="text-sm text-muted-foreground">
                                  Usuário: {getUserNameById(consulta.userId)}
                                </p>
                              </div>
                              <div className="text-right">
                                <p className="text-xs text-muted-foreground">
                                  IP: {consulta.ip}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                  {formatDate(consulta.timestamp)}
                                </p>
                              </div>
                            </div>
                          </div>
                        ))}
                      </div>
                    )}
                  </div>
                </ScrollArea>
              </CardContent>
            </Card>
          </TabsContent>
        </Tabs>
        
        {/* Pagination */}
        {pagination && pagination.totalPages > 1 && (
          <div className="flex items-center justify-center space-x-2">
            <Button 
              variant="outline" 
              size="sm"
              onClick={() => handlePageChange(currentPage - 1)}
              disabled={currentPage === 1}
            >
              <ChevronLeft className="h-4 w-4" />
            </Button>
            <span className="text-sm">
              Página {currentPage} de {pagination.totalPages}
            </span>
            <Button 
              variant="outline" 
              size="sm"
              onClick={() => handlePageChange(currentPage + 1)}
              disabled={currentPage === pagination.totalPages}
            >
              <ChevronRight className="h-4 w-4" />
            </Button>
          </div>
        )}
      </div>
    </Layout>
  );
};

export default Admin;
