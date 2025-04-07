import React, { useState, useEffect } from "react";
import { useNavigate, Link } from "react-router-dom";
import { useAuth } from "@/context/AuthContext";
import Layout from "@/components/Layout";
import { Transaction, User } from "@/types";
import { AdminStats, RecentData, PaginationData, RecentConsulta as ConsultationLog, RecentPayment as Payment, RecentUser } from "@/types/admin";

// Interface estendida para o User com as propriedades que precisamos
interface ExtendedUser extends User {
  document: string;
  phone?: string;
  createdAt: string;
  accessLevel?: string;
}
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
    description: "Tokens de API e autenticação",
    icon: Key,
    href: "/gerenciar-tokens",
    color: "bg-purple-500",
  },
  {
    title: "Relatórios",
    description: "Estatísticas e análises",
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

const AdminDashboard = () => {
  const navigate = useNavigate();
  const { user } = useAuth();
  
  // Redirect if not admin
  useEffect(() => {
    if (!user) {
      navigate("/login");
      return;
    }
    
    // Verificar se o usuário é administrador
    const isAdmin = user.isAdmin || user.accessLevel === "administrador";
    if (!isAdmin) {
      // Mostrar mensagem de erro e redirecionar
      console.error("Acesso negado: usuário não é administrador");
      navigate("/dashboard");
    }
  }, [user, navigate]);
  
  // State
  const [activeTab, setActiveTab] = useState("users"); // Alterado de "overview" para "users"
  const [searchQuery, setSearchQuery] = useState("");
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const itemsPerPage = CONFIG.itemsPerPage || 10;
  
  // Estados para os dados da API
  const [stats, setStats] = useState<AdminStats | null>(null);
  const [recentData, setRecentData] = useState<RecentData | null>(null);
  const [users, setUsers] = useState<ExtendedUser[]>([]);
  const [payments, setPayments] = useState<Payment[]>([]);
  const [consultas, setConsultas] = useState<ConsultationLog[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  
  // Função para buscar estatísticas
  const fetchStats = async () => {
    try {
      setIsLoading(true);
      // Obter o token do localStorage
      const token = localStorage.getItem("token");
      console.log("Token usado na requisição:", token);
      
      const response = await fetch(`${API_URL}/admin_stats.php`, {
        headers: {
          "Authorization": `Bearer ${token}`,
        },
      });
      
      console.log("Status da resposta:", response.status);
      
      const data = await response.json();
      if (data.success) {
        setStats(data.stats);
        setRecentData(data.recentData);
      } else {
        console.error("Erro ao buscar estatísticas:", data.error);
      }
    } catch (error) {
      console.error("Erro ao buscar estatísticas:", error);
    } finally {
      setIsLoading(false);
    }
  };

  // Função para buscar usuários
  const fetchUsers = async (page = 1) => {
    try {
      setIsLoading(true);
      // Obter o token do localStorage
      const token = localStorage.getItem("token");
      
      const response = await fetch(`${API_URL}/admin_users.php?page=${page}&limit=${itemsPerPage}`, {
        headers: {
          "Authorization": `Bearer ${token}`,
        },
      });
      
      const data = await response.json();
      if (data.success) {
        setUsers(data.users);
        setTotalPages(data.pagination.totalPages);
      } else {
        console.error("Erro ao buscar usuários:", data.error);
      }
    } catch (error) {
      console.error("Erro ao buscar usuários:", error);
    } finally {
      setIsLoading(false);
    }
  };

  // Função para buscar pagamentos
  const fetchPayments = async (page = 1) => {
    try {
      setIsLoading(true);
      // Obter o token do localStorage
      const token = localStorage.getItem("token");
      
      const response = await fetch(`${API_URL}/admin_payments.php?page=${page}&limit=${itemsPerPage}`, {
        headers: {
          "Authorization": `Bearer ${token}`,
        },
      });
      
      const data = await response.json();
      if (data.success) {
        setPayments(data.payments);
        setTotalPages(data.pagination.totalPages);
      } else {
        console.error("Erro ao buscar pagamentos:", data.error);
      }
    } catch (error) {
      console.error("Erro ao buscar pagamentos:", error);
    } finally {
      setIsLoading(false);
    }
  };

  // Função para buscar consultas
  const fetchConsultas = async (page = 1) => {
    try {
      setIsLoading(true);
      // Obter o token do localStorage
      const token = localStorage.getItem("token");
      
      const response = await fetch(`${API_URL}/admin_consultas.php?page=${page}&limit=${itemsPerPage}`, {
        headers: {
          "Authorization": `Bearer ${token}`,
        },
      });
      
      const data = await response.json();
      if (data.success) {
        setConsultas(data.consultas);
        setTotalPages(data.pagination.totalPages);
      } else {
        console.error("Erro ao buscar consultas:", data.error);
      }
    } catch (error) {
      console.error("Erro ao buscar consultas:", error);
    } finally {
      setIsLoading(false);
    }
  };
  
  // Carregar dados quando o componente for montado
  useEffect(() => {
    if (user && user.isAdmin) {
      fetchStats();
      
      // Carregar dados com base na aba atual
      if (activeTab === "users") {
        fetchUsers(currentPage);
      } else if (activeTab === "transactions") {
        fetchPayments(currentPage);
      } else if (activeTab === "logs") {
        fetchConsultas(currentPage);
      }
    }
  }, [user, activeTab, currentPage]);
  
  // Carregar dados iniciais assim que o componente for montado, independente da aba ativa
  useEffect(() => {
    if (user && user.isAdmin) {
      // Carregar todos os dados necessários para evitar problemas ao trocar de aba
      fetchUsers(1);
      fetchPayments(1);
      fetchConsultas(1);
    }
  }, [user]);
  
  // Filtrar dados com base na pesquisa
  const filteredUsers = users.filter(user => 
    user.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
    user.email.toLowerCase().includes(searchQuery.toLowerCase()) ||
    (user.document && user.document.includes(searchQuery))
  );
  
  const filteredTransactions = payments.filter(payment =>
    payment.transactionCode.toLowerCase().includes(searchQuery.toLowerCase()) ||
    (payment.userName && payment.userName.toLowerCase().includes(searchQuery.toLowerCase()))
  );
  
  const filteredLogs = consultas.filter(consulta =>
    consulta.cnpj.includes(searchQuery) ||
    consulta.domain.toLowerCase().includes(searchQuery.toLowerCase()) ||
    (consulta.userName && consulta.userName.toLowerCase().includes(searchQuery.toLowerCase()))
  );
  
  // Format date
  const formatDate = (dateString: string) => {
    if (!dateString) return "";
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('pt-BR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
    }).format(date);
  };
  
  // Handle tab change
  const handleTabChange = (value: string) => {
    setActiveTab(value);
    setCurrentPage(1);
    
    // Carregar dados com base na aba selecionada
    if (value === "users") {
      fetchUsers(1);
    } else if (value === "transactions") {
      fetchPayments(1);
    } else if (value === "logs") {
      fetchConsultas(1);
    }
  };
  
  // Handle page change
  const handlePageChange = (newPage: number) => {
    setCurrentPage(newPage);
    
    // Carregar dados da página selecionada
    if (activeTab === "users") {
      fetchUsers(newPage);
    } else if (activeTab === "transactions") {
      fetchPayments(newPage);
    } else if (activeTab === "logs") {
      fetchConsultas(newPage);
    }
  };

  return (
    <Layout>
      <div className="container py-6 max-w-6xl">
        <div className="mb-6">
          <h1 className="text-3xl font-bold tracking-tight">Painel Administrativo</h1>
          <p className="text-muted-foreground">
            Gerencie usuários, transações e consultas do sistema.
          </p>
        </div>
        
        {isLoading && activeTab === "overview" ? (
          <div className="flex justify-center items-center py-8">
            <Loader2 className="h-8 w-8 animate-spin text-primary" />
            <span className="ml-2">Carregando estatísticas...</span>
          </div>
        ) : (
          <>
            {/* Stats Overview */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
              <Card>
                <CardHeader className="pb-2">
                  <CardTitle className="text-sm font-medium">Total de Usuários</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">{stats?.totalUsers || 0}</div>
                  <p className="text-xs text-muted-foreground">
                    Usuários registrados no sistema
                  </p>
                </CardContent>
              </Card>
              
              <Card>
                <CardHeader className="pb-2">
                  <CardTitle className="text-sm font-medium">Saldo Disponível</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">
                    R$ {stats?.totalBalance.toFixed(2) || "0.00"}
                  </div>
                  <p className="text-xs text-muted-foreground">
                    Créditos em todas as contas
                  </p>
                </CardContent>
              </Card>
              
              <Card>
                <CardHeader className="pb-2">
                  <CardTitle className="text-sm font-medium">Total de Pagamentos</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">{stats?.totalPayments || 0}</div>
                  <p className="text-xs text-muted-foreground">
                    R$ {stats?.totalPaymentsValue.toFixed(2) || "0.00"} em pagamentos
                  </p>
                </CardContent>
              </Card>
              
              <Card>
                <CardHeader className="pb-2">
                  <CardTitle className="text-sm font-medium">Total de Consultas</CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">{stats?.totalConsultas || 0}</div>
                  <p className="text-xs text-muted-foreground">
                    R$ {stats?.totalConsultasValue.toFixed(2) || "0.00"} em consultas
                  </p>
                </CardContent>
              </Card>
            </div>
          </>
        )}

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
              value={searchQuery}
              onChange={(e) => {
                setSearchQuery(e.target.value);
                setCurrentPage(1); // Reset to first page on search
              }}
            />
          </div>
        </div>

        <Tabs value={activeTab} defaultValue="users" onValueChange={handleTabChange}>
          <TabsList className="grid grid-cols-3 w-full max-w-md mb-4">
            <TabsTrigger value="users">Usuários</TabsTrigger>
            <TabsTrigger value="transactions">Pagamentos</TabsTrigger>
            <TabsTrigger value="logs">Consultas</TabsTrigger>
          </TabsList>
          
          {isLoading && activeTab !== "overview" ? (
            <div className="flex justify-center items-center py-8">
              <Loader2 className="h-8 w-8 animate-spin text-primary" />
              <span className="ml-2">Carregando dados...</span>
            </div>
          ) : (
            <>
              <TabsContent value="users" className="space-y-4">
                <Card>
                  <CardHeader>
                    <CardTitle>Usuários do Sistema</CardTitle>
                    <CardDescription>
                      Total de {filteredUsers.length} usuários cadastrados
                    </CardDescription>
                  </CardHeader>
                  <CardContent>
                    <ScrollArea className="h-[400px] rounded-md border">
                      <div className="p-4">
                        {filteredUsers.length === 0 ? (
                          <div className="text-center py-8 text-muted-foreground">
                            Nenhum usuário encontrado
                          </div>
                        ) : (
                          <div className="space-y-4">
                            {filteredUsers.map((u) => (
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
                                    {u.phone && <p>Telefone: {u.phone}</p>}
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
              
              <TabsContent value="transactions" className="space-y-4">
                <Card>
                  <CardHeader>
                    <CardTitle>Histórico de Pagamentos</CardTitle>
                    <CardDescription>
                      Total de {filteredTransactions.length} pagamentos registrados
                    </CardDescription>
                  </CardHeader>
                  <CardContent>
                    <ScrollArea className="h-[400px] rounded-md border">
                      <div className="p-4">
                        {filteredTransactions.length === 0 ? (
                          <div className="text-center py-8 text-muted-foreground">
                            Nenhum pagamento encontrado
                          </div>
                        ) : (
                          <div className="space-y-4">
                            {filteredTransactions.map((payment) => (
                              <div key={payment.id} className="p-4 border rounded-lg">
                                <div className="flex items-start justify-between">
                                  <div>
                                    <h3 className="font-medium">
                                      Pagamento de R$ {payment.amount.toFixed(2)}
                                    </h3>
                                    <p className="text-sm text-muted-foreground">
                                      Usuário: {payment.userName || "Usuário Desconhecido"}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                      ID: {payment.transactionCode}
                                    </p>
                                  </div>
                                  <div className="text-right">
                                    <p className="font-semibold text-green-600">
                                      + R$ {payment.amount.toFixed(2)}
                                    </p>
                                    <Badge className={`
                                      ${payment.status === 'pago' ? 'bg-green-100 text-green-800' : 
                                        payment.status === 'pendente' ? 'bg-yellow-100 text-yellow-800' : 
                                        'bg-red-100 text-red-800'}
                                    `}>
                                      <span>
                                        {payment.status === 'pago' ? 'Pago' : 
                                        payment.status === 'pendente' ? 'Pendente' : 'Cancelado'}
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
              
              <TabsContent value="logs" className="space-y-4">
                <Card>
                  <CardHeader>
                    <CardTitle>Consultas Realizadas</CardTitle>
                    <CardDescription>
                      Total de {filteredLogs.length} consultas registradas
                    </CardDescription>
                  </CardHeader>
                  <CardContent>
                    <ScrollArea className="h-[400px] rounded-md border">
                      <div className="p-4">
                        {filteredLogs.length === 0 ? (
                          <div className="text-center py-8 text-muted-foreground">
                            Nenhuma consulta encontrada
                          </div>
                        ) : (
                          <div className="space-y-2">
                            {filteredLogs.map((log) => (
                              <div key={log.id} className="p-3 border rounded-lg bg-gray-50">
                                <div className="flex justify-between items-start">
                                  <div>
                                    <p className="font-medium">
                                      <Badge variant="outline" className="mr-2">
                                        CNPJ
                                      </Badge>
                                      {log.cnpj}
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                      Domínio: {log.domain}
                                    </p>
                                    {log.userName && (
                                      <p className="text-sm text-muted-foreground">
                                        Usuário: {log.userName}
                                      </p>
                                    )}
                                  </div>
                                  <div className="text-right">
                                    <p className="font-medium text-red-600">
                                      - R$ {log.cost.toFixed(2)}
                                    </p>
                                    <p className="text-xs text-muted-foreground">
                                      {formatDate(log.date)}
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
            </>
          )}
        </Tabs>
        
        {/* Pagination */}
        {totalPages > 1 && !isLoading && (
          <div className="flex items-center justify-center space-x-2 mt-4">
            <Button 
              variant="outline" 
              size="sm"
              onClick={() => handlePageChange(currentPage - 1)}
              disabled={currentPage === 1}
            >
              <ChevronLeft className="h-4 w-4" />
            </Button>
            <span className="text-sm">
              Página {currentPage} de {totalPages}
            </span>
            <Button 
              variant="outline" 
              size="sm"
              onClick={() => handlePageChange(currentPage + 1)}
              disabled={currentPage === totalPages}
            >
              <ChevronRight className="h-4 w-4" />
            </Button>
          </div>
        )}
      </div>
    </Layout>
  );
};

export default AdminDashboard;
