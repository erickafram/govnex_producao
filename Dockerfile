FROM node:20-alpine as build

WORKDIR /app

# Primeiro copiar e instalar dependências
COPY package*.json ./
RUN npm ci

# Copiar o resto dos arquivos do projeto
COPY . .

# Garantir que exista um arquivo index.js na pasta ui para evitar o erro
RUN mkdir -p /app/src/components/ui
RUN echo '// Arquivo de índice para evitar erro de build' > /app/src/components/ui/index.js

# Executar build em modo produção
RUN npm run build

# Etapa de produção - usar nginx para servir o conteúdo estático
FROM nginx:alpine
COPY --from=build /app/dist /usr/share/nginx/html
COPY nginx.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"] 