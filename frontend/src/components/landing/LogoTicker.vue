<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const sectionRef = ref(null)
const visible = ref(false)

const brands = [
  { name: 'Marché Central', initials: 'MC', gradient: 'from-brand to-brand-amber' },
  { name: 'Fashion Akwa', initials: 'FA', gradient: 'from-mint to-electric' },
  { name: 'Bijoux Bastos', initials: 'BB', gradient: 'from-electric to-sky' },
  { name: 'TechShop YDE', initials: 'TY', gradient: 'from-brand-amber to-brand' },
  { name: 'Chaussures Elite', initials: 'CE', gradient: 'from-brand-coral to-brand' },
  { name: 'Épices du Cameroun', initials: 'ÉC', gradient: 'from-mint to-brand-amber' },
  { name: 'Cosmo Beauty', initials: 'CB', gradient: 'from-electric to-mint' },
  { name: 'Auto Parts DLA', initials: 'AP', gradient: 'from-brand to-brand-coral' },
]

let observer
onMounted(() => {
  observer = new IntersectionObserver(([entry]) => {
    if (entry.isIntersecting) visible.value = true
  }, { threshold: 0.1 })
  if (sectionRef.value) observer.observe(sectionRef.value)
})
onUnmounted(() => observer?.disconnect())
</script>

<template>
  <section
    ref="sectionRef"
    class="py-20 border-y overflow-hidden transition-all duration-1000"
    style="border-color: var(--border-default)"
    :style="{
      opacity: visible ? 1 : 0,
      transform: visible ? 'translateY(0)' : 'translateY(20px)',
    }"
  >
    <!-- Subtle background glow -->
    <div class="absolute inset-0 pointer-events-none">
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[200px] bg-brand/3 rounded-full blur-[120px]" />
    </div>

    <div class="relative">
      <div class="flex items-center justify-center gap-2 mb-12">
        <div class="h-px flex-1 max-w-[80px]" style="background: linear-gradient(to right, transparent, rgba(255,255,255,0.08))" />
        <p class="text-center text-[11px] font-display uppercase tracking-[0.25em] text-white/25 px-4">
          Ils font confiance à Storely
        </p>
        <div class="h-px flex-1 max-w-[80px]" style="background: linear-gradient(to left, transparent, rgba(255,255,255,0.08))" />
      </div>

      <div class="relative">
        <!-- Fade edges -->
        <div class="absolute left-0 top-0 bottom-0 w-40 z-10" style="background: linear-gradient(to right, var(--bg-primary), transparent)" />
        <div class="absolute right-0 top-0 bottom-0 w-40 z-10" style="background: linear-gradient(to left, var(--bg-primary), transparent)" />

        <div class="flex animate-marquee gap-10 items-center">
          <template v-for="loop in 2" :key="loop">
            <div
              v-for="(brand, i) in brands"
              :key="`${loop}-${i}`"
              class="brand-pill flex items-center gap-3 shrink-0 px-5 py-3 rounded-2xl cursor-default group"
            >
              <!-- Logo avatar -->
              <div
                :class="`w-9 h-9 rounded-xl bg-gradient-to-br ${brand.gradient} flex items-center justify-center shadow-lg transition-transform duration-500 group-hover:scale-110`"
                :style="{ boxShadow: '0 4px 15px rgba(0,0,0,0.2)' }"
              >
                <span class="text-white font-display font-bold text-[11px] leading-none">{{ brand.initials }}</span>
              </div>
              <!-- Name -->
              <div class="flex flex-col">
                <span class="font-display font-semibold text-sm text-white/50 group-hover:text-white/80 whitespace-nowrap transition-colors duration-500">{{ brand.name }}</span>
                <div class="flex items-center gap-1 mt-0.5">
                  <svg width="10" height="10" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" fill="#2DD4A8" fill-opacity="0.6"/><polyline points="8 12 11 15 16 9" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                  <span class="text-[9px] text-white/20 font-display">Boutique vérifiée</span>
                </div>
              </div>
            </div>
          </template>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.brand-pill {
  background: rgba(255, 255, 255, 0.02);
  border: 1px solid rgba(255, 255, 255, 0.04);
  transition: all 0.5s ease;
}
.brand-pill:hover {
  background: rgba(255, 255, 255, 0.05);
  border-color: rgba(255, 255, 255, 0.08);
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(255, 255, 255, 0.04);
}
</style>
