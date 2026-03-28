<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { useTilt } from '../../composables/useTilt'

const sectionRef = ref(null)
const headerRef = ref(null)
const cardRefs = ref([])
const headerVisible = ref(false)
const cardVisibility = ref([false, false, false, false, false, false])

const tiltCleanups = []

const features = [
  {
    icon: 'rocket',
    title: 'Prêt en 10 minutes',
    desc: 'Ajoutez vos produits, vos prix et vos photos. Votre vitrine est en ligne instantanément.',
    color: '#FF6B2C',
    accent: '#FFAA33',
    metric: '10 min',
    metricLabel: 'pour créer',
  },
  {
    icon: 'link',
    title: 'Votre lien unique',
    desc: 'storely.app/votre-boutique — Un lien simple à partager sur vos cartes, flyers et réseaux.',
    color: '#6C5CE7',
    accent: '#A78BFA',
    metric: '1 lien',
    metricLabel: 'pour tout',
  },
  {
    icon: 'orders',
    title: 'Commandes en ligne',
    desc: 'Recevez des commandes directement depuis votre vitrine. Gérez tout depuis votre téléphone.',
    color: '#2DD4A8',
    accent: '#6EE7B7',
    metric: '24/7',
    metricLabel: 'toujours ouvert',
  },
  {
    icon: 'chart',
    title: 'Statistiques en temps réel',
    desc: "Voyez qui visite votre boutique, quels produits attirent le plus, et d'où viennent vos clients.",
    color: '#38BDF8',
    accent: '#7DD3FC',
    metric: 'Live',
    metricLabel: 'en direct',
  },
  {
    icon: 'promo',
    title: 'Promotions & badges',
    desc: 'Créez des promotions, des ventes flash et des réductions pour attirer plus de clients.',
    color: '#FFAA33',
    accent: '#FCD34D',
    metric: '∞',
    metricLabel: 'promotions',
  },
  {
    icon: 'shield',
    title: 'Badge Vérifié',
    desc: 'Gagnez la confiance des clients avec le badge boutique vérifiée. Démarquez-vous.',
    color: '#F472B6',
    accent: '#FB7185',
    metric: '✓',
    metricLabel: 'confiance',
  }
]

let observer
onMounted(async () => {
  observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.target === headerRef.value && entry.isIntersecting) {
        headerVisible.value = true
      }
      if (entry.target === sectionRef.value && entry.isIntersecting) {
        features.forEach((_, i) => {
          setTimeout(() => {
            cardVisibility.value[i] = true
          }, i * 180)
        })
      }
    })
  }, { threshold: 0.15 })

  if (headerRef.value) observer.observe(headerRef.value)
  if (sectionRef.value) observer.observe(sectionRef.value)

  await nextTick()
  cardRefs.value.forEach(el => {
    if (el) {
      const cleanup = useTilt(el, 5)
      if (cleanup) tiltCleanups.push(cleanup)
    }
  })
})

onUnmounted(() => {
  observer?.disconnect()
  tiltCleanups.forEach(fn => fn())
})

const cardStyle = (i) => {
  if (!cardVisibility.value[i]) {
    return {
      opacity: 0,
      transform: 'translateY(50px) scale(0.93)',
      transition: 'none',
    }
  }
  return {
    opacity: 1,
    transform: 'translateY(0) scale(1)',
    transition: `all 0.8s cubic-bezier(0.16, 1, 0.3, 1) ${i * 100}ms`,
  }
}
</script>

<template>
  <section id="features" ref="sectionRef" class="py-28 md:py-36 relative overflow-hidden">
    <!-- Background -->
    <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-brand/5 rounded-full blur-[180px] pointer-events-none" />
    <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-electric/5 rounded-full blur-[120px] pointer-events-none" />

    <div class="max-w-6xl mx-auto px-6">
      <!-- Header -->
      <div
        ref="headerRef"
        class="text-center mb-20 transition-all duration-1000 ease-out"
        :style="{
          opacity: headerVisible ? 1 : 0,
          transform: headerVisible ? 'translateY(0) scale(1)' : 'translateY(40px) scale(0.95)',
          filter: headerVisible ? 'blur(0)' : 'blur(6px)',
        }"
      >
        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full mb-6 text-xs font-display font-medium uppercase tracking-wider" style="background: rgba(255,107,44,0.08); color: #FFAA33; border: 1px solid rgba(255,107,44,0.12);">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
          Fonctionnalités
        </span>
        <h2 class="font-display font-bold text-3xl md:text-5xl lg:text-6xl text-white tracking-tight mb-5">
          Tout ce qu'il faut pour<br />
          <span class="text-gradient">briller en ligne</span>
        </h2>
        <p class="text-white/40 max-w-xl mx-auto text-lg">
          Les outils des grandes marques, adaptés à votre réalité.
        </p>
      </div>

      <!-- Premium Bento Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <div
          v-for="(f, i) in features"
          :key="i"
          :ref="el => { if (el) cardRefs[i] = el }"
          class="tilt-card feature-card group relative overflow-hidden rounded-2xl cursor-default"
          :style="cardStyle(i)"
        >
          <!-- Tilt shine overlay -->
          <div class="tilt-shine" />

          <!-- Hover glow -->
          <div class="absolute -top-24 -right-24 w-48 h-48 rounded-full blur-3xl opacity-0 group-hover:opacity-100 transition-opacity duration-700" :style="{ background: f.color + '12' }" />

          <!-- Decorative corner accent -->
          <div class="absolute top-0 right-0 w-24 h-24 opacity-0 group-hover:opacity-100 transition-all duration-500" :style="{ background: `radial-gradient(circle at 100% 0%, ${f.color}08, transparent 70%)` }" />

          <!-- Content -->
          <div class="relative p-7">
            <!-- Top row: icon + metric -->
            <div class="flex items-start justify-between mb-5">
              <!-- Icon -->
              <div class="w-12 h-12 rounded-2xl flex items-center justify-center relative" :style="{ background: `linear-gradient(135deg, ${f.color}18, ${f.color}08)`, border: `1px solid ${f.color}15` }">
                <svg v-if="f.icon === 'rocket'" width="22" height="22" viewBox="0 0 24 24" fill="none" :stroke="f.color" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 00-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 012-3.95A12.88 12.88 0 0122 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 01-4 2z"/></svg>
                <svg v-if="f.icon === 'link'" width="22" height="22" viewBox="0 0 24 24" fill="none" :stroke="f.color" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                <svg v-if="f.icon === 'orders'" width="22" height="22" viewBox="0 0 24 24" fill="none" :stroke="f.color" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                <svg v-if="f.icon === 'chart'" width="22" height="22" viewBox="0 0 24 24" fill="none" :stroke="f.color" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                <svg v-if="f.icon === 'promo'" width="22" height="22" viewBox="0 0 24 24" fill="none" :stroke="f.color" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                <svg v-if="f.icon === 'shield'" width="22" height="22" viewBox="0 0 24 24" fill="none" :stroke="f.color" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
              </div>

              <!-- Metric pill -->
              <div class="flex flex-col items-end">
                <span class="text-lg font-display font-bold" :style="{ color: f.color }">{{ f.metric }}</span>
                <span class="text-[10px] text-white/25 uppercase tracking-wider">{{ f.metricLabel }}</span>
              </div>
            </div>

            <h3 class="font-display font-bold text-lg text-white mb-2.5">{{ f.title }}</h3>
            <p class="text-sm text-white/35 leading-relaxed">{{ f.desc }}</p>

            <!-- Bottom accent line -->
            <div class="mt-5 h-[2px] rounded-full overflow-hidden" style="background: rgba(255,255,255,0.04);">
              <div class="h-full w-0 group-hover:w-full rounded-full transition-all duration-700 ease-out" :style="{ background: `linear-gradient(to right, ${f.color}, ${f.accent})` }" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.feature-card {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.05);
  transition: background 0.4s ease, border-color 0.4s ease, box-shadow 0.4s ease, transform 0.4s ease;
  animation: card-breathe 5s ease-in-out infinite;
}
.feature-card:nth-child(1) { animation-delay: 0s; }
.feature-card:nth-child(2) { animation-delay: -0.8s; }
.feature-card:nth-child(3) { animation-delay: -1.6s; }
.feature-card:nth-child(4) { animation-delay: -2.4s; }
.feature-card:nth-child(5) { animation-delay: -3.2s; }
.feature-card:nth-child(6) { animation-delay: -4s; }

@keyframes card-breathe {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-4px); }
}

.feature-card:hover {
  background: rgba(255, 255, 255, 0.04);
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow: 0 8px 40px rgba(0, 0, 0, 0.2), 0 0 0 1px rgba(255, 255, 255, 0.05);
  animation-play-state: paused;
}
</style>
