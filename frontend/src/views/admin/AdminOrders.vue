<script setup>
import { ref, onMounted } from 'vue'
import { useAuth } from '../../composables/useAuth'
const { api } = useAuth()

const orders = ref([])
const loading = ref(true)
const statusFilter = ref('')
const currentPage = ref(1)
const totalPages = ref(1)

const fetchOrders = async (page = 1) => {
  loading.value = true
  try {
    const params = new URLSearchParams({ page })
    if (statusFilter.value) params.set('status', statusFilter.value)
    const data = await api(`/api/admin/orders?${params}`)
    orders.value = data.data || []
    currentPage.value = data.current_page || 1
    totalPages.value = data.last_page || 1
  } catch { orders.value = [] }
  loading.value = false
}

const fmt = (n) => n ? Number(n).toLocaleString('fr-FR') : '0'
const statusColors = { new: '#3B82F6', confirmed: '#F59E0B', shipped: '#8B5CF6', delivered: '#10B981', cancelled: '#EF4444' }

onMounted(() => fetchOrders())
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Commandes</h1>
    <div class="mb-4">
      <select v-model="statusFilter" @change="fetchOrders(1)" class="px-4 py-2.5 rounded-lg border text-sm" style="background: var(--bg-secondary); border-color: var(--border-color); color: var(--text-primary)">
        <option value="">Tous les statuts</option>
        <option value="new">Nouveau</option>
        <option value="confirmed">Confirmé</option>
        <option value="shipped">Expédié</option>
        <option value="delivered">Livré</option>
        <option value="cancelled">Annulé</option>
      </select>
    </div>
    <div class="rounded-xl border overflow-x-auto" style="background: var(--bg-secondary); border-color: var(--border-color)">
      <div v-if="loading" class="p-8 text-center"><div class="w-6 h-6 border-2 border-red-500 border-t-transparent rounded-full animate-spin mx-auto" /></div>
      <table v-else class="w-full text-sm">
        <thead><tr class="border-b text-left opacity-50" style="border-color: var(--border-color)">
          <th class="px-4 py-3 font-medium">#</th>
          <th class="px-4 py-3 font-medium">Client</th>
          <th class="px-4 py-3 font-medium">Produit</th>
          <th class="px-4 py-3 font-medium">Boutique</th>
          <th class="px-4 py-3 font-medium">Total</th>
          <th class="px-4 py-3 font-medium">Statut</th>
          <th class="px-4 py-3 font-medium">Date</th>
        </tr></thead>
        <tbody>
          <tr v-for="o in orders" :key="o.id" class="border-t" style="border-color: var(--border-color)">
            <td class="px-4 py-3 font-mono opacity-40">{{ o.id }}</td>
            <td class="px-4 py-3"><div class="font-medium">{{ o.customer_name }}</div><div class="text-xs opacity-40">{{ o.customer_phone }}</div></td>
            <td class="px-4 py-3 text-xs">{{ o.product?.name || '—' }}</td>
            <td class="px-4 py-3 text-xs">{{ o.shop?.name || '—' }}</td>
            <td class="px-4 py-3 font-medium">{{ fmt(o.total) }} F</td>
            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold text-white" :style="{ background: statusColors[o.status] || '#6B7280' }">{{ o.status }}</span></td>
            <td class="px-4 py-3 text-xs opacity-50">{{ new Date(o.created_at).toLocaleDateString('fr-FR') }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="totalPages > 1" class="flex justify-center gap-2 mt-4">
      <button v-for="p in totalPages" :key="p" @click="fetchOrders(p)" class="w-8 h-8 rounded-lg text-xs font-medium" :class="p === currentPage ? 'bg-red-500 text-white' : 'opacity-50'" :style="p !== currentPage ? { background: 'var(--bg-secondary)' } : {}">{{ p }}</button>
    </div>
  </div>
</template>
