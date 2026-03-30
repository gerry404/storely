<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const sectionRef = ref(null)
const headerVisible = ref(false)
const activeCard = ref(-1)

const features = [
  {
    icon: 'rocket',
    tag: 'Rapidité',
    tagColor: '#FF6B2C',
    title: 'Prêt en 10 minutes',
    desc: 'Ajoutez vos produits, vos prix et vos photos. Votre vitrine est en ligne instantanément. Aucune compétence technique nécessaire.',
    color: '#FF6B2C',
    accent: '#FFAA33',
  },
  {
    icon: 'link',
    tag: 'Partage',
    tagColor: '#6C5CE7',
    title: 'Votre lien unique',
    desc: 'storely.app/votre-boutique — Un lien simple à partager sur WhatsApp, Instagram, cartes de visite, flyers et partout.',
    color: '#6C5CE7',
    accent: '#A78BFA',
  },
  {
    icon: 'orders',
    tag: 'Ventes',
    tagColor: '#2DD4A8',
    title: 'Commandes en ligne',
    desc: 'Recevez des commandes directement depuis votre vitrine. Gérez tout depuis votre téléphone, partout, à tout moment.',
    color: '#2DD4A8',
    accent: '#6EE7B7',
  },
  {
    icon: 'chart',
    tag: 'Données',
    tagColor: '#38BDF8',
    title: 'Statistiques en temps réel',
    desc: "Voyez qui visite votre boutique, quels produits attirent le plus, et d'où viennent vos clients.",
    color: '#38BDF8',
    accent: '#7DD3FC',
  },
  {
    icon: 'promo',
    tag: 'Marketing',
    tagColor: '#FFAA33',
    title: 'Promotions & badges',
    desc: 'Créez des promotions, des ventes flash et des réductions pour attirer plus de clients et booster vos ventes.',
    color: '#FFAA33',
    accent: '#FCD34D',
  },
  {
    icon: 'shield',
    tag: 'Confiance',
    tagColor: '#F472B6',
    title: 'Badge Vérifié',
    desc: 'Gagnez la confiance des clients avec le badge boutique vérifiée. Démarquez-vous de la concurrence.',
    color: '#F472B6',
    accent: '#FB7185',
  }
]

let observer
onMounted(() => {
  observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.target === sectionRef.value?.querySelector('.features-header') && entry.isIntersecting) {
        headerVisible.value = true
      }
      // Detect which card is visible
      const idx = parseInt(entry.target.dataset?.idx)
      if (!isNaN(idx) && entry.isIntersecting && entry.intersectionRatio > 0.3) {
        activeCard.value = Math.max(activeCard.value, idx)
      }
    })
  }, { threshold: [0.3, 0.6] })

  const header = sectionRef.value?.querySelector('.features-header')
  if (header) observer.observe(header)

  sectionRef.value?.querySelectorAll('.waterfall-card').forEach(el => {
    observer.observe(el)
  })
})

onUnmounted(() => {
  observer?.disconnect()
})
</script>

<template>
  <section id="features" ref="sectionRef" class="py-28 md:py-36 relative overflow-hidden">
    <!-- Background -->
    <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-brand/5 rounded-full blur-[180px] pointer-events-none" />

    <div class="max-w-5xl mx-auto px-6">
      <!-- Header -->
      <div
        class="features-header text-center mb-20 transition-all duration-1000 ease-out"
        :style="{
          opacity: headerVisible ? 1 : 0,
          transform: headerVisible ? 'translateY(0)' : 'translateY(40px)',
        }"
      >
        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full mb-6 text-xs font-display font-medium uppercase tracking-wider" style="background: rgba(255,107,44,0.08); color: #FFAA33; border: 1px solid rgba(255,107,44,0.12);">
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

      <!-- Cards Waterfall -->
      <div class="relative space-y-6">
        <div
          v-for="(f, i) in features"
          :key="i"
          :data-idx="i"
          class="waterfall-card relative rounded-2xl overflow-hidden transition-all duration-700 ease-out"
          :style="{
            opacity: activeCard >= i ? 1 : 0,
            transform: activeCard >= i ? 'translateY(0) scale(1)' : 'translateY(60px) scale(0.95)',
            transitionDelay: `${(i - Math.max(activeCard - 1, 0)) * 100}ms`,
            background: 'var(--glass-bg-card)',
            border: '1px solid var(--border-default)',
          }"
        >
          <div class="md:flex items-center gap-0">
            <!-- Left: content -->
            <div class="flex-1 p-8 md:p-10">
              <!-- Tag -->
              <span
                class="inline-block px-3 py-1 rounded-lg text-xs font-display font-semibold uppercase tracking-wider mb-4"
                :style="{ background: `${f.tagColor}12`, color: f.tagColor, border: `1px solid ${f.tagColor}18` }"
              >{{ f.tag }}</span>

              <h3 class="font-display font-bold text-2xl md:text-3xl t-heading mb-3">{{ f.title }}</h3>
              <p class="text-base t-text-muted leading-relaxed max-w-lg">{{ f.desc }}</p>

              <!-- Bottom accent line -->
              <div class="mt-6 h-[3px] rounded-full overflow-hidden w-24" :style="{ background: `${f.color}15` }">
                <div
                  class="h-full rounded-full transition-all duration-1000 ease-out"
                  :style="{
                    width: activeCard >= i ? '100%' : '0%',
                    background: `linear-gradient(to right, ${f.color}, ${f.accent})`,
                    transitionDelay: '300ms',
                  }"
                />
              </div>
            </div>

            <!-- Right: icon area -->
            <div class="shrink-0 md:w-48 flex items-center justify-center p-8 md:p-10">
              <div
                class="w-20 h-20 md:w-24 md:h-24 rounded-3xl flex items-center justify-center transition-all duration-700"
                :style="{
                  background: `linear-gradient(135deg, ${f.color}15, ${f.color}08)`,
                  border: `1px solid ${f.color}15`,
                  transform: activeCard >= i ? 'scale(1) rotate(0deg)' : 'scale(0.8) rotate(-5deg)',
                }"
              >
                <svg v-if="f.icon === 'rocket'" width="36" height="36" viewBox="0 0 24 24" fill="none" :stroke="f.color" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 00-2.91-.09z"/><path d="M12 15l-3-3a22 22 0 012-3.95A12.88 12.88 0 0122 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 01-4 2z"/></svg>
                <svg v-if="f.icon === 'link'" width="36" height="36" viewBox="0 0 24 24" fill="none" :stroke="f.color" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                <svg v-if="f.icon === 'orders'" width="36" height="36" viewBox="0 0 24 24" fill="none" :stroke="f.color" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                <svg v-if="f.icon === 'chart'" width="36" height="36" viewBox="0 0 24 24" fill="none" :stroke="f.color" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                <svg v-if="f.icon === 'promo'" width="36" height="36" viewBox="0 0 24 24" fill="none" :stroke="f.color" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                <svg v-if="f.icon === 'shield'" width="36" height="36" viewBox="0 0 24 24" fill="none" :stroke="f.color" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
              </div>
            </div>
          </div>

          <!-- Card number -->
          <div class="absolute left-8 md:left-10 bottom-0 h-10 flex items-center text-xs font-display font-medium t-text-faint" style="opacity: 0.4;">
            {{ String(i + 1).padStart(2, '0') }}
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
