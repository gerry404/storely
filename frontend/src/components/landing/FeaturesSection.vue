<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const sectionRef = ref(null)
const headerVisible = ref(false)
const entered = ref(-1)

const leftFeatures = [
  {
    tag: 'Rapidité',
    title: 'Prêt en 10 minutes',
    desc: 'Ajoutez vos produits, vos prix et vos photos. Votre vitrine est en ligne instantanément. Zéro compétence technique.',
    color: '#FF6B2C',
    accent: '#FFAA33',
  },
  {
    tag: 'Ventes',
    title: 'Commandes en ligne',
    desc: 'Recevez des commandes directement depuis votre vitrine. Gérez tout depuis votre téléphone, partout.',
    color: '#2DD4A8',
    accent: '#6EE7B7',
  },
  {
    tag: 'Marketing',
    title: 'Promotions & badges',
    desc: 'Créez des promotions, des ventes flash et des réductions pour attirer plus de clients.',
    color: '#FFAA33',
    accent: '#FCD34D',
  },
]

const rightFeatures = [
  {
    tag: 'Partage',
    title: 'Votre lien unique',
    desc: 'storely.app/votre-boutique — Un lien simple à partager sur WhatsApp, Instagram, cartes de visite.',
    color: '#6C5CE7',
    accent: '#A78BFA',
  },
  {
    tag: 'Données',
    title: 'Statistiques en temps réel',
    desc: "Voyez qui visite votre boutique, quels produits attirent le plus, et d'où viennent vos clients.",
    color: '#38BDF8',
    accent: '#7DD3FC',
  },
  {
    tag: 'Confiance',
    title: 'Badge Vérifié',
    desc: 'Gagnez la confiance des clients avec le badge boutique vérifiée. Démarquez-vous.',
    color: '#F472B6',
    accent: '#FB7185',
  },
]

let observers = []

onMounted(() => {
  // Header observer
  const headerObs = new IntersectionObserver(([e]) => {
    if (e.isIntersecting) headerVisible.value = true
  }, { threshold: 0.2 })
  const header = sectionRef.value?.querySelector('.features-header')
  if (header) headerObs.observe(header)
  observers.push(headerObs)

  // Card triggers — each trigger zone advances 'entered'
  const triggerObs = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        const idx = parseInt(e.target.dataset.trigger)
        if (!isNaN(idx) && idx > entered.value) {
          entered.value = idx
        }
      }
    })
  }, { rootMargin: '-30% 0px -30% 0px' })

  sectionRef.value?.querySelectorAll('.waterfall-trigger').forEach(el => {
    triggerObs.observe(el)
  })
  observers.push(triggerObs)
})

onUnmounted(() => {
  observers.forEach(o => o.disconnect())
})
</script>

<template>
  <section id="features" ref="sectionRef" class="relative overflow-hidden">
    <!-- Wavy top edge -->
    <div class="absolute top-0 left-0 right-0 z-10" style="margin-top: -1px;">
      <svg viewBox="0 0 1440 80" preserveAspectRatio="none" class="block w-full h-[50px] md:h-[80px]">
        <path d="M0,80 C360,0 720,60 1080,20 C1260,0 1380,40 1440,30 L1440,0 L0,0Z" fill="var(--bg-primary)" />
      </svg>
    </div>

    <!-- Orange-warm background -->
    <div class="absolute inset-0" style="background: linear-gradient(180deg, rgba(255,107,44,0.04) 0%, rgba(255,170,51,0.06) 50%, rgba(255,107,44,0.03) 100%);" />
    <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-brand/5 rounded-full blur-[180px] pointer-events-none" />
    <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-brand-amber/4 rounded-full blur-[120px] pointer-events-none" />

    <div class="relative z-[1] pt-20 pb-28 md:pt-28 md:pb-36">
      <div class="max-w-6xl mx-auto px-6">
        <!-- Header -->
        <div
          class="features-header text-center mb-20 transition-all duration-1000 ease-out"
          :style="{
            opacity: headerVisible ? 1 : 0,
            transform: headerVisible ? 'translateY(0)' : 'translateY(40px)',
          }"
        >
          <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full mb-6 text-xs font-display font-medium uppercase tracking-wider" style="background: rgba(255,107,44,0.1); color: #FFAA33; border: 1px solid rgba(255,107,44,0.15);">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
            Fonctionnalités
          </span>
          <h2 class="font-display font-bold text-3xl md:text-5xl lg:text-6xl t-heading tracking-tight mb-5">
            Tout ce qu'il faut pour<br />
            <span class="text-gradient">briller en ligne</span>
          </h2>
          <p class="t-text-muted max-w-xl mx-auto text-lg">
            Les outils des grandes marques, adaptés à votre réalité.
          </p>
        </div>

        <!-- Cards Waterfall — 2 columns on desktop -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-6">
          <!-- Left column -->
          <div class="relative space-y-0">
            <div
              v-for="(f, i) in leftFeatures"
              :key="'L'+i"
              class="waterfall-trigger"
              :data-trigger="i"
            >
              <div
                class="waterfall-card relative rounded-2xl overflow-hidden transition-all duration-700 ease-[cubic-bezier(0.16,1,0.3,1)]"
                :class="i > 0 ? '-mt-6' : ''"
                :style="{
                  opacity: entered >= i ? 1 : 0,
                  transform: entered >= i
                    ? 'translateY(0)'
                    : `translateY(${(i - entered) * 100}%)`,
                  zIndex: leftFeatures.length - i,
                  background: 'var(--bg-secondary)',
                  border: '1px solid var(--border-subtle)',
                  boxShadow: entered >= i
                    ? '0 20px 50px rgba(0,0,0,0.12), 0 8px 20px rgba(0,0,0,0.08)'
                    : 'none',
                }"
              >
                <div class="p-7 md:p-8">
                  <div class="flex items-start justify-between mb-4">
                    <span
                      class="inline-block px-3 py-1 rounded-lg text-[11px] font-display font-semibold uppercase tracking-wider"
                      :style="{ background: `${f.color}12`, color: f.color, border: `1px solid ${f.color}18` }"
                    >{{ f.tag }}</span>
                    <span class="text-xs font-display font-medium t-text-faint" style="opacity:0.3">{{ String(i * 2 + 1).padStart(2,'0') }}</span>
                  </div>
                  <h3 class="font-display font-bold text-xl md:text-2xl t-heading mb-3">{{ f.title }}</h3>
                  <p class="text-sm t-text-muted leading-relaxed">{{ f.desc }}</p>
                  <div class="mt-5 h-[3px] rounded-full overflow-hidden w-20" :style="{ background: `${f.color}10` }">
                    <div
                      class="h-full rounded-full transition-all duration-1000 ease-out"
                      :style="{
                        width: entered >= i ? '100%' : '0%',
                        background: `linear-gradient(to right, ${f.color}, ${f.accent})`,
                        transitionDelay: '400ms',
                      }"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right column (offset for cascade) -->
          <div class="relative space-y-0 lg:mt-16">
            <div
              v-for="(f, i) in rightFeatures"
              :key="'R'+i"
              class="waterfall-trigger"
              :data-trigger="i"
            >
              <div
                class="waterfall-card relative rounded-2xl overflow-hidden transition-all duration-700 ease-[cubic-bezier(0.16,1,0.3,1)]"
                :class="i > 0 ? '-mt-6' : ''"
                :style="{
                  opacity: entered >= i ? 1 : 0,
                  transform: entered >= i
                    ? 'translateY(0)'
                    : `translateY(${(i - entered) * 100}%)`,
                  zIndex: rightFeatures.length - i,
                  background: 'var(--bg-secondary)',
                  border: '1px solid var(--border-subtle)',
                  boxShadow: entered >= i
                    ? '0 20px 50px rgba(0,0,0,0.12), 0 8px 20px rgba(0,0,0,0.08)'
                    : 'none',
                }"
              >
                <div class="p-7 md:p-8">
                  <div class="flex items-start justify-between mb-4">
                    <span
                      class="inline-block px-3 py-1 rounded-lg text-[11px] font-display font-semibold uppercase tracking-wider"
                      :style="{ background: `${f.color}12`, color: f.color, border: `1px solid ${f.color}18` }"
                    >{{ f.tag }}</span>
                    <span class="text-xs font-display font-medium t-text-faint" style="opacity:0.3">{{ String(i * 2 + 2).padStart(2,'0') }}</span>
                  </div>
                  <h3 class="font-display font-bold text-xl md:text-2xl t-heading mb-3">{{ f.title }}</h3>
                  <p class="text-sm t-text-muted leading-relaxed">{{ f.desc }}</p>
                  <div class="mt-5 h-[3px] rounded-full overflow-hidden w-20" :style="{ background: `${f.color}10` }">
                    <div
                      class="h-full rounded-full transition-all duration-1000 ease-out"
                      :style="{
                        width: entered >= i ? '100%' : '0%',
                        background: `linear-gradient(to right, ${f.color}, ${f.accent})`,
                        transitionDelay: '400ms',
                      }"
                    />
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Wavy bottom edge -->
    <div class="absolute bottom-0 left-0 right-0 z-10" style="margin-bottom: -1px;">
      <svg viewBox="0 0 1440 80" preserveAspectRatio="none" class="block w-full h-[50px] md:h-[80px]">
        <path d="M0,0 C480,80 960,0 1440,50 L1440,80 L0,80Z" fill="var(--bg-primary)" />
      </svg>
    </div>
  </section>
</template>

<style scoped>
.waterfall-card {
  will-change: transform, opacity;
}
</style>
