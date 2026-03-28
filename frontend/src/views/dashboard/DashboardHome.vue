<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuth } from '../../composables/useAuth'
import { usePlan } from '../../composables/usePlan'

const { user, shop, api } = useAuth()
const { isFree, isStarter, isPro, planLabel, canUseFeature } = usePlan()

const stats = ref(null)
const orders = ref([])
const loading = ref(true)
const error = ref(null)

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

function formatCurrency(value) {
  return new Intl.NumberFormat('fr-FR').format(value) + ' F'
}

function formatDate(dateStr) {
  const d = new Date(dateStr)
  return d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'short', year: 'numeric' })
}

function orderItems(order) {
  if (!order.items || !order.items.length) return '—'
  return order.items.map(i => i.product_name || i.name).join(', ')
}

const greeting = computed(() => {
  const name = user.value?.name || ''
  return name ? `Bonjour, ${name}` : 'Bonjour'
})

async function fetchData() {
  loading.value = true
  error.value = null
  try {
    const [statsRes, ordersRes] = await Promise.all([
      api('/api/shop/stats'),
      api('/api/orders?limit=5'),
    ])
    stats.value = statsRes
    orders.value = Array.isArray(ordersRes) ? ordersRes : (ordersRes.data || ordersRes.orders || [])
  } catch (e) {
    error.value = e.message || 'Une erreur est survenue.'
  } finally {
    loading.value = false
  }
}

onMounted(fetchData)
</script>

<template>
  <div class="p-6 md:p-8 max-w-6xl">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="font-display font-bold text-2xl text-[var(--text-primary)] mb-1">{{ greeting }} 👋</h1>
      <p class="text-sm text-[var(--text-muted)]">Voici un aperçu de votre boutique aujourd'hui.</p>
    </div>

    <!-- Error State -->
    <div v-if="error" class="glass-card rounded-2xl p-8 text-center mb-8">
      <p class="text-[var(--text-primary)] font-display font-bold text-lg mb-2">Erreur de chargement</p>
      <p class="text-[var(--text-muted)] text-sm mb-4">{{ error }}</p>
      <button
        @click="fetchData"
        class="px-5 py-2.5 bg-brand text-white rounded-xl text-sm font-bold hover:bg-brand-light transition-colors"
      >
        Réessayer
      </button>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <!-- Vues aujourd'hui -->
      <div class="glass-card rounded-2xl p-5">
        <template v-if="loading">
          <div class="animate-pulse">
            <div class="h-6 bg-white/5 rounded w-24 mb-2"></div>
            <div class="h-8 bg-white/5 rounded w-16"></div>
          </div>
        </template>
        <template v-else-if="stats">
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-brand/10 flex items-center justify-center">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-brand)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </div>
          </div>
          <p class="font-display font-bold text-xl text-[var(--text-primary)]">{{ stats.views_today ?? 0 }}</p>
          <p class="text-xs text-[var(--text-muted)] mt-1">Vues aujourd'hui</p>
        </template>
      </div>

      <!-- Commandes -->
      <div class="glass-card rounded-2xl p-5">
        <template v-if="loading">
          <div class="animate-pulse">
            <div class="h-6 bg-white/5 rounded w-24 mb-2"></div>
            <div class="h-8 bg-white/5 rounded w-16"></div>
          </div>
        </template>
        <template v-else-if="stats">
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-mint/10 flex items-center justify-center">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-mint)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
            </div>
            <span v-if="stats.new_orders" class="text-xs font-mono text-mint">{{ stats.new_orders }} nouvelles</span>
          </div>
          <p class="font-display font-bold text-xl text-[var(--text-primary)]">{{ stats.total_orders ?? 0 }}</p>
          <p class="text-xs text-[var(--text-muted)] mt-1">Commandes</p>
        </template>
      </div>

      <!-- Revenus (mois) -->
      <div class="glass-card rounded-2xl p-5">
        <template v-if="loading">
          <div class="animate-pulse">
            <div class="h-6 bg-white/5 rounded w-24 mb-2"></div>
            <div class="h-8 bg-white/5 rounded w-16"></div>
          </div>
        </template>
        <template v-else-if="stats">
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-brand-amber/10 flex items-center justify-center">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-brand-amber)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            </div>
            <span v-if="stats.paid_orders_count" class="text-xs font-mono text-mint">{{ stats.paid_orders_count }} payées</span>
          </div>
          <p class="font-display font-bold text-xl text-[var(--text-primary)]">{{ formatCurrency(stats.paid_revenue_month ?? 0) }}</p>
          <p class="text-xs text-[var(--text-muted)] mt-1">Revenus collectés (mois)</p>
          <div v-if="stats.pending_revenue_month" class="mt-2 pt-2 border-t border-[var(--border-default)]">
            <div class="flex items-center justify-between">
              <span class="text-[10px] text-[var(--text-faint)]">En attente</span>
              <span class="text-xs font-medium text-amber-400">{{ formatCurrency(stats.pending_revenue_month) }}</span>
            </div>
          </div>
        </template>
      </div>

      <!-- Produits actifs -->
      <div class="glass-card rounded-2xl p-5">
        <template v-if="loading">
          <div class="animate-pulse">
            <div class="h-6 bg-white/5 rounded w-24 mb-2"></div>
            <div class="h-8 bg-white/5 rounded w-16"></div>
          </div>
        </template>
        <template v-else-if="stats">
          <div class="flex items-center justify-between mb-3">
            <div class="w-10 h-10 rounded-xl bg-electric/10 flex items-center justify-center">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--color-electric)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
            </div>
          </div>
          <p class="font-display font-bold text-xl text-[var(--text-primary)]">{{ stats.active_products ?? 0 }}/{{ stats.total_products ?? 0 }}</p>
          <p class="text-xs text-[var(--text-muted)] mt-1">Produits actifs</p>
        </template>
      </div>
    </div>

    <!-- Extra stats row for Pro -->
    <div v-if="stats && (stats.preorder_count || stats.digital_revenue_month || stats.low_stock_products)" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <!-- Digital revenue -->
      <div v-if="stats.digital_revenue_month !== undefined" class="glass-card rounded-2xl p-5">
        <div class="flex items-center gap-2 mb-3">
          <div class="w-8 h-8 rounded-lg bg-purple-500/10 flex items-center justify-center">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
          </div>
          <span class="text-xs text-[var(--text-muted)]">Revenus digitaux (mois)</span>
        </div>
        <p class="font-display font-bold text-lg text-[var(--text-primary)]">{{ formatCurrency(stats.digital_revenue_month || 0) }}</p>
        <p v-if="stats.digital_commissions_month" class="text-xs text-[var(--text-faint)] mt-1">
          Commissions : {{ formatCurrency(stats.digital_commissions_month) }}
        </p>
      </div>

      <!-- Preorders -->
      <div v-if="stats.preorder_count" class="glass-card rounded-2xl p-5">
        <div class="flex items-center gap-2 mb-3">
          <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
          <span class="text-xs text-[var(--text-muted)]">Précommandes actives</span>
        </div>
        <p class="font-display font-bold text-lg text-[var(--text-primary)]">{{ stats.preorder_count }}</p>
      </div>

      <!-- Low stock alerts -->
      <div v-if="stats.low_stock_products && stats.low_stock_products.length" class="glass-card rounded-2xl p-5">
        <div class="flex items-center gap-2 mb-3">
          <div class="w-8 h-8 rounded-lg bg-red-500/10 flex items-center justify-center">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          </div>
          <span class="text-xs text-[var(--text-muted)]">Stock bas</span>
        </div>
        <div class="space-y-1.5">
          <div v-for="p in stats.low_stock_products.slice(0, 3)" :key="p.id" class="flex items-center justify-between text-xs">
            <span class="text-[var(--text-secondary)] truncate mr-2">{{ p.name }}</span>
            <span class="font-mono text-red-400 shrink-0">{{ p.stock }}</span>
          </div>
          <p v-if="stats.low_stock_products.length > 3" class="text-[10px] text-[var(--text-faint)]">
            +{{ stats.low_stock_products.length - 3 }} autres
          </p>
        </div>
      </div>
    </div>

    <!-- Plan info card for free users -->
    <div v-if="isFree && !loading" class="mb-8 p-5 rounded-2xl border border-brand/20 bg-brand/5">
      <div class="flex items-center justify-between">
        <div>
          <p class="text-sm font-semibold text-[var(--text-primary)]">Plan {{ planLabel }}</p>
          <p class="text-xs text-[var(--text-muted)] mt-0.5">Passez au Starter pour débloquer les commandes et les statistiques</p>
        </div>
        <router-link to="/dashboard/upgrade" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-brand hover:bg-brand/90 transition-colors no-underline shrink-0">
          Voir les plans
        </router-link>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="glass-card rounded-2xl overflow-hidden">
      <div class="px-6 py-4 border-b border-[var(--border-default)] flex items-center justify-between">
        <h2 class="font-display font-bold text-[var(--text-primary)]">Commandes récentes</h2>
        <router-link to="/dashboard/orders" class="text-xs text-brand hover:text-brand-light transition-colors no-underline">
          Voir tout →
        </router-link>
      </div>

      <!-- Loading skeleton for orders -->
      <div v-if="loading" class="p-6 space-y-4">
        <div v-for="n in 5" :key="n" class="animate-pulse flex items-center gap-4">
          <div class="h-4 bg-white/5 rounded w-24"></div>
          <div class="h-4 bg-white/5 rounded w-32"></div>
          <div class="h-4 bg-white/5 rounded w-20 ml-auto"></div>
        </div>
      </div>

      <!-- Empty state -->
      <div v-else-if="!orders.length && !error" class="p-8 text-center">
        <p class="text-[var(--text-muted)] text-sm">Aucune commande pour le moment.</p>
      </div>

      <!-- Desktop table -->
      <div v-else class="hidden md:block">
        <table class="w-full">
          <thead>
            <tr class="border-b border-[var(--border-default)]">
              <th class="px-6 py-3 text-left text-xs font-display font-medium text-[var(--text-faint)] uppercase tracking-wider">Client</th>
              <th class="px-6 py-3 text-left text-xs font-display font-medium text-[var(--text-faint)] uppercase tracking-wider">Téléphone</th>
              <th class="px-6 py-3 text-left text-xs font-display font-medium text-[var(--text-faint)] uppercase tracking-wider">Produits</th>
              <th class="px-6 py-3 text-left text-xs font-display font-medium text-[var(--text-faint)] uppercase tracking-wider">Montant</th>
              <th class="px-6 py-3 text-left text-xs font-display font-medium text-[var(--text-faint)] uppercase tracking-wider">Statut</th>
              <th class="px-6 py-3 text-left text-xs font-display font-medium text-[var(--text-faint)] uppercase tracking-wider">Date</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="order in orders"
              :key="order.id"
              class="border-b border-[var(--border-default)] last:border-0 hover:bg-white/[0.02] transition-colors"
            >
              <td class="px-6 py-4 text-sm text-[var(--text-primary)]">{{ order.customer_name }}</td>
              <td class="px-6 py-4 text-sm font-mono text-[var(--text-muted)]">{{ order.customer_phone }}</td>
              <td class="px-6 py-4 text-sm text-[var(--text-muted)] max-w-[200px] truncate">{{ orderItems(order) }}</td>
              <td class="px-6 py-4 text-sm font-medium text-[var(--text-primary)]">{{ formatCurrency(order.total) }}</td>
              <td class="px-6 py-4">
                <span :class="`px-2.5 py-1 rounded-lg text-[11px] font-bold ${statusColors[order.status] || ''}`">
                  {{ statusLabels[order.status] || order.status }}
                </span>
              </td>
              <td class="px-6 py-4 text-sm text-[var(--text-muted)]">{{ formatDate(order.created_at) }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Mobile cards -->
      <div v-if="!loading && orders.length" class="md:hidden divide-y divide-[var(--border-default)]">
        <div v-for="order in orders" :key="order.id" class="p-4">
          <div class="flex items-center justify-between mb-2">
            <div>
              <p class="text-sm font-medium text-[var(--text-primary)]">{{ order.customer_name }}</p>
              <p class="text-xs text-[var(--text-faint)]">{{ order.customer_phone }}</p>
            </div>
            <span :class="`px-2 py-0.5 rounded text-[10px] font-bold ${statusColors[order.status] || ''}`">
              {{ statusLabels[order.status] || order.status }}
            </span>
          </div>
          <div class="flex items-center justify-between">
            <p class="text-xs text-[var(--text-muted)] truncate max-w-[60%]">{{ orderItems(order) }}</p>
            <p class="text-sm font-bold text-[var(--text-primary)]">{{ formatCurrency(order.total) }}</p>
          </div>
          <p class="text-xs text-[var(--text-faint)] mt-1">{{ formatDate(order.created_at) }}</p>
        </div>
      </div>
    </div>
  </div>
</template>
