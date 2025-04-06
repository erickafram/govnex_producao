
import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "@/context/AuthContext";
import Layout from "@/components/Layout";
import BalanceCard from "@/components/BalanceCard";
import TransactionList from "@/components/TransactionList";
import ConsultaLogs from "@/components/ConsultaLogs";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Transaction } from "@/types";
import { BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer } from "recharts";
import { Building2, Search, CreditCard, ArrowUpRight, History } from "lucide-react";
import { Button } from "@/components/ui/button";

// Mock data for recent transactions
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
    userId: "2",
    type: "deposit",
    amount: 50,
    status: "completed",
    description: "Recarga via PIX",
    createdAt: new Date(Date.now() - 3600000 * 72).toISOString(),
  },
];

// Mock data for consulta logs
const mockConsultaLogs = [
  {
    id: "1",
    cnpj_consultado: "60043704000173",
    dominio_origem: "infovisa.gurupi.to.gov.br",
    data_consulta: new Date(Date.now() - 3600000 * 24).toISOString(),
    custo: 0.05,
  },
  {
    id: "2",
    cnpj_consultado: "47438705000159",
    dominio_origem: "infovisa.gurupi.to.gov.br",
    data_consulta: new Date(Date.now() - 3600000 * 48).toISOString(),
    custo: 0.05,
  },
];

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
  const { user } = useAuth();
  const navigate = useNavigate();
  const [transactions, setTransactions] = useState<Transaction[]>([]);
  const [consultaLogs, setConsultaLogs] = useState<any[]>([]);
  
  useEffect(() => {
    // In a real app, fetch transactions and logs from API
    if (user) {
      setTransactions(mockTransactions);
      setConsultaLogs(mockConsultaLogs);
    }
  }, [user]);

  // Redirect to login if not authenticated
  useEffect(() => {
    if (!user) {
      navigate("/login");
    }
  }, [user, navigate]);

  if (!user) {
    return null; // or a loading spinner
  }

  return (
    <Layout>
      <div className="space-y-6">
        <h1 className="text-3xl font-bold tracking-tight">Dashboard</h1>
        <p className="text-muted-foreground">
          Bem-vindo, {user.name}. Aqui está um resumo da sua conta.
        </p>

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
                  <span className="text-3xl font-bold">5</span>
                  <span className="text-sm text-muted-foreground ml-2">restantes</span>
                </div>
                <Button 
                  onClick={() => navigate("/consulta-cnpj")} 
                  variant="outline" 
                  className="w-full"
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
              </div>
            </CardContent>
          </Card>
        </div>

        {/* Consulta Logs Section */}
        <ConsultaLogs logs={consultaLogs} />

        <div className="grid gap-6 md:grid-cols-2">
          <Card>
            <CardHeader>
              <CardTitle>Histórico Financeiro</CardTitle>
            </CardHeader>
            <CardContent>
              <div className="h-80">
                <ResponsiveContainer width="100%" height="100%">
                  <BarChart
                    data={chartData}
                    margin={{
                      top: 5,
                      right: 30,
                      left: 20,
                      bottom: 5,
                    }}
                  >
                    <CartesianGrid strokeDasharray="3 3" />
                    <XAxis dataKey="name" />
                    <YAxis />
                    <Tooltip />
                    <Bar dataKey="deposits" fill="#3B82F6" name="Entradas" />
                    <Bar dataKey="withdrawals" fill="#F87171" name="Saídas" />
                  </BarChart>
                </ResponsiveContainer>
              </div>
            </CardContent>
          </Card>

          <TransactionList transactions={transactions} />
        </div>

        <Card>
          <CardHeader>
            <div className="flex justify-between items-center">
              <CardTitle>Últimas Atividades</CardTitle>
              <Button variant="outline" size="sm" className="text-xs">
                Ver todas
                <ArrowUpRight className="ml-1 h-3 w-3" />
              </Button>
            </div>
          </CardHeader>
          <CardContent>
            <div className="space-y-4">
              <div className="bg-gray-50 p-3 rounded-lg">
                <div className="flex items-start">
                  <div className="p-2 bg-blue-100 rounded-full mr-3">
                    <Building2 className="h-4 w-4 text-blue-500" />
                  </div>
                  <div>
                    <p className="font-medium">Consulta de CNPJ realizada</p>
                    <p className="text-sm text-muted-foreground">
                      CNPJ: 47.438.705/0001-59
                    </p>
                    <p className="text-xs text-muted-foreground mt-1">
                      Há 2 dias atrás
                    </p>
                  </div>
                </div>
              </div>
              
              <div className="bg-gray-50 p-3 rounded-lg">
                <div className="flex items-start">
                  <div className="p-2 bg-green-100 rounded-full mr-3">
                    <CreditCard className="h-4 w-4 text-green-500" />
                  </div>
                  <div>
                    <p className="font-medium">Recarga de créditos concluída</p>
                    <p className="text-sm text-muted-foreground">
                      Valor: R$ 100,00
                    </p>
                    <p className="text-xs text-muted-foreground mt-1">
                      Há 3 dias atrás
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </Layout>
  );
};

export default Dashboard;
