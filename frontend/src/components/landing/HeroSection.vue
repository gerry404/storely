<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'

const loaded = ref(false)
const scrollY = ref(0)
const heroRef = ref(null)
const mockupVisible = ref(false)

// Browser mockup animation steps
const demoStep = ref(0)
const demoTyping = ref('')
const demoProducts = ref([])
const demoShowOrder = ref(false)
let demoTimer = null

const allProducts = [
  { name: 'Robe Ankara Wax', price: '15 000', color: '#FF6B2C', img: 'dress' },
  { name: 'Sac cuir tressé', price: '8 500', color: '#6C5CE7', img: 'bag' },
  { name: "Boucles d'or Sahel", price: '5 200', color: '#2DD4A8', img: 'earring' },
  { name: 'Sandales perlées', price: '12 000', color: '#FF4D6A', img: 'shoe' },
  { name: 'Bracelet bronze', price: '3 800', color: '#FFAA33', img: 'bracelet' },
  { name: 'Chemise Lin', price: '9 500', color: '#38BDF8', img: 'shirt' },
]

const typeText = (text, cb) => {
  let i = 0
  const interval = setInterval(() => {
    demoTyping.value = text.slice(0, ++i)
    if (i >= text.length) {
      clearInterval(interval)
      if (cb) setTimeout(cb, 400)
    }
  }, 50)
  return interval
}

const runDemo = () => {
  demoStep.value = 1
  // Step 1: type shop name
  setTimeout(() => {
    typeText('Élégance Douala', () => {
      // Step 2: products appear
      setTimeout(() => {
        demoStep.value = 2
        allProducts.forEach((p, i) => {
          setTimeout(() => {
            demoProducts.value = [...demoProducts.value, p]
          }, i * 250)
        })
        // Step 3: order notification
        setTimeout(() => {
          demoStep.value = 3
          demoShowOrder.value = true
          // Loop back
          setTimeout(() => {
            demoStep.value = 0
            demoTyping.value = ''
            demoProducts.value = []
            demoShowOrder.value = false
            setTimeout(runDemo, 2000)
          }, 4000)
        }, 2500)
      }, 600)
    })
  }, 800)
}

let ticking = false
const onScroll = () => {
  if (ticking) return
  ticking = true
  requestAnimationFrame(() => {
    scrollY.value = window.scrollY
    ticking = false
  })
}

onMounted(async () => {
  window.addEventListener('scroll', onScroll, { passive: true })
  await nextTick()
  setTimeout(() => { loaded.value = true }, 200)
  setTimeout(() => { mockupVisible.value = true }, 1200)
  setTimeout(runDemo, 2200)
})

onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
  if (demoTimer) clearInterval(demoTimer)
})

const eased = (factor) => {
  const t = Math.min(scrollY.value / 1000, 1)
  return scrollY.value * factor * (1 - t * 0.3)
}
</script>

<template>
  <section ref="heroRef" class="relative min-h-screen flex items-center justify-center overflow-hidden pt-24 pb-16">
    <!-- Background layers — subtle, not "AI" -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <!-- Warm gradient blobs -->
      <div class="absolute -top-32 right-[10%] w-[500px] h-[500px] bg-brand/6 rounded-full blur-[160px] animate-blob" :style="{ transform: `translateY(${eased(0.03)}px)` }" />
      <div class="absolute bottom-[10%] -left-20 w-[400px] h-[400px] bg-electric/5 rounded-full blur-[120px]" style="animation-delay: -3s" />
      <!-- Grid pattern — very faint -->
      <div class="absolute inset-0 opacity-[0.015]" style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 80px 80px;" />
    </div>

    <!-- Content -->
    <div class="relative z-10 max-w-6xl mx-auto px-6 w-full">
      <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">

        <!-- Left: Text content -->
        <div
          :style="{
            opacity: Math.max(1 - scrollY / 800, 0),
            transform: `translateY(${eased(0.08)}px)`,
          }"
        >
          <!-- Headline -->
          <h1
            class="font-display font-bold text-4xl md:text-5xl lg:text-6xl t-heading leading-[1.1] tracking-tight mb-6 transition-all duration-1000"
            :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'"
          >
            Lancez votre boutique en ligne
            <span class="text-gradient block mt-1">en quelques clics.</span>
          </h1>

          <!-- Subtitle -->
          <p
            class="text-lg md:text-xl t-text-muted leading-relaxed mb-8 max-w-lg transition-all duration-1000 delay-200"
            :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
          >
            Créez votre vitrine, ajoutez vos produits et partagez votre lien.
            Storely rend la vente en ligne <span class="t-text font-medium">simple et accessible</span>.
          </p>

          <!-- CTA -->
          <div
            class="flex flex-col sm:flex-row items-start gap-4 mb-10 transition-all duration-1000 delay-300"
            :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
          >
            <router-link to="/register" class="btn-primary text-lg !px-8 !py-4">
              <span>Créer ma vitrine</span>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </router-link>
            <a href="#features" class="btn-secondary text-lg">
              <span>Découvrir</span>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
            </a>
          </div>

          <!-- Trust signals (compact, not bubble badges) -->
          <div
            class="flex items-center gap-6 text-sm transition-all duration-1000 delay-500"
            :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
          >
            <div class="flex items-center gap-2 t-text-faint">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2DD4A8" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
              <span>Gratuit pour démarrer</span>
            </div>
            <div class="flex items-center gap-2 t-text-faint">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2DD4A8" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
              <span>Prêt en 10 min</span>
            </div>
          </div>
        </div>

        <!-- Right: Animated Browser Mockup -->
        <div
          class="relative transition-all duration-1000 ease-out"
          :class="mockupVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-12'"
          :style="{ transform: `perspective(1200px) rotateY(-2deg) rotateX(${Math.min(scrollY * 0.008, 3)}deg) translateY(${mockupVisible ? 0 : 40}px)` }"
        >
          <!-- Glow behind -->
          <div class="absolute -inset-8 bg-gradient-to-br from-brand/10 via-electric/5 to-transparent blur-3xl rounded-full pointer-events-none" />

          <!-- Browser frame -->
          <div class="relative rounded-2xl overflow-hidden shadow-2xl border" style="border-color: var(--border-subtle); background: var(--bg-secondary);">
            <!-- Browser toolbar -->
            <div class="flex items-center gap-3 px-4 py-3 border-b" style="background: var(--bg-tertiary); border-color: var(--border-default);">
              <!-- Traffic lights -->
              <div class="flex items-center gap-1.5">
                <div class="w-3 h-3 rounded-full bg-[#FF5F57]" />
                <div class="w-3 h-3 rounded-full bg-[#FFBD2E]" />
                <div class="w-3 h-3 rounded-full bg-[#28C840]" />
              </div>
              <!-- URL bar -->
              <div class="flex-1 flex items-center gap-2 px-3 py-1.5 rounded-lg" style="background: var(--input-bg); border: 1px solid var(--border-default);">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#2DD4A8" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                <span class="text-xs t-text-faint font-mono truncate">storely.app/elegance-douala</span>
              </div>
            </div>

            <!-- Browser content — animated demo -->
            <div class="relative overflow-hidden" style="height: 380px; background: var(--bg-primary);">

              <!-- Step 0-1: Creating the store -->
              <transition name="fade">
                <div v-if="demoStep <= 1" class="absolute inset-0 flex flex-col items-center justify-center p-8">
                  <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-brand to-brand-amber flex items-center justify-center mb-6 shadow-lg" style="box-shadow: 0 8px 24px rgba(255,107,44,0.3);">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                  </div>
                  <p class="text-sm t-text-muted mb-4 font-display">Nom de votre boutique</p>
                  <div class="w-64 px-4 py-3 rounded-xl border-2 border-brand/30 text-center" style="background: var(--input-bg);">
                    <span v-if="demoTyping" class="text-lg font-display font-semibold t-heading">{{ demoTyping }}</span>
                    <span v-else class="text-lg t-text-faint">|</span>
                    <span v-if="demoStep === 1 && demoTyping.length < 15" class="inline-block w-0.5 h-5 bg-brand animate-pulse ml-0.5 align-middle" />
                  </div>
                </div>
              </transition>

              <!-- Step 2: Store with products -->
              <transition name="fade">
                <div v-if="demoStep >= 2" class="absolute inset-0 overflow-hidden">
                  <!-- Store header -->
                  <div class="relative">
                    <div class="h-20 w-full" style="background: linear-gradient(135deg, #FF6B2C20, #FFAA3315);" />
                    <div class="px-5 -mt-5 flex items-end gap-3 pb-3">
                      <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-brand to-brand-amber flex items-center justify-center text-white font-bold text-lg shrink-0 shadow-lg font-display">É</div>
                      <div class="pb-1">
                        <div class="flex items-center gap-1.5">
                          <span class="font-display font-semibold text-sm t-heading">Élégance Douala</span>
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" fill="#2DD4A8"/><polyline points="8 12 11 15 16 9" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <span class="text-[11px] t-text-faint">Mode & Accessoires · Douala</span>
                      </div>
                    </div>
                  </div>

                  <!-- Products grid -->
                  <div class="px-5 pb-4">
                    <div class="grid grid-cols-3 gap-2">
                      <div
                        v-for="(p, idx) in demoProducts"
                        :key="idx"
                        class="rounded-xl overflow-hidden transition-all duration-500"
                        :style="{
                          opacity: 1,
                          transform: 'translateY(0) scale(1)',
                          background: 'var(--glass-bg-card)',
                          border: '1px solid var(--border-default)',
                        }"
                      >
                        <div class="aspect-square flex items-center justify-center relative" :style="{ background: `linear-gradient(145deg, ${p.color}15, ${p.color}05)` }">
                          <div class="w-8 h-8 rounded-lg flex items-center justify-center" :style="{ background: `${p.color}18` }">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" :stroke="p.color" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
                          </div>
                        </div>
                        <div class="px-2 py-1.5">
                          <p class="text-[9px] t-text-muted truncate">{{ p.name }}</p>
                          <p class="text-[10px] font-bold" :style="{ color: p.color }">{{ p.price }} F</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </transition>

              <!-- Step 3: Order notification overlay -->
              <transition name="notif-slide">
                <div
                  v-if="demoShowOrder"
                  class="absolute top-4 right-4 left-4 rounded-xl p-3 flex items-center gap-3 shadow-xl z-20"
                  style="background: var(--bg-elevated); border: 1px solid var(--border-subtle);"
                >
                  <div class="w-10 h-10 rounded-xl bg-mint/15 flex items-center justify-center shrink-0">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2DD4A8" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-xs font-display font-semibold t-heading">Nouvelle commande !</p>
                    <p class="text-[11px] t-text-muted truncate">Amina K. — Robe Ankara Wax × 2</p>
                  </div>
                  <span class="text-xs font-bold text-mint shrink-0">30 000 F</span>
                </div>
              </transition>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Scroll indicator -->
    <div
      class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2"
      :style="{ opacity: Math.max(1 - scrollY / 150, 0) }"
    >
      <span class="text-[10px] t-text-faint font-display uppercase tracking-widest">Défiler</span>
      <div class="w-5 h-8 border rounded-full flex justify-center pt-1.5" style="border-color: var(--border-strong);">
        <div class="w-1 h-2 bg-brand/60 rounded-full animate-bounce" />
      </div>
    </div>
  </section>
</template>

<style scoped>
.fade-enter-active { transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
.fade-leave-active { transition: all 0.4s ease-out; }
.fade-enter-from { opacity: 0; transform: scale(0.96); }
.fade-leave-to { opacity: 0; transform: scale(0.96); }

.notif-slide-enter-active { transition: all 0.5s cubic-bezier(0.16, 1, 0.3, 1); }
.notif-slide-leave-active { transition: all 0.3s ease-in; }
.notif-slide-enter-from { opacity: 0; transform: translateY(-20px) scale(0.95); }
.notif-slide-leave-to { opacity: 0; transform: translateY(-10px) scale(0.98); }
</style>
