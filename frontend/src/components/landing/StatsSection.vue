<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const sectionRef = ref(null)
const visible = ref(false)

const stats = [
  { label: 'Boutiques créées', value: 340, suffix: '+', accent: '#FF6B2C' },
  { label: 'Temps moyen de mise en ligne', value: 9, suffix: ' min', accent: '#6C5CE7' },
  { label: 'Taux de livraison Mobile Money', value: 98.7, suffix: ' %', accent: '#2DD4A8', decimals: 1 },
  { label: 'Commandes traitées', value: 12540, suffix: '+', accent: '#FFAA33' },
]

const animated = ref(stats.map(() => 0))

onMounted(() => {
  if (!sectionRef.value) return
  const observer = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting && !visible.value) {
        visible.value = true
        stats.forEach((s, i) => animateValue(s.value, i, s.decimals || 0))
      }
    })
  }, { threshold: 0.2 })
  observer.observe(sectionRef.value)
  onUnmounted(() => observer.disconnect())
})

const animateValue = (target, idx, decimals) => {
  const duration = 1400
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
</script>

<template>
  <section ref="sectionRef" class="section relative overflow-hidden" style="background: var(--bg-inverse); color: var(--text-on-inverse)">
    <div class="container-max relative">
      <div class="text-center max-w-2xl mx-auto mb-14">
        <span class="eyebrow" style="color: var(--color-brand-light)">En chiffres</span>
        <h2 class="display-xl mt-4 mb-4" style="color: white">Des résultats concrets, pas des promesses.</h2>
        <p class="text-lg" style="color: rgba(255,255,255,0.6)">
          Des marchands camerounais qui encaissent chaque jour grâce à Storely.
        </p>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
        <div v-for="(s, i) in stats" :key="s.label" class="relative p-6 md:p-8 rounded-2xl overflow-hidden" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08)">
          <div class="absolute -top-10 -right-10 w-32 h-32 rounded-full opacity-20" :style="{ background: `radial-gradient(closest-side, ${s.accent}, transparent)` }" />
          <div class="relative">
            <p class="font-display font-black text-4xl md:text-5xl mb-2" :style="{ color: s.accent }">
              {{ fmt(animated[i]) }}<span class="text-2xl md:text-3xl">{{ s.suffix }}</span>
            </p>
            <p class="text-xs md:text-sm" style="color: rgba(255,255,255,0.65)">{{ s.label }}</p>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
