#!/bin/bash
# Script para iniciar o ambiente de produção com suporte a CORS

# Diretório do projeto
PROJECT_DIR="/var/www/html/pix-credit-nexus"
cd $PROJECT_DIR

# Parar serviços existentes
echo "Parando serviços existentes..."
pkill -f "php -S"
pm2 stop all 2>/dev/null

# Limpar logs antigos
echo "Limpando logs antigos..."
rm -f $PROJECT_DIR/api/cors_log.txt
rm -f $PROJECT_DIR/frontend.log

# Iniciar o servidor PHP com suporte a CORS
echo "Iniciando servidor PHP com suporte a CORS na porta 8000..."
php $PROJECT_DIR/api/cors-server.php > $PROJECT_DIR/php_server.log 2>&1 &
BACKEND_PID=$!

# Aguardar o servidor PHP iniciar
echo "Aguardando o servidor PHP iniciar..."
sleep 2

# Verificar se o servidor PHP está rodando
if ps -p $BACKEND_PID > /dev/null; then
    echo "Servidor PHP iniciado com sucesso (PID: $BACKEND_PID)"
else
    echo "ERRO: Falha ao iniciar o servidor PHP"
    exit 1
fi

# Iniciar o frontend
echo "Iniciando frontend na porta 8082..."
cd $PROJECT_DIR
npm run dev > $PROJECT_DIR/frontend.log 2>&1 &
FRONTEND_PID=$!

# Aguardar o frontend iniciar
echo "Aguardando o frontend iniciar..."
sleep 5

# Verificar se o frontend está rodando
if ps -p $FRONTEND_PID > /dev/null; then
    echo "Frontend iniciado com sucesso (PID: $FRONTEND_PID)"
else
    echo "ERRO: Falha ao iniciar o frontend"
    exit 1
fi

echo ""
echo "=== AMBIENTE DE PRODUÇÃO INICIADO ==="
echo "API: http://161.35.60.249:8000"
echo "Frontend: http://161.35.60.249:8081"
echo ""
echo "Logs:"
echo "- API: $PROJECT_DIR/php_server.log"
echo "- Frontend: $PROJECT_DIR/frontend.log"
echo "- CORS: $PROJECT_DIR/api/cors_log.txt"
echo ""
echo "Para parar os serviços, execute:"
echo "pkill -f 'php -S'; pkill -f 'node'"
echo ""
echo "Pressione Ctrl+C para sair deste script (os serviços continuarão rodando)"

# Manter o script rodando
tail -f $PROJECT_DIR/php_server.log $PROJECT_DIR/frontend.log $PROJECT_DIR/api/cors_log.txt
