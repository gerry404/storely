<script setup>
</script>

<template>
  <section class="py-24 relative overflow-hidden">
    <div class="container-max">
      <div class="relative rounded-[32px] overflow-hidden px-8 py-16 md:px-16 md:py-20 text-center" style="background: linear-gradient(135deg, #FF6B2C 0%, #FF8F3A 55%, #FFAA33 100%);">
        <!-- decorative shapes -->
        <div class="absolute -top-20 -right-20 w-96 h-96 rounded-full" style="background: radial-gradient(closest-side, rgba(255,255,255,0.22), transparent 70%);" />
        <div class="absolute -bottom-32 -left-20 w-96 h-96 rounded-full" style="background: radial-gradient(closest-side, rgba(255,255,255,0.15), transparent 70%);" />
        <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle, rgba(255,255,255,0.15) 1.5px, transparent 1.5px); background-size: 40px 40px;" />

        <div class="relative max-w-3xl mx-auto">
          <h2 class="font-display font-black text-white tracking-tight leading-[1.05] mb-5" style="font-size: clamp(2rem, 5vw, 3.5rem);">
            Prêt à lancer votre boutique en ligne ?
          </h2>
          <p class="text-white/85 text-lg md:text-xl mb-10 max-w-2xl mx-auto leading-relaxed">
            Rejoignez les centaines de marchands camerounais qui encaissent chaque jour via Storely. Compte gratuit, sans carte bancaire, 10 minutes d'installation.
          </p>

          <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <router-link to="/register" class="btn-lg px-8 text-base"
              style="background: #0F0F14; color: white; font-family: var(--font-display); font-weight: 700; border-radius: 14px; padding: 1rem 2rem; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; box-shadow: 0 10px 24px -4px rgba(15,15,20,0.35);">
              Créer ma boutique gratuitement
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </router-link>
            <router-link to="/pricing" class="btn-lg px-8 text-base"
              style="background: rgba(255,255,255,0.15); color: white; font-family: var(--font-display); font-weight: 700; border-radius: 14px; padding: 1rem 2rem; display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none; border: 1px solid rgba(255,255,255,0.28);">
              Voir les tarifs
            </router-link>
          </div>

          <div class="flex flex-wrap justify-center gap-x-6 gap-y-2 mt-8 text-xs font-medium text-white/80">
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
