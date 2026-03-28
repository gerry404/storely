<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useTilt } from '../../composables/useTilt'

const activeStore = ref(0)
const sectionRef = ref(null)
const cardRef = ref(null)
const progress = ref(0)
let tiltCleanup = null

const stores = [
  { name: 'Élégance Mode', category: 'Mode & Accessoires', location: 'Douala, Akwa', color: 'from-brand to-brand-amber', stats: { products: 45, views: '12.3k', orders: 89 } },
  { name: 'TechZone CM', category: 'Électronique', location: 'Yaoundé, Bastos', color: 'from-electric to-sky', stats: { products: 120, views: '28.7k', orders: 234 } },
  { name: 'Beauty Palace', category: 'Cosmétiques', location: 'Douala, Bonapriso', color: 'from-brand-coral to-brand', stats: { products: 78, views: '9.1k', orders: 156 } }
]

let ticking = false
const onScroll = () => {
  if (ticking) return
  ticking = true
  requestAnimationFrame(() => {
    if (!sectionRef.value) { ticking = false; return }
    const rect = sectionRef.value.getBoundingClientRect()
    const vh = window.innerHeight
    progress.value = Math.min(Math.max(1 - rect.top / (vh * 0.7), 0), 1.5)
    ticking = false
  })
}

const initTilt = async () => {
  if (tiltCleanup) tiltCleanup()
  await nextTick()
  if (cardRef.value) {
    tiltCleanup = useTilt(cardRef.value, 4)
  }
}

onMounted(() => {
  window.addEventListener('scroll', onScroll, { passive: true })
  initTilt()
})
onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
  if (tiltCleanup) tiltCleanup()
})

watch(activeStore, () => initTilt())

const cardEnter = () => {
  const p = Math.min(Math.max((progress.value - 0.2) / 0.5, 0), 1)
  const ease = 1 - Math.pow(1 - p, 4)
  return {
    opacity: ease,
    transform: `perspective(1200px) rotateX(${(1 - ease) * 6}deg) rotateY(${(1 - ease) * -2}deg) translateY(${(1 - ease) * 50}px) scale(${0.93 + ease * 0.07})`,
    filter: `blur(${(1 - ease) * 3}px)`,
    transition: 'none'
  }
}

const tabStyle = (i) => {
  const delay = 0.1 + i * 0.08
  const p = Math.min(Math.max((progress.value - delay) / 0.4, 0), 1)
  const ease = 1 - Math.pow(1 - p, 3)
  return {
    opacity: ease,
    transform: `translateY(${(1 - ease) * 15}px)`,
    transition: 'none'
  }
}
</script>

<template>
  <section ref="sectionRef" class="py-24 md:py-32 relative">
    <div class="absolute right-0 bottom-0 w-[500px] h-[500px] bg-brand-amber/5 rounded-full blur-[150px] pointer-events-none" />

    <div class="max-w-6xl mx-auto px-6">
      <div
        class="text-center mb-16"
        :style="{
          opacity: Math.min(Math.max(progress * 2.5, 0), 1),
          transform: `translateY(${Math.max((1 - progress * 2) * 30, 0)}px)`,
          filter: `blur(${Math.max((1 - progress * 2.5) * 3, 0)}px)`
        }"
      >
        <span class="inline-block px-4 py-1.5 rounded-full glass text-xs font-display font-medium text-brand-amber uppercase tracking-wider mb-6">
          Vitrines réelles
        </span>
        <h2 class="font-display font-bold text-3xl md:text-5xl text-white tracking-tight mb-4">
          Déjà <span class="text-gradient">2 500+ boutiques</span><br />
          font confiance à Storely
        </h2>
      </div>

      <!-- Store selector tabs with stagger -->
      <div class="flex justify-center gap-3 mb-12">
        <button
          v-for="(store, i) in stores"
          :key="i"
          @click="activeStore = i"
          class="px-5 py-2.5 rounded-xl text-sm font-display font-medium transition-all duration-300"
          :class="activeStore === i
            ? 'bg-white/10 text-white border border-white/10 shadow-lg shadow-brand/5'
            : 'text-white/40 hover:text-white/60 border border-transparent'"
          :style="tabStyle(i)"
        >
          {{ store.name }}
        </button>
      </div>

      <!-- Active store showcase with tilt -->
      <div :style="cardEnter()">
        <div ref="cardRef" class="tilt-card glass-card rounded-3xl p-6 md:p-8 max-w-4xl mx-auto !transform-none">
          <div class="tilt-shine" />
          <div class="flex flex-col md:flex-row gap-8 relative">
            <div class="flex-1">
              <div class="flex items-center gap-4 mb-6">
                <div :class="`w-14 h-14 rounded-2xl bg-gradient-to-br ${stores[activeStore].color} flex items-center justify-center text-white font-display font-bold text-xl`">
                  {{ stores[activeStore].name[0] }}
                </div>
                <div>
                  <h3 class="font-display font-bold text-xl text-white">{{ stores[activeStore].name }}</h3>
                  <p class="text-sm text-white/40">{{ stores[activeStore].category }} · {{ stores[activeStore].location }}</p>
                </div>
              </div>
              <div class="grid grid-cols-3 gap-2.5">
                <div v-for="j in 6" :key="j" class="aspect-square rounded-xl bg-white/5 flex items-center justify-center group hover:bg-white/8 transition-all duration-300 hover:scale-105">
                  <svg class="w-6 h-6 text-white/10 group-hover:text-white/20 transition-colors" fill="currentColor" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                </div>
              </div>
            </div>

            <div class="md:w-64 space-y-4">
              <div v-for="(stat, key) in { 'Produits': stores[activeStore].stats.products, 'Vues / mois': stores[activeStore].stats.views, 'Commandes': stores[activeStore].stats.orders }"
                :key="key"
                class="glass rounded-xl p-4 transition-all duration-500 hover:bg-white/8"
              >
                <p class="text-xs text-white/40 font-display uppercase tracking-wider mb-1">{{ key }}</p>
                <p :class="`font-display font-bold text-2xl ${key === 'Vues / mois' ? 'text-brand' : key === 'Commandes' ? 'text-mint' : 'text-white'}`">
                  {{ stat }}
                </p>
              </div>
              <router-link to="/register" class="btn-primary !w-full !justify-center !text-sm mt-2">
                Créer la mienne
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
