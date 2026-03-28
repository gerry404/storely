<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuth } from '../../composables/useAuth'
import { getCountryByCode } from '../../data/countries'

const { api } = useAuth()
const data = ref(null)
const loading = ref(true)

const fetchDashboard = async () => {
  loading.value = true
  try {
    data.value = await api('/api/admin/dashboard')
  } catch {
    data.value = null
  }
  loading.value = false
}

const fmt = (n) => {
  if (!n) return '0'
  return Number(n).toLocaleString('fr-FR')
}

onMounted(fetchDashboard)
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Vue d'ensemble</h1>

    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="w-8 h-8 border-2 border-red-500 border-t-transparent rounded-full animate-spin" />
    </div>

    <template v-else-if="data">
      <!-- Stats grid -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="rounded-xl border p-4" style="background: var(--bg-secondary); border-color: var(--border-color)">
          <div class="text-2xl font-bold">{{ fmt(data.stats.total_users) }}</div>
          <div class="text-xs opacity-50">Utilisateurs</div>
        </div>
        <div class="rounded-xl border p-4" style="background: var(--bg-secondary); border-color: var(--border-color)">
          <div class="text-2xl font-bold">{{ fmt(data.stats.total_shops) }}</div>
          <div class="text-xs opacity-50">Boutiques ({{ data.stats.active_shops }} actives)</div>
        </div>
        <div class="rounded-xl border p-4" style="background: var(--bg-secondary); border-color: var(--border-color)">
          <div class="text-2xl font-bold">{{ fmt(data.stats.total_products) }}</div>
          <div class="text-xs opacity-50">Produits</div>
        </div>
        <div class="rounded-xl border p-4" style="background: var(--bg-secondary); border-color: var(--border-color)">
          <div class="text-2xl font-bold">{{ fmt(data.stats.total_orders) }}</div>
          <div class="text-xs opacity-50">Commandes ({{ fmt(data.stats.orders_30d) }} / 30j)</div>
        </div>
        <div class="rounded-xl border p-4" style="background: var(--bg-secondary); border-color: var(--border-color)">
          <div class="text-2xl font-bold text-green-500">{{ fmt(data.stats.total_revenue) }} <span class="text-sm font-normal opacity-60">F</span></div>
          <div class="text-xs opacity-50">Revenus totaux</div>
        </div>
        <div class="rounded-xl border p-4" style="background: var(--bg-secondary); border-color: var(--border-color)">
          <div class="text-2xl font-bold text-green-500">{{ fmt(data.stats.revenue_30d) }} <span class="text-sm font-normal opacity-60">F</span></div>
          <div class="text-xs opacity-50">Revenus 30 jours</div>
        </div>
        <div class="rounded-xl border p-4" style="background: var(--bg-secondary); border-color: var(--border-color)">
          <div class="text-2xl font-bold text-blue-500">{{ fmt(data.stats.active_subscriptions) }}</div>
          <div class="text-xs opacity-50">Abonnements actifs</div>
        </div>
        <div class="rounded-xl border p-4" style="background: var(--bg-secondary); border-color: var(--border-color)">
          <div class="text-2xl font-bold text-purple-500">{{ fmt(data.stats.digital_commissions) }} <span class="text-sm font-normal opacity-60">F</span></div>
          <div class="text-xs opacity-50">Commissions digitales</div>
        </div>
      </div>

      <!-- Plan distribution -->
      <div class="grid md:grid-cols-2 gap-6 mb-8">
        <div class="rounded-xl border p-5" style="background: var(--bg-secondary); border-color: var(--border-color)">
          <h3 class="font-semibold mb-4">Distribution des plans</h3>
          <div class="space-y-3">
            <div v-for="(count, plan) in data.plan_distribution" :key="plan">
              <div class="flex items-center justify-between text-sm mb-1">
                <span class="capitalize font-medium">{{ plan }}</span>
                <span class="opacity-60">{{ count }}</span>
              </div>
              <div class="h-2 rounded-full overflow-hidden" style="background: var(--bg-primary)">
                <div
                  class="h-full rounded-full transition-all"
                  :style="{
                    width: `${Math.max(2, (count / Math.max(data.stats.total_shops, 1)) * 100)}%`,
                    background: plan === 'free' ? '#6B7280' : plan === 'starter' ? '#F59E0B' : '#EF4444'
                  }"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Recent payments -->
        <div class="rounded-xl border p-5" style="background: var(--bg-secondary); border-color: var(--border-color)">
          <h3 class="font-semibold mb-4">Derniers paiements</h3>
          <div v-if="data.recent_payments?.length" class="space-y-2">
            <div v-for="p in data.recent_payments" :key="p.id" class="flex items-center justify-between text-sm">
              <div>
                <span class="font-medium">{{ p.user?.name || '—' }}</span>
                <span class="opacity-40 ml-1">{{ p.shop?.name }}</span>
              </div>
              <span class="font-mono text-green-500">+{{ fmt(p.amount) }} F</span>
            </div>
          </div>
          <p v-else class="text-sm opacity-40">Aucun paiement</p>
        </div>
      </div>

      <!-- Country distribution -->
      <div v-if="data.country_distribution?.length" class="rounded-xl border p-5 mb-8" style="background: var(--bg-secondary); border-color: var(--border-color)">
        <h3 class="font-semibold mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5a17.92 17.92 0 01-8.716-2.247m0 0A9.015 9.015 0 003 12c0-1.605.42-3.113 1.157-4.418"/></svg>
          Distribution par pays
        </h3>
        <div class="space-y-2.5">
          <div v-for="item in data.country_distribution" :key="item.country" class="flex items-center gap-3">
            <div class="w-8 text-center text-lg leading-none">{{ getCountryByCode(item.country)?.flag || '🌍' }}</div>
            <div class="w-28 text-sm font-medium truncate">{{ getCountryByCode(item.country)?.name || item.country }}</div>
            <div class="flex-1 h-6 rounded-lg overflow-hidden relative" style="background: var(--bg-primary)">
              <div
                class="h-full rounded-lg transition-all flex items-center justify-end pr-2"
                :style="{
                  width: `${Math.max(8, (item.count / Math.max(data.country_distribution[0]?.count, 1)) * 100)}%`,
                  background: `linear-gradient(90deg, #EF4444${item === data.country_distribution[0] ? '' : '90'}, #F97316${item === data.country_distribution[0] ? '' : '70'})`
                }"
              >
                <span class="text-[11px] font-bold text-white drop-shadow">{{ item.count }}</span>
              </div>
            </div>
            <div class="w-12 text-right text-xs opacity-40 font-mono">{{ Math.round((item.count / data.stats.total_shops) * 100) }}%</div>
          </div>
        </div>
      </div>

      <!-- Recent registrations -->
      <div class="rounded-xl border p-5" style="background: var(--bg-secondary); border-color: var(--border-color)">
        <h3 class="font-semibold mb-4">Dernières inscriptions</h3>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr class="opacity-40 text-left">
                <th class="pb-2 font-medium">Nom</th>
                <th class="pb-2 font-medium">Email</th>
                <th class="pb-2 font-medium">Date</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="u in data.recent_registrations" :key="u.id" class="border-t" style="border-color: var(--border-color)">
                <td class="py-2 font-medium">{{ u.name }}</td>
                <td class="py-2 opacity-60">{{ u.email }}</td>
                <td class="py-2 opacity-40">{{ new Date(u.created_at).toLocaleDateString('fr-FR') }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </div>
</template>
