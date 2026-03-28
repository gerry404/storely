<script setup>
import { ref, computed } from 'vue'
import { useAuth } from '../../composables/useAuth'
import { usePlan } from '../../composables/usePlan'

const { api } = useAuth()
const { currentPlan, isFree, isStarter, isPro, planLabel } = usePlan()

const annual = ref(false)
const loading = ref(false)
const error = ref('')
const success = ref('')

const plans = [
  {
    id: 'free',
    name: 'Gratuit',
    desc: 'Pour commencer',
    priceMonthly: 0,
    priceAnnual: 0,
    promoPrice: 0,
    features: [
      { text: '5 produits max', included: true },
      { text: '1 image par produit', included: true },
      { text: 'Contact WhatsApp', included: true },
      { text: 'Produits digitaux (15%)', included: true },
      { text: 'Gestion commandes', included: false },
      { text: 'Statistiques', included: false },
      { text: 'Codes promo', included: false },
    ],
  },
  {
    id: 'starter',
    name: 'Starter',
    desc: 'Pour la croissance',
    priceMonthly: 3900,
    priceAnnual: 2900,
    promoPrice: 1443,
    savingsAnnual: 12000,
    features: [
      { text: '20 produits', included: true },
      { text: '3 images par produit', included: true },
      { text: 'Gestion des commandes', included: true },
      { text: 'Statistiques de visites', included: true },
      { text: 'Bouton WhatsApp', included: true },
      { text: 'Produits digitaux (8%)', included: true },
      { text: 'Codes promo', included: false },
      { text: 'Gestion des stocks', included: false },
    ],
  },
  {
    id: 'pro',
    name: 'Pro',
    desc: 'Pour dominer',
    priceMonthly: 9900,
    priceAnnual: 7400,
    promoPrice: 3663,
    savingsAnnual: 30000,
    popular: true,
    features: [
      { text: 'Produits illimités', included: true },
      { text: '10 images par produit', included: true },
      { text: 'Statistiques complètes', included: true },
      { text: 'Gestion des stocks + alertes', included: true },
      { text: 'Commandes + précommandes', included: true },
      { text: 'Codes promo & badges', included: true },
      { text: 'Badge boutique vérifiée', included: true },
      { text: 'Export de données', included: true },
      { text: 'Aucun branding Storely', included: true },
      { text: 'Produits digitaux (4%)', included: true },
      { text: 'Support prioritaire', included: true },
    ],
  },
]

const displayPrice = (plan) => {
  if (plan.id === 'free') return '0'
  if (annual.value) return plan.priceAnnual.toLocaleString('fr')
  if (plan.promoPrice) return plan.promoPrice.toLocaleString('fr')
  return plan.priceMonthly.toLocaleString('fr')
}

const isCurrentPlan = (planId) => currentPlan.value === planId

const canUpgradeTo = (planId) => {
  const order = ['free', 'starter', 'pro']
  return order.indexOf(planId) > order.indexOf(currentPlan.value)
}

const subscribe = async (planId) => {
  if (!canUpgradeTo(planId)) return
  loading.value = true
  error.value = ''
  success.value = ''
  try {
    const data = await api('/api/payments/subscribe', {
      method: 'POST',
      body: JSON.stringify({
        plan: planId,
        billing_cycle: annual.value ? 'annual' : 'monthly',
        apply_promo: !annual.value,
      }),
    })
    // Redirect to Flutterwave payment page
    if (data.payment_link) {
      window.location.href = data.payment_link
    } else {
      error.value = 'Impossible d\'initialiser le paiement.'
    }
  } catch (e) {
    error.value = e.message || 'Erreur lors de la souscription'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="max-w-5xl mx-auto px-4 md:px-8 py-8">
    <!-- Header -->
    <div class="text-center mb-10">
      <h1 class="font-display font-bold text-2xl md:text-3xl mb-2" :style="{ color: 'var(--text-primary)' }">
        Choisissez votre plan
      </h1>
      <p class="text-sm" :style="{ color: 'var(--text-muted)' }">
        Vous êtes actuellement en plan <strong :style="{ color: 'var(--text-primary)' }">{{ planLabel }}</strong>
      </p>

      <!-- Toggle -->
      <div class="flex items-center justify-center gap-3 mt-6">
        <span class="text-sm font-medium" :style="{ color: !annual ? 'var(--text-primary)' : 'var(--text-muted)' }">Mensuel</span>
        <button
          @click="annual = !annual"
          class="relative w-[52px] h-[28px] rounded-full transition-colors duration-300 shrink-0"
          :class="annual ? 'bg-brand' : 'bg-white/10'"
        >
          <span
            class="absolute top-[2px] left-[2px] w-6 h-6 rounded-full bg-white shadow-md transition-transform duration-300"
            :style="{ transform: annual ? 'translateX(24px)' : 'translateX(0)' }"
          />
        </button>
        <span class="text-sm font-medium" :style="{ color: annual ? 'var(--text-primary)' : 'var(--text-muted)' }">
          Annuel
          <span class="text-brand text-xs font-semibold ml-1">2 mois offerts</span>
        </span>
      </div>
    </div>

    <!-- Status messages -->
    <div v-if="error" class="mb-6 p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm text-center">
      {{ error }}
    </div>
    <div v-if="success" class="mb-6 p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm text-center">
      {{ success }}
    </div>

    <!-- Plans grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
      <div
        v-for="plan in plans"
        :key="plan.id"
        class="relative rounded-2xl border p-6 transition-all"
        :class="[
          plan.popular ? 'border-brand/40 md:scale-105 md:-my-2' : '',
          isCurrentPlan(plan.id) ? 'border-brand/30' : '',
        ]"
        :style="{
          background: 'var(--bg-secondary)',
          borderColor: plan.popular || isCurrentPlan(plan.id) ? undefined : 'var(--border-default)',
        }"
      >
        <!-- Popular badge -->
        <div v-if="plan.popular" class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 rounded-full bg-brand text-white text-xs font-bold">
          Recommandé
        </div>

        <!-- Current plan indicator -->
        <div v-if="isCurrentPlan(plan.id)" class="absolute -top-3 right-4 px-3 py-0.5 rounded-full text-xs font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">
          Plan actuel
        </div>

        <h3 class="font-display font-bold text-lg mb-1" :style="{ color: 'var(--text-primary)' }">{{ plan.name }}</h3>
        <p class="text-xs mb-4" :style="{ color: 'var(--text-muted)' }">{{ plan.desc }}</p>

        <!-- Price -->
        <div class="flex items-baseline gap-1 mb-1">
          <span v-if="!annual && plan.promoPrice && plan.id !== 'free'" class="text-base line-through mr-1" :style="{ color: 'var(--text-faint)' }">
            {{ plan.priceMonthly.toLocaleString('fr') }}
          </span>
          <span class="font-display font-bold text-3xl" :style="{ color: 'var(--text-primary)' }">{{ displayPrice(plan) }}</span>
          <span class="text-sm" :style="{ color: 'var(--text-muted)' }">F CFA</span>
          <span v-if="plan.id !== 'free'" class="text-sm" :style="{ color: 'var(--text-muted)' }">/mois</span>
        </div>

        <p v-if="!annual && plan.promoPrice && plan.id !== 'free'" class="text-xs text-brand mb-4">
          pendant 3 mois, puis {{ plan.priceMonthly.toLocaleString('fr') }} F/mois
        </p>
        <p v-else-if="annual && plan.savingsAnnual" class="text-xs text-emerald-400 mb-4">
          Économisez {{ plan.savingsAnnual.toLocaleString('fr') }} F CFA/an
        </p>
        <p v-else class="mb-4">&nbsp;</p>

        <!-- Features -->
        <ul class="space-y-2 mb-6">
          <li v-for="(f, j) in plan.features" :key="j" class="flex items-start gap-2">
            <svg v-if="f.included" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2DD4A8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 mt-0.5"><polyline points="20 6 9 17 4 12"/></svg>
            <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0 mt-0.5 opacity-20"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            <span class="text-sm" :style="{ color: f.included ? 'var(--text-secondary)' : 'var(--text-faint)' }">{{ f.text }}</span>
          </li>
        </ul>

        <!-- CTA -->
        <button
          v-if="canUpgradeTo(plan.id)"
          @click="subscribe(plan.id)"
          :disabled="loading"
          class="w-full py-3 rounded-xl text-sm font-semibold transition-all"
          :class="plan.popular
            ? 'bg-gradient-to-r from-brand to-amber-500 text-white hover:shadow-lg hover:shadow-brand/20'
            : 'bg-white/10 text-white hover:bg-white/15'"
        >
          {{ loading ? 'Chargement...' : `Passer au ${plan.name}` }}
        </button>
        <div
          v-else-if="isCurrentPlan(plan.id)"
          class="w-full py-3 rounded-xl text-sm font-medium text-center border"
          :style="{ color: 'var(--text-muted)', borderColor: 'var(--border-default)' }"
        >
          Plan actuel
        </div>
        <div v-else class="w-full py-3 rounded-xl text-sm font-medium text-center" :style="{ color: 'var(--text-faint)' }">
          —
        </div>
      </div>
    </div>

    <!-- Footer note -->
    <p class="text-center text-xs mt-8" :style="{ color: 'var(--text-faint)' }">
      Paiement par Mobile Money (Orange, MTN) · Annulez à tout moment · Facturation en F CFA
    </p>
  </div>
</template>
