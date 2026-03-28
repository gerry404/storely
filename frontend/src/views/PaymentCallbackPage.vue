<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const route = useRoute()
const router = useRouter()
const { api, fetchUser } = useAuth()

const status = ref('verifying') // verifying, success, error
const message = ref('')

onMounted(async () => {
  const transactionId = route.query.transaction_id
  const txRef = route.query.tx_ref

  if (!transactionId || !txRef) {
    status.value = 'error'
    message.value = 'Paramètres de paiement manquants.'
    return
  }

  try {
    const data = await api('/api/payments/verify', {
      method: 'POST',
      body: JSON.stringify({
        transaction_id: transactionId,
        tx_ref: txRef,
      }),
    })

    if (data.status === 'success' || data.status === 'already_processed') {
      status.value = 'success'
      message.value = 'Paiement confirmé ! Votre abonnement est maintenant actif.'
      // Refresh user data to update plan info
      await fetchUser()
      // Redirect to dashboard after 3s
      setTimeout(() => router.push('/dashboard'), 3000)
    } else {
      status.value = 'error'
      message.value = data.message || 'Le paiement n\'a pas abouti.'
    }
  } catch (e) {
    status.value = 'error'
    message.value = e.message || 'Erreur lors de la vérification du paiement.'
  }
})
</script>

<template>
  <div class="min-h-screen flex items-center justify-center px-4" style="background: var(--bg-primary); color: var(--text-primary)">
    <div class="max-w-md w-full text-center">
      <!-- Verifying -->
      <template v-if="status === 'verifying'">
        <div class="w-16 h-16 mx-auto mb-6 border-4 border-orange-500 border-t-transparent rounded-full animate-spin" />
        <h1 class="text-xl font-bold mb-2">Vérification du paiement...</h1>
        <p class="text-sm opacity-60">Veuillez patienter, nous confirmons votre paiement.</p>
      </template>

      <!-- Success -->
      <template v-if="status === 'success'">
        <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, #10B981, #059669)">
          <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h1 class="text-2xl font-bold mb-2">Paiement confirmé !</h1>
        <p class="text-sm opacity-60 mb-6">{{ message }}</p>
        <p class="text-xs opacity-40">Redirection vers le tableau de bord...</p>
        <router-link to="/dashboard" class="inline-block mt-4 px-6 py-3 rounded-xl text-white font-medium no-underline" style="background: linear-gradient(135deg, #f97316, #ea580c)">
          Aller au tableau de bord
        </router-link>
      </template>

      <!-- Error -->
      <template v-if="status === 'error'">
        <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center" style="background: linear-gradient(135deg, #EF4444, #DC2626)">
          <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </div>
        <h1 class="text-2xl font-bold mb-2">Paiement échoué</h1>
        <p class="text-sm opacity-60 mb-6">{{ message }}</p>
        <div class="flex gap-3 justify-center">
          <router-link to="/dashboard/upgrade" class="px-6 py-3 rounded-xl text-white font-medium no-underline" style="background: linear-gradient(135deg, #f97316, #ea580c)">
            Réessayer
          </router-link>
          <router-link to="/dashboard" class="px-6 py-3 rounded-xl font-medium no-underline border" style="border-color: var(--border-color); color: var(--text-primary)">
            Retour
          </router-link>
        </div>
      </template>
    </div>
  </div>
</template>
