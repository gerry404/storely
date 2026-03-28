<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useTheme } from '../../composables/useTheme'
import { useAuth } from '../../composables/useAuth'
import { usePlan } from '../../composables/usePlan'
import { shopLogoUrl } from '../../composables/useStorage'
import UpgradeModal from '../../components/dashboard/UpgradeModal.vue'

const route = useRoute()
const router = useRouter()
const { theme, toggle } = useTheme()
const { user, shop, isAuthenticated, isAdmin, logout } = useAuth()
const { isPro, planLabel } = usePlan()

const sidebarOpen = ref(false)
const tabletCollapsed = ref(false)

const navItems = [
  { icon: 'home', label: 'Tableau de bord', to: '/dashboard' },
  { icon: 'bag', label: 'Produits', to: '/dashboard/products' },
  { icon: 'orders', label: 'Commandes', to: '/dashboard/orders' },
  { icon: 'chat', label: 'Messages', to: '/dashboard/chat' },
  { icon: 'promo', label: 'Promotions', to: '/dashboard/promotions' },
  { icon: 'referral', label: 'Parrainage', to: '/dashboard/referrals' },
  { icon: 'palette', label: 'Personnaliser', to: '/dashboard/customize' },
  { icon: 'settings', label: 'Paramètres', to: '/dashboard/settings' },
]

const isActive = (item) => {
  if (item.to === '/dashboard') {
    return route.path === '/dashboard'
  }
  return route.path.startsWith(item.to)
}

const closeMobileSidebar = () => {
  sidebarOpen.value = false
}

const handleLogout = async () => {
  closeMobileSidebar()
  await logout()
}

const shopAvatarLetter = () => {
  return shop.value?.name?.[0] || user.value?.name?.[0] || 'S'
}

const shopLogo = () => {
  return shopLogoUrl(shop.value)
}

const shopDisplayName = () => {
  return shop.value?.name || 'Ma Boutique'
}

const vitrineLink = () => {
  return shop.value?.slug ? `/${shop.value.slug}` : '/dashboard'
}

// Auth guard
onMounted(() => {
  if (!isAuthenticated.value) {
    router.push('/login')
  }
})

// Close mobile sidebar on route change
watch(() => route.path, () => {
  closeMobileSidebar()
})
</script>

<template>
  <div class="min-h-screen flex" style="background: var(--bg-primary)">

    <!-- ====== Desktop / Tablet Sidebar ====== -->
    <aside
      class="hidden md:flex flex-col shrink-0 border-r transition-all duration-300 fixed top-0 left-0 h-screen overflow-y-auto z-30"
      :class="tabletCollapsed ? 'w-16' : 'w-64'"
      :style="{
        background: 'var(--bg-secondary)',
        borderColor: 'var(--border-default)',
      }"
    >
      <!-- Shop header -->
      <div class="p-4 border-b" :style="{ borderColor: 'var(--border-default)' }">
        <router-link to="/dashboard" class="flex items-center gap-3 no-underline" :class="tabletCollapsed ? 'justify-center' : ''">
          <div
            class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand to-brand-amber flex items-center justify-center text-white font-bold text-sm shrink-0 overflow-hidden"
          >
            <img v-if="shopLogo()" :src="shopLogo()" class="w-full h-full object-cover" />
            <span v-else>{{ shopAvatarLetter() }}</span>
          </div>
          <div v-if="!tabletCollapsed" class="min-w-0">
            <p class="text-sm font-semibold truncate" :style="{ color: 'var(--text-primary)' }">{{ shopDisplayName() }}</p>
            <p class="text-xs truncate" :style="{ color: 'var(--text-muted)' }">{{ user?.name || 'Propriétaire' }}</p>
          </div>
        </router-link>
      </div>

      <!-- Collapse toggle -->
      <button
        @click="tabletCollapsed = !tabletCollapsed"
        class="hidden lg:hidden md:flex items-center justify-center w-full py-2 transition-colors"
        :style="{ color: 'var(--text-faint)' }"
      >
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <polyline v-if="tabletCollapsed" points="9 18 15 12 9 6" />
          <polyline v-else points="15 18 9 12 15 6" />
        </svg>
      </button>

      <!-- Navigation -->
      <nav class="flex-1 p-2 space-y-1 overflow-y-auto">
        <router-link
          v-for="item in navItems"
          :key="item.to"
          :to="item.to"
          class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all no-underline"
          :class="[
            isActive(item)
              ? 'bg-brand/10 text-brand'
              : 'hover:bg-[var(--bg-primary)]',
            tabletCollapsed ? 'justify-center px-0' : '',
          ]"
          :style="!isActive(item) ? { color: 'var(--text-muted)' } : {}"
          :title="tabletCollapsed ? item.label : undefined"
        >
          <!-- Icons -->
          <svg v-if="item.icon === 'home'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          <svg v-if="item.icon === 'bag'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
          <svg v-if="item.icon === 'orders'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          <svg v-if="item.icon === 'chat'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
          <svg v-if="item.icon === 'promo'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
          <svg v-if="item.icon === 'referral'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
          <svg v-if="item.icon === 'palette'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r="0.5" fill="currentColor"/><circle cx="17.5" cy="10.5" r="0.5" fill="currentColor"/><circle cx="8.5" cy="7.5" r="0.5" fill="currentColor"/><circle cx="6.5" cy="12" r="0.5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 011.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>
          <svg v-if="item.icon === 'settings'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
          <span v-if="!tabletCollapsed">{{ item.label }}</span>
        </router-link>
      </nav>

      <!-- Sidebar footer -->
      <div class="p-2 border-t space-y-1" :style="{ borderColor: 'var(--border-default)' }">
        <!-- Admin link -->
        <router-link
          v-if="isAdmin"
          to="/admin"
          class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all no-underline"
          :class="tabletCollapsed ? 'justify-center' : ''"
          style="background: linear-gradient(135deg, rgba(239,68,68,0.15), rgba(220,38,38,0.15)); color: #EF4444; border: 1px solid rgba(239,68,68,0.2);"
          :title="tabletCollapsed ? 'Super Admin' : undefined"
        >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          </svg>
          <span v-if="!tabletCollapsed">Super Admin</span>
        </router-link>

        <!-- Upgrade CTA -->
        <router-link
          v-if="!isPro"
          to="/dashboard/upgrade"
          class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all no-underline"
          :class="tabletCollapsed ? 'justify-center' : ''"
          style="background: linear-gradient(135deg, rgba(255,107,44,0.15), rgba(245,158,11,0.15)); color: #FF6B2C; border: 1px solid rgba(255,107,44,0.2);"
          :title="tabletCollapsed ? 'Passer au Pro' : undefined"
        >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
            <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
          </svg>
          <span v-if="!tabletCollapsed">Passer au Pro</span>
        </router-link>

        <!-- Theme toggle -->
        <button
          @click="toggle"
          class="flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm font-medium transition-all"
          :class="tabletCollapsed ? 'justify-center' : ''"
          :style="{ color: 'var(--text-muted)' }"
          :title="tabletCollapsed ? (theme === 'dark' ? 'Mode clair' : 'Mode sombre') : undefined"
        >
          <svg v-if="theme === 'dark'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
          </svg>
          <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
          </svg>
          <span v-if="!tabletCollapsed">{{ theme === 'dark' ? 'Mode clair' : 'Mode sombre' }}</span>
        </button>

        <!-- Voir ma vitrine -->
        <a
          :href="vitrineLink()"
          target="_blank"
          class="flex items-center gap-2 px-3 py-2.5 rounded-xl bg-brand/10 text-brand text-sm font-medium hover:bg-brand/15 transition-all no-underline"
          :class="tabletCollapsed ? 'justify-center' : ''"
          :title="tabletCollapsed ? 'Voir ma vitrine' : undefined"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
          <span v-if="!tabletCollapsed">Voir ma vitrine</span>
        </a>

        <!-- Logout -->
        <button
          @click="handleLogout"
          class="flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm font-medium transition-all hover:bg-red-500/10 hover:text-red-400"
          :class="tabletCollapsed ? 'justify-center' : ''"
          :style="{ color: 'var(--text-muted)' }"
          :title="tabletCollapsed ? 'Déconnexion' : undefined"
        >
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
          <span v-if="!tabletCollapsed">Déconnexion</span>
        </button>
      </div>
    </aside>

    <!-- ====== Mobile Overlay Sidebar ====== -->
    <Teleport to="body">
      <Transition name="overlay">
        <div
          v-if="sidebarOpen"
          class="md:hidden fixed inset-0 z-[60] flex"
        >
          <!-- Backdrop -->
          <div
            class="absolute inset-0 bg-black/60 backdrop-blur-sm"
            @click="closeMobileSidebar"
          />

          <!-- Sidebar panel -->
          <aside
            class="relative w-72 max-w-[80vw] h-full flex flex-col overflow-y-auto"
            :style="{
              background: 'var(--bg-secondary)',
              borderRight: '1px solid var(--border-default)',
            }"
          >
            <!-- Close button -->
            <div class="flex items-center justify-between p-4 border-b" :style="{ borderColor: 'var(--border-default)' }">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-brand to-brand-amber flex items-center justify-center text-white font-bold text-sm overflow-hidden">
                  <img v-if="shopLogo()" :src="shopLogo()" class="w-full h-full object-cover" />
                  <span v-else>{{ shopAvatarLetter() }}</span>
                </div>
                <div class="min-w-0">
                  <p class="text-sm font-semibold truncate" :style="{ color: 'var(--text-primary)' }">{{ shopDisplayName() }}</p>
                  <p class="text-xs truncate" :style="{ color: 'var(--text-muted)' }">{{ user?.name || 'Propriétaire' }}</p>
                </div>
              </div>
              <button
                @click="closeMobileSidebar"
                class="w-8 h-8 flex items-center justify-center rounded-lg transition-colors"
                :style="{ color: 'var(--text-muted)' }"
              >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </div>

            <!-- Nav -->
            <nav class="flex-1 p-2 space-y-1">
              <router-link
                v-for="item in navItems"
                :key="item.to"
                :to="item.to"
                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all no-underline"
                :class="isActive(item) ? 'bg-brand/10 text-brand' : ''"
                :style="!isActive(item) ? { color: 'var(--text-muted)' } : {}"
                @click="closeMobileSidebar"
              >
                <svg v-if="item.icon === 'home'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <svg v-if="item.icon === 'bag'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                <svg v-if="item.icon === 'orders'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
          <svg v-if="item.icon === 'chat'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
          <svg v-if="item.icon === 'promo'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
          <svg v-if="item.icon === 'referral'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                <svg v-if="item.icon === 'palette'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r="0.5" fill="currentColor"/><circle cx="17.5" cy="10.5" r="0.5" fill="currentColor"/><circle cx="8.5" cy="7.5" r="0.5" fill="currentColor"/><circle cx="6.5" cy="12" r="0.5" fill="currentColor"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 011.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.554C21.965 6.012 17.461 2 12 2z"/></svg>
                <svg v-if="item.icon === 'settings'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                {{ item.label }}
              </router-link>
            </nav>

            <!-- Mobile sidebar footer -->
            <div class="p-2 border-t space-y-1" :style="{ borderColor: 'var(--border-default)' }">
              <!-- Upgrade CTA mobile -->
              <router-link
                v-if="!isPro"
                to="/dashboard/upgrade"
                @click="closeMobileSidebar"
                class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all no-underline"
                style="background: linear-gradient(135deg, rgba(255,107,44,0.15), rgba(245,158,11,0.15)); color: #FF6B2C; border: 1px solid rgba(255,107,44,0.2);"
              >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                Passer au Pro
              </router-link>

              <button
                @click="toggle"
                class="flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm font-medium transition-all"
                :style="{ color: 'var(--text-muted)' }"
              >
                <svg v-if="theme === 'dark'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                </svg>
                <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
                </svg>
                {{ theme === 'dark' ? 'Mode clair' : 'Mode sombre' }}
              </button>

              <a
                :href="vitrineLink()"
                target="_blank"
                class="flex items-center gap-2 px-3 py-2.5 rounded-xl bg-brand/10 text-brand text-sm font-medium hover:bg-brand/15 transition-all no-underline"
                @click="closeMobileSidebar"
              >
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                Voir ma vitrine
              </a>

              <button
                @click="handleLogout"
                class="flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-sm font-medium transition-all hover:bg-red-500/10 hover:text-red-400"
                :style="{ color: 'var(--text-muted)' }"
              >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                Déconnexion
              </button>
            </div>
          </aside>
        </div>
      </Transition>
    </Teleport>

    <!-- ====== Mobile Top Header ====== -->
    <div
      class="md:hidden fixed top-0 left-0 right-0 z-50 glass-light px-4 py-3 flex items-center justify-between border-b"
      :style="{ borderColor: 'var(--border-default)' }"
    >
      <router-link to="/dashboard" class="flex items-center gap-2 no-underline">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand to-brand-amber flex items-center justify-center text-white font-bold text-xs overflow-hidden">
          <img v-if="shopLogo()" :src="shopLogo()" class="w-full h-full object-cover" />
          <span v-else>{{ shopAvatarLetter() }}</span>
        </div>
        <span class="font-display font-bold" :style="{ color: 'var(--text-primary)' }">{{ shopDisplayName() }}</span>
      </router-link>
      <button
        @click="sidebarOpen = !sidebarOpen"
        class="w-10 h-10 flex items-center justify-center rounded-xl transition-colors"
        :style="{ color: 'var(--text-primary)' }"
      >
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
    </div>

    <!-- ====== Mobile Bottom Nav (native app style) ====== -->
    <nav class="md:hidden bottom-nav px-1 pt-1.5 pb-1 flex items-center justify-around">
      <!-- Home -->
      <router-link to="/dashboard" class="bottom-nav-item" :class="route.path === '/dashboard' ? 'bottom-nav-active' : ''">
        <svg width="22" height="22" viewBox="0 0 24 24" :fill="route.path === '/dashboard' ? 'currentColor' : 'none'" :stroke="route.path === '/dashboard' ? 'none' : 'currentColor'" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline v-if="route.path !== '/dashboard'" points="9 22 9 12 15 12 15 22"/></svg>
        <span>Accueil</span>
      </router-link>
      <!-- Products -->
      <router-link to="/dashboard/products" class="bottom-nav-item" :class="route.path.startsWith('/dashboard/products') ? 'bottom-nav-active' : ''">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
        <span>Produits</span>
      </router-link>
      <!-- Orders -->
      <router-link to="/dashboard/orders" class="bottom-nav-item" :class="route.path.startsWith('/dashboard/orders') ? 'bottom-nav-active' : ''">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        <span>Commandes</span>
      </router-link>
      <!-- Chat -->
      <router-link to="/dashboard/chat" class="bottom-nav-item" :class="route.path.startsWith('/dashboard/chat') ? 'bottom-nav-active' : ''">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
        <span>Messages</span>
      </router-link>
      <!-- More (opens sidebar) -->
      <button @click="sidebarOpen = !sidebarOpen" class="bottom-nav-item">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
        <span>Plus</span>
      </button>
    </nav>

    <!-- ====== Main Content ====== -->
    <main class="flex-1 pt-16 md:pt-0 pb-20 md:pb-0 overflow-x-hidden" :class="tabletCollapsed ? 'md:ml-16' : 'md:ml-64'" style="transition: margin-left 0.3s;">
      <router-view />
    </main>

    <!-- Upgrade Modal (first visit) -->
    <UpgradeModal />
  </div>
</template>

<style scoped>
.safe-bottom {
  padding-bottom: max(0.5rem, env(safe-area-inset-bottom));
}

/* Bottom nav — native app style */
.bottom-nav-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 2px;
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 10px;
  font-weight: 500;
  color: var(--text-faint);
  text-decoration: none;
  transition: all 0.2s ease;
  -webkit-tap-highlight-color: transparent;
  min-width: 56px;
}
.bottom-nav-item:active {
  transform: scale(0.9);
}
.bottom-nav-active {
  color: var(--color-brand) !important;
}
.bottom-nav-active span {
  font-weight: 700;
}

/* Overlay transition */
.overlay-enter-active {
  transition: opacity 0.25s ease;
}
.overlay-enter-active aside {
  transition: transform 0.25s ease;
}
.overlay-leave-active {
  transition: opacity 0.2s ease;
}
.overlay-leave-active aside {
  transition: transform 0.2s ease;
}
.overlay-enter-from {
  opacity: 0;
}
.overlay-enter-from aside {
  transform: translateX(-100%);
}
.overlay-leave-to {
  opacity: 0;
}
.overlay-leave-to aside {
  transform: translateX(-100%);
}
</style>
