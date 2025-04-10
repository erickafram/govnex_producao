#!/bin/bash

# Parar containers anteriores se existirem
docker-compose -f docker-compose.prod.yml down

# Remover imagens antigas para reconstruí-las
docker-compose -f docker-compose.prod.yml build --no-cache

# Iniciar os serviços em modo detached
docker-compose -f docker-compose.prod.yml up -d

# Verificar status dos containers
docker-compose -f docker-compose.prod.yml ps

echo "========================================"
echo "Servidor está rodando!"
echo "Frontend: http://localhost:8081"
echo "API: http://localhost:8000"
echo "========================================" 