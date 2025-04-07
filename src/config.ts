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

// Determinar a URL base da API com base no ambiente
const getApiBaseUrl = () => {
  // Verificar se estamos em produção (usando o domínio do servidor)
  const isProduction = window.location.hostname === '161.35.60.249' || 
                       window.location.hostname === 'govnex.site' ||
                       window.location.hostname.endsWith('.govnex.site');
  
  if (isProduction) {
    // Em produção, precisamos usar a URL completa para a API
    // Usando a porta 8000 para o backend PHP
    return 'http://161.35.60.249:8000';
  }
  
  // Em desenvolvimento, usar o proxy configurado no Vite
  return '';
};

// URL base da API
export const API_URL = getApiBaseUrl();

// Função para obter o caminho completo da API
export const getApiUrl = (endpoint: string) => {
  const baseUrl = API_URL;
  
  // Se o endpoint já começar com /api, não adicionar novamente
  if (endpoint.startsWith('/api/')) {
    return `${baseUrl}${endpoint}`;
  }
  
  // Caso contrário, adicionar o prefixo /api/
  return `${baseUrl}/api/${endpoint}`;
};
