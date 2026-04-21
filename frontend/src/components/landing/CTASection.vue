<script setup>
import { ref } from 'vue'
import { useReveal } from '../../composables/useReveal'

const cardRef = ref(null)
useReveal(cardRef)
</script>

<template>
  <section class="py-20 md:py-28 relative overflow-hidden">
    <div class="container-max">
      <div ref="cardRef" class="reveal-mask relative rounded-[32px] overflow-hidden px-7 py-14 md:px-16 md:py-20 text-center"
           style="background: linear-gradient(135deg, #FF6B2C 0%, #FF8F3A 55%, #FFAA33 100%);">
        <div class="absolute -top-24 -right-24 w-96 h-96 rounded-full" style="background: radial-gradient(closest-side, rgba(255,255,255,0.22), transparent 70%);" />
        <div class="absolute -bottom-32 -left-24 w-[28rem] h-[28rem] rounded-full" style="background: radial-gradient(closest-side, rgba(255,255,255,0.15), transparent 70%);" />
        <div class="absolute inset-0 opacity-25" style="background-image: radial-gradient(circle, rgba(255,255,255,0.22) 1.5px, transparent 1.5px); background-size: 36px 36px;" />

        <div class="relative max-w-3xl mx-auto">
          <h2 class="font-display font-black text-white tracking-tight leading-[1.05] mb-5" style="font-size: clamp(2rem, 5vw, 3.25rem);">
            Votre première vente, ce soir.
          </h2>
          <p class="text-white/90 text-lg md:text-xl mb-10 max-w-2xl mx-auto leading-relaxed">
            Créez votre compte en 30 secondes, collez votre lien en bio sur vos réseaux sociaux, recevez votre premier paiement Mobile Money avant de vous coucher.
          </p>

          <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <router-link to="/register"
                         class="shine-sweep"
                         style="background: #0F0F14; color: white; font-family: var(--font-display); font-weight: 700; border-radius: 14px; padding: 1.05rem 2rem; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; box-shadow: 0 10px 24px -4px rgba(15,15,20,0.35); font-size: 1rem;">
              Créer ma boutique gratuitement
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
              </svg>
            </router-link>
            <router-link to="/pricing"
                         style="background: rgba(255,255,255,0.15); color: white; font-family: var(--font-display); font-weight: 700; border-radius: 14px; padding: 1.05rem 2rem; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; border: 1px solid rgba(255,255,255,0.3); font-size: 1rem;">
              Voir les tarifs
            </router-link>
          </div>

          <div class="flex flex-wrap justify-center gap-x-6 gap-y-2 mt-8 text-xs font-medium text-white/85">
            <span class="flex items-center gap-1.5">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
              Gratuit pour démarrer
            </span>
            <span class="flex items-center gap-1.5">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
              Sans carte bancaire
            </span>
            <span class="flex items-center gap-1.5">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round"><polyline points="20 6 9 17 4 12"/></svg>
              Aucun engagement
            </span>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
