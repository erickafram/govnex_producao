echo '#!/bin/bash
# Script para iniciar o frontend e o backend

# Diretório do projeto
PROJECT_DIR="/var/www/html/pix-credit-nexus"

# Função para limpar processos ao sair
cleanup() {
  echo "Parando serviços..."
  kill $FRONTEND_PID $BACKEND_PID 2>/dev/null
  exit
}

# Configurar trap para SIGINT (Ctrl+C) e SIGTERM
trap cleanup SIGINT SIGTERM

# Iniciar o servidor PHP
cd $PROJECT_DIR
echo "Iniciando servidor PHP na porta 8000..."
php -S 0.0.0.0:8000 -t api api/router.php > php_server.log 2>&1 &

BACKEND_PID=$!

# Iniciar o frontend
echo "Iniciando frontend na porta 8081..."
cd $PROJECT_DIR
npm run dev > frontend.log 2>&1 &
FRONTEND_PID=$!

echo "Serviços iniciados!"
echo "Frontend PID: $FRONTEND_PID (porta 8081)"
echo "Backend PID: $BACKEND_PID (porta 8000)"
echo "Logs em: php_server.log e frontend.log"
echo "Pressione Ctrl+C para parar os serviços"

# Manter o script rodando
wait $FRONTEND_PID $BACKEND_PID
' > /var/www/html/pix-credit-nexus/start_services.sh

# Tornar o script executável
chmod +x /var/www/html/pix-credit-nexus/start_services.sh