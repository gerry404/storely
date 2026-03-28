<script setup>
import { ref, onMounted } from 'vue'
import { useAuth } from '../../composables/useAuth'
const { api } = useAuth()

const payments = ref([])
const revenue = ref(null)
const loading = ref(true)
const statusFilter = ref('')
const currentPage = ref(1)
const totalPages = ref(1)

const fetchPayments = async (page = 1) => {
  loading.value = true
  try {
    const params = new URLSearchParams({ page })
    if (statusFilter.value) params.set('status', statusFilter.value)
    const [pData, rData] = await Promise.all([
      api(`/api/admin/payments?${params}`),
      api('/api/admin/revenue?days=30'),
    ])
    payments.value = pData.data || []
    currentPage.value = pData.current_page || 1
    totalPages.value = pData.last_page || 1
    revenue.value = rData
  } catch { payments.value = [] }
  loading.value = false
}

const fmt = (n) => n ? Number(n).toLocaleString('fr-FR') : '0'
const statusColors = { pending: '#F59E0B', successful: '#10B981', failed: '#EF4444', cancelled: '#6B7280' }

onMounted(() => fetchPayments())
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Paiements & Revenus</h1>

    <!-- Revenue stats -->
    <div v-if="revenue" class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <div class="rounded-xl border p-4" style="background: var(--bg-secondary); border-color: var(--border-color)">
        <div class="text-2xl font-bold text-green-500">{{ fmt(revenue.total) }} F</div>
        <div class="text-xs opacity-50">Revenus (30j)</div>
      </div>
      <div class="rounded-xl border p-4" style="background: var(--bg-secondary); border-color: var(--border-color)">
        <div class="text-2xl font-bold">{{ revenue.count }}</div>
        <div class="text-xs opacity-50">Transactions (30j)</div>
      </div>
      <div class="rounded-xl border p-4" style="background: var(--bg-secondary); border-color: var(--border-color)">
        <div class="text-2xl font-bold text-purple-500">{{ fmt(revenue.digital_commissions) }} F</div>
        <div class="text-xs opacity-50">Commissions digitales</div>
      </div>
      <div class="rounded-xl border p-4" style="background: var(--bg-secondary); border-color: var(--border-color)">
        <div class="text-2xl font-bold text-amber-500">{{ revenue.count > 0 ? fmt(Math.round(revenue.total / revenue.count)) : '0' }} F</div>
        <div class="text-xs opacity-50">Panier moyen</div>
      </div>
    </div>

    <div class="mb-4">
      <select v-model="statusFilter" @change="fetchPayments(1)" class="px-4 py-2.5 rounded-lg border text-sm" style="background: var(--bg-secondary); border-color: var(--border-color); color: var(--text-primary)">
        <option value="">Tous les statuts</option>
        <option value="successful">Réussi</option>
        <option value="pending">En attente</option>
        <option value="failed">Échoué</option>
      </select>
    </div>

    <div class="rounded-xl border overflow-x-auto" style="background: var(--bg-secondary); border-color: var(--border-color)">
      <div v-if="loading" class="p-8 text-center"><div class="w-6 h-6 border-2 border-red-500 border-t-transparent rounded-full animate-spin mx-auto" /></div>
      <table v-else class="w-full text-sm">
        <thead><tr class="border-b text-left opacity-50" style="border-color: var(--border-color)">
          <th class="px-4 py-3 font-medium">Ref</th>
          <th class="px-4 py-3 font-medium">Utilisateur</th>
          <th class="px-4 py-3 font-medium">Type</th>
          <th class="px-4 py-3 font-medium">Montant</th>
          <th class="px-4 py-3 font-medium">Méthode</th>
          <th class="px-4 py-3 font-medium">Statut</th>
          <th class="px-4 py-3 font-medium">Date</th>
        </tr></thead>
        <tbody>
          <tr v-for="p in payments" :key="p.id" class="border-t" style="border-color: var(--border-color)">
            <td class="px-4 py-3 font-mono text-xs opacity-40">{{ p.flw_tx_ref?.slice(0, 16) }}</td>
            <td class="px-4 py-3"><div class="font-medium text-xs">{{ p.user?.name }}</div><div class="text-[10px] opacity-40">{{ p.shop?.name }}</div></td>
            <td class="px-4 py-3 text-xs capitalize">{{ p.type }}</td>
            <td class="px-4 py-3 font-medium">{{ fmt(p.amount) }} {{ p.currency }}</td>
            <td class="px-4 py-3 text-xs opacity-60">{{ p.payment_method || '—' }}</td>
            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold text-white" :style="{ background: statusColors[p.status] || '#6B7280' }">{{ p.status }}</span></td>
            <td class="px-4 py-3 text-xs opacity-50">{{ p.paid_at ? new Date(p.paid_at).toLocaleDateString('fr-FR') : '—' }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="totalPages > 1" class="flex justify-center gap-2 mt-4">
      <button v-for="p in totalPages" :key="p" @click="fetchPayments(p)" class="w-8 h-8 rounded-lg text-xs font-medium" :class="p === currentPage ? 'bg-red-500 text-white' : 'opacity-50'" :style="p !== currentPage ? { background: 'var(--bg-secondary)' } : {}">{{ p }}</button>
    </div>
  </div>
</template>
