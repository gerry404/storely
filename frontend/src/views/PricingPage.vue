<script setup>
import PricingPreview from '../components/landing/PricingPreview.vue'
import CTASection from '../components/landing/CTASection.vue'
</script>

<template>
  <main class="pt-24">
    <PricingPreview />

    <!-- FAQ -->
    <section class="py-16 max-w-3xl mx-auto px-6">
      <h2 class="font-display font-bold text-2xl text-white text-center mb-10 reveal">Questions fréquentes</h2>
      <div class="space-y-3">
        <details
          v-for="(faq, i) in [
            { q: 'Comment je paie mon abonnement ?', a: 'Par Mobile Money (Orange Money, MTN MoMo). Vous pouvez aussi payer par carte bancaire si vous en avez une.' },
            { q: 'Je peux annuler quand je veux ?', a: 'Oui, aucun engagement. Vous pouvez annuler votre abonnement à tout moment depuis votre tableau de bord.' },
            { q: 'J\'ai besoin de compétences techniques ?', a: 'Absolument pas. Si vous savez poster une photo sur WhatsApp, vous savez utiliser Storely. C\'est aussi simple que ça.' },
            { q: 'Mes clients peuvent commander en ligne ?', a: 'Oui ! Avec le plan Pro, vos clients peuvent passer des commandes directement depuis votre vitrine.' },
            { q: 'Vous proposez un service de création de vitrine ?', a: 'Oui, notre service d\'onboarding assisté à 5 000 F permet à un de nos agents de créer votre vitrine complète pour vous.' }
          ]"
          :key="i"
          class="reveal glass-card rounded-xl group"
          :style="{ transitionDelay: `${i * 50}ms` }"
        >
          <summary class="px-6 py-4 cursor-pointer font-display font-medium text-white text-sm list-none flex items-center justify-between">
            {{ faq.q }}
            <svg class="w-4 h-4 text-white/30 transition-transform group-open:rotate-45 shrink-0 ml-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
          </summary>
          <div class="px-6 pb-4 text-sm text-white/40 leading-relaxed">{{ faq.a }}</div>
        </details>
      </div>
    </section>

    <CTASection />
  </main>
</template>
