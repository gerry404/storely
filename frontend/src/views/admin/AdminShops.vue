<script setup>
import { ref, onMounted, watch } from 'vue'
import { useAuth } from '../../composables/useAuth'

const { api } = useAuth()

const shops = ref([])
const loading = ref(true)
const search = ref('')
const planFilter = ref('')
const currentPage = ref(1)
const totalPages = ref(1)
const editingShop = ref(null)
const editForm = ref({})
const saving = ref(false)

const fetchShops = async (page = 1) => {
  loading.value = true
  try {
    const params = new URLSearchParams({ page })
    if (search.value) params.set('q', search.value)
    if (planFilter.value) params.set('plan', planFilter.value)
    const data = await api(`/api/admin/shops?${params}`)
    shops.value = data.data || []
    currentPage.value = data.current_page || 1
    totalPages.value = data.last_page || 1
  } catch { shops.value = [] }
  loading.value = false
}

const openEdit = (shop) => {
  editingShop.value = shop
  editForm.value = {
    plan: shop.plan || 'free',
    active: shop.active,
    verified: shop.verified,
    featured_at: shop.featured_at ? shop.featured_at.split('T')[0] : '',
  }
}

const saveShop = async () => {
  if (!editingShop.value) return
  saving.value = true
  try {
    const payload = { ...editForm.value }
    if (!payload.featured_at) payload.featured_at = null
    const updated = await api(`/api/admin/shops/${editingShop.value.id}`, {
      method: 'PUT',
      body: JSON.stringify(payload),
    })
    Object.assign(editingShop.value, updated)
    editingShop.value = null
  } catch { /* */ }
  saving.value = false
}

const deleteShop = async (shop) => {
  if (!confirm(`Supprimer la boutique "${shop.name}" ?`)) return
  try {
    await api(`/api/admin/shops/${shop.id}`, { method: 'DELETE' })
    shops.value = shops.value.filter(s => s.id !== shop.id)
  } catch { /* */ }
}

const doSearch = () => { currentPage.value = 1; fetchShops(1) }

onMounted(() => fetchShops())
watch([planFilter], () => doSearch())
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Boutiques</h1>

    <div class="flex gap-3 mb-4 flex-wrap">
      <input v-model="search" @keyup.enter="doSearch" placeholder="Rechercher..." class="flex-1 min-w-[200px] px-4 py-2.5 rounded-lg border text-sm" style="background: var(--bg-secondary); border-color: var(--border-color); color: var(--text-primary)" />
      <select v-model="planFilter" class="px-4 py-2.5 rounded-lg border text-sm" style="background: var(--bg-secondary); border-color: var(--border-color); color: var(--text-primary)">
        <option value="">Tous les plans</option>
        <option value="free">Free</option>
        <option value="starter">Starter</option>
        <option value="pro">Pro</option>
      </select>
    </div>

    <div class="rounded-xl border overflow-x-auto" style="background: var(--bg-secondary); border-color: var(--border-color)">
      <div v-if="loading" class="p-8 text-center">
        <div class="w-6 h-6 border-2 border-red-500 border-t-transparent rounded-full animate-spin mx-auto" />
      </div>
      <table v-else class="w-full text-sm">
        <thead>
          <tr class="border-b text-left opacity-50" style="border-color: var(--border-color)">
            <th class="px-4 py-3 font-medium">Boutique</th>
            <th class="px-4 py-3 font-medium">Propriétaire</th>
            <th class="px-4 py-3 font-medium">Plan</th>
            <th class="px-4 py-3 font-medium">Produits</th>
            <th class="px-4 py-3 font-medium">Ventes</th>
            <th class="px-4 py-3 font-medium">Note</th>
            <th class="px-4 py-3 font-medium">Statut</th>
            <th class="px-4 py-3 font-medium">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in shops" :key="s.id" class="border-t" style="border-color: var(--border-color)">
            <td class="px-4 py-3">
              <div class="font-medium">{{ s.name }}</div>
              <div class="text-xs opacity-40">{{ s.slug }}</div>
            </td>
            <td class="px-4 py-3 text-xs">{{ s.user?.name || '—' }}</td>
            <td class="px-4 py-3">
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold text-white" :style="{ background: s.plan === 'pro' ? '#EF4444' : s.plan === 'starter' ? '#F59E0B' : '#6B7280' }">
                {{ s.plan || 'free' }}
              </span>
            </td>
            <td class="px-4 py-3 text-center">{{ s.products_count }}</td>
            <td class="px-4 py-3 text-center">{{ s.orders_count }}</td>
            <td class="px-4 py-3 text-center">
              <span v-if="s.reviews_avg_rating" class="text-amber-500">{{ parseFloat(s.reviews_avg_rating).toFixed(1) }}</span>
              <span v-else class="opacity-30">—</span>
            </td>
            <td class="px-4 py-3">
              <span class="w-2 h-2 rounded-full inline-block mr-1" :class="s.active ? 'bg-green-500' : 'bg-red-500'" />
              <span class="text-xs">{{ s.active ? 'Active' : 'Inactive' }}</span>
            </td>
            <td class="px-4 py-3">
              <div class="flex gap-2">
                <button @click="openEdit(s)" class="text-xs text-blue-500 hover:underline">Modifier</button>
                <button @click="deleteShop(s)" class="text-xs text-red-500 hover:underline">Supprimer</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="totalPages > 1" class="flex justify-center gap-2 mt-4">
      <button v-for="p in totalPages" :key="p" @click="fetchShops(p)" class="w-8 h-8 rounded-lg text-xs font-medium" :class="p === currentPage ? 'bg-red-500 text-white' : 'opacity-50'" :style="p !== currentPage ? { background: 'var(--bg-secondary)' } : {}">{{ p }}</button>
    </div>

    <!-- Edit modal -->
    <div v-if="editingShop" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="editingShop = null">
      <div class="rounded-xl border p-6 max-w-md w-full" style="background: var(--bg-secondary); border-color: var(--border-color)">
        <h3 class="font-bold mb-4">Modifier "{{ editingShop.name }}"</h3>
        <div class="space-y-4">
          <div>
            <label class="text-xs font-medium opacity-50 block mb-1">Plan</label>
            <select v-model="editForm.plan" class="w-full px-4 py-2.5 rounded-lg border text-sm" style="background: var(--bg-primary); border-color: var(--border-color); color: var(--text-primary)">
              <option value="free">Free</option>
              <option value="starter">Starter</option>
              <option value="pro">Pro</option>
            </select>
          </div>
          <div class="flex gap-6">
            <label class="flex items-center gap-2 text-sm cursor-pointer">
              <input type="checkbox" v-model="editForm.active" class="accent-red-500" /> Active
            </label>
            <label class="flex items-center gap-2 text-sm cursor-pointer">
              <input type="checkbox" v-model="editForm.verified" class="accent-blue-500" /> Vérifiée
            </label>
          </div>
          <div>
            <label class="text-xs font-medium opacity-50 block mb-1">Mettre en vedette (date)</label>
            <input type="date" v-model="editForm.featured_at" class="w-full px-4 py-2.5 rounded-lg border text-sm" style="background: var(--bg-primary); border-color: var(--border-color); color: var(--text-primary)" />
          </div>
        </div>
        <div class="flex gap-3 mt-6">
          <button @click="saveShop" :disabled="saving" class="flex-1 py-2.5 rounded-lg text-sm font-medium text-white bg-red-500 hover:bg-red-600 disabled:opacity-50">{{ saving ? '...' : 'Enregistrer' }}</button>
          <button @click="editingShop = null" class="flex-1 py-2.5 rounded-lg text-sm font-medium border" style="border-color: var(--border-color)">Annuler</button>
        </div>
      </div>
    </div>
  </div>
</template>
