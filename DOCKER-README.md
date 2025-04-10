# Executando o GovNext com Docker Compose

## Pré-requisitos
- Docker instalado (https://docs.docker.com/get-docker/)
- Docker Compose instalado (https://docs.docker.com/compose/install/)

## Configuração inicial

1. Clone o repositório do projeto
2. Entre na pasta raiz do projeto onde está localizado o arquivo `docker-compose.yml`

## Como executar

Para iniciar todos os serviços, execute o seguinte comando na pasta raiz do projeto:

```bash
docker-compose up -d
```

Este comando irá:
1. Construir as imagens para o frontend e API
2. Iniciar o banco de dados MySQL
3. Iniciar o servidor da API PHP
4. Iniciar o frontend React
5. Configurar a rede entre todos os serviços

## Acessando a aplicação

- Frontend: http://localhost:8081
- API: http://localhost:8000

## Parando os serviços

Para parar todos os serviços:

```bash
docker-compose down
```

Para parar e remover todos os volumes (isso apagará os dados do banco):

```bash
docker-compose down -v
```

## Visualizando logs

Para ver os logs de todos os serviços:

```bash
docker-compose logs
```

Para acompanhar os logs em tempo real:

```bash
docker-compose logs -f
```

Para ver logs de um serviço específico:

```bash
docker-compose logs frontend
docker-compose logs api
docker-compose logs db
```

## Reconstruir imagens

Se você fizer alterações nos arquivos Dockerfile, será necessário reconstruir as imagens:

```bash
docker-compose build
docker-compose up -d
```

## Problemas comuns

1. **Erro de porta já em uso**: Certifique-se de que as portas 8081, 8000 e 3306 não estão sendo usadas por outros serviços no seu computador.

2. **Erro de conexão com o banco de dados**: O banco de dados pode levar alguns segundos para inicializar completamente. Se ocorrer um erro de conexão, aguarde alguns instantes e tente novamente.

3. **Alterações no código não refletidas**: Para o frontend, você pode precisar reconstruir a imagem com `docker-compose build frontend` após alterar o código. 