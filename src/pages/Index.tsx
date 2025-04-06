
import React from "react";
import { useNavigate } from "react-router-dom";
import { Button } from "@/components/ui/button";
import { useAuth } from "@/context/AuthContext";
import { CreditCard, Building2, LayoutDashboard } from "lucide-react";

const Index = () => {
  const navigate = useNavigate();
  const { user } = useAuth();

  // If user is already logged in, redirect to dashboard
  React.useEffect(() => {
    if (user) {
      navigate("/dashboard");
    }
  }, [user, navigate]);

  const features = [
    {
      title: "Pagamentos via PIX",
      description: "Recarregue sua conta usando PIX, o método de pagamento instantâneo do Brasil.",
      icon: CreditCard,
      color: "bg-pix text-white",
    },
    {
      title: "Consultas de CNPJ",
      description: "Acesse informações detalhadas sobre empresas brasileiras através do CNPJ.",
      icon: Building2,
      color: "bg-blue-600 text-white",
    },
    {
      title: "Controle de Créditos",
      description: "Gerencie seu saldo e acompanhe o histórico de transações no seu dashboard.",
      icon: LayoutDashboard,
      color: "bg-purple-600 text-white",
    },
  ];

  return (
    <div className="flex flex-col min-h-screen">
      {/* Hero Section */}
      <section className="relative bg-gradient-to-b from-white to-blue-50 dark:from-gray-900 dark:to-gray-800 py-20">
        <div className="container px-4 mx-auto">
          <div className="flex flex-col lg:flex-row items-center">
            <div className="lg:w-1/2 text-center lg:text-left mb-10 lg:mb-0">
              <h1 className="text-4xl md:text-5xl font-bold tracking-tight mb-4">
                <span className="text-pix">PIX</span> Credit Nexus
              </h1>
              <p className="text-xl text-gray-600 dark:text-gray-300 mb-8">
                Sistema completo para gerenciamento de pagamentos PIX, consultas de CNPJ e controle de créditos.
              </p>
              <div className="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                <Button
                  onClick={() => navigate("/login")}
                  size="lg"
                  className="pix-gradient text-white hover:opacity-90"
                >
                  Entrar
                </Button>
                <Button
                  onClick={() => navigate("/registro")}
                  variant="outline"
                  size="lg"
                >
                  Criar Conta
                </Button>
              </div>
            </div>
            <div className="lg:w-1/2 flex justify-center lg:justify-end">
              <div className="w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <div className="p-6">
                  <div className="text-center">
                    <div className="w-16 h-16 bg-pix-light rounded-full flex items-center justify-center mx-auto mb-4">
                      <CreditCard className="h-8 w-8 text-pix" />
                    </div>
                    <h3 className="text-xl font-semibold mb-2">Pagamento Rápido e Seguro</h3>
                    <p className="text-gray-600 dark:text-gray-300 mb-4">
                      Use PIX para recarregar seus créditos instantaneamente
                    </p>
                  </div>
                  <div className="mt-4 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                    <div className="flex items-center justify-between mb-2">
                      <span className="text-sm text-gray-500">Seu Saldo</span>
                      <span className="font-bold">R$ 0,00</span>
                    </div>
                    <Button className="w-full pix-gradient text-white" disabled>
                      Adicionar Créditos
                    </Button>
                    <p className="text-xs text-center mt-2 text-gray-500">
                      Faça login para acessar sua conta
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* Features */}
      <section className="py-16 bg-white dark:bg-gray-900">
        <div className="container px-4 mx-auto">
          <h2 className="text-3xl font-bold text-center mb-12">Recursos Principais</h2>
          <div className="grid md:grid-cols-3 gap-8">
            {features.map((feature, index) => (
              <div
                key={index}
                className="border rounded-xl p-6 transition-all duration-200 hover:shadow-md"
              >
                <div className={`w-12 h-12 ${feature.color} rounded-lg flex items-center justify-center mb-4`}>
                  <feature.icon className="h-6 w-6" />
                </div>
                <h3 className="text-xl font-semibold mb-2">{feature.title}</h3>
                <p className="text-gray-600 dark:text-gray-300">
                  {feature.description}
                </p>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* CTA */}
      <section className="py-16 bg-gray-50 dark:bg-gray-800">
        <div className="container px-4 mx-auto text-center">
          <h2 className="text-3xl font-bold mb-4">Comece a Usar Agora</h2>
          <p className="text-xl text-gray-600 dark:text-gray-300 mb-8 max-w-2xl mx-auto">
            Cadastre-se gratuitamente e aproveite todos os recursos do PIX Credit Nexus
          </p>
          <Button
            onClick={() => navigate("/registro")}
            size="lg"
            className="pix-gradient text-white hover:opacity-90"
          >
            Criar Conta Grátis
          </Button>
        </div>
      </section>

      {/* Footer */}
      <footer className="border-t py-8 mt-auto">
        <div className="container px-4 mx-auto">
          <div className="flex flex-col md:flex-row justify-between items-center">
            <div className="mb-4 md:mb-0">
              <p className="text-sm text-gray-500">
                &copy; {new Date().getFullYear()} PIX Credit Nexus. Todos os direitos reservados.
              </p>
            </div>
            <div className="flex space-x-4">
              <Button variant="ghost" size="sm">
                Termos de Uso
              </Button>
              <Button variant="ghost" size="sm">
                Privacidade
              </Button>
              <Button variant="ghost" size="sm">
                Contato
              </Button>
            </div>
          </div>
        </div>
      </footer>
    </div>
  );
};

export default Index;
