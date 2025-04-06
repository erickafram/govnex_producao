
import React, { useState } from "react";
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
  Phone,
  ChevronRight,
  CheckCircle,
  Users,
  BarChart,
  ChartPie,
  Clock
} from "lucide-react";
import {
  NavigationMenu,
  NavigationMenuContent,
  NavigationMenuItem,
  NavigationMenuLink,
  NavigationMenuList,
  NavigationMenuTrigger,
  navigationMenuTriggerStyle,
} from "@/components/ui/navigation-menu";
import { Separator } from "@/components/ui/separator";
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/components/ui/dialog";

const Index = () => {
  const navigate = useNavigate();
  const { user } = useAuth();
  const [infovisaOpen, setInfovisaOpen] = useState(false);
  const [apiCnpjOpen, setApiCnpjOpen] = useState(false);

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
    {
      title: "Segurança",
      description: "Protegemos seus dados com as mais modernas técnicas de criptografia e segurança.",
      icon: Shield,
      color: "bg-red-600 text-white",
    },
    {
      title: "Suporte Especializado",
      description: "Equipe técnica pronta para ajudar em todas as etapas de implementação e uso.",
      icon: Users,
      color: "bg-indigo-600 text-white",
    },
    {
      title: "Análise de Dados",
      description: "Ferramentas analíticas para transformar dados em insights estratégicos.",
      icon: ChartPie,
      color: "bg-amber-600 text-white",
    },
  ];

  const products = [
    {
      title: "Sistema Infovisa",
      description: "Gestão completa e eficiente da Vigilância Sanitária para municípios.",
      icon: Database,
      color: "bg-blue-600",
      dialog: "infovisa",
    },
    {
      title: "API Consulta CNPJ",
      description: "Serviço de API RESTful para consulta de dados cadastrais de CNPJ.",
      icon: FileSearch,
      color: "bg-green-600",
      dialog: "cnpj",
    },
  ];

  const testimonials = [
    {
      quote: "O Sistema Infovisa revolucionou nossa gestão na Vigilância Sanitária. Processos que demoravam semanas agora são concluídos em dias.",
      author: "Maria Silva",
      role: "Diretora de Vigilância Sanitária",
      organization: "Prefeitura de Palmas-TO",
    },
    {
      quote: "A API de consulta CNPJ da GovNex é extremamente confiável e rápida. Integramos aos nossos sistemas e melhoramos significativamente nosso processo de cadastro de fornecedores.",
      author: "Carlos Mendes",
      role: "CTO",
      organization: "TechSolutions Ltda",
    },
    {
      quote: "O suporte técnico da GovNex é excepcional. Sempre respondem rapidamente e resolvem qualquer problema que encontramos.",
      author: "Ana Oliveira",
      role: "Coordenadora de TI",
      organization: "Secretaria de Saúde de Gurupi-TO",
    },
  ];

  return (
    <div className="flex flex-col min-h-screen">
      {/* Navigation/Header */}
      <header className="border-b bg-white dark:bg-gray-900 sticky top-0 z-40">
        <div className="container mx-auto px-4 py-3 flex justify-between items-center">
          <div className="flex items-center">
            <h1 className="text-xl font-bold">
              <span className="text-blue-600">Gov</span>Nex
            </h1>
          </div>
          
          <div className="hidden md:flex items-center space-x-6">
            <NavigationMenu>
              <NavigationMenuList>
                <NavigationMenuItem>
                  <NavigationMenuTrigger>Soluções</NavigationMenuTrigger>
                  <NavigationMenuContent>
                    <ul className="grid gap-3 p-4 w-[400px] md:w-[500px] lg:w-[600px] grid-cols-2">
                      {products.map((product) => (
                        <li key={product.title} className="row-span-3">
                          <NavigationMenuLink asChild>
                            <a
                              className="flex h-full w-full select-none flex-col justify-end rounded-md bg-gradient-to-b from-muted/50 to-muted p-4 no-underline outline-none focus:shadow-md"
                              href="#products"
                            >
                              <div className={`w-12 h-12 ${product.color.replace("bg-", "text-")} mb-2`}>
                                <product.icon className="h-12 w-12" />
                              </div>
                              <div className="mb-2 mt-4 text-lg font-medium">
                                {product.title}
                              </div>
                              <p className="text-sm leading-tight text-muted-foreground">
                                {product.description}
                              </p>
                            </a>
                          </NavigationMenuLink>
                        </li>
                      ))}
                    </ul>
                  </NavigationMenuContent>
                </NavigationMenuItem>
                <NavigationMenuItem>
                  <NavigationMenuLink className={navigationMenuTriggerStyle()} href="#about">
                    Sobre Nós
                  </NavigationMenuLink>
                </NavigationMenuItem>
                <NavigationMenuItem>
                  <NavigationMenuLink className={navigationMenuTriggerStyle()} href="#contact">
                    Contato
                  </NavigationMenuLink>
                </NavigationMenuItem>
              </NavigationMenuList>
            </NavigationMenu>
          </div>
          
          <div className="flex items-center space-x-2">
            <Button 
              onClick={() => navigate("/login")} 
              variant="outline" 
              size="sm" 
              className="hidden sm:inline-flex"
            >
              Entrar
            </Button>
            <Button 
              onClick={() => navigate("/registro")} 
              size="sm" 
              className="bg-blue-600 hover:bg-blue-700"
            >
              Cadastrar
            </Button>
          </div>
        </div>
      </header>

      {/* Hero Section */}
      <section className="relative bg-gradient-to-b from-white to-blue-50 dark:from-gray-900 dark:to-gray-800 py-20">
        <div className="container px-4 mx-auto">
          <div className="flex flex-col lg:flex-row items-center">
            <div className="lg:w-1/2 text-center lg:text-left mb-10 lg:mb-0">
              <h1 className="text-4xl md:text-5xl font-bold tracking-tight mb-4 animate-fade-in">
                Bem-vindo à <span className="text-blue-600">GovNex</span>
              </h1>
              <p className="text-xl text-gray-600 dark:text-gray-300 mb-8 animate-fade-in" style={{animationDelay: "0.2s"}}>
                Tecnologia e inovação para transformar a gestão pública e privada.
              </p>
              <div className="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start animate-fade-in" style={{animationDelay: "0.4s"}}>
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
            <div className="lg:w-1/2 flex justify-center lg:justify-end animate-fade-in" style={{animationDelay: "0.6s"}}>
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

        {/* Wave decoration */}
        <div className="absolute bottom-0 left-0 right-0 h-16 bg-white dark:bg-gray-900" style={{ clipPath: "polygon(0 100%, 100% 100%, 100% 0, 0 100%)" }}></div>
      </section>

      {/* About Section */}
      <section id="about" className="py-16 bg-white dark:bg-gray-900">
        <div className="container px-4 mx-auto">
          <h2 className="text-3xl font-bold text-center mb-12">Sobre a GovNex</h2>
          <div className="max-w-3xl mx-auto text-center">
            <p className="text-lg text-gray-600 dark:text-gray-300 mb-8">
              Somos especializados em desenvolver soluções tecnológicas inovadoras para a gestão pública e privada. 
              Nosso compromisso é oferecer ferramentas eficientes, seguras e transparentes para transformar a administração.
            </p>
            <p className="text-lg text-gray-600 dark:text-gray-300 mb-8">
              Fundada em 2020, a GovNex nasceu da visão de transformar digitalmente o setor público brasileiro, 
              tornando os processos mais ágeis, transparentes e acessíveis. Nossa equipe combina expertise em 
              tecnologia com profundo conhecimento das necessidades e desafios da administração pública.
            </p>
          </div>
          
          <div className="grid md:grid-cols-3 gap-8 mt-12">
            {features.map((feature, index) => (
              <div
                key={index}
                className="border rounded-xl p-6 transition-all duration-300 hover:shadow-md hover-scale"
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
      <section id="products" className="py-16 bg-gray-50 dark:bg-gray-800">
        <div className="container px-4 mx-auto">
          <h2 className="text-3xl font-bold text-center mb-12">Nossos Produtos</h2>
          
          <div className="grid md:grid-cols-2 gap-8">
            {products.map((product, index) => (
              <div
                key={index}
                className="bg-white dark:bg-gray-900 rounded-xl shadow-md overflow-hidden hover-scale"
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
                  <Dialog open={product.dialog === "infovisa" ? infovisaOpen : apiCnpjOpen} 
                         onOpenChange={product.dialog === "infovisa" ? setInfovisaOpen : setApiCnpjOpen}>
                    <DialogTrigger asChild>
                      <Button
                        variant="outline"
                        className="mt-2"
                      >
                        Saiba Mais <ArrowRight className="h-4 w-4 ml-1" />
                      </Button>
                    </DialogTrigger>
                    <DialogContent className="max-w-5xl max-h-[90vh] overflow-y-auto">
                      <DialogHeader>
                        <DialogTitle className="text-2xl">{product.title}</DialogTitle>
                        <DialogDescription>
                          {product.dialog === "infovisa" ? (
                            <div className="mt-4 space-y-4">
                              <p className="text-base">
                                O Sistema Infovisa é uma plataforma robusta e integrada, projetada para modernizar e otimizar a gestão dos processos de vigilância sanitária em municípios, atendendo às necessidades do setor público e facilitando a interação com o setor privado.
                              </p>
                              
                              <div>
                                <h3 className="text-lg font-semibold mb-2">Para a Vigilância Sanitária (Setor Público):</h3>
                                <ul className="list-disc pl-5 space-y-1">
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Monitoramento Completo:</strong> Acompanhamento detalhado de inspeções, fiscalizações, autos de infração e outras ações.</span>
                                  </li>
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Gestão de Processos:</strong> Controle eficiente de licenciamentos, análise de projetos arquitetônicos, processos administrativos e tratamento de denúncias.</span>
                                  </li>
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Ordens de Serviço Inteligentes:</strong> Geração, distribuição e acompanhamento de ordens de serviço vinculadas a estabelecimentos e atividades específicas.</span>
                                  </li>
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Avaliação de Produtividade:</strong> Sistema de pontuação para monitorar e incentivar a produtividade dos fiscais e colaboradores.</span>
                                  </li>
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Relatórios Gerenciais:</strong> Geração de relatórios customizáveis para análise de dados e suporte à tomada de decisão.</span>
                                  </li>
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Alertas e Notificações:</strong> Sistema de alertas para prazos de licenças, pendências e tarefas importantes.</span>
                                  </li>
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Assinatura Digital Integrada:</strong> Facilita a assinatura eletrônica de documentos, garantindo agilidade e validade jurídica.</span>
                                  </li>
                                </ul>
                              </div>
                              
                              <div>
                                <h3 className="text-lg font-semibold mb-2">Para Empresas e Cidadãos (Setor Privado):</h3>
                                <ul className="list-disc pl-5 space-y-1">
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Portal do Cidadão/Empresa:</strong> Interface online para abertura e acompanhamento de processos (licenciamento, projetos, denúncias).</span>
                                  </li>
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Cadastro Integrado via API:</strong> Possibilidade de integração com sistemas municipais para cadastro automático de estabelecimentos e pessoas físicas/jurídicas.</span>
                                  </li>
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Acompanhamento Online:</strong> Consulta do status dos processos em tempo real, aumentando a transparência.</span>
                                  </li>
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Comunicação Facilitada:</strong> Canal direto para envio de documentos e comunicação com a vigilância.</span>
                                  </li>
                                </ul>
                              </div>
                              
                              <div>
                                <h3 className="text-lg font-semibold mb-2">Benefícios Gerais:</h3>
                                <ul className="list-disc pl-5 space-y-1">
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Agilidade e Eficiência:</strong> Redução da burocracia e do tempo de tramitação dos processos.</span>
                                  </li>
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Transparência:</strong> Maior clareza nos procedimentos e fácil acesso à informação.</span>
                                  </li>
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Padronização:</strong> Uniformização dos fluxos de trabalho e dos documentos gerados.</span>
                                  </li>
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Segurança da Informação:</strong> Armazenamento seguro e controle de acesso aos dados.</span>
                                  </li>
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Integração:</strong> Capacidade de se conectar a outros sistemas governamentais (Tributário, Protocolo, etc.).</span>
                                  </li>
                                </ul>
                              </div>
                            </div>
                          ) : (
                            <div className="mt-4 space-y-4">
                              <p className="text-base">
                                A API Consulta CNPJ da GovNex oferece uma solução rápida, confiável e fácil de integrar para acessar informações cadastrais completas de empresas brasileiras diretamente da base de dados oficial.
                              </p>
                              
                              <div>
                                <h3 className="text-lg font-semibold mb-2">Funcionalidades e Vantagens:</h3>
                                <ul className="list-disc pl-5 space-y-1">
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Consulta Abrangente:</strong> Acesso a dados como Razão Social, Nome Fantasia, CNPJ formatado, Endereço completo (CEP, Logradouro, Número, Complemento, Bairro, Município, UF), Situação Cadastral, Data de Abertura, CNAE Principal e Secundários, Natureza Jurídica, Porte da Empresa, Capital Social, Quadro Societário (QSA), Telefones e E-mail (quando disponíveis).</span>
                                  </li>
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Dados Atualizados:</strong> Informações obtidas diretamente de fontes oficiais, garantindo alta precisão e atualização.</span>
                                  </li>
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Formato RESTful JSON:</strong> Resposta padronizada em JSON, facilitando a integração com qualquer linguagem de programação e sistema.</span>
                                  </li>
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Documentação Clara:</strong> Documentação completa com exemplos de requisição/resposta, códigos de status e guia de integração.</span>
                                  </li>
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Alta Disponibilidade e Performance:</strong> Infraestrutura robusta para garantir que a API esteja sempre disponível e responda rapidamente.</span>
                                  </li>
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Segurança:</strong> Comunicação via HTTPS (SSL/TLS) para proteger a troca de informações.</span>
                                  </li>
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Planos Flexíveis:</strong> Opções de planos de assinatura ou pacotes de consulta para atender diferentes demandas.</span>
                                  </li>
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Suporte Especializado:</strong> Equipe técnica disponível para auxiliar em dúvidas e no processo de integração.</span>
                                  </li>
                                </ul>
                              </div>
                              
                              <div>
                                <h3 className="text-lg font-semibold mb-2">Casos de Uso Comuns:</h3>
                                <ul className="list-disc pl-5 space-y-1">
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Validação Cadastral:</strong> Confirmação e enriquecimento de dados de clientes e fornecedores.</span>
                                  </li>
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Automação de Onboarding:</strong> Preenchimento automático de informações em processos de cadastro.</span>
                                  </li>
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Análise de Crédito e Risco:</strong> Obtenção de informações essenciais para avaliação de crédito e compliance (KYC/KYB).</span>
                                  </li>
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Inteligência de Mercado:</strong> Coleta de dados para prospecção, análise de concorrência e estudos de mercado.</span>
                                  </li>
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Sistemas de CRM e ERP:</strong> Integração para manter a base de dados de empresas atualizada.</span>
                                  </li>
                                  <li className="flex items-baseline">
                                    <CheckCircle className="h-4 w-4 text-green-500 mr-2 flex-shrink-0" />
                                    <span><strong>Plataformas de E-commerce:</strong> Verificação de dados de vendedores e compradores PJ.</span>
                                  </li>
                                </ul>
                              </div>
                            </div>
                          )}
                        </DialogDescription>
                      </DialogHeader>
                    </DialogContent>
                  </Dialog>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Testimonials */}
      <section className="py-16 bg-white dark:bg-gray-900">
        <div className="container px-4 mx-auto">
          <h2 className="text-3xl font-bold text-center mb-12">O que Nossos Clientes Dizem</h2>
          
          <div className="grid md:grid-cols-3 gap-8">
            {testimonials.map((testimonial, index) => (
              <div key={index} className="bg-blue-50 dark:bg-gray-800 p-6 rounded-xl relative hover-scale">
                <div className="absolute -top-3 -left-3 text-4xl text-blue-500">"</div>
                <p className="text-gray-700 dark:text-gray-300 mb-4 pt-4">
                  {testimonial.quote}
                </p>
                <div className="flex items-center">
                  <div className="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                    <Users className="h-5 w-5 text-blue-600" />
                  </div>
                  <div>
                    <p className="font-medium">{testimonial.author}</p>
                    <p className="text-sm text-gray-500 dark:text-gray-400">{testimonial.role}, {testimonial.organization}</p>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* Numbers/Stats Section */}
      <section className="py-16 bg-blue-600 text-white">
        <div className="container px-4 mx-auto">
          <div className="flex flex-col md:flex-row items-center justify-around text-center">
            <div className="mb-8 md:mb-0">
              <div className="text-4xl font-bold mb-2">100+</div>
              <div className="text-lg">Municípios Atendidos</div>
            </div>
            <div className="mb-8 md:mb-0">
              <div className="text-4xl font-bold mb-2">5.000+</div>
              <div className="text-lg">Consultas de CNPJ Diárias</div>
            </div>
            <div className="mb-8 md:mb-0">
              <div className="text-4xl font-bold mb-2">99.9%</div>
              <div className="text-lg">Disponibilidade</div>
            </div>
            <div>
              <div className="text-4xl font-bold mb-2">24/7</div>
              <div className="text-lg">Suporte Técnico</div>
            </div>
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
              className="inline-flex items-center justify-center bg-green-500 hover:bg-green-600 text-white font-medium py-3 px-6 rounded-lg hover-scale"
            >
              <MessageCircle className="mr-2 h-5 w-5" />
              Conversar no WhatsApp (62 98101-3083)
            </a>
            
            <div className="mt-12 grid md:grid-cols-3 gap-6 text-center">
              <div className="p-4">
                <Phone className="h-8 w-8 mx-auto text-blue-600 mb-3" />
                <h3 className="text-lg font-semibold mb-1">Telefone</h3>
                <p className="text-gray-600 dark:text-gray-300">(62) 98101-3083</p>
              </div>
              <div className="p-4">
                <MessageCircle className="h-8 w-8 mx-auto text-blue-600 mb-3" />
                <h3 className="text-lg font-semibold mb-1">Email</h3>
                <p className="text-gray-600 dark:text-gray-300">contato@govnex.site</p>
              </div>
              <div className="p-4">
                <Clock className="h-8 w-8 mx-auto text-blue-600 mb-3" />
                <h3 className="text-lg font-semibold mb-1">Horário de Atendimento</h3>
                <p className="text-gray-600 dark:text-gray-300">Seg - Sex: 8h às 18h</p>
              </div>
            </div>
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
