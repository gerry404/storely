<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'

const sectionRef = ref(null)
const progress = ref(0)
const activeIndex = ref(0)
const isTransitioning = ref(false)
const touchStartX = ref(0)
let autoPlayTimer = null

const testimonials = [
  { name: 'Fatima B.', role: 'Vendeuse de tissus, Douala', text: 'Avant Storely, je partageais mes tissus sur WhatsApp avec des photos floues. Maintenant mes clientes voient tout en ligne et me contactent directement. Mes ventes ont doublé !', avatar: 'F', gradient: 'from-brand to-brand-amber', rating: 5 },
  { name: 'Patrick M.', role: 'Boutique électronique, Yaoundé', text: 'En 10 minutes ma boutique était en ligne. Je mets mon lien Storely sur toutes mes cartes de visite. Les clients me trouvent maintenant sur Google.', avatar: 'P', gradient: 'from-electric to-sky', rating: 5 },
  { name: 'Carine N.', role: 'Cosmétiques & beauté, Douala', text: 'Le badge vérifié a changé la donne. Les gens font confiance. Et les statistiques me montrent exactement quels produits marchent le mieux.', avatar: 'C', gradient: 'from-brand-coral to-brand', rating: 5 },
  { name: 'Abdou K.', role: 'Épicerie fine, Yaoundé', text: "Mes clients commandent directement en ligne maintenant. Fini les messages WhatsApp interminables. Tout est organisé, clair, et professionnel.", avatar: 'A', gradient: 'from-mint to-electric', rating: 5 },
  { name: 'Sandrine T.', role: 'Boutique artisanale, Bafoussam', text: "Storely m'a permis de toucher des clients bien au-delà de ma ville. Je reçois des commandes de Douala et même de l'étranger.", avatar: 'S', gradient: 'from-brand-amber to-brand-coral', rating: 5 },
]

// Visible cards: show 3 on desktop with the active one centered
const visibleCards = computed(() => {
  const cards = []
  for (let offset = -1; offset <= 1; offset++) {
    const idx = (activeIndex.value + offset + testimonials.length) % testimonials.length
    cards.push({ ...testimonials[idx], offset, idx })
  }
  return cards
})

function goTo(i) {
  if (isTransitioning.value) return
  isTransitioning.value = true
  activeIndex.value = i
  resetAutoPlay()
  setTimeout(() => { isTransitioning.value = false }, 500)
}

function next() {
  goTo((activeIndex.value + 1) % testimonials.length)
}

function prev() {
  goTo((activeIndex.value - 1 + testimonials.length) % testimonials.length)
}

function resetAutoPlay() {
  if (autoPlayTimer) clearInterval(autoPlayTimer)
  autoPlayTimer = setInterval(next, 5000)
}

function onTouchStart(e) {
  touchStartX.value = e.touches[0].clientX
}
function onTouchEnd(e) {
  const diff = touchStartX.value - e.changedTouches[0].clientX
  if (Math.abs(diff) > 50) {
    diff > 0 ? next() : prev()
  }
}

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

onMounted(() => {
  window.addEventListener('scroll', onScroll, { passive: true })
  resetAutoPlay()
})
onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
  if (autoPlayTimer) clearInterval(autoPlayTimer)
})

const cardTransform = (offset) => {
  const absOff = Math.abs(offset)
  return {
    transform: `translateX(${offset * 105}%) scale(${1 - absOff * 0.1})`,
    opacity: 1 - absOff * 0.35,
    zIndex: 10 - absOff,
    filter: absOff > 0 ? 'blur(2px)' : 'blur(0)',
    transition: 'all 0.6s cubic-bezier(0.16, 1, 0.3, 1)',
  }
}
</script>

<template>
  <section ref="sectionRef" class="relative overflow-hidden">
    <!-- Wavy top edge -->
    <div class="absolute top-0 left-0 right-0 z-10" style="margin-top: -1px;">
      <svg viewBox="0 0 1440 70" preserveAspectRatio="none" class="block w-full h-[40px] md:h-[70px]">
        <path d="M0,70 C480,10 960,60 1440,20 L1440,0 L0,0Z" fill="var(--bg-primary)" />
      </svg>
    </div>
    <!-- Accent bg -->
    <div class="absolute inset-0 pointer-events-none" style="background: linear-gradient(180deg, rgba(45,212,168,0.03) 0%, rgba(255,107,44,0.04) 50%, rgba(45,212,168,0.02) 100%);" />
    <!-- Wavy bottom edge -->
    <div class="absolute bottom-0 left-0 right-0 z-10" style="margin-bottom: -1px;">
      <svg viewBox="0 0 1440 70" preserveAspectRatio="none" class="block w-full h-[40px] md:h-[70px]">
        <path d="M0,0 C240,60 720,10 1080,50 C1280,60 1400,30 1440,40 L1440,70 L0,70Z" fill="var(--bg-primary)" />
      </svg>
    </div>
    <div class="py-24 md:py-32 relative">
    <div class="absolute inset-0 pointer-events-none" style="background: linear-gradient(to bottom, var(--bg-primary), var(--bg-secondary), var(--bg-primary))" />

    <div class="relative max-w-6xl mx-auto px-6">
      <!-- Header -->
      <div
        class="text-center mb-16"
        :style="{
          opacity: Math.min(Math.max(progress * 2.5, 0), 1),
          transform: `translateY(${Math.max((1 - progress * 2) * 30, 0)}px)`,
          filter: `blur(${Math.max((1 - progress * 2.5) * 3, 0)}px)`
        }"
      >
        <span class="inline-block px-4 py-1.5 rounded-full glass text-xs font-display font-medium text-electric uppercase tracking-wider mb-6">
          Témoignages
        </span>
        <h2 class="font-display font-bold text-3xl md:text-5xl text-white tracking-tight">
          Ce qu'en disent<br />
          <span class="text-gradient-cool">nos commerçants</span>
        </h2>
      </div>

      <!-- Carousel container -->
      <div
        class="relative max-w-2xl mx-auto"
        :style="{
          opacity: Math.min(Math.max((progress - 0.15) * 2, 0), 1),
          transform: `translateY(${Math.max((1 - progress * 1.5) * 25, 0)}px)`,
        }"
        @touchstart="onTouchStart"
        @touchend="onTouchEnd"
      >
        <!-- Cards stack -->
        <div class="relative" style="height: 320px;">
          <div
            v-for="card in visibleCards"
            :key="card.idx"
            class="testimonial-card absolute inset-0 rounded-2xl p-8 cursor-default"
            :style="cardTransform(card.offset)"
          >
            <!-- Quote icon -->
            <div class="flex items-center gap-3 mb-5">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" class="text-brand/20 shrink-0"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z" fill="currentColor"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z" fill="currentColor"/></svg>
              <!-- Stars -->
              <div class="flex gap-0.5">
                <svg v-for="s in card.rating" :key="s" width="14" height="14" viewBox="0 0 24 24" fill="#FFAA33">
                  <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
              </div>
            </div>

            <p class="text-[15px] text-white/60 leading-relaxed mb-8">{{ card.text }}</p>

            <div class="flex items-center gap-3 mt-auto pt-5 border-t border-white/5">
              <div :class="`w-11 h-11 rounded-full bg-gradient-to-br ${card.gradient} flex items-center justify-center text-white font-display font-bold text-sm shadow-lg`">
                {{ card.avatar }}
              </div>
              <div>
                <p class="font-display font-semibold text-sm text-white">{{ card.name }}</p>
                <p class="text-xs text-white/30">{{ card.role }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Navigation -->
        <div class="flex items-center justify-center gap-6 mt-8">
          <!-- Prev arrow -->
          <button
            @click="prev"
            class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 hover:bg-white/10"
            style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06);"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
          </button>

          <!-- Dots -->
          <div class="flex items-center gap-2">
            <button
              v-for="(t, i) in testimonials"
              :key="i"
              @click="goTo(i)"
              class="transition-all duration-500 rounded-full"
              :class="activeIndex === i ? 'w-6 h-2' : 'w-2 h-2'"
              :style="{
                background: activeIndex === i ? 'linear-gradient(90deg, #FF6B2C, #FFAA33)' : 'rgba(255,255,255,0.15)',
                boxShadow: activeIndex === i ? '0 0 8px rgba(255,107,44,0.4)' : 'none',
              }"
            />
          </div>

          <!-- Next arrow -->
          <button
            @click="next"
            class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 hover:bg-white/10"
            style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06);"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
          </button>
        </div>
      </div>
    </div>
    </div>
  </section>
</template>

<style scoped>
.testimonial-card {
  background: rgba(255, 255, 255, 0.025);
  border: 1px solid rgba(255, 255, 255, 0.06);
  backdrop-filter: blur(12px);
  display: flex;
  flex-direction: column;
}
</style>
