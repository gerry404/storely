<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useReveal } from '../../composables/useReveal'
import { useSnapCarousel } from '../../composables/useParallax'

const sectionRef = ref(null)
const headerRef = ref(null)
const statsGridRef = ref(null)
const carouselRef = ref(null)
useReveal(headerRef)
useReveal(statsGridRef)

const { activeIndex, scrollTo } = useSnapCarousel(carouselRef)

const stats = [
  { label: 'Boutiques actives', value: 340, suffix: '+', accent: '#FF6B2C' },
  { label: 'Temps moyen de mise en ligne', value: 9, suffix: ' min', accent: '#6C5CE7' },
  { label: 'Taux de réussite Mobile Money', value: 98.7, suffix: ' %', accent: '#2DD4A8', decimals: 1 },
  { label: 'Commandes traitées', value: 12540, suffix: '+', accent: '#FFAA33' },
]

const animated = ref(stats.map(() => 0))
let observer = null

onMounted(() => {
  if (!statsGridRef.value) return
  observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((e) => {
        if (e.isIntersecting) {
          stats.forEach((s, i) => animateValue(s.value, i, s.decimals || 0))
          observer.unobserve(e.target)
        }
      })
    },
    { threshold: 0.3 }
  )
  observer.observe(statsGridRef.value)
})
onUnmounted(() => observer?.disconnect())

const animateValue = (target, idx, decimals) => {
  const duration = 1500
  const start = performance.now()
  const step = (now) => {
    const t = Math.min((now - start) / duration, 1)
    const eased = 1 - Math.pow(1 - t, 3)
    animated.value[idx] = decimals > 0 ? +(target * eased).toFixed(decimals) : Math.floor(target * eased)
    if (t < 1) requestAnimationFrame(step)
    else animated.value[idx] = target
  }
  requestAnimationFrame(step)
}

const fmt = (n) => n.toLocaleString('fr-FR')

const testimonials = [
  {
    name: 'Amina Ngonga',
    role: 'Fondatrice, Élégance Douala',
    city: 'Douala',
    photo: '/landing/fashion.jpg',
    color: '#FF6B2C',
    quote: 'Avant, je vendais sur Instagram avec des captures d\'écran de paiements. Aujourd\'hui, mes clientes ouvrent mon lien, payent en MoMo, et je reçois une notification. En un mois sur Storely, j\'ai fait plus qu\'en six mois sur les réseaux.',
    metric: { value: '+340%', label: 'de ventes en 30 jours' },
  },
  {
    name: 'Joseph Mbarga',
    role: 'Gérant, Tech Bamenda',
    city: 'Bamenda',
    photo: '/landing/call.jpg',
    color: '#6C5CE7',
    quote: 'Tout est au même endroit. Les commandes, les paiements, les livraisons, le chat client. J\'ai arrêté de jongler entre WhatsApp, Excel et mes comptes MoMo. Je passe maintenant mes journées à vendre, pas à faire de l\'administration.',
    metric: { value: '92 commandes', label: 'en novembre' },
  },
  {
    name: 'Claire Tchoumi',
    role: 'Créatrice, Maison Claire',
    city: 'Yaoundé',
    photo: '/landing/vibrant.jpg',
    color: '#2DD4A8',
    quote: 'La relance panier abandonné m\'a rapporté 280 000 FCFA le premier mois, sans que je lève le petit doigt. C\'est comme avoir une vendeuse qui travaille pour moi 24 heures sur 24, mais qui ne réclame pas de salaire.',
    metric: { value: '280 000 F', label: 'récupérés en panier abandonné' },
  },
]
</script>

<template>
  <section ref="sectionRef" class="section-lg relative overflow-hidden">
    <div class="halo-brand" style="top: 30%; right: -20%; opacity: 0.5;" />

    <div class="container-max relative">
      <div ref="headerRef" class="reveal-mask max-w-2xl mx-auto text-center mb-12">
        <span class="eyebrow">Des chiffres, pas des promesses</span>
        <h2 class="display-xl mt-4 mb-4">Les marchands camerounais vendent déjà.</h2>
        <p class="text-lg" style="color: var(--text-muted)">
          Pas de témoignages fabriqués, pas de chiffres gonflés. Des entrepreneurs réels qui encaissent chaque jour avec Storely.
        </p>
      </div>

      <!-- Stats strip -->
      <div ref="statsGridRef" class="reveal-stagger grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-14">
        <div v-for="(s, i) in stats" :key="s.label"
             class="relative p-5 md:p-6 rounded-2xl overflow-hidden"
             style="background: var(--card-bg); border: 1px solid var(--card-border); box-shadow: var(--card-shadow);">
          <div class="absolute -top-8 -right-8 w-24 h-24 rounded-full opacity-70" :style="{ background: `radial-gradient(closest-side, ${s.accent}22, transparent 70%)` }" />
          <p class="font-display font-black text-3xl md:text-4xl mb-1" :style="{ color: s.accent }">
            {{ fmt(animated[i]) }}<span class="text-xl md:text-2xl">{{ s.suffix }}</span>
          </p>
          <p class="text-xs md:text-sm" style="color: var(--text-muted)">{{ s.label }}</p>
        </div>
      </div>

      <!-- Desktop 3-col grid -->
      <div class="hidden md:grid md:grid-cols-3 gap-5">
        <article v-for="t in testimonials" :key="t.name" class="card lift-card p-0 overflow-hidden flex flex-col">
          <!-- Photo header -->
          <div class="relative aspect-[5/3] overflow-hidden">
            <img :src="t.photo" :alt="t.name" class="absolute inset-0 w-full h-full object-cover" loading="lazy" />
            <div class="absolute inset-0" style="background: linear-gradient(180deg, transparent 40%, rgba(15,15,20,0.6));" />
            <div class="absolute bottom-3 left-4 right-4 flex items-end justify-between">
              <div class="text-white">
                <p class="font-display font-bold text-base">{{ t.name }}</p>
                <p class="text-xs opacity-85">{{ t.role }}</p>
              </div>
              <span class="text-[10px] font-display font-bold px-2 py-1 rounded-full"
                    style="background: rgba(255,255,255,0.95); color: #0F0F14">
                {{ t.city }}
              </span>
            </div>
          </div>
          <div class="p-6 flex flex-col flex-1">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" :stroke="t.color" stroke-width="2" class="mb-3 opacity-70">
              <path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/>
              <path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/>
            </svg>
            <p class="text-sm leading-relaxed flex-1 mb-5" style="color: var(--text-primary)">« {{ t.quote }} »</p>
            <div class="p-3.5 rounded-xl" :style="{ background: `${t.color}0f`, border: `1px solid ${t.color}26` }">
              <p class="font-display font-black text-xl" :style="{ color: t.color }">{{ t.metric.value }}</p>
              <p class="text-[11px]" style="color: var(--text-muted)">{{ t.metric.label }}</p>
            </div>
          </div>
        </article>
      </div>

      <!-- Mobile carousel -->
      <div class="md:hidden -mx-6">
        <div ref="carouselRef" class="snap-row">
          <article v-for="t in testimonials" :key="t.name" class="card p-0 overflow-hidden flex flex-col">
            <div class="relative aspect-[4/3] overflow-hidden">
              <img :src="t.photo" :alt="t.name" class="absolute inset-0 w-full h-full object-cover" loading="lazy" />
              <div class="absolute inset-0" style="background: linear-gradient(180deg, transparent 40%, rgba(15,15,20,0.6));" />
              <div class="absolute bottom-3 left-4 right-4 flex items-end justify-between">
                <div class="text-white">
                  <p class="font-display font-bold text-base">{{ t.name }}</p>
                  <p class="text-xs opacity-85">{{ t.role }}</p>
                </div>
                <span class="text-[10px] font-display font-bold px-2 py-1 rounded-full"
                      style="background: rgba(255,255,255,0.95); color: #0F0F14">
                  {{ t.city }}
                </span>
              </div>
            </div>
            <div class="p-5 flex flex-col flex-1">
              <p class="text-sm leading-relaxed flex-1 mb-4" style="color: var(--text-primary)">« {{ t.quote }} »</p>
              <div class="p-3 rounded-xl" :style="{ background: `${t.color}0f`, border: `1px solid ${t.color}26` }">
                <p class="font-display font-black text-lg" :style="{ color: t.color }">{{ t.metric.value }}</p>
                <p class="text-[11px]" style="color: var(--text-muted)">{{ t.metric.label }}</p>
              </div>
            </div>
          </article>
        </div>
        <div class="snap-dots px-6">
          <button v-for="(t, i) in testimonials" :key="i"
                  :class="{ active: activeIndex === i }"
                  @click="scrollTo(i)"
                  :aria-label="`Témoignage ${i + 1}`" />
        </div>
      </div>
    </div>
  </section>
</template>
