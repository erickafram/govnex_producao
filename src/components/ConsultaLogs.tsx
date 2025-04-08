import React, { useState } from "react";
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { FileSearch, ExternalLink, ChevronRight } from "lucide-react";
import { Card, CardContent, CardHeader, CardTitle, CardFooter } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { useNavigate } from "react-router-dom";

export interface ConsultaLog {
  id: string;
  cnpj_consultado: string;
  dominio_origem: string;
  data_consulta: string;
  custo: number;
}

interface ConsultaLogsProps {
  logs: ConsultaLog[];
}

const ConsultaLogs: React.FC<ConsultaLogsProps> = ({ logs }) => {
  const navigate = useNavigate();
  const [expanded, setExpanded] = useState(false);

  // Limite a quantidade de logs exibidos, a menos que expandido
  const displayLogs = expanded ? logs : logs.slice(0, 5);

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
            return mysqlDate.toLocaleDateString("pt-BR", {
              day: "2-digit",
              month: "2-digit",
              year: "numeric",
              hour: "2-digit",
              minute: "2-digit",
            });
          }
        }
        return dateString; // Retornar a string original se não conseguir converter
      }

      return date.toLocaleDateString("pt-BR", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
      });
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

  return (
    <Card>
      <CardHeader className="flex flex-row items-center justify-between">
        <CardTitle className="text-xl font-semibold">
          <span className="flex items-center">
            <FileSearch className="mr-2 h-5 w-5 text-blue-600" />
            Consultas de CNPJ Realizadas
          </span>
        </CardTitle>
        {logs.length > 0 && (
          <Button variant="ghost" size="sm" onClick={() => navigate("/registro-consultas")}>
            Ver tudo
            <ChevronRight className="ml-1 h-4 w-4" />
          </Button>
        )}
      </CardHeader>
      <CardContent>
        {logs.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-8 text-center">
            <FileSearch className="h-12 w-12 text-muted-foreground mb-4" />
            <p className="text-lg font-medium">Nenhuma consulta realizada ainda</p>
            <p className="text-muted-foreground">
              As consultas de CNPJ que você realizar aparecerão aqui.
            </p>
          </div>
        ) : (
          <div className="overflow-x-auto">
            <Table>
              <TableCaption>Histórico de consultas recentes</TableCaption>
              <TableHeader>
                <TableRow>
                  <TableHead>CNPJ</TableHead>
                  <TableHead>Domínio</TableHead>
                  <TableHead>Data</TableHead>
                  <TableHead className="text-right">Custo (R$)</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {displayLogs.map((log) => (
                  <TableRow key={log.id}>
                    <TableCell className="font-medium">{formatCNPJ(log.cnpj_consultado)}</TableCell>
                    <TableCell>
                      <div className="flex items-center">
                        {log.dominio_origem}
                        <ExternalLink className="ml-1 h-3 w-3 text-muted-foreground" />
                      </div>
                    </TableCell>
                    <TableCell>{formatDate(log.data_consulta)}</TableCell>
                    <TableCell className="text-right">{typeof log.custo === 'number' ? log.custo.toFixed(2) : '0.00'}</TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </div>
        )}
      </CardContent>
      {logs.length > 5 && !expanded && (
        <CardFooter className="flex justify-center pt-0">
          <Button variant="outline" size="sm" onClick={() => setExpanded(true)}>
            Mostrar mais
            <ChevronRight className="ml-1 h-4 w-4" />
          </Button>
        </CardFooter>
      )}
    </Card>
  );
};

export default ConsultaLogs;
