import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import { VitePWA } from 'vite-plugin-pwa'

export default defineConfig({
  plugins: [
    vue(),
    tailwindcss(),
    VitePWA({
      registerType: 'autoUpdate',
      includeAssets: ['favicon.svg', 'icons/*.png', 'icons/*.svg'],
      manifest: {
        id: '/',
        name: 'Storely - Votre Vitrine Digitale',
        short_name: 'Storely',
        description: 'Créez et gérez votre boutique en ligne. Produits, commandes, clients — tout dans votre poche.',
        theme_color: '#FF6B2C',
        background_color: '#0A0A0F',
        display: 'standalone',
        orientation: 'portrait',
        scope: '/',
        start_url: '/?source=pwa',
        lang: 'fr',
        dir: 'ltr',
        categories: ['business', 'shopping', 'productivity'],
        icons: [
          { src: '/icons/icon-72.png', sizes: '72x72', type: 'image/png' },
          { src: '/icons/icon-96.png', sizes: '96x96', type: 'image/png' },
          { src: '/icons/icon-128.png', sizes: '128x128', type: 'image/png' },
          { src: '/icons/icon-144.png', sizes: '144x144', type: 'image/png' },
          { src: '/icons/icon-152.png', sizes: '152x152', type: 'image/png' },
          { src: '/icons/icon-192.png', sizes: '192x192', type: 'image/png' },
          { src: '/icons/icon-384.png', sizes: '384x384', type: 'image/png' },
          { src: '/icons/icon-512.png', sizes: '512x512', type: 'image/png' },
          { src: '/icons/icon-maskable-192.png', sizes: '192x192', type: 'image/png', purpose: 'maskable' },
          { src: '/icons/icon-maskable-512.png', sizes: '512x512', type: 'image/png', purpose: 'maskable' },
        ],
        shortcuts: [
          {
            name: 'Mon Dashboard',
            short_name: 'Dashboard',
            url: '/dashboard?source=shortcut',
            icons: [{ src: '/icons/icon-96.png', sizes: '96x96' }],
          },
          {
            name: 'Mes Produits',
            short_name: 'Produits',
            url: '/dashboard/products?source=shortcut',
            icons: [{ src: '/icons/icon-96.png', sizes: '96x96' }],
          },
          {
            name: 'Mes Commandes',
            short_name: 'Commandes',
            url: '/dashboard/orders?source=shortcut',
            icons: [{ src: '/icons/icon-96.png', sizes: '96x96' }],
          },
          {
            name: 'Explorer',
            short_name: 'Marketplace',
            url: '/marketplace?source=shortcut',
            icons: [{ src: '/icons/icon-96.png', sizes: '96x96' }],
          },
        ],
        screenshots: [
          {
            src: '/screenshots/dashboard.png',
            sizes: '1080x1920',
            type: 'image/png',
            form_factor: 'narrow',
            label: 'Dashboard Storely',
          },
        ],
        edge_side_panel: { preferred_width: 400 },
        handle_links: 'preferred',
        launch_handler: { client_mode: 'navigate-existing' },
      },
      workbox: {
        globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2,woff,ttf}'],
        cleanupOutdatedCaches: true,
        skipWaiting: true,
        clientsClaim: true,
        navigationPreload: true,
        runtimeCaching: [
          // API — network first with offline fallback
          {
            urlPattern: /\/api\//,
            handler: 'NetworkFirst',
            options: {
              cacheName: 'api-cache',
              networkTimeoutSeconds: 8,
              expiration: { maxEntries: 200, maxAgeSeconds: 60 * 60 * 24 },
              cacheableResponse: { statuses: [0, 200] },
            }
          },
          // Storage/images — cache first
          {
            urlPattern: /\/storage\//,
            handler: 'CacheFirst',
            options: {
              cacheName: 'storage-cache',
              expiration: { maxEntries: 500, maxAgeSeconds: 60 * 60 * 24 * 30 },
              cacheableResponse: { statuses: [0, 200] },
            }
          },
          // External images
          {
            urlPattern: /\.(?:png|jpg|jpeg|svg|gif|webp|avif)$/,
            handler: 'CacheFirst',
            options: {
              cacheName: 'image-cache',
              expiration: { maxEntries: 300, maxAgeSeconds: 60 * 60 * 24 * 30 },
              cacheableResponse: { statuses: [0, 200] },
            }
          },
          // Google Fonts
          {
            urlPattern: /^https:\/\/fonts\.(googleapis|gstatic)\.com/,
            handler: 'CacheFirst',
            options: {
              cacheName: 'google-fonts',
              expiration: { maxEntries: 30, maxAgeSeconds: 60 * 60 * 24 * 365 },
              cacheableResponse: { statuses: [0, 200] },
            }
          },
        ],
      },
    })
  ],
  server: {
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true
      },
      '/storage': {
        target: 'http://localhost:8000',
        changeOrigin: true
      }
    }
  }
})
