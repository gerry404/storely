<script setup>
import { ref, onMounted } from 'vue'
import { useAuth } from '../../composables/useAuth'
const { api } = useAuth()

const referrals = ref([])
const loading = ref(true)
const statusFilter = ref('')

const fetchReferrals = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    if (statusFilter.value) params.set('status', statusFilter.value)
    const data = await api(`/api/admin/referrals?${params}`)
    referrals.value = data.data || []
  } catch { referrals.value = [] }
  loading.value = false
}

const rewardReferral = async (ref) => {
  try {
    await api(`/api/admin/referrals/${ref.id}/reward`, { method: 'POST' })
    ref.status = 'rewarded'
    ref.rewarded_at = new Date().toISOString()
  } catch { /* */ }
}

const statusColors = { pending: '#F59E0B', qualified: '#3B82F6', rewarded: '#10B981' }
const statusLabels = { pending: 'En attente', qualified: 'Qualifié', rewarded: 'Récompensé' }

onMounted(fetchReferrals)
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Parrainages</h1>
    <div class="mb-4">
      <select v-model="statusFilter" @change="fetchReferrals" class="px-4 py-2.5 rounded-lg border text-sm" style="background: var(--bg-secondary); border-color: var(--border-color); color: var(--text-primary)">
        <option value="">Tous</option>
        <option value="pending">En attente</option>
        <option value="qualified">Qualifiés</option>
        <option value="rewarded">Récompensés</option>
      </select>
    </div>
    <div class="rounded-xl border overflow-x-auto" style="background: var(--bg-secondary); border-color: var(--border-color)">
      <div v-if="loading" class="p-8 text-center"><div class="w-6 h-6 border-2 border-red-500 border-t-transparent rounded-full animate-spin mx-auto" /></div>
      <table v-else class="w-full text-sm">
        <thead><tr class="border-b text-left opacity-50" style="border-color: var(--border-color)">
          <th class="px-4 py-3 font-medium">Parrain</th>
          <th class="px-4 py-3 font-medium">Filleul</th>
          <th class="px-4 py-3 font-medium">Statut</th>
          <th class="px-4 py-3 font-medium">Mois</th>
          <th class="px-4 py-3 font-medium">Date</th>
          <th class="px-4 py-3 font-medium">Actions</th>
        </tr></thead>
        <tbody>
          <tr v-for="r in referrals" :key="r.id" class="border-t" style="border-color: var(--border-color)">
            <td class="px-4 py-3"><div class="font-medium text-xs">{{ r.referrer?.name }}</div><div class="text-[10px] opacity-40">{{ r.referrer?.email }}</div></td>
            <td class="px-4 py-3"><div class="font-medium text-xs">{{ r.referred?.name }}</div><div class="text-[10px] opacity-40">{{ r.referred?.email }}</div></td>
            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold text-white" :style="{ background: statusColors[r.status] }">{{ statusLabels[r.status] }}</span></td>
            <td class="px-4 py-3 text-center">{{ r.reward_months }}</td>
            <td class="px-4 py-3 text-xs opacity-50">{{ new Date(r.created_at).toLocaleDateString('fr-FR') }}</td>
            <td class="px-4 py-3">
              <button v-if="r.status === 'qualified'" @click="rewardReferral(r)" class="text-xs text-green-500 hover:underline">Récompenser</button>
              <span v-else class="text-xs opacity-30">—</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
