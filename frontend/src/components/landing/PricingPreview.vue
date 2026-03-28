<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { useTilt } from '../../composables/useTilt'
import { useMagnetic } from '../../composables/useMagnetic'

const sectionRef = ref(null)
const cardRefs = ref([])
const btnRefs = ref([])
const progress = ref(0)
const tiltCleanups = []
const magneticCleanups = []
const annual = ref(false)

const plans = [
  {
    id: 'free',
    name: 'Gratuit',
    price: '0',
    priceAnnual: '0',
    period: '',
    desc: 'Pour tester et commencer',
    color: 'white',
    features: [
      'Jusqu\'a 5 produits',
      '1 image par produit',
      'Lien storely.app/votre-boutique',
      'Contact WhatsApp',
      'Produits digitaux (commission 15%)',
    ],
    cta: 'Commencer gratuitement',
    popular: false,
  },
  {
    id: 'starter',
    name: 'Starter',
    price: '3 900',
    priceAnnual: '2 900',
    promoPrice: '1 443',
    savingsAnnual: '12 000',
    period: '/mois',
    desc: 'Pour les boutiques en croissance',
    color: 'white',
    features: [
      'Jusqu\'a 20 produits',
      '3 images par produit',
      'Statistiques de visites',
      'Gestion des commandes',
      'Bouton WhatsApp',
      'Branding Storely réduit',
      'Produits digitaux (commission 8%)',
    ],
    cta: 'Commencer',
    popular: false,
  },
  {
    id: 'pro',
    name: 'Pro',
    price: '9 900',
    priceAnnual: '7 400',
    promoPrice: '3 663',
    savingsAnnual: '30 000',
    period: '/mois',
    desc: 'Pour dominer votre marché',
    color: 'brand',
    features: [
      'Produits illimités',
      '10 images par produit',
      'Statistiques complètes',
      'Gestion des stocks',
      'Commandes en ligne',
      'Codes promo & badges',
      'Badge boutique vérifiée',
      'Précommandes',
      'Export de données',
      'Aucun branding Storely',
      'Produits digitaux (commission 4%)',
      'Support prioritaire',
    ],
    cta: 'Choisir Pro',
    popular: true,
  }
]

const displayPrice = (plan) => {
  if (plan.id === 'free') return '0'
  if (plan.promoPrice && !annual.value) return plan.promoPrice
  return annual.value ? plan.priceAnnual : plan.price
}

const originalPrice = (plan) => {
  if (plan.id === 'free' || annual.value) return null
  if (plan.promoPrice) return plan.price
  return null
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

onMounted(async () => {
  window.addEventListener('scroll', onScroll, { passive: true })
  await nextTick()
  cardRefs.value.forEach(el => {
    if (el) {
      const cleanup = useTilt(el, 4)
      if (cleanup) tiltCleanups.push(cleanup)
    }
  })
  btnRefs.value.forEach(el => {
    if (el) {
      const cleanup = useMagnetic(el, 0.2)
      if (cleanup) magneticCleanups.push(cleanup)
    }
  })
})

onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
  tiltCleanups.forEach(fn => fn())
  magneticCleanups.forEach(fn => fn())
})

const planStyle = (i) => {
  const delay = 0.15 + i * 0.15
  const p = Math.min(Math.max((progress.value - delay) / 0.5, 0), 1)
  const ease = 1 - Math.pow(1 - p, 4)
  const yOff = (1 - ease) * 60
  const scale = 0.92 + ease * 0.08
  return {
    opacity: ease,
    transform: `translateY(${yOff}px) scale(${scale})`,
    filter: `blur(${(1 - ease) * 3}px)`,
    transition: 'none'
  }
}
</script>

<template>
  <section ref="sectionRef" class="py-24 md:py-32 relative" id="pricing">
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-brand/5 rounded-full blur-[150px] pointer-events-none" />

    <div class="relative max-w-6xl mx-auto px-6">
      <div
        class="text-center mb-16"
        :style="{
          opacity: Math.min(Math.max(progress * 2.5, 0), 1),
          transform: `translateY(${Math.max((1 - progress * 2) * 30, 0)}px)`,
          filter: `blur(${Math.max((1 - progress * 2.5) * 3, 0)}px)`
        }"
      >
        <span class="inline-block px-4 py-1.5 rounded-full glass text-xs font-display font-medium text-brand uppercase tracking-wider mb-6">
          Tarifs transparents
        </span>
        <h2 class="font-display font-bold text-3xl md:text-5xl text-white tracking-tight mb-4">
          Investissez dans<br />
          <span class="text-gradient">votre visibilité</span>
        </h2>
        <p class="text-white/40 max-w-lg mx-auto mb-8">Moins cher qu'un espace publicitaire. Plus efficace qu'une page Facebook.</p>

        <!-- Annual toggle -->
        <div class="flex items-center justify-center gap-3">
          <span class="text-sm font-medium" :class="!annual ? 'text-white' : 'text-white/40'">Mensuel</span>
          <button
            @click="annual = !annual"
            class="relative w-[52px] h-[28px] rounded-full transition-colors duration-300 shrink-0"
            :class="annual ? 'bg-brand' : 'bg-white/10'"
          >
            <span
              class="absolute top-[2px] left-[2px] w-6 h-6 rounded-full bg-white shadow-md transition-transform duration-300"
              :style="{ transform: annual ? 'translateX(24px)' : 'translateX(0)' }"
            />
          </button>
          <span class="text-sm font-medium" :class="annual ? 'text-white' : 'text-white/40'">
            Annuel
            <span class="text-brand text-xs font-semibold ml-1">2 mois offerts</span>
          </span>
        </div>

        <!-- Promo banner -->
        <div v-if="!annual" class="mt-6 inline-flex items-center gap-2 px-4 py-2 rounded-full bg-brand/10 border border-brand/20">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#FF6B2C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
          <span class="text-sm text-brand font-medium">Lancement : <strong>-63%</strong> les 3 premiers mois</span>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-5 max-w-5xl mx-auto">
        <div
          v-for="(plan, i) in plans"
          :key="i"
          :ref="el => { if (el) cardRefs[i] = el }"
          class="tilt-card relative rounded-3xl p-[1px] group"
          :class="plan.popular ? 'bg-gradient-to-b from-brand/60 to-brand/10 md:scale-105 md:-my-2' : ''"
          :style="planStyle(i)"
        >
          <div class="tilt-shine" />

          <div v-if="plan.popular" class="absolute -top-3 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-brand text-white text-xs font-display font-bold z-10">
            Populaire
          </div>

          <div class="h-full rounded-3xl p-7 relative overflow-hidden" style="background: var(--bg-secondary)">
            <div v-if="plan.popular" class="absolute top-0 right-0 w-40 h-40 bg-brand/10 rounded-full blur-3xl" />

            <div class="relative">
              <h3 class="font-display font-bold text-lg text-white mb-1">{{ plan.name }}</h3>
              <p class="text-sm text-white/40 mb-5">{{ plan.desc }}</p>

              <div class="flex items-baseline gap-1 mb-1">
                <span v-if="originalPrice(plan)" class="text-lg text-white/30 line-through mr-1">{{ originalPrice(plan) }}</span>
                <span class="font-display font-bold text-3xl text-white">{{ displayPrice(plan) }}</span>
                <span class="text-sm text-white/30">F CFA</span>
                <span v-if="plan.period" class="text-sm text-white/30">{{ plan.period }}</span>
              </div>

              <p v-if="!annual && plan.promoPrice" class="text-xs text-brand mb-5">pendant 3 mois, puis {{ plan.price }} F/mois</p>
              <p v-else-if="annual && plan.savingsAnnual" class="text-xs text-emerald-400 mb-5">Économisez {{ plan.savingsAnnual }} F CFA/an</p>
              <p v-else class="mb-5">&nbsp;</p>

              <ul class="space-y-2.5 mb-7">
                <li v-for="(f, j) in plan.features" :key="j" class="flex items-start gap-2.5">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" :stroke="plan.popular ? '#FF6B2C' : '#2DD4A8'" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 mt-0.5"><polyline points="20 6 9 17 4 12"/></svg>
                  <span class="text-sm text-white/60">{{ f }}</span>
                </li>
              </ul>

              <router-link
                :ref="el => { if (el && el.$el) btnRefs[i] = el.$el }"
                to="/register"
                class="magnetic block text-center py-3 rounded-xl font-medium text-sm transition-all"
                :class="plan.popular
                  ? 'bg-brand text-white hover:bg-brand/90 shadow-lg shadow-brand/20'
                  : plan.id === 'free'
                    ? 'bg-white/5 text-white/70 hover:bg-white/10 border border-white/10'
                    : 'bg-white/10 text-white hover:bg-white/15'"
              >
                {{ plan.cta }}
              </router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Social proof -->
      <div
        class="text-center mt-10"
        :style="{ opacity: Math.min(Math.max((progress - 0.6) * 3, 0), 1) }"
      >
        <p class="text-sm text-white/40 mb-2">Paiement par Mobile Money (Orange, MTN) · Annulez a tout moment</p>
        <p class="text-xs text-white/25">Rejoint par 2 500+ commercants a travers le Cameroun</p>
      </div>
    </div>
  </section>
</template>
