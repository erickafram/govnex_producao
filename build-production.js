const { execSync } = require('child_process');
const fs = require('fs');
const path = require('path');

console.log('🚀 Iniciando build de produção para o Pix Credit Nexus...');

// Verificar se estamos no diretório correto
if (!fs.existsSync(path.join(process.cwd(), 'package.json'))) {
  console.error('❌ Erro: Este script deve ser executado no diretório raiz do projeto!');
  process.exit(1);
}

try {
  // Verificar se o arquivo .env.production existe
  if (!fs.existsSync(path.join(process.cwd(), '.env.production'))) {
    console.error('❌ Erro: Arquivo .env.production não encontrado!');
    process.exit(1);
  }

  console.log('📦 Instalando dependências...');
  execSync('npm install', { stdio: 'inherit' });

  console.log('🔨 Construindo aplicação para produção...');
  execSync('npm run build', { stdio: 'inherit', env: { ...process.env, NODE_ENV: 'production' } });

  console.log('✅ Build de produção concluído com sucesso!');
  console.log('\nPara implantar a aplicação:');
  console.log('1. Copie o conteúdo da pasta "dist" para o diretório do servidor web');
  console.log('2. Configure o servidor web para servir a aplicação na porta 8081');
  console.log('3. Certifique-se de que os arquivos da API estão disponíveis em http://161.35.60.249/react/govnex/pix-credit-nexus/api');
  console.log('\nPara testar localmente:');
  console.log('npm run preview');
} catch (error) {
  console.error(`❌ Erro durante o build: ${error.message}`);
  process.exit(1);
}
