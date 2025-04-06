
import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "@/context/AuthContext";
import Layout from "@/components/Layout";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
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
import { Shield, Plus, Copy, CheckCircle, XCircle, RefreshCw } from "lucide-react";
import { ApiToken } from "@/types";
import { toast } from "@/hooks/use-toast";
import { useToast } from "@/hooks/use-toast";

// Mock data for API tokens
const mockTokens: ApiToken[] = [
  {
    id: "1",
    token: "8ab984d986b155d84b4f88dec6d4f8c3cd2e11c685d9805107df78e94ab488ca",
    createdAt: new Date(Date.now() - 3600000 * 24 * 5).toISOString(),
    isActive: true,
  },
  {
    id: "2",
    token: "6cd984d986b155d84b4f88dec6d4f8c3cd2e11c685d9805107df78e94ab488bb",
    createdAt: new Date(Date.now() - 3600000 * 24 * 10).toISOString(),
    isActive: false,
  },
];

const TokenManagement = () => {
  const { user } = useAuth();
  const navigate = useNavigate();
  const { toast } = useToast();
  const [tokens, setTokens] = useState<ApiToken[]>([]);
  const [isGenerating, setIsGenerating] = useState(false);
  const [searchTerm, setSearchTerm] = useState("");

  // Load mock data
  useEffect(() => {
    setTokens(mockTokens);
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
      hour: "2-digit",
      minute: "2-digit",
    });
  };

  const generateToken = () => {
    setIsGenerating(true);
    
    // Simulate API call
    setTimeout(() => {
      const characters = "abcdef0123456789";
      let token = "";
      for (let i = 0; i < 64; i++) {
        token += characters.charAt(Math.floor(Math.random() * characters.length));
      }
      
      const newToken: ApiToken = {
        id: `${tokens.length + 1}`,
        token,
        createdAt: new Date().toISOString(),
        isActive: true,
      };
      
      setTokens([newToken, ...tokens]);
      setIsGenerating(false);
      
      toast({
        title: "Token gerado com sucesso",
        description: "O novo token está pronto para ser utilizado",
      });
    }, 1000);
  };

  const toggleTokenStatus = (id: string) => {
    setTokens(tokens.map(token => 
      token.id === id ? { ...token, isActive: !token.isActive } : token
    ));
    
    const token = tokens.find(t => t.id === id);
    if (token) {
      toast({
        title: token.isActive ? "Token desativado" : "Token ativado",
        description: token.isActive 
          ? "O token não poderá mais ser utilizado" 
          : "O token está ativo e pronto para uso",
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

  const filteredTokens = tokens.filter(token => 
    token.token.toLowerCase().includes(searchTerm.toLowerCase())
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
          <Button 
            onClick={generateToken} 
            disabled={isGenerating}
            className="gap-2"
          >
            {isGenerating ? (
              <RefreshCw className="h-4 w-4 animate-spin" />
            ) : (
              <Plus className="h-4 w-4" />
            )}
            {isGenerating ? "Gerando..." : "Gerar Novo Token"}
          </Button>
        </div>

        <div className="flex mb-4">
          <Input
            placeholder="Buscar token..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
            className="max-w-sm"
          />
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Tokens Disponíveis</CardTitle>
            <CardDescription>
              Total de {filteredTokens.length} tokens gerados
            </CardDescription>
          </CardHeader>
          <CardContent>
            {filteredTokens.length === 0 ? (
              <div className="text-center py-8 text-muted-foreground">
                Nenhum token encontrado
              </div>
            ) : (
              <Table>
                <TableCaption>Lista de tokens de API</TableCaption>
                <TableHeader>
                  <TableRow>
                    <TableHead>Token</TableHead>
                    <TableHead>Data de Criação</TableHead>
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
                      <TableCell>{formatDate(token.createdAt)}</TableCell>
                      <TableCell>
                        <Badge className={token.isActive ? "bg-green-100 text-green-800" : "bg-red-100 text-red-800"}>
                          {token.isActive ? (
                            <CheckCircle className="h-3 w-3 mr-1" />
                          ) : (
                            <XCircle className="h-3 w-3 mr-1" />
                          )}
                          {token.isActive ? "Ativo" : "Inativo"}
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
                            variant={token.isActive ? "destructive" : "outline"} 
                            size="sm"
                            onClick={() => toggleTokenStatus(token.id)}
                          >
                            {token.isActive ? "Desativar" : "Ativar"}
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
