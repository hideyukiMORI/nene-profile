import tailwindcss from '@tailwindcss/vite'
import react from '@vitejs/plugin-react'
import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'

export default defineConfig({
  plugins: [react(), tailwindcss()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
      '@tests': fileURLToPath(new URL('./tests', import.meta.url)),
    },
  },
  server: {
    proxy: {
      '/admin': 'http://localhost:8490',
      '/health': 'http://localhost:8490',
    },
  },
  build: {
    // Tier A same-origin: admin SPA builds into the PHP public_html/admin.
    outDir: '../public_html/admin',
    emptyOutDir: true,
  },
})
