import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "@/context/AuthContext";
import Layout from "@/components/Layout";
import PixQRCode from "@/components/PixQRCode";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Separator } from "@/components/ui/separator";
import { createPixPayment, simulatePaymentCompletion } from "@/services/pixService";
import { CreditCard } from "lucide-react";
import { Transaction } from "@/types";
import { useToast } from "@/components/ui/use-toast";

const RechargeAmounts = [20, 50, 100, 200, 500];

const Recharge = () => {
  const { user } = useAuth();
  const navigate = useNavigate();
  const { toast } = useToast();
  const [amount, setAmount] = useState<number | "">("");
  const [customAmount, setCustomAmount] = useState<string>("");
  const [transaction, setTransaction] = useState<Transaction | null>(null);
  const [loading, setLoading] = useState(false);
  const [verifying, setVerifying] = useState(false);

  // Redirect to login if not authenticated
  React.useEffect(() => {
    if (!user) {
      navigate("/login");
    }
  }, [user, navigate]);

  if (!user) {
    return null; // or a loading spinner
  }

  const handleSelectAmount = (value: number) => {
    setAmount(value);
    setCustomAmount(value.toString());
  };

  const handleCustomAmountChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = e.target.value;
    // Allow only numbers and a single decimal point
    if (/^\d*\.?\d{0,2}$/.test(value) || value === "") {
      setCustomAmount(value);
      setAmount(value === "" ? "" : parseFloat(value));
    }
  };

  const handleCreatePayment = async () => {
    if (!amount || amount < 1) {
      toast({
        title: "Valor mínimo não atingido",
        description: "O valor mínimo para recarga é de R$ 1,00",
        variant: "destructive",
      });
      return;
    }

    setLoading(true);
    try {
      const newTransaction = await createPixPayment(user.id, Number(amount));
      setTransaction(newTransaction);
    } catch (error) {
      toast({
        title: "Erro ao criar pagamento",
        description: error instanceof Error ? error.message : "Ocorreu um erro ao gerar o código PIX",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  const handleVerifyPayment = async () => {
    if (!transaction) return;

    setVerifying(true);
    try {
      // In a real app, this would check with a payment provider
      await new Promise(resolve => setTimeout(resolve, 2000)); // Simulate API call
      
      // For demo purposes, simulate payment completion
      const updatedTransaction = await simulatePaymentCompletion(transaction);
      setTransaction(updatedTransaction);
      
      if (updatedTransaction.status === "completed") {
        toast({
          title: "Pagamento confirmado!",
          description: `Recarga de R$ ${typeof amount === 'number' ? amount.toFixed(2) : '0.00'} realizada com sucesso.`,
        });
        
        // In a real app, we would refresh the user's balance here
        setTimeout(() => {
          navigate("/dashboard");
        }, 2000);
      } else {
        toast({
          title: "Aguardando pagamento",
          description: "Ainda não identificamos seu pagamento. Tente novamente em alguns instantes.",
        });
      }
    } catch (error) {
      toast({
        title: "Erro ao verificar pagamento",
        description: "Não foi possível verificar o status do seu pagamento.",
        variant: "destructive",
      });
    } finally {
      setVerifying(false);
    }
  };

  return (
    <Layout>
      <div className="max-w-2xl mx-auto space-y-6">
        <h1 className="text-3xl font-bold tracking-tight">Recarga de Créditos</h1>
        <p className="text-muted-foreground">
          Adicione créditos à sua conta para realizar consultas de CNPJ.
        </p>

        {!transaction ? (
          <div className="space-y-6">
            <div className="bg-card border rounded-lg p-6">
              <h2 className="text-xl font-semibold mb-4">Selecione um valor para recarga</h2>
              
              <div className="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
                {RechargeAmounts.map((value) => (
                  <Button
                    key={value}
                    variant={amount === value ? "default" : "outline"}
                    className={amount === value ? "pix-gradient text-white" : ""}
                    onClick={() => handleSelectAmount(value)}
                  >
                    R$ {value.toFixed(2)}
                  </Button>
                ))}
              </div>
              
              <Separator className="my-6" />
              
              <div className="space-y-4">
                <h3 className="font-medium">Ou digite um valor personalizado:</h3>
                <div className="flex gap-2">
                  <div className="relative flex-1">
                    <span className="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">
                      R$
                    </span>
                    <Input
                      value={customAmount}
                      onChange={handleCustomAmountChange}
                      className="pl-9"
                      placeholder="0,00"
                    />
                  </div>
                  <Button 
                    onClick={handleCreatePayment}
                    disabled={!amount || amount < 1 || loading}
                    className="pix-gradient text-white"
                  >
                    {loading ? "Gerando..." : "Confirmar"}
                  </Button>
                </div>
                <p className="text-sm text-muted-foreground">
                  Valor mínimo: R$ 1,00
                </p>
              </div>
            </div>
            
            <div className="bg-blue-50 text-blue-800 p-4 rounded-lg flex items-start">
              <CreditCard className="h-5 w-5 mt-0.5 mr-3 flex-shrink-0" />
              <div className="text-sm">
                <p className="font-medium">Pagamento Seguro via PIX</p>
                <p>
                  Após a confirmação, você receberá um QR Code para pagar usando o 
                  PIX do seu banco. O crédito será adicionado instantaneamente após a confirmação.
                </p>
              </div>
            </div>
          </div>
        ) : (
          <PixQRCode
            amount={transaction.amount}
            pixCode={transaction.pixCode || ""}
            qrCodeUrl={transaction.qrCodeUrl || ""}
            onRefresh={handleVerifyPayment}
            isLoading={verifying}
          />
        )}
      </div>
    </Layout>
  );
};

export default Recharge;
