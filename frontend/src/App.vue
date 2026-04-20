<script setup>
import { ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import AppNavbar from './components/shared/AppNavbar.vue'
import AppFooter from './components/shared/AppFooter.vue'
import InstallPrompt from './components/shared/InstallPrompt.vue'
import { useSmoothScroll } from './composables/useSmoothScroll'

const route = useRoute()
const showNavbar = ref(true)
const showFooter = ref(true)

// Init Lenis smooth scroll
const { scrollY, scrollProgress } = useSmoothScroll()

watch(route, (r) => {
  const isDashboard = r.path.startsWith('/dashboard')
  const isAdmin = r.path.startsWith('/admin')
  const isStorefront = r.name === 'storefront'
  const isProductPage = r.name === 'product-page'
  const isPaymentCallback = r.name === 'payment-callback'
  const isAuth = r.name === 'login' || r.name === 'register' || r.name === 'google-callback'
  const hideChrome = isDashboard || isAdmin || isStorefront || isProductPage || isPaymentCallback || isAuth
  showNavbar.value = !hideChrome
  showFooter.value = !hideChrome
}, { immediate: true })
</script>

<template>
  <div class="min-h-screen">
    <AppNavbar v-if="showNavbar" />
    <router-view v-slot="{ Component }">
      <transition name="page" mode="out-in">
        <component :is="Component" />
      </transition>
    </router-view>
    <AppFooter v-if="showFooter" />
    <InstallPrompt />
  </div>
</template>
