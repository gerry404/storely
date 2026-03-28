<script setup>
import { ref, onMounted } from 'vue'
import { useAuth } from '../../composables/useAuth'
const { api } = useAuth()

const logs = ref([])
const loading = ref(true)
const actionFilter = ref('')
const currentPage = ref(1)
const totalPages = ref(1)

const fetchLogs = async (page = 1) => {
  loading.value = true
  try {
    const params = new URLSearchParams({ page })
    if (actionFilter.value) params.set('action', actionFilter.value)
    const data = await api(`/api/admin/logs?${params}`)
    logs.value = data.data || []
    currentPage.value = data.current_page || 1
    totalPages.value = data.last_page || 1
  } catch { logs.value = [] }
  loading.value = false
}

onMounted(() => fetchLogs())
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Logs d'activité</h1>
    <div class="mb-4">
      <select v-model="actionFilter" @change="fetchLogs(1)" class="px-4 py-2.5 rounded-lg border text-sm" style="background: var(--bg-secondary); border-color: var(--border-color); color: var(--text-primary)">
        <option value="">Toutes les actions</option>
        <option value="update_user_role">Changement rôle</option>
        <option value="update_shop">Modification boutique</option>
        <option value="delete_shop">Suppression boutique</option>
        <option value="delete_user">Suppression utilisateur</option>
        <option value="award_badge">Attribution badge</option>
        <option value="revoke_badge">Retrait badge</option>
        <option value="reward_referral">Récompense parrainage</option>
        <option value="update_setting">Modification paramètre</option>
      </select>
    </div>
    <div class="rounded-xl border overflow-x-auto" style="background: var(--bg-secondary); border-color: var(--border-color)">
      <div v-if="loading" class="p-8 text-center"><div class="w-6 h-6 border-2 border-red-500 border-t-transparent rounded-full animate-spin mx-auto" /></div>
      <table v-else class="w-full text-sm">
        <thead><tr class="border-b text-left opacity-50" style="border-color: var(--border-color)">
          <th class="px-4 py-3 font-medium">Admin</th>
          <th class="px-4 py-3 font-medium">Action</th>
          <th class="px-4 py-3 font-medium">Cible</th>
          <th class="px-4 py-3 font-medium">Détails</th>
          <th class="px-4 py-3 font-medium">Date</th>
        </tr></thead>
        <tbody>
          <tr v-for="l in logs" :key="l.id" class="border-t" style="border-color: var(--border-color)">
            <td class="px-4 py-3 text-xs font-medium">{{ l.admin?.name || '—' }}</td>
            <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-white/10">{{ l.action }}</span></td>
            <td class="px-4 py-3 text-xs opacity-50">{{ l.target_type }} #{{ l.target_id }}</td>
            <td class="px-4 py-3 text-xs opacity-40 max-w-[200px] truncate">{{ l.details ? JSON.stringify(l.details) : '—' }}</td>
            <td class="px-4 py-3 text-xs opacity-50">{{ new Date(l.created_at).toLocaleString('fr-FR') }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="totalPages > 1" class="flex justify-center gap-2 mt-4">
      <button v-for="p in totalPages" :key="p" @click="fetchLogs(p)" class="w-8 h-8 rounded-lg text-xs font-medium" :class="p === currentPage ? 'bg-red-500 text-white' : 'opacity-50'" :style="p !== currentPage ? { background: 'var(--bg-secondary)' } : {}">{{ p }}</button>
    </div>
  </div>
</template>
