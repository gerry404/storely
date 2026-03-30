<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const sectionRef = ref(null)
const visible = ref(false)
const activeMetric = ref(0)

const metrics = [
  {
    icon: 'clock',
    value: '10 min',
    label: 'Pour créer votre boutique',
    desc: 'Pas de code, pas de designer. Ajoutez vos produits, vos prix et vous êtes en ligne.',
    color: '#FF6B2C',
  },
  {
    icon: 'share',
    value: '1 lien',
    label: 'À partager partout',
    desc: 'WhatsApp, Instagram, Facebook, TikTok, carte de visite — un seul lien pour tout.',
    color: '#6C5CE7',
  },
  {
    icon: 'phone',
    value: '100%',
    label: 'Mobile-first',
    desc: 'Gérez votre boutique depuis votre téléphone. Vos clients commandent depuis le leur.',
    color: '#2DD4A8',
  },
  {
    icon: 'wallet',
    value: '0 FCFA',
    label: 'Pour commencer',
    desc: 'Le plan gratuit est vraiment gratuit. Aucune carte bancaire demandée.',
    color: '#FFAA33',
  },
]

let observer
let rotateTimer

onMounted(() => {
  observer = new IntersectionObserver(([entry]) => {
    if (entry.isIntersecting) visible.value = true
  }, { threshold: 0.2 })
  if (sectionRef.value) observer.observe(sectionRef.value)

  rotateTimer = setInterval(() => {
    activeMetric.value = (activeMetric.value + 1) % metrics.length
  }, 4000)
})

onUnmounted(() => {
  observer?.disconnect()
  if (rotateTimer) clearInterval(rotateTimer)
})
</script>

<template>
  <section ref="sectionRef" id="why-storely" class="py-24 md:py-32 relative">
    <div class="absolute left-0 top-0 w-[500px] h-[500px] bg-brand/5 rounded-full blur-[150px] pointer-events-none" />

    <div class="max-w-6xl mx-auto px-6">
      <!-- Header -->
      <div
        class="text-center mb-16 transition-all duration-1000"
        :style="{
          opacity: visible ? 1 : 0,
          transform: visible ? 'translateY(0)' : 'translateY(30px)',
        }"
      >
        <span class="inline-block px-4 py-1.5 rounded-full text-xs font-display font-medium uppercase tracking-wider mb-6" style="background: rgba(108,92,231,0.08); color: #A78BFA; border: 1px solid rgba(108,92,231,0.12);">
          Pourquoi Storely
        </span>
        <h2 class="font-display font-bold text-3xl md:text-5xl t-heading tracking-tight mb-4">
          Conçu pour les commerçants<br />
          <span class="text-gradient">qui veulent vendre plus</span>
        </h2>
        <p class="t-text-muted max-w-xl mx-auto text-lg">
          Tout ce dont vous avez besoin, rien de ce qui vous ralentit.
        </p>
      </div>

      <!-- Metrics grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <button
          v-for="(m, i) in metrics"
          :key="i"
          @click="activeMetric = i"
          class="group text-left p-6 rounded-2xl transition-all duration-500 cursor-pointer relative overflow-hidden"
          :class="activeMetric === i ? 'scale-[1.02]' : ''"
          :style="{
            opacity: visible ? 1 : 0,
            transform: visible ? 'translateY(0)' : 'translateY(30px)',
            transitionDelay: `${i * 100}ms`,
            background: activeMetric === i ? `${m.color}08` : 'var(--glass-bg-card)',
            border: `1px solid ${activeMetric === i ? m.color + '25' : 'var(--border-default)'}`,
          }"
        >
          <!-- Active indicator -->
          <div
            class="absolute top-0 left-0 w-1 rounded-r-full transition-all duration-500"
            :style="{
              height: activeMetric === i ? '100%' : '0%',
              background: m.color,
            }"
          />
          <p
            class="font-display font-bold text-3xl mb-1 transition-colors duration-300"
            :style="{ color: activeMetric === i ? m.color : 'var(--heading-text)' }"
          >{{ m.value }}</p>
          <p class="font-display font-medium text-sm t-heading mb-2">{{ m.label }}</p>
          <p class="text-sm t-text-faint leading-relaxed">{{ m.desc }}</p>
        </button>
      </div>
    </div>
  </section>
</template>
