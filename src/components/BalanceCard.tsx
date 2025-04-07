
import React from "react";
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { useNavigate } from "react-router-dom";
import { PlusCircle } from "lucide-react";

interface BalanceCardProps {
  balance: number;
}

const BalanceCard: React.FC<BalanceCardProps> = ({ balance }) => {
  const navigate = useNavigate();

  return (
    <Card className="w-full card-transition">
      <CardHeader className="pb-2">
        <CardTitle className="text-lg font-medium text-muted-foreground">Seu saldo</CardTitle>
      </CardHeader>
      <CardContent>
        <div className="flex flex-col space-y-3">
          <div className="flex items-baseline">
            <span className="text-3xl font-bold">
              R$ {typeof balance === 'number' ? balance.toFixed(2) : '0.00'}
            </span>
          </div>
          <Button 
            onClick={() => navigate("/recarga")} 
            className="w-full pix-gradient text-white hover:opacity-90"
          >
            <PlusCircle className="mr-2 h-4 w-4" />
            Adicionar créditos
          </Button>
        </div>
      </CardContent>
    </Card>
  );
};

export default BalanceCard;
