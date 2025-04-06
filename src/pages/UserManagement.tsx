
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
import { Search, Shield, Edit, DollarSign, Globe, Trash, Plus } from "lucide-react";
import { User } from "@/types";
import { useToast } from "@/hooks/use-toast";

// Updated mock users data with domain fields
const mockUsers: User[] = [
  {
    id: "1",
    name: "Admin User",
    email: "admin@example.com",
    document: "123.456.789-10",
    phone: "(11) 99999-9999",
    domain: null,
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
    domain: "example.com",
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
    domain: "joaosilva.com.br",
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
    domain: null,
    balance: 125,
    isAdmin: false,
    createdAt: new Date(Date.now() - 3600000 * 24).toISOString(),
  },
];

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

  // Load mock data
  useEffect(() => {
    setUsers(mockUsers);
  }, []);

  // Redirect if not admin
  useEffect(() => {
    if (!user || !user.isAdmin) {
      navigate("/dashboard");
    }
  }, [user, navigate]);

  if (!user || !user.isAdmin) {
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

  const handleSaveChanges = () => {
    if (!selectedUser) return;

    const updatedUsers = users.map(u => 
      u.id === selectedUser.id ? 
        { 
          ...u, 
          domain: domainInput || null,
          balance: parseFloat(creditInput) || 0
        } : u
    );
    
    setUsers(updatedUsers);
    setIsDialogOpen(false);
    
    toast({
      title: "Alterações salvas",
      description: `Dados do usuário ${selectedUser.name} atualizados com sucesso`,
    });
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
                          R$ {user.balance.toFixed(2)}
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
