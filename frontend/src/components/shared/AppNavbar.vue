<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useTheme } from '../../composables/useTheme'

const { theme, toggle } = useTheme()
const scrolled = ref(false)
const mobileOpen = ref(false)

const handleScroll = () => {
  scrolled.value = window.scrollY > 20
}

onMounted(() => window.addEventListener('scroll', handleScroll))
onUnmounted(() => window.removeEventListener('scroll', handleScroll))
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

        <!-- Desktop CTA + Theme Toggle -->
        <div class="hidden md:flex items-center gap-3">
          <!-- Theme Toggle -->
          <button
            @click="toggle"
            class="relative w-10 h-10 rounded-xl flex items-center justify-center hover:bg-white/5 transition-all group"
            :title="theme === 'dark' ? 'Mode clair' : 'Mode sombre'"
          >
            <!-- Sun icon (visible in dark mode) -->
            <svg
              v-if="theme === 'dark'"
              width="18" height="18" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="text-white/60 group-hover:text-brand-amber transition-colors"
            >
              <circle cx="12" cy="12" r="5"/>
              <line x1="12" y1="1" x2="12" y2="3"/>
              <line x1="12" y1="21" x2="12" y2="23"/>
              <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
              <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
              <line x1="1" y1="12" x2="3" y2="12"/>
              <line x1="21" y1="12" x2="23" y2="12"/>
              <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
              <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
            </svg>
            <!-- Moon icon (visible in light mode) -->
            <svg
              v-else
              width="18" height="18" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="text-black/50 group-hover:text-electric transition-colors"
            >
              <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
            </svg>
          </button>

          <router-link to="/login" class="px-4 py-2 text-sm font-medium text-white/70 hover:text-white transition-all no-underline">
            Connexion
          </router-link>
          <router-link to="/register" class="btn-primary !py-2.5 !px-5 !text-sm !shadow-[0_3px_0_0_#C4481A,0_6px_16px_rgba(255,107,44,0.2)]">
            Commencer gratuit
          </router-link>
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
              <circle cx="12" cy="12" r="5"/>
              <line x1="12" y1="1" x2="12" y2="3"/>
              <line x1="12" y1="21" x2="12" y2="23"/>
              <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
              <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
              <line x1="1" y1="12" x2="3" y2="12"/>
              <line x1="21" y1="12" x2="23" y2="12"/>
              <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
              <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
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
            @click="mobileOpen = !mobileOpen"
          >
            <div class="flex flex-col gap-1.5 w-5">
              <span
                class="h-0.5 rounded transition-all duration-300 origin-center"
                :class="[mobileOpen ? 'rotate-45 translate-y-[4px]' : '', theme === 'dark' ? 'bg-white' : 'bg-black/70']"
              />
              <span
                class="h-0.5 rounded transition-all duration-300"
                :class="[mobileOpen ? 'opacity-0 scale-x-0' : '', theme === 'dark' ? 'bg-white' : 'bg-black/70']"
              />
              <span
                class="h-0.5 rounded transition-all duration-300 origin-center"
                :class="[mobileOpen ? '-rotate-45 -translate-y-[4px]' : '', theme === 'dark' ? 'bg-white' : 'bg-black/70']"
              />
            </div>
          </button>
        </div>
      </div>

      <!-- Mobile Menu -->
      <transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 -translate-y-2 scale-95"
        enter-to-class="opacity-100 translate-y-0 scale-100"
        leave-active-class="transition-all duration-200 ease-in"
        leave-from-class="opacity-100 translate-y-0 scale-100"
        leave-to-class="opacity-0 -translate-y-2 scale-95"
      >
        <div v-if="mobileOpen" class="md:hidden mt-2 glass-light rounded-2xl p-4 space-y-2">
          <router-link
            to="/"
            @click="mobileOpen = false"
            class="block px-4 py-3 rounded-xl text-white/80 hover:text-white hover:bg-white/5 transition-all no-underline"
          >
            Accueil
          </router-link>
          <router-link
            to="/pricing"
            @click="mobileOpen = false"
            class="block px-4 py-3 rounded-xl text-white/80 hover:text-white hover:bg-white/5 transition-all no-underline"
          >
            Tarifs
          </router-link>
          <router-link
            to="/marketplace"
            @click="mobileOpen = false"
            class="block px-4 py-3 rounded-xl text-white/80 hover:text-white hover:bg-white/5 transition-all no-underline"
          >
            Explorer
          </router-link>
          <hr class="border-white/10" />
          <router-link
            to="/login"
            @click="mobileOpen = false"
            class="block px-4 py-3 rounded-xl text-white/80 hover:text-white hover:bg-white/5 transition-all no-underline"
          >
            Connexion
          </router-link>
          <router-link
            to="/register"
            @click="mobileOpen = false"
            class="block text-center btn-primary !w-full !justify-center"
          >
            Commencer gratuit
          </router-link>
        </div>
      </transition>
    </div>
  </nav>
</template>
