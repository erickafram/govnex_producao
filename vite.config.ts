import { defineConfig, loadEnv } from "vite";
import react from "@vitejs/plugin-react-swc";
import path from "path";
import { componentTagger } from "lovable-tagger";

// https://vitejs.dev/config/
export default defineConfig(({ mode }) => {
  // Carregar variáveis de ambiente com base no modo (development, production)
  const env = loadEnv(mode, process.cwd(), '');

  console.log(`Modo: ${mode}, Variáveis de ambiente carregadas:`, env);
  
  // Determinar se estamos em produção
  const isProd = mode === 'production';
  console.log(`Ambiente: ${isProd ? 'Produção' : 'Desenvolvimento'}`);

  return {
    server: {
      host: "::",
      port: 8081, // Porta para o frontend
      proxy: {
        // Proxy API requests to the PHP server
        '/api': {
          target: isProd ? 'http://localhost:80' : 'http://localhost:80',
          changeOrigin: true,
          secure: false,
          rewrite: (path) => {
            if (isProd) {
              // Em produção, simplesmente remover o prefixo /api
              return path.replace(/^\/api/, '/api');
            } else {
              // Em desenvolvimento, manter o caminho original
              return path.replace(/^\/api/, '/react/govnex_producao/api');
            }
          },
          configure: (proxy, _options) => {
            proxy.on('error', (err, _req, _res) => {
              console.log('proxy error', err);
            });
            proxy.on('proxyReq', (proxyReq, req, _res) => {
              console.log('Enviando requisição para:', req.method, req.url);
            });
            proxy.on('proxyRes', (proxyRes, req, _res) => {
              console.log('Recebendo resposta de:', req.method, req.url, 'status:', proxyRes.statusCode);
            });
          }
        },
        // Proxy para a pasta temp (QR codes)
        '/temp': {
          target: 'http://localhost:80',
          changeOrigin: true,
          secure: false,
          rewrite: (path) => {
            if (isProd) {
              return path.replace(/^\/temp/, '/temp');
            } else {
              return path.replace(/^\/temp/, '/react/govnex_producao/temp');
            }
          }
        }
      }
    },
    plugins: [
      react(),
      mode === 'development' &&
      componentTagger(),
    ].filter(Boolean),
    resolve: {
      alias: {
        "@": path.resolve(__dirname, "./src"),
      },
    },
    build: {
      outDir: 'dist',
      assetsDir: 'assets',
      // Configuração específica para produção
      rollupOptions: {
        output: {
          manualChunks: {
            vendor: ['react', 'react-dom', 'react-router-dom'],
            ui: ['@/components/ui']
          }
        }
      }
    }
  };
});