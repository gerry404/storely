<script setup>
import { ref, onMounted } from 'vue'
import { useAuth } from '../../composables/useAuth'
const { api } = useAuth()

const subs = ref([])
const loading = ref(true)
const statusFilter = ref('')

const fetchSubs = async (page = 1) => {
  loading.value = true
  try {
    const params = new URLSearchParams({ page })
    if (statusFilter.value) params.set('status', statusFilter.value)
    const data = await api(`/api/admin/subscriptions?${params}`)
    subs.value = data.data || []
  } catch { subs.value = [] }
  loading.value = false
}

const fmt = (n) => n ? Number(n).toLocaleString('fr-FR') : '0'

onMounted(() => fetchSubs())
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Abonnements</h1>
    <div class="mb-4">
      <select v-model="statusFilter" @change="fetchSubs(1)" class="px-4 py-2.5 rounded-lg border text-sm" style="background: var(--bg-secondary); border-color: var(--border-color); color: var(--text-primary)">
        <option value="">Tous</option>
        <option value="active">Actifs</option>
        <option value="cancelled">Annulés</option>
        <option value="expired">Expirés</option>
      </select>
    </div>
    <div class="rounded-xl border overflow-x-auto" style="background: var(--bg-secondary); border-color: var(--border-color)">
      <div v-if="loading" class="p-8 text-center"><div class="w-6 h-6 border-2 border-red-500 border-t-transparent rounded-full animate-spin mx-auto" /></div>
      <table v-else class="w-full text-sm">
        <thead><tr class="border-b text-left opacity-50" style="border-color: var(--border-color)">
          <th class="px-4 py-3 font-medium">Boutique</th>
          <th class="px-4 py-3 font-medium">Plan</th>
          <th class="px-4 py-3 font-medium">Cycle</th>
          <th class="px-4 py-3 font-medium">Montant</th>
          <th class="px-4 py-3 font-medium">Statut</th>
          <th class="px-4 py-3 font-medium">Début</th>
          <th class="px-4 py-3 font-medium">Expiration</th>
        </tr></thead>
        <tbody>
          <tr v-for="s in subs" :key="s.id" class="border-t" style="border-color: var(--border-color)">
            <td class="px-4 py-3 font-medium text-xs">{{ s.shop?.name || '—' }}</td>
            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold text-white" :style="{ background: s.plan === 'pro' ? '#EF4444' : '#F59E0B' }">{{ s.plan }}</span></td>
            <td class="px-4 py-3 text-xs capitalize">{{ s.billing_cycle }}</td>
            <td class="px-4 py-3">{{ fmt(s.amount) }} F</td>
            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold text-white" :style="{ background: s.status === 'active' ? '#10B981' : s.status === 'cancelled' ? '#EF4444' : '#6B7280' }">{{ s.status }}</span></td>
            <td class="px-4 py-3 text-xs opacity-50">{{ s.starts_at ? new Date(s.starts_at).toLocaleDateString('fr-FR') : '—' }}</td>
            <td class="px-4 py-3 text-xs opacity-50">{{ s.expires_at ? new Date(s.expires_at).toLocaleDateString('fr-FR') : '—' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
