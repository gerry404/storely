<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const status = ref('verifying') // verifying, success, error
const message = ref('')

onMounted(async () => {
  const txRef = route.query.tx_ref
  const transactionId = route.query.transaction_id

  if (!txRef || !transactionId) {
    status.value = 'error'
    message.value = 'Paramètres de paiement manquants.'
    return
  }

  try {
    const res = await fetch('/api/payments/verify', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ tx_ref: txRef, transaction_id: transactionId }),
    })
    const data = await res.json()

    if (res.ok && (data.status === 'success' || data.status === 'already_processed')) {
      status.value = 'success'
      message.value = data.message || 'Paiement confirmé !'
    } else {
      status.value = 'error'
      message.value = data.message || 'Le paiement a échoué.'
    }
  } catch {
    status.value = 'error'
    message.value = 'Erreur de vérification du paiement.'
  }
})
</script>

<template>
  <div class="min-h-screen flex items-center justify-center p-6" style="background: var(--bg-primary, #0A0A0F)">
    <!-- Verifying -->
    <div v-if="status === 'verifying'" class="text-center">
      <div class="w-16 h-16 mx-auto mb-6 border-3 border-brand border-t-transparent rounded-full animate-spin" />
      <h1 class="font-display font-bold text-xl text-white mb-2">Vérification du paiement...</h1>
      <p class="text-sm text-white/40">Un instant, nous confirmons votre transaction.</p>
    </div>

    <!-- Success -->
    <div v-else-if="status === 'success'" class="text-center max-w-md">
      <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center" style="background: rgba(34,197,94,0.1)">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#22C55E" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
      </div>
      <h1 class="font-display font-bold text-2xl text-white mb-2">Paiement confirmé !</h1>
      <p class="text-sm text-white/50 mb-2">{{ message }}</p>
      <p class="text-sm text-white/40 mb-8">Votre commande a été confirmée. Le vendeur va préparer votre produit.</p>
      <button @click="window.history.back()" class="px-8 py-3 rounded-xl text-sm font-bold text-white transition-all" style="background: linear-gradient(135deg, #FF6B2C, #FF8F5C)">
        Retour à la boutique
      </button>
    </div>

    <!-- Error -->
    <div v-else class="text-center max-w-md">
      <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center" style="background: rgba(239,68,68,0.1)">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </div>
      <h1 class="font-display font-bold text-2xl text-white mb-2">Paiement échoué</h1>
      <p class="text-sm text-white/50 mb-8">{{ message }}</p>
      <button @click="window.history.back()" class="px-8 py-3 rounded-xl text-sm font-bold text-white/70 border border-white/10 hover:bg-white/5 transition-all">
        Retour
      </button>
    </div>
  </div>
</template>
