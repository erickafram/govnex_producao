/**
 * Funções de utilidade para lidar com chamadas de API
 */

// URLs base para API
const LOCAL_API_URL = '/api';
const PRODUCTION_API_URL = 'https://govnex.site/api';

/**
 * Retorna a URL base da API com base no ambiente
 * @returns URL base da API
 */
export function getApiUrl(endpoint?: string): string {
  const isProduction = window.location.hostname !== 'localhost';
  const baseUrl = isProduction ? PRODUCTION_API_URL : LOCAL_API_URL;
  
  if (endpoint) {
    // Se o endpoint já começar com /, não adicionar outro
    return `${baseUrl}${endpoint.startsWith('/') ? endpoint : `/${endpoint}`}`;
  }
  
  return baseUrl;
}

/**
 * Faz uma requisição à API com tratamento de erros
 * @param endpoint Endpoint da API
 * @param options Opções da requisição
 * @returns Resposta da API
 */
export async function apiRequest<T>(endpoint: string, options?: RequestInit): Promise<T> {
  try {
    const url = getApiUrl(endpoint);
    const token = localStorage.getItem('token');
    
    const headers: HeadersInit = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...options?.headers
    };
    
    // Adicionar token de autenticação se disponível
    if (token) {
      headers['Authorization'] = `Bearer ${token}`;
    }
    
    const response = await fetch(url, {
      ...options,
      headers
    });
    
    // Verificar se a resposta é um JSON válido
    const contentType = response.headers.get('content-type');
    if (contentType && contentType.includes('application/json')) {
      const data = await response.json();
      
      if (!response.ok) {
        throw new Error(data.error || `Erro ${response.status}: ${response.statusText}`);
      }
      
      return data as T;
    } else {
      // Se não for JSON, tratar como texto
      const text = await response.text();
      console.error('Resposta não-JSON recebida:', text);
      throw new Error(`Resposta inválida do servidor: ${response.status} ${response.statusText}`);
    }
  } catch (error) {
    console.error('Erro na requisição à API:', error);
    throw error;
  }
}
