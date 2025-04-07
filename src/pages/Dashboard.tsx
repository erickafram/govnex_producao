import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "@/context/AuthContext";
import Layout from "@/components/Layout";
import BalanceCard from "@/components/BalanceCard";
import TransactionList from "@/components/TransactionList";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Transaction } from "@/types";
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from "recharts";
import { Building2, Search, CreditCard, ArrowUpRight, History, RefreshCw, Receipt, ArrowDown, ArrowUp } from "lucide-react";
import { Button } from "@/components/ui/button";
import { useToast } from "@/components/ui/use-toast";
import { Badge } from "@/components/ui/badge";
import { Alert, AlertTitle, AlertDescription } from "@/components/ui/alert";
import { AlertTriangle } from "lucide-react";

// API URL
const API_URL = "http://localhost:8000";

// Mock data for the chart
const chartData = [
  { name: "Jan", deposits: 120, withdrawals: 80 },
  { name: "Fev", deposits: 200, withdrawals: 90 },
  { name: "Mar", deposits: 150, withdrawals: 120 },
  { name: "Abr", deposits: 180, withdrawals: 50 },
  { name: "Mai", deposits: 250, withdrawals: 110 },
  { name: "Jun", deposits: 170, withdrawals: 90 },
];

const Dashboard = () => {
  const { user, updateUser } = useAuth();
  const navigate = useNavigate();
  const { toast } = useToast();
  const [transactions, setTransactions] = useState<Transaction[]>([]);
  const [consultasDisponiveis, setConsultasDisponiveis] = useState<number>(0);
  const [isLoading, setIsLoading] = useState<boolean>(true);
  const [shouldRefresh, setShouldRefresh] = useState<boolean>(true);

  useEffect(() => {
    // Fetch user data from API
    const fetchUserData = async () => {
      if (!user || !shouldRefresh) return;

      try {
        setIsLoading(true);

        // Fetch consultas data
        const response = await fetch(`${API_URL}/api/consultas.php?userId=${user.id}`);

        if (!response.ok) {
          // Não deslogar o usuário em caso de erro 401 ou outros erros de HTTP
          // Apenas registra o erro e continua
          console.error(`Erro ao buscar dados: ${response.status}`);
          throw new Error('Falha ao carregar dados de consultas');
        }

        const data = await response.json();

        if (data.success) {
          setConsultasDisponiveis(data.consultasDisponiveis);

          // Atualizar o saldo do usuário no context com o valor atual do backend
          if (data.credito !== undefined && user) {
            // Atualiza no localStorage para persistir a mudança
            const updatedUser = { ...user, balance: data.credito };
            // Usa a função updateUser do contexto
            updateUser(updatedUser);
          }
        }

        // Buscar as transações reais do usuário
        try {
          const token = localStorage.getItem('token') || 'dev_token_user_1';
          const transactionsResponse = await fetch(`${API_URL}/api/mock_transactions.php?limit=5`, {
            headers: {
              'Authorization': `Bearer ${token}`
            }
          });

          if (transactionsResponse.ok) {
            const transactionData = await transactionsResponse.json();
            if (transactionData.success && transactionData.transactions) {
              setTransactions(transactionData.transactions);
              console.log('Transações carregadas com sucesso:', transactionData.transactions.length);
            }
          }
        } catch (transactionError) {
          console.error('Erro ao buscar transações:', transactionError);
          // Em caso de erro, usar transações de exemplo
          setTransactions([
            {
              id: "1",
              userId: user.id,
              type: "deposit",
              amount: 100,
              status: "completed",
              description: "Recarga via PIX",
              createdAt: new Date(Date.now() - 3600000 * 24).toISOString(),
            },
            {
              id: "2",
              userId: user.id,
              type: "withdrawal",
              amount: 25,
              status: "completed",
              description: "Consulta de CNPJ",
              createdAt: new Date(Date.now() - 3600000 * 48).toISOString(),
            }
          ]);
        }

        // Depois de carregar com sucesso, não precisamos mais recarregar
        setShouldRefresh(false);
      } catch (error) {
        console.error('Erro ao carregar dados:', error);
        // Mostrar o erro mas não encerrar a sessão
        toast({
          title: "Erro ao carregar dados",
          description: error instanceof Error ? error.message : "Falha ao carregar dados do usuário",
          variant: "destructive",
        });
      } finally {
        setIsLoading(false);
      }
    };

    fetchUserData();
  }, [user, toast, shouldRefresh]);

  // Redirect to login if not authenticated
  // Modificado para ser menos agressivo e apenas redirecionar quando user é definitivamente null
  useEffect(() => {
    // Só redireciona se não estiver carregando e o usuário for null
    if (!isLoading && !user) {
      navigate("/login");
    }
  }, [user, navigate, isLoading]);

  // Mostrar um spinner de carregamento quando estiver carregando os dados
  if (isLoading) {
    return (
      <Layout>
        <div className="min-h-screen flex flex-col items-center justify-center">
          <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mb-4"></div>
          <p className="text-muted-foreground">Carregando dados...</p>
        </div>
      </Layout>
    );
  }

  // Botão para atualizar os dados
  const handleRefresh = () => {
    // Se já estiver carregando, não faça nada
    if (isLoading) return;

    // Reinicia o estado para forçar uma nova busca de dados
    setIsLoading(true);
    setShouldRefresh(true);

    // Exibe um toast informando que os dados estão sendo atualizados
    toast({
      title: "Atualizando dados",
      description: "Buscando as informações mais recentes...",
    });
  };

  if (!user) {
    return null; // or a loading spinner
  }

  return (
    <Layout>
      <div className="space-y-6">
        <div className="flex justify-between items-center">
          <h1 className="text-3xl font-bold tracking-tight">Dashboard</h1>
          <Button
            variant="outline"
            onClick={handleRefresh}
            disabled={isLoading || shouldRefresh}
            className="flex items-center"
          >
            <RefreshCw className="mr-2 h-4 w-4" />
            {isLoading ? "Atualizando..." : "Atualizar dados"}
          </Button>
        </div>
        <p className="text-muted-foreground">
          Bem-vindo, {user.name}. Aqui está um resumo da sua conta.
        </p>

        {/* Aviso importante sobre aumento do custo da API */}
        <Alert className="my-4 border-amber-500 bg-amber-50 dark:bg-amber-950/20">
          <AlertTriangle className="h-5 w-5 text-amber-600" />
          <AlertTitle className="text-amber-800 dark:text-amber-400 font-bold">Aviso Importante!</AlertTitle>
          <AlertDescription className="text-amber-700 dark:text-amber-300">
            A partir de 03/04/2025 nossa API passará a custar R$ 0,12 por consulta devido ao aumento da base com dados e informações de estabelecimentos vindos da Receita Federal.
          </AlertDescription>
        </Alert>

        <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-4">
          <BalanceCard balance={user.balance} />

          <Card className="card-transition">
            <CardHeader className="pb-2">
              <CardTitle className="text-lg font-medium text-muted-foreground">
                Consultas de CNPJ
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="flex flex-col space-y-3">
                <div className="flex items-baseline">
                  <span className="text-3xl font-bold">{isLoading ? '...' : consultasDisponiveis}</span>
                  <span className="text-sm text-muted-foreground ml-2">restantes</span>
                </div>
                <Button
                  onClick={() => navigate("/consulta-cnpj")}
                  variant="outline"
                  className="w-full"
                  disabled={isLoading || consultasDisponiveis <= 0}
                >
                  <Search className="mr-2 h-4 w-4" />
                  Consultar CNPJ
                </Button>
              </div>
            </CardContent>
          </Card>

          <Card className="card-transition md:col-span-2">
            <CardHeader className="pb-2">
              <CardTitle className="text-lg font-medium text-muted-foreground">
                Ações Rápidas
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div className="grid grid-cols-2 gap-4">
                {!user.isAdmin && (
                  <Button
                    onClick={() => navigate("/recarga")}
                    className="bg-blue-600 text-white hover:bg-blue-700 h-auto py-4"
                  >
                    <div className="flex flex-col items-center text-center">
                      <CreditCard className="h-6 w-6 mb-2" />
                      <span>Adicionar Créditos</span>
                    </div>
                  </Button>
                )}
                <Button
                  onClick={() => navigate("/consulta-cnpj")}
                  variant="outline"
                  className="h-auto py-4"
                >
                  <div className="flex flex-col items-center text-center">
                    <Building2 className="h-6 w-6 mb-2" />
                    <span>Consultar CNPJ</span>
                  </div>
                </Button>
                <Button
                  onClick={() => navigate("/registro-consultas")}
                  variant="outline"
                  className="h-auto py-4"
                >
                  <div className="flex flex-col items-center text-center">
                    <History className="h-6 w-6 mb-2" />
                    <span>Registro de Consultas</span>
                  </div>
                </Button>
                <Button
                  onClick={() => navigate("/transacoes")}
                  variant="outline"
                  className="h-auto py-4"
                >
                  <div className="flex flex-col items-center text-center">
                    <Receipt className="h-6 w-6 mb-2" />
                    <span>Histórico Financeiro</span>
                  </div>
                </Button>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </Layout>
  );
};

export default Dashboard;
