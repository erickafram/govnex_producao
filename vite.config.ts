import { defineConfig } from "vite";
import react from "@vitejs/plugin-react-swc";
import path from "path";
import { componentTagger } from "lovable-tagger";

// https://vitejs.dev/config/
export default defineConfig(({ mode }) => ({
  server: {
    host: "::",
    port: 8081,
    proxy: {
      // Proxy API requests to the PHP server
      '/api': {
        target: 'http://localhost/react/govnex/novogovnex/pix-credit-nexus', // Caminho correto para o servidor Apache/PHP
        changeOrigin: true,
        secure: false,
        rewrite: (path) => path.replace(/^\/api/, '/api') // Mantém o prefixo /api
      },
      // Proxy para a pasta temp (QR codes)
      '/temp': {
        target: 'http://localhost/react/govnex/novogovnex/pix-credit-nexus',
        changeOrigin: true,
        secure: false
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
}));
