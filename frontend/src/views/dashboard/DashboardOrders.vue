<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuth } from '../../composables/useAuth'

const { api } = useAuth()

const orders = ref([])
const loading = ref(true)
const loadingMore = ref(false)
const currentPage = ref(1)
const hasMorePages = ref(false)
const activeFilter = ref('all')
const updatingStatus = ref(null)

const filters = [
  { key: 'all', label: 'Toutes' },
  { key: 'new', label: 'Nouvelles' },
  { key: 'confirmed', label: 'Confirmées' },
  { key: 'shipped', label: 'Expédiées' },
  { key: 'delivered', label: 'Livrées' },
  { key: 'cancelled', label: 'Annulées' },
  { key: 'preorder', label: 'Précommandes' },
  { key: 'paid', label: 'Payées' },
]

const paymentStatusLabels = {
  unpaid: 'Non payé',
  pending: 'En attente',
  paid: 'Payé',
  failed: 'Échoué',
}
const paymentStatusColors = {
  unpaid: 'bg-white/5 text-white/30',
  pending: 'bg-amber-500/10 text-amber-400',
  paid: 'bg-emerald-500/10 text-emerald-400',
  failed: 'bg-red-500/10 text-red-400',
}

const statusColors = {
  new: 'bg-brand/10 text-brand',
  confirmed: 'bg-sky/10 text-sky',
  shipped: 'bg-electric/10 text-electric',
  delivered: 'bg-mint/10 text-mint',
  cancelled: 'bg-brand-coral/10 text-brand-coral',
}

const statusLabels = {
  new: 'Nouveau',
  confirmed: 'Confirmé',
  shipped: 'Expédié',
  delivered: 'Livré',
  cancelled: 'Annulé',
}

const filterCounts = computed(() => {
  const counts = { all: orders.value.length }
  for (const key of ['new', 'confirmed', 'shipped', 'delivered', 'cancelled']) {
    counts[key] = orders.value.filter(o => o.status === key).length
  }
  counts.preorder = orders.value.filter(o => o.is_preorder).length
  counts.paid = orders.value.filter(o => o.payment_status === 'paid').length
  return counts
})

const filteredOrders = computed(() => {
  if (activeFilter.value === 'all') return orders.value
  if (activeFilter.value === 'preorder') return orders.value.filter(o => o.is_preorder)
  if (activeFilter.value === 'paid') return orders.value.filter(o => o.payment_status === 'paid')
  return orders.value.filter(o => o.status === activeFilter.value)
})

function formatCurrency(amount) {
  return new Intl.NumberFormat('fr-FR').format(amount) + ' F'
}

function formatDate(date) {
  return new Date(date).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

function getItemNames(order) {
  if (order.product) return order.product.name
  if (order.items && order.items.length) return order.items.map(i => i.product_name || i.name || '').filter(Boolean).join(', ')
  return '—'
}

function whatsappUrl(phone) {
  if (!phone) return '#'
  const cleaned = phone.replace(/\D/g, '')
  return `https://wa.me/${cleaned}`
}

async function fetchOrders(page = 1) {
  try {
    const data = await api(`/api/orders?page=${page}`)
    if (page === 1) {
      orders.value = data.data || []
    } else {
      orders.value.push(...(data.data || []))
    }
    currentPage.value = page
    hasMorePages.value = data.current_page < data.last_page
  } catch (e) {
    console.error('Failed to fetch orders:', e)
  }
}

async function loadMore() {
  loadingMore.value = true
  await fetchOrders(currentPage.value + 1)
  loadingMore.value = false
}

async function updateStatus(orderId, newStatus) {
  updatingStatus.value = orderId
  try {
    await api(`/api/orders/${orderId}/status`, {
      method: 'PUT',
      body: JSON.stringify({ status: newStatus }),
    })
    const idx = orders.value.findIndex(o => o.id === orderId)
    if (idx !== -1) {
      orders.value[idx] = { ...orders.value[idx], status: newStatus }
    }
  } catch (e) {
    console.error('Failed to update status:', e)
  } finally {
    updatingStatus.value = null
  }
}

onMounted(async () => {
  loading.value = true
  await fetchOrders(1)
  loading.value = false
})
</script>

<template>
  <div class="p-6 md:p-8 max-w-6xl">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="font-display font-bold text-2xl text-white mb-1">Commandes</h1>
      <p class="text-sm text-white/40">Gérez les commandes de vos clients</p>
    </div>

    <!-- Filter tabs -->
    <div class="flex gap-2 mb-6 overflow-x-auto no-scrollbar">
      <button
        v-for="filter in filters"
        :key="filter.key"
        @click="activeFilter = filter.key"
        :class="[
          'px-4 py-2 rounded-xl text-sm font-medium shrink-0 transition-all',
          activeFilter === filter.key
            ? 'bg-white/10 text-white'
            : 'text-white/40 hover:bg-white/5'
        ]"
      >
        {{ filter.label }} ({{ filterCounts[filter.key] || 0 }})
      </button>
    </div>

    <!-- Loading skeletons -->
    <div v-if="loading" class="space-y-3">
      <div v-for="n in 5" :key="n" class="glass-card rounded-2xl p-5 animate-pulse">
        <div class="flex flex-col md:flex-row md:items-center gap-4">
          <div class="flex-1 space-y-3">
            <div class="flex items-center gap-3">
              <div class="h-4 w-20 bg-white/10 rounded"></div>
              <div class="h-4 w-16 bg-white/10 rounded-lg"></div>
            </div>
            <div class="h-4 w-32 bg-white/10 rounded"></div>
            <div class="h-3 w-48 bg-white/5 rounded"></div>
          </div>
          <div class="flex items-center gap-3">
            <div class="h-5 w-20 bg-white/10 rounded"></div>
            <div class="h-8 w-24 bg-white/5 rounded-xl"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div
      v-else-if="filteredOrders.length === 0"
      class="glass-card rounded-2xl p-12 text-center"
    >
      <div class="w-16 h-16 rounded-2xl bg-white/5 flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-white/20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 11.625l2.25-2.25M12 11.625l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
        </svg>
      </div>
      <p class="font-display font-semibold text-white mb-1">Aucune commande</p>
      <p class="text-sm text-white/40">
        {{ activeFilter === 'all' ? 'Vous n\'avez pas encore de commandes.' : 'Aucune commande avec ce statut.' }}
      </p>
    </div>

    <!-- Orders list -->
    <div v-else class="space-y-3">
      <div
        v-for="order in filteredOrders"
        :key="order.id"
        class="glass-card rounded-2xl p-5"
      >
        <div class="flex flex-col gap-4">
          <!-- Top row: order ID, status badge, status dropdown -->
          <div class="flex flex-wrap items-center gap-3">
            <span class="font-mono text-xs text-white/30">#ST-{{ order.id }}</span>
            <span
              :class="[
                'px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wide',
                statusColors[order.status] || 'bg-white/10 text-white/60'
              ]"
            >
              {{ statusLabels[order.status] || order.status }}
            </span>
            <span
              :class="[
                'px-2 py-0.5 rounded-lg text-[10px] font-bold',
                paymentStatusColors[order.payment_status || 'unpaid'] || 'bg-white/5 text-white/30'
              ]"
            >
              <svg v-if="order.payment_status === 'paid'" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="inline -mt-px mr-0.5"><polyline points="20 6 9 17 4 12"/></svg>
              {{ paymentStatusLabels[order.payment_status || 'unpaid'] || 'Non payé' }}
            </span>
            <div class="ml-auto">
              <select
                @change="updateStatus(order.id, $event.target.value)"
                :value="order.status"
                :disabled="updatingStatus === order.id"
                class="text-xs rounded-lg px-2 py-1 bg-white/5 border border-white/10 text-white/70 focus:outline-none focus:border-white/20 transition-colors cursor-pointer disabled:opacity-50"
              >
                <option value="new" class="bg-zinc-900 text-white">Nouveau</option>
                <option value="confirmed" class="bg-zinc-900 text-white">Confirmé</option>
                <option value="shipped" class="bg-zinc-900 text-white">Expédié</option>
                <option value="delivered" class="bg-zinc-900 text-white">Livré</option>
                <option value="cancelled" class="bg-zinc-900 text-white">Annulé</option>
              </select>
            </div>
          </div>

          <!-- Customer & items info -->
          <div class="flex flex-col md:flex-row md:items-center gap-4">
            <div class="flex-1 min-w-0">
              <p class="font-display font-semibold text-white text-sm">{{ order.customer_name }}</p>
              <p class="text-xs text-white/40 mt-0.5">{{ order.customer_phone }}</p>
              <p class="text-xs text-white/30 mt-1 truncate">{{ getItemNames(order) }}</p>
            </div>

            <!-- Amount, date & actions -->
            <div class="flex items-center gap-3 shrink-0">
              <div class="text-right mr-2">
                <span class="font-display font-bold block" :class="order.payment_status === 'paid' ? 'text-emerald-400' : 'text-white'">{{ formatCurrency(order.total) }}</span>
                <span v-if="order.payment_status === 'paid' && order.seller_amount" class="text-[10px] text-emerald-400/60 block">Net: {{ formatCurrency(order.seller_amount) }}</span>
                <span class="text-[11px] text-white/30">{{ formatDate(order.created_at) }}</span>
              </div>
              <a
                :href="whatsappUrl(order.customer_phone)"
                target="_blank"
                rel="noopener noreferrer"
                class="btn-whatsapp !py-2 !px-3 !text-xs !rounded-xl !shadow-[0_2px_0_0_#1DA851] shrink-0"
              >
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                </svg>
                Contacter
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Load more -->
    <div v-if="!loading && hasMorePages" class="mt-6 text-center">
      <button
        @click="loadMore"
        :disabled="loadingMore"
        class="px-6 py-2.5 rounded-xl text-sm font-medium bg-white/5 text-white/60 hover:bg-white/10 hover:text-white transition-all border border-white/10 disabled:opacity-50"
      >
        <span v-if="loadingMore" class="flex items-center gap-2">
          <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
          </svg>
          Chargement...
        </span>
        <span v-else>Charger plus</span>
      </button>
    </div>
  </div>
</template>
