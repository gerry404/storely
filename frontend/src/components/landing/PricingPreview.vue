<script setup>
import { ref } from 'vue'

const billing = ref('monthly')

const plans = [
  {
    name: 'Découverte',
    price: { monthly: 0, yearly: 0 },
    desc: 'Pour démarrer et tester la vente en ligne sans engagement.',
    cta: 'Commencer gratuitement',
    ctaStyle: 'btn-secondary',
    features: [
      'Jusqu\'à 10 produits',
      'Vitrine boutique basique',
      'Partage WhatsApp',
      'Badge "Créé avec Storely"',
    ],
    badge: null,
  },
  {
    name: 'Starter',
    price: { monthly: 5000, yearly: 50000 },
    desc: 'Pour vendre sérieusement avec paiement en ligne et stats.',
    cta: 'Choisir Starter',
    ctaStyle: 'btn-primary',
    highlight: true,
    features: [
      'Produits illimités',
      'Paiements Mobile Money + Carte',
      'Réconciliation auto paiements',
      'Panier abandonné WhatsApp',
      'Zones de livraison',
      'Statistiques avancées',
      'Sans badge Storely',
    ],
    badge: 'Le plus populaire',
  },
  {
    name: 'Pro',
    price: { monthly: 15000, yearly: 150000 },
    desc: 'Pour les boutiques qui scalent et exigent tout.',
    cta: 'Choisir Pro',
    ctaStyle: 'btn-ink',
    features: [
      'Tout du plan Starter',
      'Page builder storefront',
      'IA fiches produit illimitées',
      'Produits digitaux',
      'Précommandes',
      'Domaine personnalisé',
      'Support prioritaire 7j/7',
    ],
    badge: null,
  },
]

const formatPrice = (p) => {
  if (p === 0) return 'Gratuit'
  return `${p.toLocaleString('fr-FR')} F`
}
</script>

<template>
  <section class="section-lg relative">
    <div class="container-max">
      <div class="text-center max-w-2xl mx-auto mb-12">
        <span class="eyebrow">Tarifs transparents</span>
        <h2 class="display-xl mt-4 mb-4">Un plan pour chaque étape de votre boutique.</h2>
        <p class="text-lg" style="color: var(--text-muted)">
          Démarrez gratuitement. Passez à un plan payant quand vos ventes décollent. Changez de plan quand vous voulez.
        </p>

        <!-- Billing toggle -->
        <div class="inline-flex items-center p-1 rounded-full mt-6" style="background: var(--bg-tertiary); border: 1px solid var(--border-default)">
          <button
            @click="billing = 'monthly'"
            class="px-4 py-1.5 rounded-full text-sm font-semibold font-display transition-all"
            :style="billing === 'monthly' ? { background: 'var(--bg-secondary)', color: 'var(--text-primary)', boxShadow: 'var(--card-shadow)' } : { color: 'var(--text-muted)' }"
          >Mensuel</button>
          <button
            @click="billing = 'yearly'"
            class="px-4 py-1.5 rounded-full text-sm font-semibold font-display transition-all flex items-center gap-1.5"
            :style="billing === 'yearly' ? { background: 'var(--bg-secondary)', color: 'var(--text-primary)', boxShadow: 'var(--card-shadow)' } : { color: 'var(--text-muted)' }"
          >
            Annuel
            <span class="badge badge-mint text-[9px]">-17%</span>
          </button>
        </div>
      </div>

      <div class="grid md:grid-cols-3 gap-5 max-w-5xl mx-auto">
        <div v-for="(plan, i) in plans" :key="plan.name"
             class="relative p-7 md:p-8 rounded-2xl flex flex-col"
             :style="plan.highlight
               ? { background: 'var(--bg-inverse)', color: 'var(--text-on-inverse)', border: '1px solid var(--bg-inverse)', boxShadow: 'var(--card-shadow-xl)', transform: 'scale(1.02)' }
               : { background: 'var(--card-bg)', border: '1px solid var(--card-border)', boxShadow: 'var(--card-shadow)' }">
          <!-- Popular badge -->
          <div v-if="plan.badge" class="absolute -top-3 left-1/2 -translate-x-1/2">
            <span class="px-3 py-1 rounded-full text-[10px] font-black font-display uppercase tracking-wider text-white" style="background: var(--color-brand); box-shadow: 0 4px 12px rgba(255,107,44,0.4)">{{ plan.badge }}</span>
          </div>

          <h3 class="font-display font-bold text-xl mb-1" :style="plan.highlight ? { color: 'white' } : { color: 'var(--text-primary)' }">{{ plan.name }}</h3>
          <p class="text-sm mb-5" :style="plan.highlight ? { color: 'rgba(255,255,255,0.6)' } : { color: 'var(--text-muted)' }">{{ plan.desc }}</p>

          <div class="mb-6">
            <span class="font-display font-black text-4xl" :style="plan.highlight ? { color: 'white' } : { color: 'var(--text-primary)' }">
              {{ formatPrice(plan.price[billing]) }}
            </span>
            <span v-if="plan.price[billing] > 0" class="text-sm ml-1" :style="plan.highlight ? { color: 'rgba(255,255,255,0.55)' } : { color: 'var(--text-muted)' }">
              / {{ billing === 'monthly' ? 'mois' : 'an' }}
            </span>
          </div>

          <ul class="space-y-3 mb-7 flex-1">
            <li v-for="f in plan.features" :key="f" class="flex items-start gap-2.5 text-sm" :style="plan.highlight ? { color: 'rgba(255,255,255,0.85)' } : { color: 'var(--text-secondary)' }">
              <span class="inline-flex w-4 h-4 rounded-full shrink-0 mt-0.5 items-center justify-center" :style="plan.highlight ? { background: 'rgba(45,212,168,0.25)', color: '#2DD4A8' } : { background: 'rgba(45,212,168,0.15)', color: '#0F9E7A' }">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              </span>
              {{ f }}
            </li>
          </ul>

          <router-link to="/register"
                       class="w-full"
                       :class="plan.highlight ? 'btn-primary' : plan.ctaStyle">
            {{ plan.cta }}
          </router-link>
        </div>
      </div>

      <p class="text-center text-sm mt-8" style="color: var(--text-muted)">
        Tous les plans incluent : SSL, sauvegardes, support email, mises à jour gratuites.
        <router-link to="/pricing" class="ml-1 font-semibold no-underline" style="color: var(--color-brand)">Voir tous les détails →</router-link>
      </p>
    </div>
  </section>
</template>
