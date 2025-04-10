FROM node:20-alpine as build

WORKDIR /app

COPY package*.json ./
RUN npm ci

# Copia apenas os arquivos necessários
COPY tsconfig*.json ./
COPY vite.config.ts ./
COPY index.html ./
COPY tailwind.config.ts ./
COPY postcss.config.js ./
COPY public ./public
COPY src ./src

# Corrige o problema com o diretório components/ui
RUN find /app/src -type d -name "ui" -exec touch {}/.gitkeep \;
RUN find /app/src -type d -exec ls -la {} \;

# Build com detalhes de debug
RUN npm run build

FROM nginx:alpine
COPY --from=build /app/dist /usr/share/nginx/html
COPY nginx.conf /etc/nginx/conf.d/default.conf
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"] 