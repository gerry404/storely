<script setup>
import { onMounted, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const router = useRouter()
const route = useRoute()
const { api } = useAuth()
const error = ref('')

onMounted(async () => {
  const code = route.query.code
  if (!code) {
    error.value = 'Code d\'autorisation manquant.'
    setTimeout(() => router.push('/login'), 2000)
    return
  }

  try {
    const data = await api('/api/auth/google/callback', {
      method: 'POST',
      body: JSON.stringify({ code }),
    })

    // Set session
    localStorage.setItem('storely-token', data.token)
    localStorage.setItem('storely-user', JSON.stringify(data.user))
    localStorage.setItem('storely-shop', JSON.stringify(data.shop))
    if (data.plan_info) localStorage.setItem('storely-plan-info', JSON.stringify(data.plan_info))

    router.push('/dashboard')
  } catch (err) {
    error.value = err.message || 'Échec de la connexion Google.'
    setTimeout(() => router.push('/login'), 3000)
  }
})
</script>

<template>
  <main class="min-h-screen flex items-center justify-center" style="background: var(--bg-primary);">
    <div class="text-center">
      <div v-if="!error" class="flex flex-col items-center gap-4">
        <svg width="32" height="32" viewBox="0 0 24 24" class="animate-spin" fill="none" style="color: var(--color-brand);">
          <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.25"/>
          <path d="M12 2a10 10 0 019.95 9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
        </svg>
        <p class="t-text-muted text-sm font-display">Connexion en cours...</p>
      </div>
      <div v-else class="flex flex-col items-center gap-3">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#FF4D6A" stroke-width="2">
          <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
        </svg>
        <p class="text-sm" style="color: #FF4D6A;">{{ error }}</p>
        <p class="text-xs t-text-faint">Redirection...</p>
      </div>
    </div>
  </main>
</template>
