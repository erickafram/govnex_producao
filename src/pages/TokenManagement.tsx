import React, { useState, useEffect, useRef } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "@/context/AuthContext";
import Layout from "@/components/Layout";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
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
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
  DialogClose
} from "@/components/ui/dialog";
import {
  Shield,
  Plus,
  Copy,
  CheckCircle,
  XCircle,
  RefreshCw,
  Trash2,
  Calendar,
  User,
  Search
} from "lucide-react";
import { ApiToken, User as UserType } from "@/types";
import { useToast } from "@/components/ui/use-toast";
import { format, parseISO, isValid, addMonths } from "date-fns";
import { ptBR } from "date-fns/locale";

// API URL
const API_URL = "http://localhost:8000";

const TokenManagement = () => {
  const { user } = useAuth();
  const navigate = useNavigate();
  const { toast } = useToast();
  const [tokens, setTokens] = useState<ApiToken[]>([]);
  const [users, setUsers] = useState<UserType[]>([]);
  const [isLoading, setIsLoading] = useState(true);
  const [isLoadingUsers, setIsLoadingUsers] = useState(true);
  const [isGenerating, setIsGenerating] = useState(false);
  const [searchTerm, setSearchTerm] = useState("");
  const [showNewTokenDialog, setShowNewTokenDialog] = useState(false);
  const [newTokenData, setNewTokenData] = useState({
    description: "",
    userId: "",
    expiresAt: ""
  });
  const [newGeneratedToken, setNewGeneratedToken] = useState("");
  const dialogCloseRef = useRef<HTMLButtonElement>(null);

  // Carregar tokens e usuários do backend
  useEffect(() => {
    if (!user || !user.isAdmin) {
      navigate("/dashboard");
      return;
    }

    // Buscar lista de usuários
    const fetchUsers = async () => {
      setIsLoadingUsers(true);
      try {
        const response = await fetch(`/api/get_users_list.php`);
        if (!response.ok) {
          throw new Error(`Falha ao carregar usuários: ${response.status}`);
        }

        const data = await response.json();
        if (data.success) {
          setUsers(data.users || []);
        } else {
          throw new Error(data.error || 'Erro ao buscar usuários');
        }
      } catch (error) {
        console.error('Erro ao carregar usuários:', error);
        toast({
          title: "Erro ao carregar usuários",
          description: error instanceof Error ? error.message : "Falha ao carregar dados de usuários",
          variant: "destructive",
        });
      } finally {
        setIsLoadingUsers(false);
      }
    };

    // Buscar tokens
    const fetchTokens = async () => {
      setIsLoading(true);
      try {
        console.log('Iniciando requisição para API de tokens');
        const response = await fetch(`${API_URL}/api/tokens.php`);
        console.log('Resposta recebida:', response.status);

        if (!response.ok) {
          const errorText = await response.text();
          console.error('Texto de erro da API:', errorText);
          throw new Error(`Falha ao carregar tokens: ${response.status} - ${errorText}`);
        }

        const data = await response.json();
        console.log('Dados recebidos:', data);

        if (data.success) {
          // Garantir que is_active seja interpretado como boolean
          const processedTokens = data.tokens.map((token: any) => ({
            ...token,
            is_active: token.is_active === true, // Força conversão para boolean
            isActive: token.is_active === true // Adiciona isActive como alias para compatibilidade
          }));

          console.log('Tokens processados:', processedTokens);
          setTokens(processedTokens || []);
        } else {
          console.error('Erro nos dados retornados:', data);
          throw new Error(data.error || 'Erro ao buscar tokens');
        }
      } catch (error) {
        console.error('Erro detalhado ao carregar tokens:', error);
        toast({
          title: "Erro ao carregar tokens",
          description: `${error instanceof Error ? error.message : "Falha ao carregar dados"}. Usando dados de teste.`,
          variant: "destructive",
        });

        // Tentar usar o endpoint simplificado como fallback
        try {
          const fallbackResponse = await fetch(`${API_URL}/api/generate-token.php`);
          if (fallbackResponse.ok) {
            const fallbackData = await fallbackResponse.json();

            // Criar um token no formato que o componente espera
            const generatedTokens: ApiToken[] = [{
              id: "1",
              token: fallbackData.token,
              description: "Token gerado (fallback)",
              userId: null,
              userName: null,
              createdAt: new Date().toISOString(),
              expiresAt: null,
              is_active: true,
              isActive: true,
            }];

            setTokens(generatedTokens);
            setIsLoading(false);

            toast({
              title: "Tokens carregados",
              description: "Usando método alternativo para exibir tokens",
            });

            return;
          }
        } catch (fallbackError) {
          console.error('Erro ao usar método alternativo:', fallbackError);
        }

        // Se ambos falharem, use os dados de exemplo
        const sampleTokens: ApiToken[] = [
          {
            id: "1",
            token: "8ab984d986b155d84b4f88dec6d4f8c3cd2e11c685d9805107df78e94ab488ca",
            description: "Token de teste (amostra)",
            userId: null,
            userName: null,
            createdAt: new Date().toISOString(),
            expiresAt: null,
            is_active: true,
            isActive: true,
          }
        ];

        setTokens(sampleTokens);
      } finally {
        setIsLoading(false);
      }
    };

    fetchTokens();
    fetchUsers();
  }, [user, navigate, toast]);

  if (!user || !user.isAdmin) {
    return null;
  }

  const formatDate = (dateString: string | null) => {
    if (!dateString) return "N/A";

    try {
      const date = parseISO(dateString);
      if (!isValid(date)) return "Data inválida";

      return format(date, "dd/MM/yyyy HH:mm", { locale: ptBR });
    } catch (error) {
      console.error("Erro ao formatar data:", error);
      return "Erro na data";
    }
  };

  const resetNewTokenForm = () => {
    setNewTokenData({
      description: "",
      userId: "",
      expiresAt: ""
    });
    setNewGeneratedToken("");
  };

  const generateToken = async () => {
    setIsGenerating(true);

    try {
      const expiresAt = newTokenData.expiresAt
        ? new Date(newTokenData.expiresAt).toISOString()
        : null;

      const response = await fetch(`${API_URL}/api/tokens.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          description: newTokenData.description || null,
          userId: newTokenData.userId || null,
          expiresAt: expiresAt
        }),
      });

      if (!response.ok) {
        const errorText = await response.text();
        throw new Error(`Falha ao gerar token: ${errorText}`);
      }

      const data = await response.json();

      if (data.success) {
        // Mostrar o token gerado ao usuário
        setNewGeneratedToken(data.token.token);

        // Atualizar a lista de tokens
        setTokens([data.token, ...tokens]);

        toast({
          title: "Token gerado com sucesso",
          description: "O novo token está pronto para ser utilizado",
        });
      } else {
        throw new Error(data.error || 'Erro ao gerar token');
      }
    } catch (error) {
      console.error('Erro ao gerar token:', error);
      toast({
        title: "Erro ao gerar token",
        description: error instanceof Error ? error.message : "Falha ao processar a solicitação",
        variant: "destructive",
      });
    } finally {
      setIsGenerating(false);
    }
  };

  const toggleTokenStatus = async (id: string) => {
    try {
      const token = tokens.find(t => t.id === id);
      if (!token) return;

      // Usar is_active se disponível, senão usar isActive
      const currentStatus = token.is_active !== undefined ? token.is_active : token.isActive;
      const newStatus = !currentStatus;

      const response = await fetch(`${API_URL}/api/tokens.php?id=${id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          isActive: newStatus
        }),
      });

      if (!response.ok) {
        throw new Error('Falha ao atualizar status do token');
      }

      const data = await response.json();

      if (data.success) {
        // Atualizar o estado local
        setTokens(tokens.map(token =>
          token.id === id ? { ...token, is_active: newStatus, isActive: newStatus } : token
        ));

        toast({
          title: newStatus ? "Token ativado" : "Token desativado",
          description: newStatus
            ? "O token está ativo e pronto para uso"
            : "O token não poderá mais ser utilizado",
        });
      } else {
        throw new Error(data.error || 'Erro ao atualizar token');
      }
    } catch (error) {
      console.error('Erro ao atualizar token:', error);
      toast({
        title: "Erro ao atualizar token",
        description: error instanceof Error ? error.message : "Falha ao processar a solicitação",
        variant: "destructive",
      });
    }
  };

  const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text).then(
      () => {
        toast({
          title: "Copiado!",
          description: "Token copiado para a área de transferência",
        });
      },
      (err) => {
        console.error("Erro ao copiar: ", err);
        toast({
          title: "Erro ao copiar",
          description: "Não foi possível copiar o token",
          variant: "destructive",
        });
      }
    );
  };

  const handleNewTokenSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    generateToken();
  };

  const closeAndResetDialog = () => {
    if (dialogCloseRef.current) {
      dialogCloseRef.current.click();
    }
    setTimeout(resetNewTokenForm, 300);
  };

  const filteredTokens = tokens.filter(token =>
  (token.token.toLowerCase().includes(searchTerm.toLowerCase()) ||
    (token.description?.toLowerCase() || '').includes(searchTerm.toLowerCase()))
  );

  return (
    <Layout>
      <div className="space-y-6">
        <div className="flex justify-between items-center">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Gerenciamento de Tokens API</h1>
            <p className="text-muted-foreground">
              Crie e gerencie tokens de acesso para a API de consulta CNPJ
            </p>
          </div>

          <Dialog>
            <DialogTrigger asChild>
              <Button className="gap-2" onClick={() => setShowNewTokenDialog(true)}>
                <Plus className="h-4 w-4" />
                Gerar Novo Token
              </Button>
            </DialogTrigger>
            <DialogContent className="sm:max-w-md">
              <DialogHeader>
                <DialogTitle>Gerar Novo Token de API</DialogTitle>
                <DialogDescription>
                  Configure as propriedades do novo token de acesso à API.
                </DialogDescription>
              </DialogHeader>

              {newGeneratedToken ? (
                <div className="space-y-4">
                  <div className="space-y-2">
                    <Label htmlFor="token">Token Gerado</Label>
                    <div className="relative">
                      <Input
                        id="token"
                        value={newGeneratedToken}
                        readOnly
                        className="pr-10 font-mono text-xs"
                      />
                      <Button
                        size="sm"
                        variant="ghost"
                        className="absolute right-0 top-0 h-full px-3"
                        onClick={() => copyToClipboard(newGeneratedToken)}
                      >
                        <Copy className="h-4 w-4" />
                      </Button>
                    </div>
                    <p className="text-sm text-muted-foreground">
                      Importante: Copie este token agora. Por segurança, ele não será exibido novamente.
                    </p>
                  </div>

                  <DialogFooter>
                    <Button
                      type="button"
                      variant="secondary"
                      onClick={closeAndResetDialog}
                    >
                      Fechar
                    </Button>
                  </DialogFooter>
                </div>
              ) : (
                <form onSubmit={handleNewTokenSubmit} className="space-y-4">
                  <div className="space-y-2">
                    <Label htmlFor="description">Descrição (opcional)</Label>
                    <Input
                      id="description"
                      placeholder="Ex: Token para sistema X"
                      value={newTokenData.description}
                      onChange={(e) => setNewTokenData({ ...newTokenData, description: e.target.value })}
                    />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="expiresAt">Data de Expiração (opcional)</Label>
                    <Input
                      id="expiresAt"
                      type="datetime-local"
                      value={newTokenData.expiresAt}
                      onChange={(e) => setNewTokenData({ ...newTokenData, expiresAt: e.target.value })}
                    />
                  </div>

                  <div className="space-y-2">
                    <Label htmlFor="userId">Associar ao Usuário (opcional)</Label>
                    <div className="relative">
                      <select
                        id="userId"
                        className="w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        value={newTokenData.userId}
                        onChange={(e) => setNewTokenData({ ...newTokenData, userId: e.target.value })}
                      >
                        <option value="">Selecione um usuário</option>
                        {users.map((user) => (
                          <option key={user.id} value={user.id}>
                            {user.name} ({user.email})
                          </option>
                        ))}
                      </select>
                      <div className="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                        <User className="h-4 w-4 text-muted-foreground" />
                      </div>
                    </div>
                  </div>

                  <DialogFooter>
                    <DialogClose asChild>
                      <Button type="button" variant="outline" ref={dialogCloseRef}>
                        Cancelar
                      </Button>
                    </DialogClose>
                    <Button type="submit" disabled={isGenerating}>
                      {isGenerating ? (
                        <>
                          <RefreshCw className="h-4 w-4 animate-spin mr-2" />
                          Gerando...
                        </>
                      ) : (
                        "Gerar Token"
                      )}
                    </Button>
                  </DialogFooter>
                </form>
              )}
            </DialogContent>
          </Dialog>
        </div>

        <div className="flex mb-4">
          <Input
            placeholder="Buscar token ou descrição..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="max-w-sm"
          />
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Tokens Disponíveis</CardTitle>
            <CardDescription>
              Total de {filteredTokens.length} tokens gerenciados
            </CardDescription>
          </CardHeader>
          <CardContent>
            {isLoading ? (
              <div className="py-8 flex justify-center">
                <RefreshCw className="h-8 w-8 animate-spin text-muted-foreground" />
              </div>
            ) : filteredTokens.length === 0 ? (
              <div className="text-center py-8 text-muted-foreground">
                Nenhum token encontrado
              </div>
            ) : (
              <Table>
                <TableCaption>Lista de tokens de API</TableCaption>
                <TableHeader>
                  <TableRow>
                    <TableHead>Token</TableHead>
                    <TableHead>Descrição</TableHead>
                    <TableHead>Usuário</TableHead>
                    <TableHead>Criado em</TableHead>
                    <TableHead>Expira em</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead className="text-right">Ações</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {filteredTokens.map((token) => (
                    <TableRow key={token.id}>
                      <TableCell className="font-mono text-xs">
                        {token.token.substring(0, 20)}...
                      </TableCell>
                      <TableCell>
                        {token.description || <span className="text-muted-foreground italic">Sem descrição</span>}
                      </TableCell>
                      <TableCell>
                        {token.userName ? (
                          <div className="flex items-center gap-1">
                            <User className="h-3 w-3" />
                            <span>{token.userName}</span>
                          </div>
                        ) : (
                          <span className="text-muted-foreground italic">Não associado</span>
                        )}
                      </TableCell>
                      <TableCell>{formatDate(token.createdAt)}</TableCell>
                      <TableCell>
                        {token.expiresAt ? (
                          <span className="flex items-center gap-1">
                            <Calendar className="h-3 w-3" />
                            {formatDate(token.expiresAt)}
                          </span>
                        ) : (
                          <span className="text-muted-foreground italic">Sem expiração</span>
                        )}
                      </TableCell>
                      <TableCell>
                        <Badge className={token.isActive || token.is_active ? "bg-green-100 text-green-800" : "bg-red-100 text-red-800"}>
                          {token.isActive || token.is_active ? (
                            <CheckCircle className="h-3 w-3 mr-1" />
                          ) : (
                            <XCircle className="h-3 w-3 mr-1" />
                          )}
                          {token.isActive || token.is_active ? "Ativo" : "Inativo"}
                        </Badge>
                      </TableCell>
                      <TableCell className="text-right">
                        <div className="flex justify-end gap-2">
                          <Button
                            variant="outline"
                            size="sm"
                            onClick={() => copyToClipboard(token.token)}
                          >
                            <Copy className="h-3 w-3 mr-1" />
                            Copiar
                          </Button>
                          <Button
                            variant={token.isActive || token.is_active ? "destructive" : "outline"}
                            size="sm"
                            onClick={() => toggleTokenStatus(token.id)}
                          >
                            {token.isActive || token.is_active ? "Desativar" : "Ativar"}
                          </Button>
                        </div>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            )}
          </CardContent>
        </Card>
      </div>
    </Layout>
  );
};

export default TokenManagement;
