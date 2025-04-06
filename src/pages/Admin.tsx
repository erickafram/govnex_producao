
import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "@/context/AuthContext";
import Layout from "@/components/Layout";
import { Transaction, User } from "@/types";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/components/ui/tabs";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Search, Download, ChevronLeft, ChevronRight, User as UserIcon, Check, X } from "lucide-react";
import { ScrollArea } from "@/components/ui/scroll-area";
import { Separator } from "@/components/ui/separator";

// Mock users data
const mockUsers: User[] = [
  {
    id: "1",
    name: "Admin User",
    email: "admin@example.com",
    document: "123.456.789-10",
    phone: "(11) 99999-9999",
    balance: 1000,
    isAdmin: true,
    createdAt: new Date(Date.now() - 3600000 * 24 * 10).toISOString(),
  },
  {
    id: "2",
    name: "Regular User",
    email: "user@example.com",
    document: "987.654.321-00",
    phone: "(11) 88888-8888",
    balance: 250,
    isAdmin: false,
    createdAt: new Date(Date.now() - 3600000 * 24 * 5).toISOString(),
  },
  {
    id: "3",
    name: "João Silva",
    email: "joao@example.com",
    document: "111.222.333-44",
    phone: "(21) 99999-9999",
    balance: 50,
    isAdmin: false,
    createdAt: new Date(Date.now() - 3600000 * 24 * 2).toISOString(),
  },
  {
    id: "4",
    name: "Maria Oliveira",
    email: "maria@example.com",
    document: "444.555.666-77",
    phone: "(31) 99999-9999",
    balance: 125,
    isAdmin: false,
    createdAt: new Date(Date.now() - 3600000 * 24).toISOString(),
  },
];

// Mock transactions data
const mockTransactions: Transaction[] = [
  {
    id: "1",
    userId: "2",
    type: "deposit",
    amount: 100,
    status: "completed",
    description: "Recarga via PIX",
    createdAt: new Date(Date.now() - 3600000 * 24).toISOString(),
  },
  {
    id: "2",
    userId: "2",
    type: "withdrawal",
    amount: 25,
    status: "completed",
    description: "Consulta de CNPJ",
    createdAt: new Date(Date.now() - 3600000 * 48).toISOString(),
  },
  {
    id: "3",
    userId: "3",
    type: "deposit",
    amount: 50,
    status: "completed",
    description: "Recarga via PIX",
    createdAt: new Date(Date.now() - 3600000 * 72).toISOString(),
  },
  {
    id: "4",
    userId: "4",
    type: "deposit",
    amount: 200,
    status: "completed",
    description: "Recarga via PIX",
    createdAt: new Date(Date.now() - 3600000 * 96).toISOString(),
  },
  {
    id: "5",
    userId: "4",
    type: "withdrawal",
    amount: 75,
    status: "completed",
    description: "Consulta de CNPJ",
    createdAt: new Date(Date.now() - 3600000 * 120).toISOString(),
  },
  {
    id: "6",
    userId: "3",
    type: "deposit",
    amount: 100,
    status: "pending",
    description: "Recarga via PIX",
    createdAt: new Date().toISOString(),
  },
];

// Mock logs data
const mockLogs = [
  {
    id: "1",
    userId: "2",
    action: "login",
    details: "Login bem-sucedido",
    timestamp: new Date(Date.now() - 3600000).toISOString(),
    ip: "192.168.1.1",
  },
  {
    id: "2",
    userId: "3",
    action: "cnpj_query",
    details: "Consulta CNPJ: 12.345.678/0001-90",
    timestamp: new Date(Date.now() - 3600000 * 2).toISOString(),
    ip: "192.168.1.2",
  },
  {
    id: "3",
    userId: "4",
    action: "payment",
    details: "Pagamento de R$ 200,00 recebido",
    timestamp: new Date(Date.now() - 3600000 * 3).toISOString(),
    ip: "192.168.1.3",
  },
  {
    id: "4",
    userId: "2",
    action: "cnpj_query",
    details: "Consulta CNPJ: 98.765.432/0001-21",
    timestamp: new Date(Date.now() - 3600000 * 4).toISOString(),
    ip: "192.168.1.1",
  },
  {
    id: "5",
    userId: "1",
    action: "admin",
    details: "Acesso ao painel de administração",
    timestamp: new Date(Date.now() - 3600000 * 5).toISOString(),
    ip: "192.168.1.4",
  },
];

const Admin = () => {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [users, setUsers] = useState<User[]>([]);
  const [transactions, setTransactions] = useState<Transaction[]>([]);
  const [logs, setLogs] = useState<any[]>([]);
  const [searchTerm, setSearchTerm] = useState("");
  const [currentPage, setCurrentPage] = useState(1);
  const [itemsPerPage, setItemsPerPage] = useState(10);

  // Load mock data
  useEffect(() => {
    setUsers(mockUsers);
    setTransactions(mockTransactions);
    setLogs(mockLogs);
  }, []);

  // Redirect if not admin
  useEffect(() => {
    if (!user || !user.isAdmin) {
      navigate("/dashboard");
    }
  }, [user, navigate]);

  if (!user || !user.isAdmin) {
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

  // Filter users based on search term
  const filteredUsers = users.filter(user => 
    user.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    user.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
    user.document.includes(searchTerm)
  );

  // Filter transactions based on search term
  const filteredTransactions = transactions.filter(transaction => 
    transaction.description.toLowerCase().includes(searchTerm.toLowerCase()) ||
    transaction.id.includes(searchTerm) ||
    transaction.userId.includes(searchTerm)
  );

  // Filter logs based on search term
  const filteredLogs = logs.filter(log => 
    log.action.toLowerCase().includes(searchTerm.toLowerCase()) ||
    log.details.toLowerCase().includes(searchTerm.toLowerCase()) ||
    log.userId.includes(searchTerm) ||
    log.ip.includes(searchTerm)
  );

  // Pagination
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  const paginatedUsers = filteredUsers.slice(startIndex, endIndex);
  const paginatedTransactions = filteredTransactions.slice(startIndex, endIndex);
  const paginatedLogs = filteredLogs.slice(startIndex, endIndex);
  
  const totalPages = Math.ceil(Math.max(
    filteredUsers.length, 
    filteredTransactions.length, 
    filteredLogs.length
  ) / itemsPerPage);

  const handlePageChange = (newPage: number) => {
    if (newPage > 0 && newPage <= totalPages) {
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
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Painel Administrativo</h1>
            <p className="text-muted-foreground">
              Gerencie usuários, transações e logs do sistema
            </p>
          </div>
          <Button variant="outline" className="gap-2">
            <Download className="h-4 w-4" />
            Exportar Dados
          </Button>
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

        <Tabs defaultValue="users">
          <TabsList className="grid grid-cols-3 w-full max-w-md mb-4">
            <TabsTrigger value="users">Usuários</TabsTrigger>
            <TabsTrigger value="transactions">Transações</TabsTrigger>
            <TabsTrigger value="logs">Logs</TabsTrigger>
          </TabsList>
          
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
                    {paginatedUsers.length === 0 ? (
                      <div className="text-center py-8 text-muted-foreground">
                        Nenhum usuário encontrado
                      </div>
                    ) : (
                      <div className="space-y-4">
                        {paginatedUsers.map((u) => (
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
                              </div>
                              <div className="flex gap-2">
                                <Button variant="outline" size="sm">
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
            </Card>
          </TabsContent>
          
          <TabsContent value="transactions" className="space-y-4">
            <Card>
              <CardHeader>
                <CardTitle>Histórico de Transações</CardTitle>
                <CardDescription>
                  Total de {filteredTransactions.length} transações registradas
                </CardDescription>
              </CardHeader>
              <CardContent>
                <ScrollArea className="h-[400px] rounded-md border">
                  <div className="p-4">
                    {paginatedTransactions.length === 0 ? (
                      <div className="text-center py-8 text-muted-foreground">
                        Nenhuma transação encontrada
                      </div>
                    ) : (
                      <div className="space-y-4">
                        {paginatedTransactions.map((transaction) => (
                          <div key={transaction.id} className="p-4 border rounded-lg">
                            <div className="flex items-start justify-between">
                              <div>
                                <h3 className="font-medium">
                                  {transaction.description}
                                </h3>
                                <p className="text-sm text-muted-foreground">
                                  Usuário: {getUserNameById(transaction.userId)}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                  ID: {transaction.id}
                                </p>
                              </div>
                              <div className="text-right">
                                <p className={`font-semibold ${
                                  transaction.type === 'deposit' ? 'text-green-600' : 'text-red-600'
                                }`}>
                                  {transaction.type === 'deposit' ? '+' : '-'} 
                                  R$ {transaction.amount.toFixed(2)}
                                </p>
                                <Badge className={`
                                  ${transaction.status === 'completed' ? 'bg-green-100 text-green-800' : 
                                    transaction.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                    'bg-red-100 text-red-800'}
                                `}>
                                  {transaction.status === 'completed' ? (
                                    <Check className="h-3 w-3 mr-1" />
                                  ) : transaction.status === 'pending' ? (
                                    <svg className="h-3 w-3 mr-1" viewBox="0 0 24 24" fill="none">
                                      <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"></circle>
                                      <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                  ) : (
                                    <X className="h-3 w-3 mr-1" />
                                  )}
                                  <span className="capitalize">
                                    {transaction.status === 'completed' ? 'Concluído' : 
                                    transaction.status === 'pending' ? 'Pendente' : 
                                    transaction.status === 'failed' ? 'Falhou' : 'Expirado'}
                                  </span>
                                </Badge>
                                <p className="text-xs text-muted-foreground mt-1">
                                  {formatDate(transaction.createdAt)}
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
                <CardTitle>Logs do Sistema</CardTitle>
                <CardDescription>
                  Total de {filteredLogs.length} registros de atividade
                </CardDescription>
              </CardHeader>
              <CardContent>
                <ScrollArea className="h-[400px] rounded-md border">
                  <div className="p-4">
                    {paginatedLogs.length === 0 ? (
                      <div className="text-center py-8 text-muted-foreground">
                        Nenhum log encontrado
                      </div>
                    ) : (
                      <div className="space-y-2">
                        {paginatedLogs.map((log) => (
                          <div key={log.id} className="p-3 border rounded-lg bg-gray-50">
                            <div className="flex justify-between items-start">
                              <div>
                                <p className="font-medium">
                                  <Badge variant="outline" className="mr-2 capitalize">
                                    {log.action}
                                  </Badge>
                                  {log.details}
                                </p>
                                <p className="text-sm text-muted-foreground">
                                  Usuário: {getUserNameById(log.userId)}
                                </p>
                              </div>
                              <div className="text-right">
                                <p className="text-xs text-muted-foreground">
                                  IP: {log.ip}
                                </p>
                                <p className="text-xs text-muted-foreground">
                                  {formatDate(log.timestamp)}
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
        {totalPages > 1 && (
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

export default Admin;
