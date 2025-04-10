#!/bin/bash

# Script para preparar o ambiente antes do build Docker

echo "Preparando ambiente para build..."

# Criar arquivo index.js na pasta components/ui
echo "Criando arquivo index.js na pasta components/ui..."
mkdir -p src/components/ui
echo '// Arquivo de índice para evitar erro de build
// Export de componentes comuns
export * from "./button";
export * from "./input";
export * from "./card";
// Adicione outros exports conforme necessário
' > src/components/ui/index.js

# Verificar se o arquivo foi criado
if [ -f src/components/ui/index.js ]; then
  echo "✅ Arquivo index.js criado com sucesso!"
else
  echo "❌ Falha ao criar arquivo index.js!"
  exit 1
fi

echo "Buscando por imports problemáticos..."
# Buscar por imports que usam o diretório ui diretamente
grep -r "from ['\"].*components\/ui['\"];" src/ || echo "Nenhum import problemático encontrado."

echo "Ambiente preparado! Execute docker-compose agora:"
echo "docker-compose -f docker-compose.prod.yml up -d" 