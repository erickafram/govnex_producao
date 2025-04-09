
import React from "react";
import { Transaction } from "@/types";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import {
  ArrowDownCircle,
  ArrowUpCircle,
  Clock,
  CheckCircle,
  XCircle
} from "lucide-react";
import { Badge } from "@/components/ui/badge";

interface TransactionListProps {
  transactions: Transaction[];
}

const TransactionList: React.FC<TransactionListProps> = ({ transactions }) => {
  // Status icons mapping
  const statusIcons = {
    pending: <Clock className="h-4 w-4 text-yellow-500" />,
    completed: <CheckCircle className="h-4 w-4 text-green-500" />,
    failed: <XCircle className="h-4 w-4 text-red-500" />,
    expired: <XCircle className="h-4 w-4 text-red-500" />,
  };

  // Status badge variant mapping
  const statusVariants: Record<string, string> = {
    pending: "text-yellow-700 bg-yellow-100",
    completed: "text-green-700 bg-green-100",
    failed: "text-red-700 bg-red-100",
    expired: "text-red-700 bg-red-100",
  };

  // Type icons mapping
  const typeIcons = {
    deposit: <ArrowDownCircle className="h-4 w-4 text-green-500" />,
    withdrawal: <ArrowUpCircle className="h-4 w-4 text-red-500" />,
    fee: <ArrowUpCircle className="h-4 w-4 text-orange-500" />,
  };

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

  return (
    <Card className="w-full">
      <CardHeader>
        <CardTitle>Histórico de Transações</CardTitle>
      </CardHeader>
      <CardContent>
        {transactions.length === 0 ? (
          <div className="text-center py-8 text-muted-foreground">
            Nenhuma transação encontrada
          </div>
        ) : (
          <div className="space-y-4">
            {transactions.map((transaction) => (
              <div
                key={transaction.id}
                className="flex items-center justify-between p-4 rounded-lg border bg-card"
              >
                <div className="flex items-center gap-3">
                  <div className="p-2 rounded-full bg-primary/10">
                    {typeIcons[transaction.type]}
                  </div>
                  <div>
                    <p className="font-medium">{transaction.description}</p>
                    <p className="text-sm text-muted-foreground">
                      {formatDate(transaction.createdAt)}
                    </p>
                  </div>
                </div>
                <div className="flex flex-col items-end">
                  <p className={`font-semibold ${transaction.type === 'deposit' ? 'text-green-600' : 'text-red-600'}`}>
                    {transaction.type === 'deposit' ? '+' : '-'}
                    R$ {transaction.amount.toFixed(2)}
                  </p>
                  <Badge variant="outline" className={statusVariants[transaction.status]}>
                    <span className="flex items-center gap-1">
                      {statusIcons[transaction.status as keyof typeof statusIcons]}
                      <span className="capitalize">
                        {transaction.status === 'pending' ? 'Pendente' :
                          transaction.status === 'completed' ? 'Concluído' :
                            transaction.status === 'failed' ? 'Falhou' : 'Expirado'}
                      </span>
                    </span>
                  </Badge>
                </div>
              </div>
            ))}
          </div>
        )}
      </CardContent>
    </Card>
  );
};

export default TransactionList;
