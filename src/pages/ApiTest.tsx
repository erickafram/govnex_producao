import React, { useState, useEffect } from "react";
import Layout from "@/components/Layout";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { useToast } from "@/components/ui/use-toast";
import { getApiUrl } from "@/config";

const ApiTest = () => {
  const { toast } = useToast();
  const [apiUrl, setApiUrl] = useState<string>("");
  const [response, setResponse] = useState<any>(null);
  const [loading, setLoading] = useState<boolean>(false);
  const [customEndpoint, setCustomEndpoint] = useState<string>("test_api.php");

  useEffect(() => {
    // Obter a URL base da API
    const baseUrl = getApiUrl("").replace("/api/", "");
    setApiUrl(baseUrl);
  }, []);

  const testApi = async () => {
    setLoading(true);
    try {
      const fullUrl = getApiUrl(customEndpoint);
      console.log("Testando API em:", fullUrl);

      const response = await fetch(fullUrl);
      const data = await response.json();

      setResponse({
        status: response.status,
        statusText: response.statusText,
        data
      });

      toast({
        title: `Resposta recebida (${response.status})`,
        description: "Confira os detalhes abaixo",
        variant: response.ok ? "default" : "destructive",
      });
    } catch (error) {
      console.error("Erro ao testar API:", error);
      setResponse({
        error: true,
        message: error instanceof Error ? error.message : "Erro desconhecido"
      });

      toast({
        title: "Erro ao testar API",
        description: error instanceof Error ? error.message : "Erro desconhecido",
        variant: "destructive",
      });
    } finally {
      setLoading(false);
    }
  };

  return (
    <Layout>
      <div className="container mx-auto py-6">
        <h1 className="text-2xl font-bold mb-6">Teste de API</h1>

        <Card className="mb-6">
          <CardHeader>
            <CardTitle>Configuração da API</CardTitle>
            <CardDescription>
              Teste a conexão com a API para diagnosticar problemas
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid gap-4">
              <div className="grid gap-2">
                <Label htmlFor="apiUrl">URL Base da API</Label>
                <Input
                  id="apiUrl"
                  value={apiUrl}
                  onChange={(e) => setApiUrl(e.target.value)}
                  disabled
                />
              </div>

              <div className="grid gap-2">
                <Label htmlFor="endpoint">Endpoint para testar</Label>
                <Input
                  id="endpoint"
                  value={customEndpoint}
                  onChange={(e) => setCustomEndpoint(e.target.value)}
                  placeholder="Exemplo: test_api.php"
                />
              </div>

              <Button onClick={testApi} disabled={loading}>
                {loading ? "Testando..." : "Testar API"}
              </Button>
            </div>
          </CardContent>
        </Card>

        {response && (
          <Card>
            <CardHeader>
              <CardTitle>
                Resposta da API {response.status && `(${response.status} ${response.statusText})`}
              </CardTitle>
            </CardHeader>
            <CardContent>
              <pre className="bg-gray-100 dark:bg-gray-800 p-4 rounded overflow-auto max-h-[500px]">
                {JSON.stringify(response, null, 2)}
              </pre>
            </CardContent>
          </Card>
        )}
      </div>
    </Layout>
  );
};

export default ApiTest;
