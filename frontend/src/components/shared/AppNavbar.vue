<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useTheme } from '../../composables/useTheme'
import { useAuth } from '../../composables/useAuth'

const { theme, toggle } = useTheme()
const { user, shop, isAuthenticated, logout } = useAuth()
const scrolled = ref(false)
const sidebarOpen = ref(false)
const userMenuOpen = ref(false)

const handleScroll = () => {
  scrolled.value = window.scrollY > 20
}

const closeSidebar = () => { sidebarOpen.value = false }
const closeUserMenu = (e) => {
  if (!e.target.closest('.user-menu-wrapper')) userMenuOpen.value = false
}

const userInitials = computed(() => {
  if (!user.value?.name) return '?'
  return user.value.name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2)
})

onMounted(() => {
  window.addEventListener('scroll', handleScroll)
  document.addEventListener('click', closeUserMenu)
})
onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
  document.removeEventListener('click', closeUserMenu)
})
</script>

<template>
  <nav
    class="fixed top-0 left-0 right-0 z-50 transition-all duration-500"
    :class="scrolled ? 'py-2' : 'py-4'"
  >
    <div
      class="mx-auto max-w-6xl px-4 transition-all duration-500"
      :class="scrolled ? 'max-w-5xl' : ''"
    >
      <div
        class="flex items-center justify-between rounded-2xl px-6 py-3 transition-all duration-500"
        :class="scrolled
          ? 'glass-light shadow-lg shadow-black/20'
          : 'bg-transparent'"
      >
        <!-- Logo -->
        <router-link to="/" class="flex items-center gap-2 no-underline">
          <img src="/logo.png" alt="Storely" class="w-9 h-9 object-contain" />
          <span class="font-display text-xl font-bold tracking-tight" style="color: var(--logo-text)">Storely</span>
        </router-link>

        <!-- Desktop Nav -->
        <div class="hidden md:flex items-center gap-1">
          <router-link
            to="/"
            class="px-4 py-2 rounded-xl text-sm font-medium text-white/70 hover:text-white hover:bg-white/5 transition-all no-underline"
          >
            Accueil
          </router-link>
          <router-link
            to="/pricing"
            class="px-4 py-2 rounded-xl text-sm font-medium text-white/70 hover:text-white hover:bg-white/5 transition-all no-underline"
          >
            Tarifs
          </router-link>
          <router-link
            to="/marketplace"
            class="px-4 py-2 rounded-xl text-sm font-medium text-white/70 hover:text-white hover:bg-white/5 transition-all no-underline"
          >
            Explorer
          </router-link>
          <a
            href="#features"
            class="px-4 py-2 rounded-xl text-sm font-medium text-white/70 hover:text-white hover:bg-white/5 transition-all no-underline"
          >
            Fonctions
          </a>
        </div>

        <!-- Desktop Right Side -->
        <div class="hidden md:flex items-center gap-3">
          <!-- Theme Toggle -->
          <button
            @click="toggle"
            class="relative w-10 h-10 rounded-xl flex items-center justify-center hover:bg-white/5 transition-all group"
            :title="theme === 'dark' ? 'Mode clair' : 'Mode sombre'"
          >
            <svg
              v-if="theme === 'dark'"
              width="18" height="18" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="text-white/60 group-hover:text-brand-amber transition-colors"
            >
              <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
              <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
              <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
              <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
            <svg
              v-else
              width="18" height="18" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="text-black/50 group-hover:text-electric transition-colors"
            >
              <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
            </svg>
          </button>

          <!-- Not logged in -->
          <template v-if="!isAuthenticated">
            <router-link to="/login" class="px-4 py-2 text-sm font-medium text-white/70 hover:text-white transition-all no-underline">
              Connexion
            </router-link>
            <router-link to="/register" class="btn-primary !py-2.5 !px-5 !text-sm !shadow-[0_3px_0_0_#C4481A,0_6px_16px_rgba(255,107,44,0.2)]">
              Commencer gratuit
            </router-link>
          </template>

          <!-- Logged in — User menu -->
          <template v-else>
            <router-link to="/dashboard" class="px-4 py-2 rounded-xl text-sm font-medium text-white/70 hover:text-white hover:bg-white/5 transition-all no-underline">
              Dashboard
            </router-link>
            <div class="relative user-menu-wrapper">
              <button
                @click.stop="userMenuOpen = !userMenuOpen"
                class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-xl hover:bg-white/5 transition-all"
              >
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-brand to-brand-amber flex items-center justify-center text-white text-xs font-bold">
                  {{ userInitials }}
                </div>
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-white/40 transition-transform" :class="userMenuOpen ? 'rotate-180' : ''">
                  <polyline points="6 9 12 15 18 9"/>
                </svg>
              </button>

              <!-- Dropdown -->
              <transition
                enter-active-class="transition-all duration-200 ease-out"
                enter-from-class="opacity-0 scale-95 -translate-y-1"
                enter-to-class="opacity-100 scale-100 translate-y-0"
                leave-active-class="transition-all duration-150 ease-in"
                leave-from-class="opacity-100 scale-100"
                leave-to-class="opacity-0 scale-95"
              >
                <div v-if="userMenuOpen" class="absolute right-0 top-full mt-2 w-56 rounded-xl overflow-hidden shadow-2xl z-50" style="background: var(--bg-secondary); border: 1px solid var(--border-subtle)">
                  <div class="px-4 py-3 border-b" style="border-color: var(--border-default)">
                    <p class="text-sm font-semibold truncate" style="color: var(--text-primary)">{{ user?.name }}</p>
                    <p class="text-xs truncate" style="color: var(--text-muted)">{{ user?.email }}</p>
                  </div>
                  <div class="py-1">
                    <router-link to="/dashboard" @click="userMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-white/5 transition-all no-underline" style="color: var(--text-secondary)">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                      Dashboard
                    </router-link>
                    <router-link v-if="shop?.slug" :to="`/${shop.slug}`" @click="userMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-white/5 transition-all no-underline" style="color: var(--text-secondary)">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                      Ma boutique
                    </router-link>
                    <router-link to="/dashboard/settings" @click="userMenuOpen = false" class="flex items-center gap-3 px-4 py-2.5 text-sm hover:bg-white/5 transition-all no-underline" style="color: var(--text-secondary)">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                      Paramètres
                    </router-link>
                  </div>
                  <div class="py-1 border-t" style="border-color: var(--border-default)">
                    <button @click="logout(); userMenuOpen = false" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-400 hover:bg-red-500/10 transition-all">
                      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                      Déconnexion
                    </button>
                  </div>
                </div>
              </transition>
            </div>
          </template>
        </div>

        <!-- Mobile: Theme Toggle + Menu Button -->
        <div class="md:hidden flex items-center gap-1">
          <button
            @click="toggle"
            class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-white/5 transition-all"
          >
            <svg
              v-if="theme === 'dark'"
              width="18" height="18" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="text-white/60"
            >
              <circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
              <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
              <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
              <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
            <svg
              v-else
              width="18" height="18" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="text-black/50"
            >
              <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
            </svg>
          </button>

          <button
            class="w-10 h-10 flex items-center justify-center rounded-xl hover:bg-white/5 transition-all"
            @click="sidebarOpen = true"
          >
            <div class="flex flex-col gap-1.5 w-5">
              <span class="h-0.5 rounded transition-all duration-300" :class="theme === 'dark' ? 'bg-white' : 'bg-black/70'" />
              <span class="h-0.5 rounded transition-all duration-300 w-3.5" :class="theme === 'dark' ? 'bg-white' : 'bg-black/70'" />
              <span class="h-0.5 rounded transition-all duration-300" :class="theme === 'dark' ? 'bg-white' : 'bg-black/70'" />
            </div>
          </button>
        </div>
      </div>
    </div>
  </nav>

  <!-- Mobile Sidebar Overlay -->
  <Teleport to="body">
    <transition
      enter-active-class="transition-opacity duration-300"
      enter-from-class="opacity-0"
      leave-active-class="transition-opacity duration-200"
      leave-to-class="opacity-0"
    >
      <div v-if="sidebarOpen" class="fixed inset-0 z-[60] bg-black/60 backdrop-blur-sm md:hidden" @click="closeSidebar" />
    </transition>

    <transition
      enter-active-class="transition-transform duration-300 ease-[cubic-bezier(0.16,1,0.3,1)]"
      enter-from-class="translate-x-full"
      leave-active-class="transition-transform duration-200 ease-in"
      leave-to-class="translate-x-full"
    >
      <div v-if="sidebarOpen" class="fixed top-0 right-0 bottom-0 w-[280px] z-[70] md:hidden overflow-y-auto" style="background: var(--bg-secondary)">
        <!-- Sidebar header -->
        <div class="flex items-center justify-between px-5 py-4 border-b" style="border-color: var(--border-default)">
          <router-link to="/" @click="closeSidebar" class="flex items-center gap-2 no-underline">
            <img src="/logo.png" alt="Storely" class="w-8 h-8 object-contain" />
            <span class="font-display text-lg font-bold" style="color: var(--logo-text)">Storely</span>
          </router-link>
          <button @click="closeSidebar" class="w-9 h-9 rounded-xl flex items-center justify-center hover:bg-white/5 transition-all" style="color: var(--text-muted)">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>

        <!-- User info (if logged in) -->
        <div v-if="isAuthenticated" class="px-5 py-4 border-b" style="border-color: var(--border-default)">
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

        <!-- Nav links -->
        <div class="px-3 py-3 space-y-1">
          <router-link
            to="/"
            @click="closeSidebar"
            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all no-underline"
            style="color: var(--text-secondary)"
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Accueil
          </router-link>
          <router-link
            to="/pricing"
            @click="closeSidebar"
            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all no-underline"
            style="color: var(--text-secondary)"
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
            Tarifs
          </router-link>
          <router-link
            to="/marketplace"
            @click="closeSidebar"
            class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all no-underline"
            style="color: var(--text-secondary)"
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Explorer
          </router-link>

          <template v-if="isAuthenticated">
            <div class="my-2 mx-2 border-t" style="border-color: var(--border-default)" />
            <router-link
              to="/dashboard"
              @click="closeSidebar"
              class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all no-underline"
              style="color: var(--text-secondary)"
            >
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
              Dashboard
            </router-link>
            <router-link
              v-if="shop?.slug"
              :to="`/${shop.slug}`"
              @click="closeSidebar"
              class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all no-underline"
              style="color: var(--text-secondary)"
            >
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
              Ma boutique
            </router-link>
            <router-link
              to="/dashboard/settings"
              @click="closeSidebar"
              class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all no-underline"
              style="color: var(--text-secondary)"
            >
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
              Paramètres
            </router-link>
          </template>
        </div>

        <!-- Bottom section -->
        <div class="absolute bottom-0 left-0 right-0 px-3 py-4 border-t" style="border-color: var(--border-default)">
          <template v-if="!isAuthenticated">
            <router-link
              to="/login"
              @click="closeSidebar"
              class="block w-full text-center px-4 py-3 rounded-xl text-sm font-medium mb-2 transition-all no-underline"
              style="color: var(--text-secondary); background: var(--glass-bg-light)"
            >
              Connexion
            </router-link>
            <router-link
              to="/register"
              @click="closeSidebar"
              class="block text-center btn-primary !w-full !justify-center"
            >
              Commencer gratuit
            </router-link>
          </template>
          <button
            v-else
            @click="logout(); closeSidebar()"
            class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-xl text-sm font-medium text-red-400 transition-all"
            style="background: rgba(239, 68, 68, 0.08)"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
            Déconnexion
          </button>
        </div>
      </div>
    </transition>
  </Teleport>
</template>
