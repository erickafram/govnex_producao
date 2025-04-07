
import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "@/context/AuthContext";
import Layout from "@/components/Layout";
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from "@/components/ui/card";
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
import { FileSearch, Download, ChevronLeft, ChevronRight, Search, CalendarIcon } from "lucide-react";
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover";
import { Calendar } from "@/components/ui/calendar";
import { format } from "date-fns";
import { ptBR } from "date-fns/locale";
import { Badge } from "@/components/ui/badge";

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
  }
];

const ConsultationLogs = () => {
  const { user } = useAuth();
  const navigate = useNavigate();
  const [logs, setLogs] = useState<any[]>([]);
  const [searchTerm, setSearchTerm] = useState("");
  const [date, setDate] = useState<Date | undefined>(undefined);
  const [currentPage, setCurrentPage] = useState(1);
  const logsPerPage = 10;

  useEffect(() => {
    if (user) {
      // In a real app, fetch logs from API
      setLogs(mockConsultaLogs);
    } else {
      navigate("/login");
    }
  }, [user, navigate]);

  const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return format(date, "dd/MM/yyyy HH:mm", { locale: ptBR });
  };

  const formatCNPJ = (cnpj: string) => {
    // Format CNPJ: XX.XXX.XXX/XXXX-XX
    if (cnpj.length !== 14) return cnpj;
    return cnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, "$1.$2.$3/$4-$5");
  };

  // Filter logs based on search term and date
  const filteredLogs = logs.filter(log => {
    const matchesSearch = searchTerm
      ? log.cnpj_consultado.includes(searchTerm) || log.dominio_origem.toLowerCase().includes(searchTerm.toLowerCase())
      : true;

    const matchesDate = date
      ? new Date(log.data_consulta).toDateString() === date.toDateString()
      : true;

    return matchesSearch && matchesDate;
  });

  // Calculate pagination
  const indexOfLastLog = currentPage * logsPerPage;
  const indexOfFirstLog = indexOfLastLog - logsPerPage;
  const currentLogs = filteredLogs.slice(indexOfFirstLog, indexOfLastLog);
  const totalPages = Math.ceil(filteredLogs.length / logsPerPage);

  // Calculate total cost
  const totalCost = filteredLogs.reduce((sum, log) => sum + log.custo, 0);

  return (
    <Layout>
      <div className="space-y-6">
        <div className="flex flex-col md:flex-row md:items-center md:justify-between">
          <div>
            <h1 className="text-3xl font-bold tracking-tight">Registro de Consultas</h1>
            <p className="text-muted-foreground">
              Histórico de todas as consultas de CNPJ realizadas
            </p>
          </div>
          <div className="mt-4 md:mt-0">
            <Button
              variant="outline"
              onClick={() => {
                // In a real app, this would download CSV/Excel file
                alert("Exportação de registros será disponibilizada em breve");
              }}
            >
              <Download className="mr-2 h-4 w-4" />
              Exportar
            </Button>
          </div>
        </div>

        <Card>
          <CardHeader>
            <CardTitle>Filtros de Pesquisa</CardTitle>
            <CardDescription>
              Utilize os filtros abaixo para encontrar registros específicos
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="flex flex-col md:flex-row gap-4">
              <div className="relative flex-1">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input
                  placeholder="Buscar por CNPJ ou domínio"
                  value={searchTerm}
                  onChange={(e) => setSearchTerm(e.target.value)}
                  className="pl-9"
                />
              </div>
              <Popover>
                <PopoverTrigger asChild>
                  <Button variant="outline" className="w-full md:w-auto">
                    <CalendarIcon className="mr-2 h-4 w-4" />
                    {date ? format(date, "dd/MM/yyyy", { locale: ptBR }) : "Filtrar por data"}
                  </Button>
                </PopoverTrigger>
                <PopoverContent className="w-auto p-0" align="start">
                  <Calendar
                    mode="single"
                    selected={date}
                    onSelect={setDate}
                    initialFocus
                  />
                </PopoverContent>
              </Popover>
              {date && (
                <Button
                  variant="ghost"
                  size="sm"
                  onClick={() => setDate(undefined)}
                  className="w-full md:w-auto"
                >
                  Limpar data
                </Button>
              )}
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-col md:flex-row items-start md:items-center justify-between">
            <div>
              <CardTitle>Consultas Realizadas</CardTitle>
              <CardDescription>
                Total de {filteredLogs.length} consultas | Custo total: R$ {totalCost.toFixed(2)}
              </CardDescription>
            </div>
            {filteredLogs.length > 0 && searchTerm && (
              <Badge variant="outline" className="mt-2 md:mt-0 bg-blue-50 text-blue-700">
                {filteredLogs.length} resultados para "{searchTerm}"
              </Badge>
            )}
          </CardHeader>
          <CardContent>
            {filteredLogs.length === 0 ? (
              <div className="flex flex-col items-center justify-center py-8 text-center">
                <FileSearch className="h-12 w-12 text-muted-foreground mb-4" />
                <p className="text-lg font-medium">Nenhuma consulta encontrada</p>
                <p className="text-muted-foreground">
                  Tente ajustar os filtros de pesquisa ou volte mais tarde.
                </p>
              </div>
            ) : (
              <div className="overflow-x-auto">
                <Table>
                  <TableCaption>Histórico de consultas de CNPJ</TableCaption>
                  <TableHeader>
                    <TableRow>
                      <TableHead>CNPJ</TableHead>
                      <TableHead>Domínio</TableHead>
                      <TableHead>Data</TableHead>
                      <TableHead className="text-right">Custo (R$)</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {currentLogs.map((log) => (
                      <TableRow key={log.id} className="hover:bg-gray-50">
                        <TableCell className="font-medium">{formatCNPJ(log.cnpj_consultado)}</TableCell>
                        <TableCell>{log.dominio_origem}</TableCell>
                        <TableCell>{formatDate(log.data_consulta)}</TableCell>
                        <TableCell className="text-right">{log.custo.toFixed(2)}</TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </div>
            )}

            {/* Pagination */}
            {totalPages > 1 && (
              <div className="flex items-center justify-center space-x-2 mt-6">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => setCurrentPage(prev => Math.max(prev - 1, 1))}
                  disabled={currentPage === 1}
                >
                  <ChevronLeft className="h-4 w-4" />
                </Button>
                <span className="text-sm text-muted-foreground">
                  Página {currentPage} de {totalPages}
                </span>
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => setCurrentPage(prev => Math.min(prev + 1, totalPages))}
                  disabled={currentPage === totalPages}
                >
                  <ChevronRight className="h-4 w-4" />
                </Button>
              </div>
            )}
          </CardContent>
        </Card>
      </div>
    </Layout>
  );
};

export default ConsultationLogs;
