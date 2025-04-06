
import React from "react";
import { 
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { FileSearch, ExternalLink } from "lucide-react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";

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
                {logs.map((log) => (
                  <TableRow key={log.id}>
                    <TableCell className="font-medium">{formatCNPJ(log.cnpj_consultado)}</TableCell>
                    <TableCell>
                      <div className="flex items-center">
                        {log.dominio_origem}
                        <ExternalLink className="ml-1 h-3 w-3 text-muted-foreground" />
                      </div>
                    </TableCell>
                    <TableCell>{formatDate(log.data_consulta)}</TableCell>
                    <TableCell className="text-right">{log.custo.toFixed(2)}</TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </div>
        )}
      </CardContent>
    </Card>
  );
};

export default ConsultaLogs;
