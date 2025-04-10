#!/bin/bash

echo "=== Inicializando ambiente de produção com Docker ==="

# Executar script de pré-build para resolver o problema do UI
echo "Executando preparação do ambiente..."
chmod +x pre-build.sh
./pre-build.sh

# Parar containers anteriores se existirem
echo "Parando containers anteriores..."
docker-compose -f docker-compose.prod.yml down

# Remover imagens antigas para reconstruí-las
echo "Reconstruindo imagens..."
docker-compose -f docker-compose.prod.yml build --no-cache

# Iniciar os serviços em modo detached
echo "Iniciando serviços..."
docker-compose -f docker-compose.prod.yml up -d

# Verificar status dos containers
echo "Verificando status dos containers..."
docker-compose -f docker-compose.prod.yml ps

echo "========================================"
echo "🚀 Servidor está rodando!"
echo "Frontend: http://localhost:8081"
echo "API: http://localhost:8000"
echo "========================================"

echo "Para ver os logs em tempo real: docker-compose -f docker-compose.prod.yml logs -f" 