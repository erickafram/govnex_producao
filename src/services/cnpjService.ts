
import { CNPJData } from "@/types";

// Mock CNPJ data for demonstration
const mockCNPJData: Record<string, CNPJData> = {
  "12345678000190": {
    cnpj: "12.345.678/0001-90",
    razaoSocial: "EMPRESA DEMONSTRATIVA LTDA",
    nomeFantasia: "DEMO COMPANY",
    situacao: "ATIVA",
    dataSituacao: "10/01/2010",
    endereco: {
      logradouro: "RUA EXEMPLO",
      numero: "123",
      complemento: "SALA 1",
      cep: "12345-678",
      bairro: "CENTRO",
      municipio: "SÃO PAULO",
      uf: "SP"
    },
    atividadePrincipal: {
      codigo: "62.01-5-01",
      descricao: "DESENVOLVIMENTO DE PROGRAMAS DE COMPUTADOR SOB ENCOMENDA"
    },
    capitalSocial: 100000,
    socios: [
      {
        nome: "JOÃO SILVA",
        qualificacao: "SÓCIO-ADMINISTRADOR",
        pais: "BRASIL"
      },
      {
        nome: "MARIA SANTOS",
        qualificacao: "SÓCIO",
        pais: "BRASIL"
      }
    ]
  },
  "98765432000121": {
    cnpj: "98.765.432/0001-21",
    razaoSocial: "COMERCIO EXEMPLO S.A.",
    nomeFantasia: "LOJA EXEMPLO",
    situacao: "ATIVA",
    dataSituacao: "15/03/2015",
    endereco: {
      logradouro: "AVENIDA COMERCIAL",
      numero: "456",
      complemento: "ANDAR 2",
      cep: "54321-876",
      bairro: "JARDIM EUROPA",
      municipio: "RIO DE JANEIRO",
      uf: "RJ"
    },
    atividadePrincipal: {
      codigo: "47.51-2-01",
      descricao: "COMÉRCIO VAREJISTA ESPECIALIZADO DE EQUIPAMENTOS E SUPRIMENTOS DE INFORMÁTICA"
    },
    capitalSocial: 250000,
    socios: [
      {
        nome: "PEDRO OLIVEIRA",
        qualificacao: "PRESIDENTE",
        pais: "BRASIL"
      }
    ]
  }
};

// Function to normalize CNPJ by removing special characters
const normalizeCNPJ = (cnpj: string): string => {
  return cnpj.replace(/[^\d]/g, "");
};

// Function to validate CNPJ format (simplified)
const validateCNPJ = (cnpj: string): boolean => {
  const normalizedCNPJ = normalizeCNPJ(cnpj);
  return normalizedCNPJ.length === 14;
};

// Function to query CNPJ data
export const queryCNPJ = async (cnpj: string): Promise<CNPJData> => {
  // Validate CNPJ format
  if (!validateCNPJ(cnpj)) {
    throw new Error("CNPJ inválido. Formato esperado: XX.XXX.XXX/XXXX-XX");
  }
  
  // Normalize CNPJ
  const normalizedCNPJ = normalizeCNPJ(cnpj);
  
  // Simulate API delay
  await new Promise(resolve => setTimeout(resolve, 1000));
  
  // Check if we have mock data for this CNPJ
  if (mockCNPJData[normalizedCNPJ]) {
    return mockCNPJData[normalizedCNPJ];
  }
  
  // For demonstration, return a mock response for any valid CNPJ
  if (normalizedCNPJ === "12345678901234") {
    throw new Error("CNPJ não encontrado na base de dados");
  }
  
  // Generate a random response for demonstration
  return {
    cnpj: cnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, "$1.$2.$3/$4-$5"),
    razaoSocial: `EMPRESA ${normalizedCNPJ.substring(0, 5)} LTDA`,
    nomeFantasia: `EMPRESA ${normalizedCNPJ.substring(0, 3)}`,
    situacao: Math.random() > 0.2 ? "ATIVA" : "BAIXADA",
    dataSituacao: "01/01/2020",
    endereco: {
      logradouro: "RUA GERADA AUTOMATICAMENTE",
      numero: String(Math.floor(Math.random() * 1000) + 1),
      complemento: "",
      cep: "00000-000",
      bairro: "CENTRO",
      municipio: "SÃO PAULO",
      uf: "SP"
    },
    atividadePrincipal: {
      codigo: "00.00-0-00",
      descricao: "ATIVIDADE GENÉRICA DE DEMONSTRAÇÃO"
    },
    capitalSocial: Math.floor(Math.random() * 1000000)
  };
};
