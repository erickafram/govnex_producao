#!/bin/bash
# Script para iniciar o ambiente de produção

# Diretório do projeto
PROJECT_DIR="/var/www/html/react/govnex/pix-credit-nexus"
cd $PROJECT_DIR

# Parar serviços existentes
echo "Parando serviços existentes..."
pkill -f "php -S" || true
pm2 stop all 2>/dev/null || true

# Limpar logs antigos
echo "Limpando logs antigos..."
rm -f $PROJECT_DIR/api/logs/*.log
rm -f $PROJECT_DIR/frontend.log

# Verificar se o Apache está rodando
echo "Verificando se o Apache está rodando..."
if systemctl is-active --quiet apache2; then
    echo "Apache está rodando"
else
    echo "Iniciando Apache..."
    systemctl start apache2
fi

# Construir a aplicação para produção
echo "Construindo aplicação para produção..."
cd $PROJECT_DIR
NODE_ENV=production npm run build

# Iniciar o frontend com o servidor de produção
echo "Iniciando servidor de produção na porta 8081..."
cd $PROJECT_DIR
pm2 delete pix-credit-nexus-frontend 2>/dev/null || true
pm2 serve dist 8081 --name pix-credit-nexus-frontend --spa

echo ""
echo "=== AMBIENTE DE PRODUÇÃO INICIADO ==="
echo "API: http://161.35.60.249/react/govnex/pix-credit-nexus/api"
echo "Frontend: http://161.35.60.249:8081"
echo "Frontend (domínio): http://govnex.site:8081"
echo ""
echo "Para acessar a aplicação, abra um dos seguintes URLs:"
echo "- http://161.35.60.249:8081"
echo "- http://govnex.site:8081"
echo ""
echo "Para parar os serviços, execute:"
echo "pm2 stop pix-credit-nexus-frontend"
echo ""
echo "Para ver os logs do frontend:"
echo "pm2 logs pix-credit-nexus-frontend"
echo ""
echo "Pressione Ctrl+C para sair deste script (os serviços continuarão rodando)"
