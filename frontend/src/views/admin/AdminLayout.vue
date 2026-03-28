<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '../../composables/useAuth'
import { useTheme } from '../../composables/useTheme'

const route = useRoute()
const router = useRouter()
const { user, isAuthenticated, logout } = useAuth()
const { theme, toggle } = useTheme()

const sidebarOpen = ref(false)

const navItems = [
  { icon: 'dashboard', label: 'Vue d\'ensemble', to: '/admin' },
  { icon: 'users', label: 'Utilisateurs', to: '/admin/users' },
  { icon: 'shops', label: 'Boutiques', to: '/admin/shops' },
  { icon: 'orders', label: 'Commandes', to: '/admin/orders' },
  { icon: 'payments', label: 'Paiements', to: '/admin/payments' },
  { icon: 'subscriptions', label: 'Abonnements', to: '/admin/subscriptions' },
  { icon: 'badges', label: 'Badges', to: '/admin/badges' },
  { icon: 'promos', label: 'Promotions', to: '/admin/promotions' },
  { icon: 'referrals', label: 'Parrainages', to: '/admin/referrals' },
  { icon: 'settings', label: 'Paramètres', to: '/admin/settings' },
  { icon: 'logs', label: 'Logs', to: '/admin/logs' },
]

const isActive = (item) => {
  if (item.to === '/admin') return route.path === '/admin'
  return route.path.startsWith(item.to)
}

onMounted(() => {
  if (!isAuthenticated.value) router.push('/login')
})

watch(() => route.path, () => { sidebarOpen.value = false })

const handleLogout = async () => {
  await logout()
}
</script>

<template>
  <div class="min-h-screen flex" style="background: var(--bg-primary); color: var(--text-primary)">
    <!-- Sidebar -->
    <aside
      class="hidden md:flex flex-col w-60 flex-shrink-0 border-r fixed top-0 left-0 h-screen overflow-y-auto z-30"
      style="background: var(--bg-secondary); border-color: var(--border-color)"
    >
      <div class="p-4 border-b" style="border-color: var(--border-color)">
        <router-link to="/admin" class="flex items-center gap-2 no-underline">
          <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white font-bold text-xs" style="background: linear-gradient(135deg, #EF4444, #DC2626)">
            SA
          </div>
          <div>
            <p class="text-sm font-bold" style="color: var(--text-primary)">Super Admin</p>
            <p class="text-[10px] opacity-40">{{ user?.name }}</p>
          </div>
        </router-link>
      </div>

      <nav class="flex-1 p-2 space-y-0.5">
        <router-link
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm transition-all no-underline"
          :class="isActive(item) ? 'bg-red-500/10 text-red-500 font-medium' : 'opacity-60 hover:opacity-100 hover:bg-white/5'"
          :style="!isActive(item) ? { color: 'var(--text-primary)' } : {}"
        >
          <!-- Icons -->
          <svg v-if="item.icon === 'dashboard'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1" stroke-width="2"/><rect x="14" y="3" width="7" height="7" rx="1" stroke-width="2"/><rect x="3" y="14" width="7" height="7" rx="1" stroke-width="2"/><rect x="14" y="14" width="7" height="7" rx="1" stroke-width="2"/></svg>
          <svg v-if="item.icon === 'users'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
          <svg v-if="item.icon === 'shops'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
          <svg v-if="item.icon === 'orders'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
          <svg v-if="item.icon === 'payments'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
          <svg v-if="item.icon === 'subscriptions'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
          <svg v-if="item.icon === 'badges'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M12 15l-3.5 2 .67-3.89L6 10.11l3.94-.34L12 6l2.06 3.77 3.94.34-3.17 3L15.5 17z"/></svg>
          <svg v-if="item.icon === 'promos'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
          <svg v-if="item.icon === 'referrals'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
          <svg v-if="item.icon === 'settings'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
          <svg v-if="item.icon === 'logs'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
          <span>{{ item.label }}</span>
        </router-link>
      </nav>

      <div class="p-2 border-t space-y-1" style="border-color: var(--border-color)">
        <button @click="toggle" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm w-full transition-all opacity-60 hover:opacity-100" style="color: var(--text-primary)">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
          {{ theme === 'dark' ? 'Mode clair' : 'Mode sombre' }}
        </button>
        <router-link to="/dashboard" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm w-full transition-all opacity-60 hover:opacity-100 no-underline" style="color: var(--text-primary)">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M15 19l-7-7 7-7"/></svg>
          Retour Dashboard
        </router-link>
        <button @click="handleLogout" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm w-full transition-all text-red-500 hover:bg-red-500/10">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
          Déconnexion
        </button>
      </div>
    </aside>

    <!-- Mobile header -->
    <div class="md:hidden fixed top-0 left-0 right-0 h-14 flex items-center justify-between px-4 border-b z-40" style="background: var(--bg-secondary); border-color: var(--border-color)">
      <button @click="sidebarOpen = !sidebarOpen">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <span class="text-sm font-bold text-red-500">Super Admin</span>
      <button @click="toggle">
        <svg class="w-5 h-5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
      </button>
    </div>

    <!-- Mobile sidebar overlay -->
    <transition name="fade">
      <div v-if="sidebarOpen" @click="sidebarOpen = false" class="md:hidden fixed inset-0 bg-black/50 z-40" />
    </transition>
    <transition name="slide-left">
      <aside v-if="sidebarOpen" class="md:hidden fixed top-0 left-0 h-screen w-64 border-r z-50 overflow-y-auto" style="background: var(--bg-secondary); border-color: var(--border-color)">
        <div class="p-4 border-b flex items-center justify-between" style="border-color: var(--border-color)">
          <span class="text-sm font-bold text-red-500">Super Admin</span>
          <button @click="sidebarOpen = false">
            <svg class="w-5 h-5 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>
        <nav class="p-2 space-y-0.5">
          <router-link
            v-for="item in navItems"
            :key="item.to"
            :to="item.to"
            @click="sidebarOpen = false"
            class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm no-underline"
            :class="isActive(item) ? 'bg-red-500/10 text-red-500 font-medium' : 'opacity-60'"
            :style="!isActive(item) ? { color: 'var(--text-primary)' } : {}"
          >
            {{ item.label }}
          </router-link>
        </nav>
      </aside>
    </transition>

    <!-- Main content -->
    <main class="flex-1 md:ml-60 min-h-screen pt-14 md:pt-0">
      <div class="p-4 md:p-6 lg:p-8">
        <router-view />
      </div>
    </main>
  </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
.slide-left-enter-active, .slide-left-leave-active { transition: transform 0.3s ease; }
.slide-left-enter-from, .slide-left-leave-to { transform: translateX(-100%); }
</style>
