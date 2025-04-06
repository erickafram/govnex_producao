
import React from "react";
import { useNavigate } from "react-router-dom";
import { Button } from "@/components/ui/button";
import { useAuth } from "@/context/AuthContext";
import { 
  ArrowRight, 
  Building2, 
  Shield, 
  Lightbulb, 
  BarChart3, 
  FileSearch, 
  MessageCircle,
  Database,
  Phone
} from "lucide-react";

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
      title: "Inovação",
      description: "Utilizamos tecnologia de ponta para desenvolver soluções disruptivas.",
      icon: Lightbulb,
      color: "bg-blue-600 text-white",
    },
    {
      title: "Eficiência",
      description: "Sistemas integrados para otimizar processos e aumentar a produtividade.",
      icon: BarChart3,
      color: "bg-green-600 text-white",
    },
    {
      title: "Transparência",
      description: "Facilitamos o acesso à informação, promovendo clareza e confiança.",
      icon: Shield,
      color: "bg-purple-600 text-white",
    },
  ];

  const products = [
    {
      title: "Sistema Infovisa",
      description: "Gestão completa e eficiente da Vigilância Sanitária para municípios.",
      icon: Database,
      color: "bg-blue-600",
    },
    {
      title: "API Consulta CNPJ",
      description: "Serviço de API RESTful para consulta de dados cadastrais de CNPJ.",
      icon: FileSearch,
      color: "bg-green-600",
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
                Bem-vindo à <span className="text-blue-600">GovNex</span>
              </h1>
              <p className="text-xl text-gray-600 dark:text-gray-300 mb-8">
                Tecnologia e inovação para transformar a gestão pública e privada.
              </p>
              <div className="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                <Button
                  onClick={() => navigate("/login")}
                  size="lg"
                  className="bg-blue-600 text-white hover:bg-blue-700"
                >
                  Entrar
                </Button>
                <Button
                  onClick={() => navigate("/registro")}
                  variant="outline"
                  size="lg"
                >
                  Cadastrar
                </Button>
              </div>
            </div>
            <div className="lg:w-1/2 flex justify-center lg:justify-end">
              <div className="w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
                <div className="p-6">
                  <div className="text-center">
                    <div className="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                      <Building2 className="h-8 w-8 text-blue-600" />
                    </div>
                    <h3 className="text-xl font-semibold mb-2">Soluções para Gestão Pública</h3>
                    <p className="text-gray-600 dark:text-gray-300 mb-4">
                      Conheça nossas ferramentas para transformar a administração pública
                    </p>
                  </div>
                  <div className="mt-4 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                    <Button 
                      onClick={() => {
                        const contactSection = document.getElementById("contact");
                        contactSection?.scrollIntoView({ behavior: "smooth" });
                      }}
                      className="w-full bg-blue-600 text-white hover:bg-blue-700"
                    >
                      Fale Conosco
                    </Button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {/* About Section */}
      <section className="py-16 bg-white dark:bg-gray-900">
        <div className="container px-4 mx-auto">
          <h2 className="text-3xl font-bold text-center mb-12">Sobre a GovNex</h2>
          <div className="max-w-3xl mx-auto text-center">
            <p className="text-lg text-gray-600 dark:text-gray-300 mb-8">
              Somos especializados em desenvolver soluções tecnológicas inovadoras para a gestão pública e privada. 
              Nosso compromisso é oferecer ferramentas eficientes, seguras e transparentes para transformar a administração.
            </p>
          </div>
          
          <div className="grid md:grid-cols-3 gap-8 mt-12">
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

      {/* Products */}
      <section className="py-16 bg-gray-50 dark:bg-gray-800">
        <div className="container px-4 mx-auto">
          <h2 className="text-3xl font-bold text-center mb-12">Nossos Produtos</h2>
          
          <div className="grid md:grid-cols-2 gap-8">
            {products.map((product, index) => (
              <div
                key={index}
                className="bg-white dark:bg-gray-900 rounded-xl shadow-md overflow-hidden"
              >
                <div className={`h-2 ${product.color}`}></div>
                <div className="p-6">
                  <div className="flex items-center mb-4">
                    <div className={`w-10 h-10 rounded-full bg-${product.color.split('-')[1]}-100 flex items-center justify-center mr-3`}>
                      <product.icon className={`h-5 w-5 text-${product.color.split('-')[1]}-600`} />
                    </div>
                    <h3 className="text-xl font-semibold">{product.title}</h3>
                  </div>
                  <p className="text-gray-600 dark:text-gray-300 mb-4">
                    {product.description}
                  </p>
                  <Button
                    variant="outline"
                    className="mt-2"
                    onClick={() => {
                      const contactSection = document.getElementById("contact");
                      contactSection?.scrollIntoView({ behavior: "smooth" });
                    }}
                  >
                    Saiba Mais <ArrowRight className="h-4 w-4 ml-1" />
                  </Button>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Contact */}
      <section id="contact" className="py-16 bg-white dark:bg-gray-900">
        <div className="container px-4 mx-auto">
          <div className="max-w-3xl mx-auto text-center">
            <h2 className="text-3xl font-bold mb-4">Entre em Contato</h2>
            <p className="text-lg text-gray-600 dark:text-gray-300 mb-8">
              Tem alguma dúvida ou quer saber mais sobre nossas soluções? Fale conosco diretamente pelo WhatsApp!
            </p>
            <a 
              href="https://wa.me/5562981013083" 
              target="_blank" 
              rel="noopener noreferrer"
              className="inline-flex items-center justify-center bg-green-500 hover:bg-green-600 text-white font-medium py-3 px-6 rounded-lg"
            >
              <MessageCircle className="mr-2 h-5 w-5" />
              Conversar no WhatsApp (62 98101-3083)
            </a>
          </div>
        </div>
      </section>

      {/* Footer */}
      <footer className="border-t py-8 mt-auto">
        <div className="container px-4 mx-auto">
          <div className="flex flex-col md:flex-row justify-between items-center">
            <div className="mb-4 md:mb-0">
              <p className="text-sm text-gray-500">
                &copy; {new Date().getFullYear()} GovNex. Todos os direitos reservados.
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
