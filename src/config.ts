// Configurações da aplicação
export const CONFIG = {
  itemsPerPage: 10,
  defaultCreditAmount: 50,
  cors: {
    origin: '*',
    methods: 'GET,HEAD,PUT,PATCH,POST,DELETE',
    preflightContinue: false,
    optionsSuccessStatus: 200,
  },
};

// Definição de tipo para import.meta.env do Vite
interface ImportMetaEnv {
  VITE_API_BASE_URL?: string;
  [key: string]: any;
}

// Extender o tipo ImportMeta
interface ImportMeta {
  env: ImportMetaEnv;
}

// Determinar a URL base da API com base no ambiente
const getApiBaseUrl = () => {
  // Primeiro, verificar se existe uma variável de ambiente definida
  // @ts-ignore - Ignora o erro de tipagem para o Vite env
  const envApiUrl = import.meta.env?.VITE_API_BASE_URL;
  if (envApiUrl) {
    console.log('Usando URL da API definida no .env:', envApiUrl);
    return envApiUrl;
  }

  // Verificar se estamos em produção (usando o domínio do servidor)
  const isProduction = window.location.hostname === '161.35.60.249' ||
    window.location.hostname === 'govnex.site' ||
    window.location.hostname.endsWith('.govnex.site');

  if (isProduction) {
    // Em produção, usar o caminho absoluto para a API
    console.log('Ambiente de produção detectado, usando URL de produção');
    return 'http://161.35.60.249/govnex_producao/api';
  }

  // Em desenvolvimento, usar o caminho para a API local
  console.log('Ambiente de desenvolvimento detectado, usando caminho local');
  return '/api'; // Simplificando para usar o proxy definido no vite.config.ts
};

// URL base da API
export const API_URL = getApiBaseUrl();
console.log('API_URL configurada:', API_URL);

// Função para obter o caminho completo da API
export const getApiUrl = (endpoint: string) => {
  const baseUrl = API_URL;

  // Se o endpoint já começar com /, remover para evitar barras duplas
  const cleanEndpoint = endpoint.startsWith('/') ? endpoint.substring(1) : endpoint;

  return `${baseUrl}/${cleanEndpoint}`;
};