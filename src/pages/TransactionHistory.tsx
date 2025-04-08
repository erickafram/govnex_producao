import React, { useState, useEffect } from "react";
import { useAuth } from "@/context/AuthContext";
import Layout from "@/components/Layout";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
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
import { Calendar, ArrowDown, ArrowUp, RefreshCw, ChevronLeft, ChevronRight } from "lucide-react";
import { useToast } from "@/components/ui/use-toast";
import { Transaction } from "@/types";

const TransactionHistory: React.FC = () => {
    const { user } = useAuth();
    const { toast } = useToast();
    const [transactions, setTransactions] = useState<Transaction[]>([]);
    const [isLoading, setIsLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    
    // Pagination state
    const [currentPage, setCurrentPage] = useState(1);
    const [totalPages, setTotalPages] = useState(1);
    const [pageSize] = useState(10);

    // Format date to local format
    const formatDate = (dateString: string) => {
        const date = new Date(dateString);
        return new Intl.DateTimeFormat('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }).format(date);
    };

    // Format currency
    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: 'BRL'
        }).format(amount);
    };

    // Get status text based on status code
    const getStatusText = (status: string) => {
        const statusMap: Record<string, string> = {
            'pending': 'Pendente',
            'completed': 'Concluído',
            'failed': 'Falhou'
        };
        return statusMap[status] || status;
    };

    // Get status badge variant based on status
    const getStatusVariant = (status: string) => {
        const variantMap: Record<string, string> = {
            'pending': 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'completed': 'bg-green-100 text-green-800 border-green-200',
            'failed': 'bg-red-100 text-red-800 border-red-200'
        };
        return variantMap[status] || '';
    };

    // Fetch transactions from API
    const fetchTransactions = async (page: number = currentPage) => {
        setIsLoading(true);
        setError(null);

        try {
            // Usar o token do localStorage ou o token de desenvolvimento
            const token = localStorage.getItem('token') || 'dev_token_user_1';
            
            console.log("Buscando transações com token:", token);

            // Usar o caminho relativo para o proxy configurado no vite.config.ts
            const response = await fetch(`/api/payments.php?page=${page}&limit=${pageSize}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                credentials: 'same-origin'
            });

            console.log("Status da resposta:", response.status);
            
            if (!response.ok) {
                const errorData = await response.json();
                console.error("Erro na resposta:", errorData);
                throw new Error(errorData.error || "Falha ao carregar transações");
            }

            const data = await response.json();
            console.log("Dados recebidos:", data);
            
            if (data.success) {
                setTransactions(data.transactions || []);
                setTotalPages(data.pagination?.totalPages || 1);
                setCurrentPage(data.pagination?.currentPage || 1);
            } else {
                throw new Error(data.error || "Falha ao carregar transações");
            }
        } catch (error) {
            console.error("Erro ao carregar transações:", error);
            setError(error instanceof Error ? error.message : "Erro desconhecido");
        } finally {
            setIsLoading(false);
        }
    };

    // Load transactions on component mount
    useEffect(() => {
        fetchTransactions(1);
    }, []);

    // Handle page change
    const handlePageChange = (newPage: number) => {
        if (newPage >= 1 && newPage <= totalPages) {
            setCurrentPage(newPage);
            fetchTransactions(newPage);
        }
    };

    // Handle refresh
    const handleRefresh = () => {
        fetchTransactions(currentPage);
        toast({
            title: "Atualizando transações",
            description: "Buscando as transações mais recentes...",
        });
    };

    return (
        <Layout>
            <div className="container mx-auto py-6">
                <div className="flex justify-between items-center mb-6">
                    <h1 className="text-3xl font-bold">Histórico de Transações</h1>
                    <Button onClick={handleRefresh} variant="outline" className="flex items-center gap-2">
                        <RefreshCw className="h-4 w-4" />
                        Atualizar
                    </Button>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Suas Transações</CardTitle>
                        <CardDescription>
                            Visualize o histórico de todas as suas transações de recarga e uso de créditos.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        {error && (
                            <div className="bg-red-50 text-red-700 p-4 rounded-md mb-4">
                                <p className="font-medium">Erro ao carregar transações</p>
                                <p className="text-sm">{error}</p>
                            </div>
                        )}

                        {isLoading ? (
                            <div className="flex justify-center py-8">
                                <RefreshCw className="h-8 w-8 animate-spin text-muted-foreground" />
                            </div>
                        ) : transactions.length === 0 ? (
                            <div className="text-center py-8 text-muted-foreground">
                                <p>Nenhuma transação encontrada</p>
                                <p className="text-sm mt-2">As transações aparecerão aqui quando você fizer recargas ou usar créditos</p>
                            </div>
                        ) : (
                            <Table>
                                <TableCaption>Lista de transações da sua conta</TableCaption>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Data</TableHead>
                                        <TableHead>Descrição</TableHead>
                                        <TableHead>Tipo</TableHead>
                                        <TableHead>Valor</TableHead>
                                        <TableHead>Status</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {transactions.map((transaction) => (
                                        <TableRow key={transaction.id}>
                                            <TableCell>
                                                <div className="flex items-center">
                                                    <Calendar className="h-4 w-4 mr-2 text-muted-foreground" />
                                                    {formatDate(transaction.createdAt)}
                                                </div>
                                            </TableCell>
                                            <TableCell>{transaction.description || "Transação"}</TableCell>
                                            <TableCell>
                                                {transaction.type === 'deposit' ? (
                                                    <Badge variant="outline" className="bg-green-50 text-green-700 border-green-200">
                                                        <ArrowDown className="h-3 w-3 mr-1" />
                                                        Entrada
                                                    </Badge>
                                                ) : (
                                                    <Badge variant="outline" className="bg-red-50 text-red-700 border-red-200">
                                                        <ArrowUp className="h-3 w-3 mr-1" />
                                                        Saída
                                                    </Badge>
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <span className={transaction.type === 'deposit' ? 'text-green-600 font-medium' : 'text-red-600 font-medium'}>
                                                    {transaction.type === 'deposit' ? '+' : '-'}{formatCurrency(transaction.amount)}
                                                </span>
                                            </TableCell>
                                            <TableCell>
                                                <Badge className={getStatusVariant(transaction.status)}>
                                                    {getStatusText(transaction.status)}
                                                </Badge>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        )}

                        {/* Pagination */}
                        {totalPages > 1 && (
                            <div className="flex justify-between items-center mt-4">
                                <div className="text-sm text-muted-foreground">
                                    Página {currentPage} de {totalPages}
                                </div>
                                <div className="flex gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => handlePageChange(currentPage - 1)}
                                        disabled={currentPage === 1 || isLoading}
                                    >
                                        <ChevronLeft className="h-4 w-4" />
                                        Anterior
                                    </Button>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => handlePageChange(currentPage + 1)}
                                        disabled={currentPage === totalPages || isLoading}
                                    >
                                        Próxima
                                        <ChevronRight className="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </Layout>
    );
};

export default TransactionHistory;
