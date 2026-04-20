<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useTheme } from '../../composables/useTheme'
import { useAuth } from '../../composables/useAuth'

const { theme, toggle } = useTheme()
const { user, shop, isAuthenticated, logout } = useAuth()
const scrolled = ref(false)
const sidebarOpen = ref(false)
const userMenuOpen = ref(false)

const handleScroll = () => { scrolled.value = window.scrollY > 16 }
const closeSidebar = () => { sidebarOpen.value = false }
const closeUserMenu = (e) => {
  if (!e.target.closest('.user-menu-wrapper')) userMenuOpen.value = false
}

const userInitials = computed(() => {
  if (!user.value?.name) return '?'
  return user.value.name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2)
})

onMounted(() => {
  window.addEventListener('scroll', handleScroll, { passive: true })
  document.addEventListener('click', closeUserMenu)
  handleScroll()
})
onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  document.removeEventListener('click', closeUserMenu)
})
</script>

<template>
  <nav
    class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
    :style="{
      background: scrolled ? 'var(--nav-bg-scrolled)' : 'transparent',
      borderBottom: scrolled ? `1px solid var(--nav-border-scrolled)` : '1px solid transparent',
      backdropFilter: scrolled ? 'saturate(180%) blur(14px)' : 'none',
      WebkitBackdropFilter: scrolled ? 'saturate(180%) blur(14px)' : 'none',
    }"
  >
    <div class="container-max flex items-center justify-between h-16">
      <!-- Logo -->
      <router-link to="/" class="flex items-center gap-2.5 no-underline shrink-0">
        <img src="/logo.png" alt="Storely" class="w-8 h-8 object-contain" />
        <span class="font-display text-lg font-bold tracking-tight" style="color: var(--logo-text)">Storely</span>
      </router-link>

      <!-- Desktop Nav -->
      <div class="hidden lg:flex items-center gap-1">
        <router-link
          v-for="link in [
            { to: '/', label: 'Accueil' },
            { to: '/pricing', label: 'Tarifs' },
            { to: '/marketplace', label: 'Explorer' },
            { to: '/help', label: 'Aide' },
          ]"
          :key="link.to"
          :to="link.to"
          class="px-3.5 py-2 rounded-lg text-sm font-medium transition-colors no-underline"
          style="color: var(--nav-text)"
          active-class="!text-[var(--nav-text-active)]"
          @mouseenter="$event.target.style.color = 'var(--nav-text-hover)'"
          @mouseleave="$event.target.style.color = 'var(--nav-text)'"
        >
          {{ link.label }}
        </router-link>
      </div>

      <!-- Desktop Right -->
      <div class="hidden lg:flex items-center gap-2">
        <button
          @click="toggle"
          class="btn-icon"
          :title="theme === 'dark' ? 'Mode clair' : 'Mode sombre'"
          aria-label="Changer le thème"
        >
          <svg v-if="theme === 'dark'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
          </svg>
          <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
          </svg>
        </button>

        <template v-if="!isAuthenticated">
          <router-link to="/login" class="btn-ghost text-sm">Connexion</router-link>
          <router-link to="/register" class="btn-primary btn-sm">
            Commencer gratuitement
          </router-link>
        </template>

        <template v-else>
          <router-link to="/dashboard" class="btn-ghost text-sm">Dashboard</router-link>
          <div class="relative user-menu-wrapper">
            <button
              @click.stop="userMenuOpen = !userMenuOpen"
              class="flex items-center gap-2 pl-1 pr-2.5 py-1 rounded-full transition-colors"
              :style="{ background: userMenuOpen ? 'var(--bg-tertiary)' : 'transparent' }"
            >
              <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand to-brand-amber flex items-center justify-center text-white text-xs font-bold">
                {{ userInitials }}
              </div>
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="transition-transform" :class="userMenuOpen ? 'rotate-180' : ''" style="color: var(--text-muted)">
                <polyline points="6 9 12 15 18 9"/>
              </svg>
            </button>

            <transition
              enter-active-class="transition duration-150 ease-out"
              enter-from-class="opacity-0 scale-95 -translate-y-1"
              enter-to-class="opacity-100 scale-100"
              leave-active-class="transition duration-100 ease-in"
              leave-from-class="opacity-100 scale-100"
              leave-to-class="opacity-0 scale-95"
            >
              <div v-if="userMenuOpen" class="absolute right-0 top-full mt-2 w-60 rounded-xl overflow-hidden card-static" style="box-shadow: var(--card-shadow-xl)">
                <div class="px-4 py-3 border-b" style="border-color: var(--border-default); background: var(--bg-tertiary)">
                  <p class="text-sm font-semibold truncate" style="color: var(--text-primary)">{{ user?.name }}</p>
                  <p class="text-xs truncate" style="color: var(--text-muted)">{{ user?.email }}</p>
                </div>
                <div class="py-1">
                  <router-link to="/dashboard" @click="userMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 text-sm no-underline transition-colors" style="color: var(--text-secondary)" @mouseenter="$event.currentTarget.style.background = 'var(--bg-tertiary)'" @mouseleave="$event.currentTarget.style.background = 'transparent'">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    Dashboard
                  </router-link>
                  <router-link v-if="shop?.slug" :to="`/${shop.slug}`" @click="userMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 text-sm no-underline transition-colors" style="color: var(--text-secondary)" @mouseenter="$event.currentTarget.style.background = 'var(--bg-tertiary)'" @mouseleave="$event.currentTarget.style.background = 'transparent'">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    Ma boutique
                  </router-link>
                  <router-link to="/dashboard/settings" @click="userMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 text-sm no-underline transition-colors" style="color: var(--text-secondary)" @mouseenter="$event.currentTarget.style.background = 'var(--bg-tertiary)'" @mouseleave="$event.currentTarget.style.background = 'transparent'">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9z"/></svg>
                    Paramètres
                  </router-link>
                </div>
                <div class="py-1 border-t" style="border-color: var(--border-default)">
                  <button @click="logout(); userMenuOpen = false" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-500 transition-colors" @mouseenter="$event.currentTarget.style.background = 'rgba(239,68,68,0.08)'" @mouseleave="$event.currentTarget.style.background = 'transparent'">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Déconnexion
                  </button>
                </div>
              </div>
            </transition>
          </div>
        </template>
      </div>

      <!-- Mobile -->
      <div class="lg:hidden flex items-center gap-1.5">
        <button @click="toggle" class="btn-icon" aria-label="Thème">
          <svg v-if="theme === 'dark'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"/>
          </svg>
          <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
          </svg>
        </button>
        <button class="btn-icon" @click="sidebarOpen = true" aria-label="Menu">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
            <line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="14" y2="17"/>
          </svg>
        </button>
      </div>
    </div>
  </nav>

  <!-- Mobile Sidebar -->
  <Teleport to="body">
    <transition
      enter-active-class="transition-opacity duration-250"
      enter-from-class="opacity-0"
      leave-active-class="transition-opacity duration-200"
      leave-to-class="opacity-0"
    >
      <div v-if="sidebarOpen" class="fixed inset-0 z-[60] lg:hidden" style="background: var(--overlay-bg)" @click="closeSidebar" />
    </transition>

    <transition
      enter-active-class="transition-transform duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]"
      enter-from-class="translate-x-full"
      leave-active-class="transition-transform duration-200 ease-in"
      leave-to-class="translate-x-full"
    >
      <aside v-if="sidebarOpen" class="fixed top-0 right-0 bottom-0 w-[320px] z-[70] lg:hidden overflow-y-auto flex flex-col" style="background: var(--bg-secondary); box-shadow: var(--card-shadow-xl)">
        <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color: var(--border-default)">
          <router-link to="/" @click="closeSidebar" class="flex items-center gap-2 no-underline">
            <img src="/logo.png" alt="Storely" class="w-8 h-8 object-contain" />
            <span class="font-display text-lg font-bold" style="color: var(--logo-text)">Storely</span>
          </router-link>
          <button @click="closeSidebar" class="btn-icon" style="width: 2.25rem; height: 2.25rem">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>

        <div v-if="isAuthenticated" class="px-5 py-4 border-b" style="border-color: var(--border-default); background: var(--bg-tertiary)">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-brand to-brand-amber flex items-center justify-center text-white text-sm font-bold">
              {{ userInitials }}
            </div>
            <div class="min-w-0">
              <p class="text-sm font-semibold truncate" style="color: var(--text-primary)">{{ user?.name }}</p>
              <p class="text-xs truncate" style="color: var(--text-muted)">{{ shop?.name || user?.email }}</p>
            </div>
          </div>
        </div>

        <nav class="px-3 py-3 space-y-0.5 flex-1">
          <router-link v-for="link in [
              { to: '/', label: 'Accueil', icon: 'home' },
              { to: '/pricing', label: 'Tarifs', icon: 'tag' },
              { to: '/marketplace', label: 'Explorer', icon: 'search' },
              { to: '/help', label: 'Aide', icon: 'help' },
            ]"
            :key="link.to"
            :to="link.to"
            @click="closeSidebar"
            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors no-underline"
            style="color: var(--text-secondary)"
            @mouseenter="$event.currentTarget.style.background = 'var(--bg-tertiary)'"
            @mouseleave="$event.currentTarget.style.background = 'transparent'"
          >
            <svg v-if="link.icon === 'home'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <svg v-else-if="link.icon === 'tag'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
            <svg v-else-if="link.icon === 'search'" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            {{ link.label }}
          </router-link>

          <template v-if="isAuthenticated">
            <div class="my-2 h-px" style="background: var(--border-default)" />
            <router-link to="/dashboard" @click="closeSidebar" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors no-underline" style="color: var(--text-secondary)">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
              Dashboard
            </router-link>
            <router-link v-if="shop?.slug" :to="`/${shop.slug}`" @click="closeSidebar" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors no-underline" style="color: var(--text-secondary)">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
              Ma boutique
            </router-link>
            <router-link to="/dashboard/settings" @click="closeSidebar" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors no-underline" style="color: var(--text-secondary)">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9z"/></svg>
              Paramètres
            </router-link>
          </template>
        </nav>

        <div class="px-4 py-4 border-t" style="border-color: var(--border-default)">
          <template v-if="!isAuthenticated">
            <router-link to="/login" @click="closeSidebar" class="btn-secondary btn-sm w-full mb-2">
              Connexion
            </router-link>
            <router-link to="/register" @click="closeSidebar" class="btn-primary btn-sm w-full">
              Commencer gratuitement
            </router-link>
          </template>
          <button v-else @click="logout(); closeSidebar()" class="flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl text-sm font-semibold text-red-500" style="background: rgba(239, 68, 68, 0.08)">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Déconnexion
          </button>
        </div>
      </aside>
    </transition>
  </Teleport>
</template>
