<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const sectionRef = ref(null)
const progress = ref(0)

const steps = [
  {
    num: '01',
    title: 'Créez votre compte',
    desc: 'En 2 minutes avec votre numéro ou email. Aucune carte bancaire requise.',
    icon: 'user',
    color: '#FF6B2C',
    accent: '#FFAA33',
  },
  {
    num: '02',
    title: 'Ajoutez vos produits',
    desc: 'Photos, prix en FCFA, descriptions. Aussi simple que poster sur WhatsApp.',
    icon: 'camera',
    color: '#6C5CE7',
    accent: '#A78BFA',
  },
  {
    num: '03',
    title: 'Partagez votre lien',
    desc: 'Votre vitrine est en ligne. Partagez sur WhatsApp, Instagram, Facebook.',
    icon: 'share',
    color: '#2DD4A8',
    accent: '#6EE7B7',
  }
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

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }))
onUnmounted(() => window.removeEventListener('scroll', onScroll))

const stepReveal = (i) => {
  const delay = 0.1 + i * 0.18
  const p = Math.min(Math.max((progress.value - delay) / 0.4, 0), 1)
  return 1 - Math.pow(1 - p, 4)
}

const lineProgress = () => {
  return Math.min(Math.max((progress.value - 0.15) / 0.65, 0), 1)
}
</script>

<template>
  <section ref="sectionRef" class="relative overflow-hidden">
    <!-- Wavy top edge -->
    <div class="absolute top-0 left-0 right-0 z-10" style="margin-top: -1px;">
      <svg viewBox="0 0 1440 80" preserveAspectRatio="none" class="block w-full h-[50px] md:h-[80px]">
        <path d="M0,80 C240,20 480,60 720,30 C960,0 1200,50 1440,20 L1440,0 L0,0Z" fill="var(--bg-primary)" />
      </svg>
    </div>

    <!-- Accent bg -->
    <div class="absolute inset-0 pointer-events-none" style="background: linear-gradient(180deg, rgba(108,92,231,0.04) 0%, rgba(56,189,248,0.05) 50%, rgba(108,92,231,0.03) 100%);" />

    <!-- Wavy bottom edge -->
    <div class="absolute bottom-0 left-0 right-0 z-10" style="margin-bottom: -1px;">
      <svg viewBox="0 0 1440 80" preserveAspectRatio="none" class="block w-full h-[50px] md:h-[80px]">
        <path d="M0,0 C360,70 720,10 1080,50 C1260,70 1380,30 1440,40 L1440,80 L0,80Z" fill="var(--bg-primary)" />
      </svg>
    </div>

    <div class="pt-20 pb-28 md:pt-28 md:pb-36">

    <div class="relative max-w-5xl mx-auto px-6">
      <!-- Header -->
      <div
        class="text-center mb-20"
        :style="{
          opacity: Math.min(Math.max(progress * 2.5, 0), 1),
          transform: `translateY(${Math.max((1 - progress * 2) * 25, 0)}px)`,
        }"
      >
        <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full glass text-xs font-display font-medium text-mint uppercase tracking-wider mb-6">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
          Comment ça marche
        </span>
        <h2 class="font-display font-bold text-3xl md:text-5xl text-white tracking-tight mb-4">
          Trois étapes,<br />
          <span class="text-gradient-cool">zéro complication</span>
        </h2>
        <p class="text-white/35 max-w-lg mx-auto">
          De l'inscription à votre première vente en quelques minutes.
        </p>
      </div>

      <!-- Steps -->
      <div class="relative">
        <!-- ═══ Desktop connecting line — passes through the center of number circles ═══ -->
        <div class="hidden md:block absolute pointer-events-none" style="z-index: 0; top: 44px; left: 16.667%; right: 16.667%;">
          <!-- Track -->
          <div class="h-[2px] w-full rounded-full" style="background: rgba(255,255,255,0.04)" />
          <!-- Animated fill -->
          <div
            class="h-[2px] rounded-full -mt-[2px] relative"
            :style="{
              width: `${lineProgress() * 100}%`,
              transition: 'none',
            }"
          >
            <!-- Gradient fill -->
            <div class="absolute inset-0 rounded-full" style="background: linear-gradient(90deg, #FF6B2C 0%, #6C5CE7 50%, #2DD4A8 100%)" />
            <!-- Glow -->
            <div class="absolute inset-0 blur-[6px] rounded-full opacity-50" style="background: linear-gradient(90deg, #FF6B2C 0%, #6C5CE7 50%, #2DD4A8 100%)" />
            <!-- Tip dot -->
            <div
              v-if="lineProgress() > 0.03"
              class="absolute right-0 top-1/2 -translate-y-1/2 translate-x-1/2 w-2.5 h-2.5 rounded-full bg-white"
              :style="{
                boxShadow: `0 0 12px rgba(255,255,255,0.6), 0 0 4px rgba(255,255,255,0.9)`,
              }"
            />
          </div>
        </div>

        <!-- Steps grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10 md:gap-6 relative" style="z-index: 1;">
          <div
            v-for="(step, i) in steps"
            :key="i"
            class="flex flex-col items-center text-center"
            :style="{
              opacity: stepReveal(i),
              transform: `translateY(${(1 - stepReveal(i)) * 40}px)`,
              transition: 'none',
            }"
          >
            <!-- Number circle — this is what the line passes through -->
            <div class="relative mb-6">
              <div
                class="w-[88px] h-[88px] rounded-full flex items-center justify-center relative"
                :style="{
                  background: `radial-gradient(circle at 30% 30%, ${step.color}18, ${step.color}06)`,
                  border: `1.5px solid ${step.color}25`,
                }"
              >
                <!-- Inner icon -->
                <svg v-if="step.icon === 'user'" width="32" height="32" viewBox="0 0 24 24" fill="none" :stroke="step.color" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <svg v-if="step.icon === 'camera'" width="32" height="32" viewBox="0 0 24 24" fill="none" :stroke="step.color" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/><circle cx="12" cy="13" r="4"/></svg>
                <svg v-if="step.icon === 'share'" width="32" height="32" viewBox="0 0 24 24" fill="none" :stroke="step.color" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>

                <!-- Step number badge -->
                <div
                  class="absolute -bottom-1 -right-1 w-7 h-7 rounded-full flex items-center justify-center shadow-lg ring-2"
                  :style="{
                    background: `linear-gradient(135deg, ${step.color}, ${step.accent})`,
                    boxShadow: `0 4px 14px ${step.color}40`,
                    ringColor: 'var(--bg-secondary)',
                  }"
                  style="--tw-ring-color: var(--bg-primary)"
                >
                  <span class="text-[11px] font-display font-bold text-white">{{ i + 1 }}</span>
                </div>
              </div>
            </div>

            <!-- Text -->
            <h3 class="font-display font-bold text-xl text-white mb-3">{{ step.title }}</h3>
            <p class="text-sm text-white/40 leading-relaxed max-w-[260px] mx-auto">{{ step.desc }}</p>

            <!-- Mobile vertical connector -->
            <div
              v-if="i < steps.length - 1"
              class="md:hidden w-[2px] h-12 mt-6 rounded-full"
              :style="{
                background: `linear-gradient(to bottom, ${step.color}30, ${steps[i+1].color}30)`,
              }"
            />
          </div>
        </div>
      </div>
    </div>
    </div>
  </section>
</template>
