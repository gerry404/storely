<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { useMagnetic } from '../../composables/useMagnetic'

const sectionRef = ref(null)
const btnRef = ref(null)
const progress = ref(0)
const countRevealed = ref(false)
const count = ref(0)
const targetCount = 2547
let magneticCleanup = null
let countInterval = null

let ticking = false
const onScroll = () => {
  if (ticking) return
  ticking = true
  requestAnimationFrame(() => {
    if (!sectionRef.value) { ticking = false; return }
    const rect = sectionRef.value.getBoundingClientRect()
    const vh = window.innerHeight
    progress.value = Math.min(Math.max(1 - rect.top / (vh * 0.65), 0), 1.2)

    // Trigger counter when section is ~40% visible
    if (progress.value > 0.4 && !countRevealed.value) {
      countRevealed.value = true
      animateCount()
    }
    ticking = false
  })
}

function animateCount() {
  const duration = 1800
  const start = performance.now()
  const step = (now) => {
    const elapsed = now - start
    const p = Math.min(elapsed / duration, 1)
    const ease = 1 - Math.pow(1 - p, 3)
    count.value = Math.round(ease * targetCount)
    if (p < 1) requestAnimationFrame(step)
  }
  requestAnimationFrame(step)
}

onMounted(async () => {
  window.addEventListener('scroll', onScroll, { passive: true })
  await nextTick()
  if (btnRef.value?.$el) magneticCleanup = useMagnetic(btnRef.value.$el, 0.3)
})
onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
  if (magneticCleanup) magneticCleanup()
  if (countInterval) clearInterval(countInterval)
})
</script>

<template>
  <section ref="sectionRef" class="py-28 md:py-36 relative overflow-hidden">
    <!-- Background -->
    <div class="absolute inset-0">
      <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba(255,107,44,0.06), var(--bg-primary), rgba(108,92,231,0.04))" />
      <!-- Main breathing glow -->
      <div
        class="absolute top-1/2 left-1/2 w-[900px] h-[900px] bg-brand/8 rounded-full blur-[200px]"
        :style="{
          transform: `translate(-50%, -50%) scale(${0.7 + progress * 0.4})`,
          opacity: 0.3 + progress * 0.4,
          transition: 'none'
        }"
      />
      <!-- Secondary glow -->
      <div
        class="absolute top-1/2 left-1/2 w-[500px] h-[500px] bg-electric/5 rounded-full blur-[150px]"
        :style="{
          transform: `translate(-40%, -60%) scale(${0.6 + progress * 0.3})`,
          transition: 'none'
        }"
      />
      <!-- Third glow -->
      <div
        class="absolute top-1/2 left-1/2 w-[300px] h-[300px] bg-mint/4 rounded-full blur-[100px]"
        :style="{
          transform: `translate(-60%, -30%) scale(${0.5 + progress * 0.4})`,
          transition: 'none'
        }"
      />
    </div>

    <div class="relative max-w-3xl mx-auto px-6 text-center">
      <!-- CTA Card with animated gradient border -->
      <div
        class="cta-card-wrapper relative rounded-3xl p-[1px]"
        :style="{
          opacity: Math.min(Math.max((progress - 0.1) / 0.5, 0), 1),
          transform: `perspective(1000px) rotateX(${Math.max((1 - progress) * 3, 0)}deg) scale(${0.9 + Math.min(Math.max((progress - 0.1) / 0.5, 0), 1) * 0.1}) translateY(${Math.max((1 - progress) * 40, 0)}px)`,
          filter: `blur(${Math.max((1 - progress * 1.5) * 3, 0)}px)`
        }"
      >
        <!-- Animated gradient border -->
        <div class="absolute inset-0 rounded-3xl overflow-hidden">
          <div class="cta-border-gradient absolute inset-[-2px] rounded-3xl" />
        </div>

        <!-- Inner card -->
        <div class="relative rounded-3xl p-10 md:p-16 overflow-hidden" style="background: var(--bg-secondary)">
          <!-- Inner glows -->
          <div class="absolute top-0 right-0 w-60 h-60 bg-brand/8 rounded-full blur-[80px] pointer-events-none" />
          <div class="absolute bottom-0 left-0 w-40 h-40 bg-electric/6 rounded-full blur-[60px] pointer-events-none" />

          <!-- Social proof counter -->
          <div class="flex items-center justify-center gap-3 mb-8">
            <!-- Stacked avatars -->
            <div class="flex -space-x-2">
              <div v-for="(g, i) in ['from-brand to-brand-amber', 'from-electric to-sky', 'from-mint to-electric', 'from-brand-coral to-brand']" :key="i"
                :class="`w-8 h-8 rounded-full bg-gradient-to-br ${g} border-2 flex items-center justify-center`"
                :style="{ borderColor: 'var(--bg-secondary)', zIndex: 4 - i }"
              >
                <span class="text-white text-[9px] font-bold">{{ ['AM', 'FK', 'PO', 'CN'][i] }}</span>
              </div>
            </div>
            <div class="flex items-baseline gap-1.5 pl-1">
              <span class="font-display font-bold text-lg text-white tabular-nums">{{ count.toLocaleString('fr-FR') }}+</span>
              <span class="text-xs text-white/35">commerçants actifs</span>
            </div>
          </div>

          <h2 class="font-display font-bold text-3xl md:text-5xl text-white tracking-tight mb-5 relative">
            Prêt à faire passer votre<br />
            boutique au <span class="text-gradient">niveau supérieur ?</span>
          </h2>
          <p class="text-white/40 mb-10 max-w-lg mx-auto relative">
            Rejoignez les milliers de commerçants qui ont déjà transformé leur business avec Storely.
          </p>

          <!-- CTA buttons -->
          <div class="flex flex-col sm:flex-row items-center justify-center gap-4 relative">
            <router-link ref="btnRef" to="/register" class="btn-primary text-lg !px-8 !py-4 magnetic group">
              <span>Créer ma vitrine gratuite</span>
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-300 group-hover:translate-x-1">
                <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
              </svg>
            </router-link>
            <p class="text-xs text-white/30">Aucune carte bancaire requise</p>
          </div>

          <!-- Trust badges -->
          <div class="flex items-center justify-center gap-6 mt-10 pt-8 border-t border-white/5">
            <div class="flex items-center gap-1.5">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2DD4A8" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
              <span class="text-[11px] text-white/30">SSL sécurisé</span>
            </div>
            <div class="w-px h-4 bg-white/5" />
            <div class="flex items-center gap-1.5">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#FFAA33" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
              <span class="text-[11px] text-white/30">Mobile Money</span>
            </div>
            <div class="w-px h-4 bg-white/5" />
            <div class="flex items-center gap-1.5">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#6C5CE7" stroke-width="2"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
              <span class="text-[11px] text-white/30">Sans engagement</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.cta-border-gradient {
  background: conic-gradient(
    from 0deg,
    rgba(255, 107, 44, 0.4),
    rgba(108, 92, 231, 0.3),
    rgba(45, 212, 168, 0.3),
    rgba(255, 170, 51, 0.3),
    rgba(255, 107, 44, 0.4)
  );
  animation: border-spin 6s linear infinite;
}

@keyframes border-spin {
  to { transform: rotate(360deg); }
}

.cta-card-wrapper {
  isolation: isolate;
}
</style>
