<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useReveal } from '../../composables/useReveal'

const sectionRef = ref(null)
const headerRef = ref(null)
const stepsRef = ref(null)
useReveal(headerRef)

// Progress de remplissage de la ligne (0 -> 1) liée au scroll dans la section.
const progress = ref(0)
let rafId = null

const onScroll = () => {
  const el = sectionRef.value
  if (!el) return
  const rect = el.getBoundingClientRect()
  const vh = window.innerHeight
  // On démarre quand le haut de la section atteint 75% du viewport
  // et on finit quand le bas atteint 30%
  const start = vh * 0.75
  const end = vh * 0.3
  const total = rect.height + (start - end)
  const scrolled = start - rect.top
  const p = Math.max(0, Math.min(1, scrolled / total))
  progress.value = p
}

const tick = () => {
  onScroll()
  rafId = null
}
const requestTick = () => { if (!rafId) rafId = requestAnimationFrame(tick) }

onMounted(() => {
  window.addEventListener('scroll', requestTick, { passive: true })
  window.addEventListener('resize', requestTick)
  onScroll()
})
onUnmounted(() => {
  window.removeEventListener('scroll', requestTick)
  window.removeEventListener('resize', requestTick)
  if (rafId) cancelAnimationFrame(rafId)
})

const steps = [
  {
    num: '01',
    time: '30 sec',
    title: 'Créez votre compte',
    text: 'Email, numéro, nom de boutique. Pas de carte bancaire, pas d\'engagement. Vous êtes en ligne avant la fin de votre pause.',
    art: 'signup',
  },
  {
    num: '02',
    time: '6 min',
    title: 'Habillez votre boutique',
    text: 'Logo, couleurs, bannière, 5 premiers produits. L\'IA écrit les descriptions pour vous. Vous gardez la main, toujours.',
    art: 'setup',
  },
  {
    num: '03',
    time: 'Immédiat',
    title: 'Partagez votre lien et vendez',
    text: 'storely.app/votre-boutique. Une seule URL à coller dans toutes vos bios réseaux sociaux. Les paiements arrivent sur votre Mobile Money.',
    art: 'sell',
  },
]
</script>

<template>
  <section ref="sectionRef" class="section-lg relative" style="background: var(--section-alt-bg)">
    <div class="container-max">
      <div ref="headerRef" class="reveal-mask text-center max-w-2xl mx-auto mb-14">
        <span class="eyebrow">Moins de 10 minutes</span>
        <h2 class="display-xl mt-4 mb-4">De l'idée à la première vente, sans friction.</h2>
        <p class="text-lg" style="color: var(--text-muted)">
          Trois étapes honnêtes, pas de promesse vide. On vous guide à chaque clic, et on vous laisse aller à votre rythme.
        </p>
      </div>

      <div ref="stepsRef" class="grid md:grid-cols-3 gap-5 relative">
        <!-- Scroll-linked progress line, desktop -->
        <div class="progress-line hidden md:block" :style="{ '--progress': progress }">
          <div class="fill" />
        </div>

        <article v-for="(s, i) in steps" :key="s.num"
                 class="relative rounded-2xl p-6 md:p-7 transition-all duration-500"
                 :style="{
                   background: progress > (i / steps.length) ? 'var(--card-bg)' : 'var(--card-bg)',
                   border: progress > (i / steps.length) ? '1px solid rgba(255,107,44,0.25)' : '1px solid var(--card-border)',
                   boxShadow: progress > (i / steps.length) ? 'var(--card-shadow-hover)' : 'var(--card-shadow)',
                   transform: progress > (i / steps.length) ? 'translateY(-2px)' : 'translateY(0)',
                 }">
          <div class="flex items-start justify-between mb-5">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-display font-black text-base text-white transition-all duration-500"
                 :style="{
                   background: i === 0 ? 'linear-gradient(135deg, #FF6B2C, #FFAA33)'
                            : i === 1 ? 'linear-gradient(135deg, #6C5CE7, #A78BFA)'
                            :            'linear-gradient(135deg, #2DD4A8, #38BDF8)',
                   transform: progress > (i / steps.length) ? 'scale(1.05)' : 'scale(1)',
                   boxShadow: progress > (i / steps.length) ? '0 10px 24px -6px rgba(255,107,44,0.35)' : 'none',
                 }">
              {{ s.num }}
            </div>
            <span class="badge">{{ s.time }}</span>
          </div>
          <h3 class="font-display font-bold text-lg md:text-xl mb-3 tracking-tight" style="color: var(--text-primary)">{{ s.title }}</h3>
          <p class="text-sm leading-relaxed mb-6" style="color: var(--text-muted)">{{ s.text }}</p>

          <!-- Petite illustration inline par étape -->
          <div class="rounded-xl overflow-hidden p-3 flex items-center gap-2 text-xs font-mono"
               style="background: var(--bg-tertiary); border: 1px solid var(--border-default); color: var(--text-muted);">
            <template v-if="s.art === 'signup'">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2DD4A8" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              <span>Compte créé, <span style="color: var(--color-brand)">0 F</span></span>
            </template>
            <template v-else-if="s.art === 'setup'">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6C5CE7" stroke-width="2.5"><path d="M12 2l2.4 7.4H22l-6 4.5 2.3 7.3L12 16.6l-6.3 4.6L8 13.9 2 9.4h7.6z"/></svg>
              <span>5 fiches générées par IA</span>
            </template>
            <template v-else>
              <span class="inline-flex w-3.5 h-3.5 rounded-full" style="background: #25D366" />
              <span class="truncate">storely.app/votre-boutique</span>
            </template>
          </div>
        </article>
      </div>

      <div class="text-center mt-12">
        <router-link to="/register" class="btn-primary btn-lg shine-sweep">
          Commencer maintenant, c'est gratuit
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
          </svg>
        </router-link>
      </div>
    </div>
  </section>
</template>
