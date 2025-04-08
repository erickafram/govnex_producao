import { defineConfig, loadEnv } from "vite";
import react from "@vitejs/plugin-react-swc";
import path from "path";
import { componentTagger } from "lovable-tagger";

// https://vitejs.dev/config/
export default defineConfig(({ mode }) => {
  // Carregar variáveis de ambiente com base no modo (development, production)
  const env = loadEnv(mode, process.cwd(), '');
  
  console.log(`Modo: ${mode}, Variáveis de ambiente carregadas:`, env);
  
  return {
    server: {
      host: "::",
      port: 8081, // Porta para o frontend
      proxy: {
        // Proxy API requests to the PHP server
        '/api': {
          target: 'http://localhost/react/govnex/pix-credit-nexus/api', // Caminho correto para o servidor Apache/PHP
          changeOrigin: true,
          secure: false,
          rewrite: (path) => path.replace(/^\/api/, ''), // Remove o prefixo /api
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
          target: 'http://localhost/react/govnex/pix-credit-nexus/temp',
          changeOrigin: true,
          secure: false,
          rewrite: (path) => path.replace(/^\/temp/, '')
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
