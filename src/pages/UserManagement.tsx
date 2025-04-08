import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "@/context/AuthContext";
import Layout from "@/components/Layout";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { 
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";
import { 
  Table, 
  TableBody, 
  TableCaption, 
  TableCell, 
  TableHead, 
  TableHeader, 
  TableRow 
} from "@/components/ui/table";
import { Badge } from "@/components/ui/badge";
import { Search, Shield, Edit, DollarSign, Globe, Trash, Plus, Loader2 } from "lucide-react";
import { User as BaseUser } from "@/types";
import { useToast } from "@/hooks/use-toast";
import { API_URL } from "@/config";

// Interface estendida para o User com as propriedades que precisamos
interface User extends BaseUser {
  document: string;
  phone?: string;
  createdAt: string;
  accessLevel?: string;
}

// Interface para dados de paginação
interface PaginationData {
  total: number;
  page: number;
  limit: number;
  totalPages: number;
}

const UserManagement = () => {
  const { user } = useAuth();
  const navigate = useNavigate();
  const { toast } = useToast();
  const [users, setUsers] = useState<User[]>([]);
  const [searchTerm, setSearchTerm] = useState("");
  const [selectedUser, setSelectedUser] = useState<User | null>(null);
  const [isDialogOpen, setIsDialogOpen] = useState(false);
  const [domainInput, setDomainInput] = useState("");
  const [creditInput, setcreditInput] = useState("");
  const [isLoading, setIsLoading] = useState(true);
  const [currentPage, setCurrentPage] = useState(1);
  const [itemsPerPage] = useState(10);
  const [pagination, setPagination] = useState<PaginationData | null>(null);

  // Buscar usuários da API
  const fetchUsers = async (page = 1) => {
    setIsLoading(true);
    try {
      const token = localStorage.getItem("token");
      if (!token) {
        throw new Error("Token não encontrado");
      }

      const response = await fetch(`/api/manage_users.php?page=${page}&limit=${itemsPerPage}`, {
        headers: {
          "Authorization": `Bearer ${token}`,
        },
      });

      if (!response.ok) {
        throw new Error("Erro ao buscar usuários");
      }

      const data = await response.json();
      setUsers(data.users);
      setPagination(data.pagination);
    } catch (error) {
      console.error("Erro ao buscar usuários:", error);
      toast({
        title: "Erro",
        description: "Não foi possível carregar a lista de usuários",
        variant: "destructive",
      });
    } finally {
      setIsLoading(false);
    }
  };

  // Carregar usuários ao montar o componente
  useEffect(() => {
    if (user && user.isAdmin) {
      fetchUsers(currentPage);
    }
  }, [user, currentPage]);

  // Redirect if not admin
  useEffect(() => {
    if (!user) {
      navigate("/login");
      return;
    }
    
    // Verificar se o usuário é administrador
    const isAdmin = user.isAdmin || user.accessLevel === "administrador";
    if (!isAdmin) {
      toast({
        title: "Acesso negado",
        description: "Você não tem permissão para acessar esta página",
        variant: "destructive"
      });
      navigate("/dashboard");
    }
  }, [user, navigate, toast]);

  // Se o usuário não estiver logado ou não for administrador, não renderiza nada
  if (!user || !(user.isAdmin || user.accessLevel === "administrador")) {
    return null;
  }

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return date.toLocaleDateString("pt-BR", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    });
  };

  const openEditDialog = (user: User) => {
    setSelectedUser(user);
    setDomainInput(user.domain || "");
    setcreditInput(user.balance.toString());
    setIsDialogOpen(true);
  };

  const handleSaveChanges = async () => {
    if (!selectedUser) return;
    
    try {
      const token = localStorage.getItem("token");
      if (!token) {
        throw new Error("Token não encontrado");
      }

      // Chamar a API para atualizar o usuário
      const response = await fetch(`/api/update_user.php`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Authorization": `Bearer ${token}`,
        },
        body: JSON.stringify({
          userId: selectedUser.id,
          domain: domainInput,
          balance: parseFloat(creditInput.replace(',', '.')) || 0
        }),
      });

      const data = await response.json();
      
      if (!response.ok) {
        throw new Error(data.error || "Erro ao atualizar usuário");
      }
      
      // Atualizar a lista de usuários com os dados atualizados
      if (data.user) {
        // Atualizar o usuário selecionado com os novos valores
        const updatedUser = {
          ...selectedUser,
          domain: domainInput,
          dominio: domainInput,
          balance: parseFloat(creditInput.replace(',', '.')),
          credito: parseFloat(creditInput.replace(',', '.'))
        };
        
        // Atualizar a lista de usuários
        const updatedUsers = users.map(u => 
          u.id === selectedUser.id ? updatedUser : u
        );
        
        // Atualizar os estados
        setUsers(updatedUsers);
        setSelectedUser(updatedUser);
      } else {
        // Recarregar a lista de usuários se não recebemos o usuário atualizado
        fetchUsers(currentPage);
      }
      
      setIsDialogOpen(false);
      
      toast({
        title: "Alterações salvas",
        description: `Dados do usuário ${selectedUser.name} atualizados com sucesso`,
      });
    } catch (error) {
      console.error("Erro ao atualizar usuário:", error);
      toast({
        title: "Erro",
        description: error instanceof Error ? error.message : "Não foi possível atualizar os dados do usuário",
        variant: "destructive",
      });
    }
  };

  const filteredUsers = users.filter(user => 
    user.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
    user.email.toLowerCase().includes(searchTerm.toLowerCase()) ||
    user.document.includes(searchTerm) ||
    (user.domain && user.domain.toLowerCase().includes(searchTerm.toLowerCase()))
  );

  return (
    <Layout>
      <div className="space-y-6">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Gerenciamento de Usuários</h1>
            <p className="text-muted-foreground">
              Administre usuários, domínios e créditos
            </p>
          </div>
        </div>

        <div className="flex mb-4">
          <div className="relative flex-1 max-w-sm">
            <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
            <Input
              placeholder="Buscar usuário..."
              className="pl-8"
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
            />
          </div>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Usuários do Sistema</CardTitle>
            <CardDescription>
              Total de {filteredUsers.length} usuários cadastrados
            </CardDescription>
          </CardHeader>
          <CardContent>
            {filteredUsers.length === 0 ? (
              <div className="text-center py-8 text-muted-foreground">
                Nenhum usuário encontrado
              </div>
            ) : (
              <Table>
                <TableCaption>Lista de usuários do sistema</TableCaption>
                <TableHeader>
                  <TableRow>
                    <TableHead>Nome</TableHead>
                    <TableHead>Documento</TableHead>
                    <TableHead>Domínio</TableHead>
                    <TableHead>Crédito</TableHead>
                    <TableHead>Cadastro</TableHead>
                    <TableHead className="text-right">Ações</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {filteredUsers.map((user) => (
                    <TableRow key={user.id}>
                      <TableCell>
                        <div>
                          <p className="font-medium">{user.name}</p>
                          <p className="text-sm text-muted-foreground">{user.email}</p>
                        </div>
                      </TableCell>
                      <TableCell>{user.document}</TableCell>
                      <TableCell>
                        {user.domain ? (
                          <div className="flex items-center">
                            <Globe className="h-3 w-3 mr-1 text-blue-500" />
                            <span>{user.domain}</span>
                          </div>
                        ) : (
                          <span className="text-muted-foreground">Não definido</span>
                        )}
                      </TableCell>
                      <TableCell>
                        <Badge className="bg-green-100 text-green-800">
                          R$ {typeof user.balance === 'number' ? user.balance.toFixed(2) : '0.00'}
                        </Badge>
                      </TableCell>
                      <TableCell>{formatDate(user.createdAt)}</TableCell>
                      <TableCell className="text-right">
                        <Button 
                          variant="outline" 
                          size="sm"
                          onClick={() => openEditDialog(user)}
                        >
                          <Edit className="h-3 w-3 mr-1" />
                          Editar
                        </Button>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            )}
            
            {pagination && pagination.totalPages > 1 && (
              <div className="flex justify-center mt-4 space-x-2">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => setCurrentPage(prev => Math.max(prev - 1, 1))}
                  disabled={currentPage === 1 || isLoading}
                >
                  Anterior
                </Button>
                <span className="py-2 px-3 text-sm">
                  Página {currentPage} de {pagination.totalPages}
                </span>
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => setCurrentPage(prev => Math.min(prev + 1, pagination.totalPages))}
                  disabled={currentPage === pagination.totalPages || isLoading}
                >
                  Próxima
                </Button>
              </div>
            )}
          </CardContent>
        </Card>

        <Dialog open={isDialogOpen} onOpenChange={setIsDialogOpen}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>Editar Usuário</DialogTitle>
              <DialogDescription>
                Atualize as informações de domínio e crédito do usuário.
              </DialogDescription>
            </DialogHeader>
            
            {selectedUser && (
              <>
                <div className="space-y-4 py-4">
                  <div>
                    <h3 className="font-medium">{selectedUser.name}</h3>
                    <p className="text-sm text-muted-foreground">{selectedUser.email}</p>
                  </div>
                  
                  <div className="space-y-2">
                    <label htmlFor="domain" className="text-sm font-medium">
                      Domínio
                    </label>
                    <div className="flex items-center">
                      <Globe className="h-4 w-4 mr-2 text-muted-foreground" />
                      <Input
                        id="domain"
                        value={domainInput}
                        onChange={(e) => setDomainInput(e.target.value)}
                        placeholder="exemplo.com.br"
                      />
                    </div>
                  </div>
                  
                  <div className="space-y-2">
                    <label htmlFor="credit" className="text-sm font-medium">
                      Crédito (R$)
                    </label>
                    <div className="flex items-center">
                      <DollarSign className="h-4 w-4 mr-2 text-muted-foreground" />
                      <Input
                        id="credit"
                        value={creditInput}
                        onChange={(e) => setcreditInput(e.target.value)}
                        placeholder="0.00"
                        type="number"
                        step="0.01"
                        min="0"
                      />
                    </div>
                  </div>
                </div>
                
                <DialogFooter>
                  <Button variant="outline" onClick={() => setIsDialogOpen(false)}>
                    Cancelar
                  </Button>
                  <Button onClick={handleSaveChanges}>Salvar Alterações</Button>
                </DialogFooter>
              </>
            )}
          </DialogContent>
        </Dialog>
      </div>
    </Layout>
  );
};

export default UserManagement;
