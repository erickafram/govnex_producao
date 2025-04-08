import React, { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "@/context/AuthContext";
import Layout from "@/components/Layout";
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Label } from "@/components/ui/label";
import { useToast } from "@/components/ui/use-toast";
import { Loader2, Copy, Check, RefreshCw } from "lucide-react";
import { maskCPF } from "@/lib/utils";

// API URL
const API_URL = "/api";

const Recarga = () => {
  const { user, updateUser } = useAuth();
  const navigate = useNavigate();
  const { toast } = useToast();
  const [amount, setAmount] = useState<string>("400");
  const [cpf, setCpf] = useState<string>("");
  const [name, setName] = useState<string>("");
  const [loading, setLoading] = useState<boolean>(false);
  const [paymentData, setPaymentData] = useState<any>(null);
  const [copied, setCopied] = useState<boolean>(false);
  const [checkingStatus, setCheckingStatus] = useState<boolean>(false);
  const [statusInterval, setStatusInterval] = useState<NodeJS.Timeout | null>(null);
  const [error, setError] = useState<string>("");
  const [timeLeft, setTimeLeft] = useState<number>(120); // 2 minutos em segundos
  const [timerInterval, setTimerInterval] = useState<NodeJS.Timeout | null>(null);

  useEffect(() => {
    // Pre-fill name if user is logged in
    if (user) {
      setName(user.name || "");
    }
  }, [user]);

  useEffect(() => {
    // Clean up interval on unmount
    return () => {
      if (statusInterval) {
        clearInterval(statusInterval);
      }
      if (timerInterval) {
        clearInterval(timerInterval);
      }
    };
  }, [statusInterval, timerInterval]);
  
  // Efeito para atualizar o temporizador
  useEffect(() => {
    if (paymentData && timeLeft > 0) {
      const timer = setInterval(() => {
        setTimeLeft((prev) => {
          if (prev <= 1) {
            clearInterval(timer);
            // Expirar o QR code quando o tempo acabar
            toast({
              title: "QR Code expirado",
              description: "O tempo para pagamento expirou. Gere um novo QR code.",
              variant: "destructive",
            });
            resetForm(); // Resetar o formulário para gerar um novo QR code
            return 0;
          }
          return prev - 1;
        });
      }, 1000);
      
      setTimerInterval(timer);
      
      return () => clearInterval(timer);
    }
  }, [paymentData]);

  const handleAmountChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    // Only allow numbers
    const value = e.target.value.replace(/[^0-9]/g, "");
    setAmount(value);
  };

  const handleCPFChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = e.target.value.replace(/[^0-9]/g, "");
    setCpf(maskCPF(value));
  };

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    try {
      setError("");
      setLoading(true);

      const token = localStorage.getItem("token");
      const response = await fetch(`${API_URL}/create_payment.php`, {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Authorization": `Bearer ${token}`,
        },
        body: JSON.stringify({
          amount: parseFloat(amount),
          cpf: cpf.replace(/[^0-9]/g, ""),
          name: name,
        }),
      });

      // Verificar se a resposta é JSON válido
      const contentType = response.headers.get("content-type");
      if (!contentType || !contentType.includes("application/json")) {
        const textResponse = await response.text();
        console.error("Resposta não-JSON recebida:", textResponse);
        throw new Error(`Resposta inválida do servidor: ${textResponse.substring(0, 100)}...`);
      }

      const data = await response.json();
      
      if (!response.ok) {
        throw new Error(data.message || "Erro ao processar pagamento");
      }

      if (data.success) {
        setPaymentData(data);
        toast({
          title: "Pagamento criado",
          description: "Escaneie o QR Code ou copie o código PIX para pagar",
        });

        // Start checking payment status
        const interval = setInterval(() => {
          checkPaymentStatus(data.transaction_id);
        }, 10000); // Check every 10 seconds
        
        setStatusInterval(interval);
      } else {
        setError(data.message || "Erro ao criar pagamento");
      }
    } catch (err) {
      console.error("Erro ao criar pagamento:", err);
      setError(`Erro ao criar pagamento: ${err instanceof Error ? err.message : String(err)}`);
    } finally {
      setLoading(false);
    }
  };

  const checkPaymentStatus = async (transactionId: string) => {
    if (checkingStatus) return;

    try {
      setCheckingStatus(true);
      const token = localStorage.getItem("token");
      console.log(`Verificando status do pagamento: ${API_URL}/check_payment.php?transaction_id=${transactionId}`);
      const response = await fetch(
        `${API_URL}/check_payment.php?transaction_id=${transactionId}`,
        {
          headers: {
            "Authorization": `Bearer ${token}`,
          },
        }
      );

      // Verificar se a resposta é JSON válido
      const contentType = response.headers.get("content-type");
      if (!contentType || !contentType.includes("application/json")) {
        const textResponse = await response.text();
        console.error("Resposta não-JSON recebida:", textResponse);
        throw new Error(`Resposta inválida do servidor: ${textResponse.substring(0, 100)}...`);
      }

      const data = await response.json();
      console.log('Resposta da verificação de pagamento:', data);
      
      if (data.success && data.payment && data.payment.status === "pago") {
        // Payment confirmed
        toast({
          title: "Pagamento confirmado!",
          description: "Sua recarga foi processada com sucesso",
          variant: "default",
        });

        // Clear interval
        if (statusInterval) {
          clearInterval(statusInterval);
          setStatusInterval(null);
        }

        // Atualizar saldo do usuário
        if (updateUser) {
          updateUser({
            ...user,
            credito: (parseFloat(user?.credito || "0") + parseFloat(amount)).toString()
          });
        }

        // Redirect to dashboard after 2 seconds
        setTimeout(() => {
          navigate("/dashboard");
        }, 2000);
      }
    } catch (err) {
      console.error("Erro ao verificar status:", err);
    } finally {
      setCheckingStatus(false);
    }
  };

  const copyPixCode = () => {
    if (paymentData && paymentData.pix_code) {
      navigator.clipboard.writeText(paymentData.pix_code);
      setCopied(true);
      setTimeout(() => setCopied(false), 3000);
      
      toast({
        title: "Código copiado!",
        description: "O código PIX foi copiado para a área de transferência",
      });
    }
  };

  const resetForm = () => {
    setPaymentData(null);
    if (statusInterval) {
      clearInterval(statusInterval);
      setStatusInterval(null);
    }
  };

  if (!user) {
    return (
      <Layout>
        <div className="flex items-center justify-center min-h-[60vh]">
          <Card className="w-full max-w-md">
            <CardHeader>
              <CardTitle>Acesso Restrito</CardTitle>
              <CardDescription>
                Você precisa estar logado para acessar esta página
              </CardDescription>
            </CardHeader>
            <CardFooter>
              <Button onClick={() => navigate("/login")} className="w-full">
                Fazer Login
              </Button>
            </CardFooter>
          </Card>
        </div>
      </Layout>
    );
  }

  return (
    <Layout>
      <div className="container mx-auto py-6">
        <h1 className="text-3xl font-bold mb-6">Recarga de Créditos</h1>

        {!paymentData ? (
          <Card className="max-w-md mx-auto">
            <CardHeader>
              <CardTitle>Adicionar Créditos</CardTitle>
              <CardDescription>
                Preencha os dados abaixo para gerar um QR Code PIX
              </CardDescription>
            </CardHeader>
            <CardContent>
              <form onSubmit={handleSubmit} className="space-y-4">
                <div className="space-y-2">
                  <Label htmlFor="amount">Valor da recarga (R$)</Label>
                  <Input
                    id="amount"
                    type="text"
                    value={amount}
                    onChange={handleAmountChange}
                    placeholder="Valor em reais"
                    required
                  />
                  <p className="text-xs text-muted-foreground">
                    Valor mínimo: R$ 400,00
                  </p>
                </div>

                <div className="space-y-2">
                  <Label htmlFor="cpf">CPF (apenas números)</Label>
                  <Input
                    id="cpf"
                    type="text"
                    value={cpf}
                    onChange={handleCPFChange}
                    placeholder="000.000.000-00"
                    maxLength={14}
                    required
                  />
                </div>

                <div className="space-y-2">
                  <Label htmlFor="name">Nome Completo</Label>
                  <Input
                    id="name"
                    type="text"
                    value={name}
                    onChange={(e) => setName(e.target.value)}
                    placeholder="Seu nome completo"
                    required
                  />
                </div>

                {error && (
                  <div className="text-red-500 text-sm">
                    {error}
                  </div>
                )}

                <Button type="submit" className="w-full" disabled={loading}>
                  {loading ? (
                    <>
                      <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                      Processando...
                    </>
                  ) : (
                    "Gerar QR Code PIX"
                  )}
                </Button>
              </form>
            </CardContent>
          </Card>
        ) : (
          <Card className="max-w-md mx-auto">
            <CardHeader>
              <CardTitle>Pagamento PIX</CardTitle>
              <CardDescription>
                Escaneie o QR Code ou copie o código PIX para pagar
              </CardDescription>
            </CardHeader>
            <CardContent className="space-y-4">
              <div className="flex flex-col items-center">
                <div className="text-center mb-4">
                  <p className="font-semibold text-lg">
                    Valor: R$ {parseFloat(amount).toFixed(2)}
                  </p>
                  <p className="text-sm text-muted-foreground">
                    O pagamento será processado automaticamente
                  </p>
                  <div className="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded-md">
                    <p className="text-sm font-medium text-yellow-700">
                      Tempo restante: {Math.floor(timeLeft / 60)}:{(timeLeft % 60).toString().padStart(2, '0')}
                    </p>
                    <p className="text-xs text-yellow-600">
                      Este QR code expira em 2 minutos
                    </p>
                  </div>
                </div>

                <div className="border p-4 rounded-lg mb-4">
                  <img
                    src={`/temp/qrcode_${paymentData.transaction_id}.png`}
                    alt="QR Code PIX"
                    className="w-64 h-64 mx-auto"
                  />
                </div>

                <div className="w-full space-y-2">
                  <Label htmlFor="pix-code">Código PIX Copia e Cola</Label>
                  <div className="flex">
                    <Input
                      id="pix-code"
                      value={paymentData.pix_code}
                      readOnly
                      className="flex-1 pr-10"
                    />
                    <Button
                      type="button"
                      variant="ghost"
                      size="icon"
                      className="ml-[-40px]"
                      onClick={copyPixCode}
                    >
                      {copied ? (
                        <Check className="h-4 w-4 text-green-500" />
                      ) : (
                        <Copy className="h-4 w-4" />
                      )}
                    </Button>
                  </div>
                </div>
              </div>
            </CardContent>
            <CardFooter className="flex justify-between">
              <Button variant="outline" onClick={resetForm}>
                Cancelar
              </Button>
              <Button
                variant="outline"
                onClick={() => checkPaymentStatus(paymentData.transaction_id)}
                disabled={checkingStatus}
              >
                {checkingStatus ? (
                  <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                ) : (
                  <RefreshCw className="mr-2 h-4 w-4" />
                )}
                Verificar Pagamento
              </Button>
            </CardFooter>
          </Card>
        )}
      </div>
    </Layout>
  );
};

export default Recarga;
