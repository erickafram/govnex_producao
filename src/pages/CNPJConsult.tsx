import React, { useState } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "@/context/AuthContext";
import Layout from "@/components/Layout";
import CNPJResult from "@/components/CNPJResult";
import { queryCNPJ } from "@/services/cnpjService";
import { CNPJData } from "@/types";
import { z } from "zod";
import { useForm } from "react-hook-form";
import { zodResolver } from "@hookform/resolvers/zod";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  Form,
  FormControl,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from "@/components/ui/form";
import { useToast } from "@/components/ui/use-toast";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { AlertTriangle, Building2, Search } from "lucide-react";

const formSchema = z.object({
  cnpj: z.string().min(14, "CNPJ inválido")
});

type FormData = z.infer<typeof formSchema>;

const CNPJConsult = () => {
  const { user } = useAuth();
  const navigate = useNavigate();
  const { toast } = useToast();
  const [loading, setLoading] = useState(false);
  const [result, setResult] = useState<CNPJData | null>(null);

  const form = useForm<FormData>({
    resolver: zodResolver(formSchema),
    defaultValues: {
      cnpj: "",
    },
  });

  // Redirect to login if not authenticated
  React.useEffect(() => {
    if (!user) {
      navigate("/login");
    }
  }, [user, navigate]);

  if (!user) {
    return null; // or a loading spinner
  }

  // Function to mask CNPJ input
  const maskCNPJ = (value: string) => {
    return value
      .replace(/\D/g, "")
      .replace(/^(\d{2})(\d)/, "$1.$2")
      .replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3")
      .replace(/\.(\d{3})(\d)/, ".$1/$2")
      .replace(/(\d{4})(\d)/, "$1-$2")
      .substring(0, 18);
  };

  const onSubmit = async (data: FormData) => {
    if (user.balance < 0.12) {
      toast({
        title: "Saldo insuficiente",
        description: "Você não tem crédito suficiente para realizar uma consulta.",
        variant: "destructive",
      });
      return;
    }

    setLoading(true);
    try {
      const result = await queryCNPJ(data.cnpj);
      setResult(result);

      // Atualizar o saldo do usuário no localStorage
      const userStr = localStorage.getItem('user');
      if (userStr) {
        const userData = JSON.parse(userStr);
        userData.balance = userData.balance - 0.12; // Custo da consulta
        localStorage.setItem('user', JSON.stringify(userData));

        // Atualizar o contexto de autenticação
        if (user) {
          user.balance = userData.balance;
        }
      }

      toast({
        title: "Consulta realizada com sucesso",
        description: `Informações da empresa ${result.razaoSocial} foram carregadas.`,
      });
    } catch (error) {
      toast({
        title: "Erro na consulta",
        description: error instanceof Error ? error.message : "Não foi possível consultar este CNPJ",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  const handleNewSearch = () => {
    setResult(null);
    form.reset();
  };

  return (
    <Layout>
      <div className="max-w-3xl mx-auto space-y-6">
        <h1 className="text-3xl font-bold tracking-tight">Consulta de CNPJ</h1>
        <p className="text-muted-foreground">
          Consulte informações detalhadas de empresas através do CNPJ.
        </p>

        {!result ? (
          <>
            <Card>
              <CardHeader>
                <CardTitle className="flex items-center">
                  <Building2 className="mr-2 h-5 w-5" />
                  Informações da Empresa
                </CardTitle>
                <CardDescription>
                  Digite o CNPJ da empresa que deseja consultar.
                </CardDescription>
              </CardHeader>
              <CardContent>
                <Form {...form}>
                  <form onSubmit={form.handleSubmit(onSubmit)} className="space-y-4">
                    <FormField
                      control={form.control}
                      name="cnpj"
                      render={({ field: { onChange, ...rest } }) => (
                        <FormItem>
                          <FormLabel>CNPJ</FormLabel>
                          <FormControl>
                            <div className="flex">
                              <Input
                                {...rest}
                                placeholder="12.345.678/0001-90"
                                onChange={(e) => {
                                  const value = e.target.value;
                                  const masked = maskCNPJ(value);
                                  e.target.value = masked;
                                  onChange(e);
                                }}
                                className="rounded-r-none"
                                disabled={loading}
                              />
                              <Button
                                type="submit"
                                className="rounded-l-none pix-gradient text-white"
                                disabled={loading}
                              >
                                {loading ? (
                                  "Consultando..."
                                ) : (
                                  <>
                                    <Search className="mr-2 h-4 w-4" />
                                    Consultar
                                  </>
                                )}
                              </Button>
                            </div>
                          </FormControl>
                          <FormMessage />
                        </FormItem>
                      )}
                    />
                  </form>
                </Form>

                <div className="mt-4 text-sm">
                  <p className="text-muted-foreground">
                    Custo da consulta: <span className="font-medium text-foreground">R$ 0,12</span>
                  </p>
                  <p className="text-muted-foreground">
                    Seu saldo: <span className="font-medium text-foreground">R$ {typeof user.balance === 'number' ? user.balance.toFixed(2) : '0.00'}</span>
                  </p>
                  <p className="text-muted-foreground">
                    Consultas disponíveis: <span className="font-medium text-foreground">{typeof user.balance === 'number' ? Math.floor(user.balance / 0.12) : 0}</span>
                  </p>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardContent className="pt-6">
                <div className="flex items-start">
                  <AlertTriangle className="h-5 w-5 text-amber-500 mt-0.5 mr-3 flex-shrink-0" />
                  <div className="text-sm">
                    <p className="font-medium">Demonstração</p>
                    <p className="text-muted-foreground">
                      Para testar, use os CNPJs: "12.345.678/0001-90" ou "98.765.432/0001-21".
                      Você também pode inserir qualquer CNPJ válido para ver uma resposta simulada.
                    </p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </>
        ) : (
          <div className="space-y-4">
            <CNPJResult data={result} />
            <div className="flex justify-center">
              <Button onClick={handleNewSearch}>Nova Consulta</Button>
            </div>
          </div>
        )}
      </div>
    </Layout>
  );
};

export default CNPJConsult;
