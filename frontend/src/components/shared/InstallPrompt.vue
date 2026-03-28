<script setup>
import { ref, onMounted, computed } from 'vue'
import { usePWA } from '../../composables/usePWA'

const { isStandalone, isOnline, isIOS, canInstall, updateAvailable, install, applyUpdate, hapticSuccess } = usePWA()

const showInstall = ref(false)
const showIOSGuide = ref(false)
const dismissed = ref(false)

onMounted(() => {
  // Show install prompt after 3s (only if not already installed)
  setTimeout(() => {
    if (!isStandalone.value && !localStorage.getItem('storely-install-dismissed')) {
      showInstall.value = true
    }
  }, 3000)
})

const handleInstall = async () => {
  if (isIOS.value) {
    showIOSGuide.value = true
    return
  }
  const accepted = await install()
  if (accepted) {
    hapticSuccess()
    showInstall.value = false
  }
}

const dismiss = () => {
  showInstall.value = false
  dismissed.value = true
  localStorage.setItem('storely-install-dismissed', Date.now().toString())
}

const dismissIOSGuide = () => { showIOSGuide.value = false }
</script>

<template>
  <!-- Offline indicator -->
  <transition
    enter-active-class="transition-all duration-300 ease-out"
    enter-from-class="opacity-0 -translate-y-full"
    leave-active-class="transition-all duration-200 ease-in"
    leave-to-class="opacity-0 -translate-y-full"
  >
    <div
      v-if="!isOnline"
      class="fixed top-0 left-0 right-0 z-[100] flex items-center justify-center gap-2 py-2.5 px-4 text-xs font-semibold text-white"
      style="background: linear-gradient(135deg, #EF4444, #DC2626); padding-top: calc(0.625rem + env(safe-area-inset-top))"
    >
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 5.636a9 9 0 010 12.728M5.636 5.636a9 9 0 000 12.728M12 12h.01"/>
        <line x1="1" y1="1" x2="23" y2="23" stroke-width="2"/>
      </svg>
      Hors connexion — certaines fonctionnalités peuvent être limitées
    </div>
  </transition>

  <!-- Update available banner -->
  <transition
    enter-active-class="transition-all duration-400 ease-out"
    enter-from-class="opacity-0 translate-y-full"
    leave-active-class="transition-all duration-200 ease-in"
    leave-to-class="opacity-0 translate-y-full"
  >
    <div
      v-if="updateAvailable"
      class="fixed bottom-20 left-4 right-4 md:left-auto md:right-6 md:w-[360px] z-[90] rounded-2xl p-4 shadow-2xl"
      style="background: linear-gradient(135deg, #1a1a2e, #16213e); border: 1px solid rgba(255,255,255,0.1)"
    >
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-blue-500/20 flex items-center justify-center shrink-0">
          <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-white">Mise à jour disponible</p>
          <p class="text-[11px] text-white/40 mt-0.5">Nouvelle version de Storely</p>
        </div>
        <button
          @click="applyUpdate"
          class="px-4 py-2 rounded-xl text-xs font-bold text-white shrink-0"
          style="background: linear-gradient(135deg, #3B82F6, #2563EB)"
        >
          Mettre à jour
        </button>
      </div>
    </div>
  </transition>

  <!-- Install prompt — App Store style -->
  <transition
    enter-active-class="transition-all duration-500 ease-[cubic-bezier(0.16,1,0.3,1)]"
    enter-from-class="opacity-0 translate-y-full scale-95"
    leave-active-class="transition-all duration-300 ease-in"
    leave-to-class="opacity-0 translate-y-full scale-95"
  >
    <div
      v-if="showInstall && !isStandalone && !dismissed"
      class="fixed bottom-4 left-3 right-3 md:left-auto md:right-6 md:bottom-6 md:w-[400px] z-[80] rounded-3xl overflow-hidden shadow-2xl"
      style="background: linear-gradient(145deg, #12121a, #1a1a2e); border: 1px solid rgba(255,255,255,0.08)"
    >
      <!-- Glow -->
      <div class="absolute -top-20 -right-20 w-40 h-40 rounded-full opacity-20 blur-3xl pointer-events-none" style="background: #FF6B2C" />

      <div class="relative p-5">
        <div class="flex items-start gap-4">
          <!-- App icon -->
          <div class="w-16 h-16 rounded-2xl overflow-hidden shrink-0 shadow-lg bg-[#0A0A0F]">
            <img src="/logo.png" alt="Storely" class="w-full h-full object-contain p-1" />
          </div>

          <div class="flex-1 min-w-0">
            <h3 class="font-bold text-white text-base">Storely</h3>
            <p class="text-[11px] text-white/40 mt-0.5">Votre boutique dans la poche</p>
            <!-- Stars -->
            <div class="flex items-center gap-1 mt-1.5">
              <svg v-for="i in 5" :key="i" class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
              <span class="text-[10px] text-white/30 ml-1">2 500+ utilisateurs</span>
            </div>
          </div>

          <!-- Close -->
          <button @click="dismiss" class="w-7 h-7 rounded-full flex items-center justify-center text-white/20 hover:text-white/50 hover:bg-white/5 transition shrink-0 -mt-1 -mr-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
          </button>
        </div>

        <!-- Features -->
        <div class="flex items-center gap-3 mt-4 mb-4">
          <div class="flex-1 text-center px-2 py-2 rounded-xl" style="background: rgba(255,255,255,0.04)">
            <svg class="w-5 h-5 mx-auto mb-1 text-brand opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <p class="text-[10px] text-white/50 font-medium">Ultra rapide</p>
          </div>
          <div class="flex-1 text-center px-2 py-2 rounded-xl" style="background: rgba(255,255,255,0.04)">
            <svg class="w-5 h-5 mx-auto mb-1 text-green-400 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path d="M18.364 5.636a9 9 0 010 12.728M5.636 5.636a9 9 0 000 12.728"/><circle cx="12" cy="12" r="3"/></svg>
            <p class="text-[10px] text-white/50 font-medium">Hors ligne</p>
          </div>
          <div class="flex-1 text-center px-2 py-2 rounded-xl" style="background: rgba(255,255,255,0.04)">
            <svg class="w-5 h-5 mx-auto mb-1 text-purple-400 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <p class="text-[10px] text-white/50 font-medium">Notifications</p>
          </div>
        </div>

        <!-- Install button -->
        <button
          @click="handleInstall"
          class="w-full py-3.5 rounded-2xl text-sm font-bold text-white transition-all active:scale-[0.97]"
          style="background: linear-gradient(135deg, #FF6B2C, #FF8F5C); box-shadow: 0 4px 16px rgba(255,107,44,0.3)"
        >
          {{ isIOS ? 'Comment installer' : 'Installer l\'application' }}
        </button>
      </div>
    </div>
  </transition>

  <!-- iOS Install Guide -->
  <Teleport to="body">
    <transition
      enter-active-class="transition-all duration-300"
      enter-from-class="opacity-0"
      leave-active-class="transition-all duration-200"
      leave-to-class="opacity-0"
    >
      <div v-if="showIOSGuide" class="fixed inset-0 z-[100] flex items-end justify-center">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="dismissIOSGuide" />
        <div class="relative w-full max-w-md rounded-t-3xl p-6 pb-10 animate-slide-up" style="background: linear-gradient(145deg, #12121a, #1a1a2e)">
          <div class="w-10 h-1 rounded-full bg-white/10 mx-auto mb-5" />
          <h3 class="font-bold text-lg text-white text-center mb-5">Installer Storely sur iOS</h3>

          <div class="space-y-4">
            <div class="flex items-center gap-4 p-3 rounded-xl" style="background: rgba(255,255,255,0.04)">
              <div class="w-10 h-10 rounded-xl bg-blue-500/20 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
              </div>
              <div>
                <p class="text-sm font-medium text-white">1. Appuyez sur le bouton partage</p>
                <p class="text-[11px] text-white/40">L'icône en bas de Safari (carré avec flèche)</p>
              </div>
            </div>

            <div class="flex items-center gap-4 p-3 rounded-xl" style="background: rgba(255,255,255,0.04)">
              <div class="w-10 h-10 rounded-xl bg-green-500/20 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
              </div>
              <div>
                <p class="text-sm font-medium text-white">2. "Sur l'écran d'accueil"</p>
                <p class="text-[11px] text-white/40">Faites défiler et appuyez sur cette option</p>
              </div>
            </div>

            <div class="flex items-center gap-4 p-3 rounded-xl" style="background: rgba(255,255,255,0.04)">
              <div class="w-10 h-10 rounded-xl bg-brand/20 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-brand" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>
              </div>
              <div>
                <p class="text-sm font-medium text-white">3. Appuyez "Ajouter"</p>
                <p class="text-[11px] text-white/40">Storely apparaîtra sur votre écran d'accueil</p>
              </div>
            </div>
          </div>

          <button @click="dismissIOSGuide" class="w-full mt-5 py-3 rounded-xl text-sm font-medium text-white/60 hover:text-white transition" style="background: rgba(255,255,255,0.05)">
            J'ai compris
          </button>
        </div>
      </div>
    </transition>
  </Teleport>
</template>
