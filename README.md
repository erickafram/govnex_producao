<<<<<<< HEAD
<<<<<<< HEAD
# PIX Credit Nexus

Sistema de gerenciamento de créditos PIX com consulta de CNPJ.

## Configuração

1. Certifique-se de ter o PHP 7.4+ e o MySQL instalados
2. Configure o arquivo `.env` com suas credenciais de banco de dados
3. Importe o arquivo `api/database.sql` para criar a estrutura do banco de dados
4. Execute o script para adicionar usuários de demonstração:
   ```
   php api/add_demo_users.php
   ```

## Executando o projeto

Para iniciar o frontend e o backend juntos:

```
npm run dev:all
```

Isso iniciará:

- Frontend React em: http://localhost:8081
- Backend PHP em: http://localhost:8000

## Credenciais de teste

- **Usuário comum**: user@example.com / password
- **Administrador**: admin@example.com / password

## Estrutura do Projeto

### Frontend (React + TypeScript + Vite)

- `/src` - Código fonte do frontend
  - `/components` - Componentes React reutilizáveis
  - `/context` - Contextos React (AuthContext, etc.)
  - `/hooks` - Hooks personalizados
  - `/pages` - Páginas da aplicação
  - `/services` - Serviços para comunicação com a API
  - `/types` - Definições de tipos TypeScript

### Backend (PHP)

- `/api` - Código fonte do backend
  - `/controllers` - Controladores da API
  - `/models` - Modelos de dados
  - `config.php` - Configuração do banco de dados e utilitários
  - `*.php` - Endpoints da API

## Funcionalidades

- Autenticação de usuários
- Gerenciamento de créditos
- Consulta de CNPJ
- Registro de consultas
- Painel administrativo
=======
# govnex
>>>>>>> 9bbce1bbc22ff7b5a3a206768dc2d074457933e4
=======
# govnex
>>>>>>> bf14345237b6a08b15f0f43e95f1501d1de11738
