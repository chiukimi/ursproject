import { fileURLToPath, URL } from 'node:url'
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import vueDevTools from 'vite-plugin-vue-devtools'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    vueDevTools(),
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    },
  },
  server: {
    proxy: {
      // 添加中油API代理
      // '/api/cpc': {
      //   target: 'https://vipmbr.cpc.com.tw',
      //   changeOrigin: true,
      //   rewrite: (path) => path.replace(/^\/api\/cpc/, '/cpcstn'),
      //   secure: false
      // }
      '/air-api': {
        target: 'https://sta.ci.taiwan.gov.tw',
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/air-api/, '')
      }
    }
  }
})