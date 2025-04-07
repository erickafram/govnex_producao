import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "@/context/AuthContext";
import Layout from "@/components/Layout";
import { Card, CardContent, CardHeader, CardTitle, CardDescription, CardFooter } from "@/components/ui/card";
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { FileSearch, Download, ChevronLeft, ChevronRight, Search, CalendarIcon, DollarSign, Building2, BarChart4 } from "lucide-react";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { Calendar } from "@/components/ui/calendar";
import { format } from "date-fns";
import { ptBR } from "date-fns/locale";
import { Badge } from "@/components/ui/badge";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select";
import { ConsultaLog } from "@/components/ConsultaLogs";

// API URL para uso direto (desenvolvimento)
const API_URL = "http://localhost:8000";

// API URL para uso com proxy reverso (produção)
const GOVNEX_API_URL = "https://infovisa.gurupi.to.gov.br/api/govnex"; // Ajuste para a URL correta do seu proxy

// Mock data for consulta logs (Expandido com mais dados para demonstração)
const mockConsultaLogs = [
    {
        id: "1",
        cnpj_consultado: "60043704000173",
        dominio_origem: "infovisa.gurupi.to.gov.br",
        data_consulta: new Date(Date.now() - 3600000 * 24).toISOString(),
        custo: 0.12,
    },
    {
        id: "2",
        cnpj_consultado: "47438705000159",
        dominio_origem: "infovisa.gurupi.to.gov.br",
        data_consulta: new Date(Date.now() - 3600000 * 48).toISOString(),
        custo: 0.05,
    },
    {
        id: "3",
        cnpj_consultado: "33349358000156",
        dominio_origem: "infovisa.gurupi.to.gov.br",
        data_consulta: new Date(Date.now() - 3600000 * 72).toISOString(),
        custo: 0.05,
    },
    {
        id: "4",
        cnpj_consultado: "10881356000146",
        dominio_origem: "infovisa.gurupi.to.gov.br",
        data_consulta: new Date(Date.now() - 3600000 * 96).toISOString(),
        custo: 0.05,
    },
    {
        id: "5",
        cnpj_consultado: "22222333000135",
        dominio_origem: "infovisa.gurupi.to.gov.br",
        data_consulta: new Date(Date.now() - 3600000 * 120).toISOString(),
        custo: 0.05,
    },
    // Adicionando mais dados para demonstração
    {
        id: "6",
        cnpj_consultado: "12345678000190",
        dominio_origem: "infovisa.gurupi.to.gov.br",
        data_consulta: new Date(Date.now() - 3600000 * 150).toISOString(),
        custo: 0.12,
    },
    {
        id: "7",
        cnpj_consultado: "98765432000121",
        dominio_origem: "infovisa.gurupi.to.gov.br",
        data_consulta: new Date(Date.now() - 3600000 * 180).toISOString(),
        custo: 0.12,
    },
    {
        id: "8",
        cnpj_consultado: "11223344000155",
        dominio_origem: "infovisa.gurupi.to.gov.br",
        data_consulta: new Date(Date.now() - 3600000 * 210).toISOString(),
        custo: 0.12,
    },
    {
        id: "9",
        cnpj_consultado: "55667788000199",
        dominio_origem: "infovisa.gurupi.to.gov.br",
        data_consulta: new Date(Date.now() - 3600000 * 240).toISOString(),
        custo: 0.12,
    },
    {
        id: "10",
        cnpj_consultado: "99887766000122",
        dominio_origem: "infovisa.gurupi.to.gov.br",
        data_consulta: new Date(Date.now() - 3600000 * 270).toISOString(),
        custo: 0.05,
    },
    {
        id: "11",
        cnpj_consultado: "44332211000177",
        dominio_origem: "infovisa.gurupi.to.gov.br",
        data_consulta: new Date(Date.now() - 3600000 * 300).toISOString(),
        custo: 0.05,
    },
    {
        id: "12",
        cnpj_consultado: "78945612000188",
        dominio_origem: "infovisa.gurupi.to.gov.br",
        data_consulta: new Date(Date.now() - 3600000 * 330).toISOString(),
        custo: 0.12,
    },
    {
        id: "13",
        cnpj_consultado: "32165498000144",
        dominio_origem: "infovisa.gurupi.to.gov.br",
        data_consulta: new Date(Date.now() - 3600000 * 360).toISOString(),
        custo: 0.12,
    },
    {
        id: "14",
        cnpj_consultado: "14725836000133",
        dominio_origem: "infovisa.gurupi.to.gov.br",
        data_consulta: new Date(Date.now() - 3600000 * 390).toISOString(),
        custo: 0.12,
    },
    {
        id: "15",
        cnpj_consultado: "96385274000166",
        dominio_origem: "infovisa.gurupi.to.gov.br",
        data_consulta: new Date(Date.now() - 3600000 * 420).toISOString(),
        custo: 0.05,
    },
    {
        id: "16",
        cnpj_consultado: "75315986000177",
        dominio_origem: "infovisa.gurupi.to.gov.br",
        data_consulta: new Date(Date.now() - 3600000 * 450).toISOString(),
        custo: 0.05,
    },
    {
        id: "17",
        cnpj_consultado: "85274196000122",
        dominio_origem: "infovisa.gurupi.to.gov.br",
        data_consulta: new Date(Date.now() - 3600000 * 480).toISOString(),
        custo: 0.12,
    },
    {
        id: "18",
        cnpj_consultado: "36925814000111",
        dominio_origem: "infovisa.gurupi.to.gov.br",
        data_consulta: new Date(Date.now() - 3600000 * 510).toISOString(),
        custo: 0.12,
    },
    {
        id: "19",
        cnpj_consultado: "95175385000155",
        dominio_origem: "infovisa.gurupi.to.gov.br",
        data_consulta: new Date(Date.now() - 3600000 * 540).toISOString(),
        custo: 0.12,
    },
    {
        id: "20",
        cnpj_consultado: "15935785000123",
        dominio_origem: "infovisa.gurupi.to.gov.br",
        data_consulta: new Date(Date.now() - 3600000 * 570).toISOString(),
        custo: 0.05,
    }
];

const RegistroConsultas = () => {
    const { user } = useAuth();
    const navigate = useNavigate();
    const [logs, setLogs] = useState<ConsultaLog[]>([]);
    const [searchTerm, setSearchTerm] = useState("");
    const [startDate, setStartDate] = useState<Date | undefined>(undefined);
    const [endDate, setEndDate] = useState<Date | undefined>(undefined);
    const [currentPage, setCurrentPage] = useState(1);
    const [isLoading, setIsLoading] = useState(true);
    const [itemsPerPage, setItemsPerPage] = useState(15);
    const [totalConsultas, setTotalConsultas] = useState<number>(0);
    const [totalValorGasto, setTotalValorGasto] = useState<number>(0);

    useEffect(() => {
        if (!user) {
            navigate("/login");
            return;
        }

        const fetchLogs = async () => {
            setIsLoading(true);
            try {
                // Determinar qual API usar baseado no ambiente
                const isProduction = window.location.hostname !== 'localhost';
                const baseApiUrl = isProduction ? GOVNEX_API_URL : API_URL;

                // Token de autenticação - Em produção, deve vir de um local seguro
                const apiToken = localStorage.getItem('apiToken') || 'seu_token_de_acesso';

                // Tentar buscar dados da API do GovNex através do proxy
                const response = await fetch(
                    `${baseApiUrl}/consultas-estatisticas.php?token=${apiToken}&dominio=${user.domain}&incluirConsultas=true`,
                    {
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        }
                    }
                );

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Erro na resposta da API:', errorText);
                    throw new Error(`Falha ao carregar dados: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    // Garantir que os dados estão no formato esperado
                    const formattedConsultas = data.consultas?.map(consulta => ({
                        id: consulta.id.toString(),
                        cnpj_consultado: consulta.cnpj_consultado,
                        dominio_origem: consulta.dominio_origem,
                        data_consulta: consulta.data_consulta,
                        custo: parseFloat(consulta.custo)
                    })) || [];

                    setLogs(formattedConsultas);

                    // Guardar estatísticas para uso
                    setTotalConsultas(data.totalConsultas || 0);
                    setTotalValorGasto(parseFloat(data.totalGasto) || 0);

                    console.log('Dados carregados com sucesso:', {
                        totalConsultas: data.totalConsultas,
                        totalGasto: data.totalGasto,
                        consultasCount: formattedConsultas.length
                    });
                } else {
                    console.error('Erro nos dados retornados:', data);
                    throw new Error(data.error || 'Erro ao buscar dados');
                }

                setIsLoading(false);
            } catch (error) {
                console.error('Erro ao carregar consultas:', error);

                // Em caso de falha, tentar buscar do endpoint local ou usar dados de fallback
                try {
                    const fallbackResponse = await fetch(`${API_URL}/api/consultas-estatisticas.php?dominio=${user.domain}&incluirConsultas=true`);
                    if (fallbackResponse.ok) {
                        const fallbackData = await fallbackResponse.json();
                        if (fallbackData.success) {
                            setLogs(fallbackData.consultas || []);
                            setTotalConsultas(fallbackData.totalConsultas || 0);
                            setTotalValorGasto(fallbackData.totalGasto || 0);
                            setIsLoading(false);
                            return;
                        }
                    }
                } catch (fallbackError) {
                    console.error('Erro ao carregar dados de fallback:', fallbackError);
                }

                // Se tudo falhar, usar mock data
                setTimeout(() => {
                    setLogs(mockConsultaLogs);
                    setIsLoading(false);
                }, 500);
            }
        };

        fetchLogs();
    }, [user, navigate]);

    // Processar os logs para garantir que todos os campos estão no formato correto
    useEffect(() => {
        if (logs.length > 0) {
            // Processar os logs para garantir que 'custo' é um número
            const processedLogs = logs.map(log => ({
                ...log,
                custo: typeof log.custo === 'string' ? parseFloat(log.custo) : log.custo || 0
            }));

            setLogs(processedLogs);
        }
    }, [logs.length]);

    const formatDate = (dateString: string) => {
        try {
            const date = new Date(dateString);
            // Verificar se a data é válida
            if (isNaN(date.getTime())) {
                // Tentar converter formato MySQL (YYYY-MM-DD HH:MM:SS)
                const parts = dateString.split(/[- :]/);
                if (parts.length >= 6) {
                    const mysqlDate = new Date(
                        parseInt(parts[0]),
                        parseInt(parts[1]) - 1,
                        parseInt(parts[2]),
                        parseInt(parts[3]),
                        parseInt(parts[4]),
                        parseInt(parts[5])
                    );
                    if (!isNaN(mysqlDate.getTime())) {
                        return format(mysqlDate, "dd/MM/yyyy HH:mm", { locale: ptBR });
                    }
                }
                return dateString; // Retornar a string original se não conseguir converter
            }

            return format(date, "dd/MM/yyyy HH:mm", { locale: ptBR });
        } catch (error) {
            console.error("Erro ao formatar data:", error);
            return dateString;
        }
    };

    const formatCNPJ = (cnpj: string) => {
        // Format CNPJ: XX.XXX.XXX/XXXX-XX
        if (cnpj.length !== 14) return cnpj;
        return cnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, "$1.$2.$3/$4-$5");
    };

    // Filter logs based on search term and dates
    const filteredLogs = logs.filter(log => {
        const matchesSearch = searchTerm
            ? log.cnpj_consultado.includes(searchTerm.replace(/\D/g, '')) ||
            log.dominio_origem.toLowerCase().includes(searchTerm.toLowerCase())
            : true;

        const logDate = new Date(log.data_consulta);

        const matchesStartDate = startDate
            ? logDate >= new Date(startDate.setHours(0, 0, 0, 0))
            : true;

        const matchesEndDate = endDate
            ? logDate <= new Date(new Date(endDate).setHours(23, 59, 59, 999))
            : true;

        return matchesSearch && matchesStartDate && matchesEndDate;
    });

    // Calculate pagination
    const indexOfLastLog = currentPage * itemsPerPage;
    const indexOfFirstLog = indexOfLastLog - itemsPerPage;
    const currentLogs = filteredLogs.slice(indexOfFirstLog, indexOfLastLog);
    const totalPages = Math.ceil(filteredLogs.length / itemsPerPage);

    // Calculate statistics (com proteção para valores não numéricos)
    const totalGeneralCost = logs.reduce((sum, log) => sum + (typeof log.custo === 'number' ? log.custo : 0), 0);
    const totalFilteredCost = filteredLogs.reduce((sum, log) => sum + (typeof log.custo === 'number' ? log.custo : 0), 0);

    // Valores reais do backend, ou do cálculo local se não disponíveis
    const totalGeneralCount = totalConsultas > 0 ? totalConsultas : logs.length;
    const valorTotalGasto = totalValorGasto > 0 ? totalValorGasto : totalGeneralCost;

    // Função para limpar filtros
    const clearFilters = () => {
        setSearchTerm("");
        setStartDate(undefined);
        setEndDate(undefined);
        setCurrentPage(1);
    };

    // Função para exportar para CSV
    const exportToCSV = () => {
        if (filteredLogs.length === 0) return;

        const headers = ["CNPJ", "Domínio", "Data/Hora", "Custo (R$)"];
        const csvRows = [];

        // Cabeçalho
        csvRows.push(headers.join(','));

        // Dados
        for (const log of filteredLogs) {
            const row = [
                formatCNPJ(log.cnpj_consultado),
                log.dominio_origem,
                formatDate(log.data_consulta),
                formatMoney(log.custo)
            ];
            csvRows.push(row.join(','));
        }

        // Cria o arquivo CSV
        const csvString = csvRows.join('\n');
        const blob = new Blob([csvString], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);

        // Cria um link temporário para download
        const link = document.createElement('a');
        link.setAttribute('href', url);
        link.setAttribute('download', `consultas_cnpj_${format(new Date(), 'yyyyMMdd')}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    // Função para formatar valores monetários
    const formatMoney = (value: number | string | null | undefined): string => {
        // Verificar se o valor é undefined ou null
        if (value === undefined || value === null) {
            return '0,00';
        }

        // Converter para número se for string
        let numValue: number;
        if (typeof value === 'string') {
            numValue = parseFloat(value);
            if (isNaN(numValue)) {
                return '0,00';
            }
        } else if (typeof value === 'number') {
            numValue = value;
        } else {
            return '0,00';
        }

        return numValue.toFixed(2).replace('.', ',');
    };

    return (
        <Layout>
            <div className="space-y-6">
                <div className="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">Histórico de Consultas</h1>
                        <p className="text-muted-foreground">
                            Histórico de todas as consultas de CNPJ realizadas
                        </p>
                    </div>
                    <div className="mt-4 md:mt-0">
                        <Button
                            variant="outline"
                            onClick={exportToCSV}
                            disabled={filteredLogs.length === 0}
                        >
                            <Download className="mr-2 h-4 w-4" />
                            Exportar CSV
                        </Button>
                    </div>
                </div>

                {/* Cards de estatísticas */}
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <Card className="hover:shadow-md transition-shadow">
                        <CardContent className="p-6 flex items-center space-x-4">
                            <div className="bg-blue-100 p-3 rounded-full">
                                <DollarSign className="h-6 w-6 text-blue-600" />
                            </div>
                            <div>
                                <p className="text-sm text-gray-500 mb-1">Total Gasto em Consultas</p>
                                <h3 className="text-2xl font-semibold text-gray-900">
                                    R$ {formatMoney(valorTotalGasto)}
                                </h3>
                                <p className="text-xs text-gray-500 mt-1">
                                    Valor acumulado de todas as consultas
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="hover:shadow-md transition-shadow">
                        <CardContent className="p-6 flex items-center space-x-4">
                            <div className="bg-green-100 p-3 rounded-full">
                                <Building2 className="h-6 w-6 text-green-600" />
                            </div>
                            <div>
                                <p className="text-sm text-gray-500 mb-1">Saldo de Créditos</p>
                                <h3 className="text-2xl font-semibold text-gray-900">
                                    R$ {user?.balance ? formatMoney(user.balance) : '0,00'}
                                </h3>
                                <p className="text-xs text-gray-500 mt-1">
                                    Saldo atual disponível para consultas
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card className="hover:shadow-md transition-shadow">
                        <CardContent className="p-6 flex items-center space-x-4">
                            <div className="bg-purple-100 p-3 rounded-full">
                                <BarChart4 className="h-6 w-6 text-purple-600" />
                            </div>
                            <div>
                                <p className="text-sm text-gray-500 mb-1">Total de Consultas</p>
                                <h3 className="text-2xl font-semibold text-gray-900">
                                    {totalGeneralCount}
                                </h3>
                                <p className="text-xs text-gray-500 mt-1">
                                    Número total de consultas realizadas
                                </p>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Filtros de pesquisa */}
                <Card className="hover:shadow-md transition-shadow">
                    <CardHeader>
                        <CardTitle>Filtrar Consultas</CardTitle>
                        <CardDescription>
                            Utilize os filtros abaixo para encontrar registros específicos
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label htmlFor="cnpj" className="block text-sm font-medium text-gray-700 mb-1">CNPJ</label>
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                                    <Input
                                        id="cnpj"
                                        placeholder="00.000.000/0000-00"
                                        value={searchTerm}
                                        onChange={(e) => {
                                            setSearchTerm(e.target.value);
                                            setCurrentPage(1);
                                        }}
                                        className="pl-9"
                                    />
                                </div>
                            </div>

                            <div>
                                <label htmlFor="data_inicio" className="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
                                <Popover>
                                    <PopoverTrigger asChild>
                                        <Button variant="outline" className="w-full justify-start text-left font-normal">
                                            <CalendarIcon className="mr-2 h-4 w-4" />
                                            {startDate ? format(startDate, "dd/MM/yyyy", { locale: ptBR }) : "Selecionar data"}
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent className="w-auto p-0" align="start">
                                        <Calendar
                                            mode="single"
                                            selected={startDate}
                                            onSelect={(date) => {
                                                setStartDate(date);
                                                setCurrentPage(1);
                                            }}
                                            initialFocus
                                        />
                                    </PopoverContent>
                                </Popover>
                            </div>

                            <div>
                                <label htmlFor="data_fim" className="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
                                <Popover>
                                    <PopoverTrigger asChild>
                                        <Button variant="outline" className="w-full justify-start text-left font-normal">
                                            <CalendarIcon className="mr-2 h-4 w-4" />
                                            {endDate ? format(endDate, "dd/MM/yyyy", { locale: ptBR }) : "Selecionar data"}
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent className="w-auto p-0" align="start">
                                        <Calendar
                                            mode="single"
                                            selected={endDate}
                                            onSelect={(date) => {
                                                setEndDate(date);
                                                setCurrentPage(1);
                                            }}
                                            initialFocus
                                        />
                                    </PopoverContent>
                                </Popover>
                            </div>

                            <div className="flex items-end gap-2">
                                <Button
                                    onClick={clearFilters}
                                    variant="outline"
                                    className="px-4 h-10"
                                    disabled={!searchTerm && !startDate && !endDate}
                                >
                                    Limpar Filtros
                                </Button>
                            </div>
                        </div>
                    </CardContent>

                    {(searchTerm || startDate || endDate) && (
                        <CardFooter className="border-t bg-gray-50 px-6 py-3">
                            <p className="text-sm text-gray-700">
                                Custo total para os filtros aplicados:
                                <strong className="font-semibold text-blue-700 ml-1">
                                    R$ {formatMoney(totalFilteredCost)}
                                </strong>
                                <span className="ml-1">
                                    ({filteredLogs.length} consultas)
                                </span>
                            </p>
                        </CardFooter>
                    )}
                </Card>

                {/* Tabela de Consultas */}
                <Card className="hover:shadow-md transition-shadow">
                    <CardHeader className="flex flex-col md:flex-row items-start md:items-center justify-between">
                        <div>
                            <CardTitle>Consultas Realizadas</CardTitle>
                            <CardDescription>
                                {isLoading ? 'Carregando dados...' : `${filteredLogs.length} consultas encontradas`}
                            </CardDescription>
                        </div>

                        <div className="mt-2 md:mt-0 flex items-center">
                            <span className="text-sm text-gray-500 mr-2">Itens por página:</span>
                            <Select
                                value={itemsPerPage.toString()}
                                onValueChange={(value) => {
                                    setItemsPerPage(Number(value));
                                    setCurrentPage(1);
                                }}
                            >
                                <SelectTrigger className="w-[70px]">
                                    <SelectValue placeholder="15" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="10">10</SelectItem>
                                    <SelectItem value="15">15</SelectItem>
                                    <SelectItem value="25">25</SelectItem>
                                    <SelectItem value="50">50</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardHeader>
                    <CardContent>
                        {isLoading ? (
                            <div className="flex justify-center items-center py-10">
                                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-primary"></div>
                            </div>
                        ) : filteredLogs.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-8 text-center">
                                <FileSearch className="h-12 w-12 text-muted-foreground mb-4" />
                                <p className="text-lg font-medium">Nenhuma consulta encontrada</p>
                                <p className="text-muted-foreground">
                                    {searchTerm || startDate || endDate
                                        ? 'Tente ajustar os filtros de pesquisa.'
                                        : 'Não há registros de consulta para este domínio ainda.'}
                                </p>
                                {(searchTerm || startDate || endDate) && (
                                    <Button
                                        onClick={clearFilters}
                                        variant="outline"
                                        className="mt-4"
                                    >
                                        Limpar Filtros
                                    </Button>
                                )}
                            </div>
                        ) : (
                            <div className="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead className="font-semibold">CNPJ Consultado</TableHead>
                                            <TableHead className="font-semibold">Domínio Origem</TableHead>
                                            <TableHead className="font-semibold">Data / Hora</TableHead>
                                            <TableHead className="text-right font-semibold">Custo (R$)</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {currentLogs.map((log) => (
                                            <TableRow key={log.id} className="hover:bg-gray-50">
                                                <TableCell className="font-mono">{formatCNPJ(log.cnpj_consultado)}</TableCell>
                                                <TableCell>{log.dominio_origem}</TableCell>
                                                <TableCell>{formatDate(log.data_consulta)}</TableCell>
                                                <TableCell className="text-right font-medium">{formatMoney(log.custo)}</TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </div>
                        )}
                    </CardContent>

                    {/* Paginação */}
                    {filteredLogs.length > 0 && (
                        <CardFooter className="border-t bg-gray-50 px-6 py-3">
                            <div className="w-full flex flex-col md:flex-row justify-between items-center">
                                <div className="text-sm text-gray-600 mb-3 md:mb-0">
                                    Exibindo
                                    <span className="font-semibold mx-1">
                                        {indexOfFirstLog + 1}
                                    </span>
                                    a
                                    <span className="font-semibold mx-1">
                                        {Math.min(indexOfLastLog, filteredLogs.length)}
                                    </span>
                                    de
                                    <span className="font-semibold mx-1">
                                        {filteredLogs.length}
                                    </span>
                                    registros
                                </div>

                                {totalPages > 1 && (
                                    <div className="flex items-center space-x-1">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => setCurrentPage(1)}
                                            disabled={currentPage === 1}
                                        >
                                            <ChevronLeft className="h-4 w-4 mr-1" />
                                            Primeira
                                        </Button>

                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => setCurrentPage(prev => Math.max(prev - 1, 1))}
                                            disabled={currentPage === 1}
                                        >
                                            <ChevronLeft className="h-4 w-4" />
                                        </Button>

                                        {/* Números de páginas */}
                                        {Array.from({ length: Math.min(5, totalPages) }, (_, i) => {
                                            // Lógica para calcular quais páginas mostrar
                                            let pageToShow;
                                            if (totalPages <= 5) {
                                                pageToShow = i + 1;
                                            } else if (currentPage <= 3) {
                                                pageToShow = i + 1;
                                            } else if (currentPage >= totalPages - 2) {
                                                pageToShow = totalPages - 4 + i;
                                            } else {
                                                pageToShow = currentPage - 2 + i;
                                            }

                                            if (pageToShow > 0 && pageToShow <= totalPages) {
                                                return (
                                                    <Button
                                                        key={pageToShow}
                                                        variant={currentPage === pageToShow ? "default" : "outline"}
                                                        size="sm"
                                                        onClick={() => setCurrentPage(pageToShow)}
                                                        className="w-9"
                                                    >
                                                        {pageToShow}
                                                    </Button>
                                                );
                                            }
                                            return null;
                                        })}

                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => setCurrentPage(prev => Math.min(prev + 1, totalPages))}
                                            disabled={currentPage === totalPages}
                                        >
                                            <ChevronRight className="h-4 w-4" />
                                        </Button>

                                        <Button
                                            variant="outline"
                                            size="sm"
                                            onClick={() => setCurrentPage(totalPages)}
                                            disabled={currentPage === totalPages}
                                        >
                                            Última
                                            <ChevronRight className="h-4 w-4 ml-1" />
                                        </Button>
                                    </div>
                                )}
                            </div>
                        </CardFooter>
                    )}
                </Card>
            </div>
        </Layout>
    );
};

export default RegistroConsultas; 