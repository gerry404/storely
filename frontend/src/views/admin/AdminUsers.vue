<script setup>
import { ref, onMounted, watch } from 'vue'
import { useAuth } from '../../composables/useAuth'

const { api } = useAuth()

const users = ref([])
const loading = ref(true)
const search = ref('')
const roleFilter = ref('')
const currentPage = ref(1)
const totalPages = ref(1)
const editingUser = ref(null)
const editRole = ref('')
const saving = ref(false)

const fetchUsers = async (page = 1) => {
  loading.value = true
  try {
    const params = new URLSearchParams({ page })
    if (search.value) params.set('q', search.value)
    if (roleFilter.value) params.set('role', roleFilter.value)
    const data = await api(`/api/admin/users?${params}`)
    users.value = data.data || []
    currentPage.value = data.current_page || 1
    totalPages.value = data.last_page || 1
  } catch { users.value = [] }
  loading.value = false
}

const openEdit = (user) => {
  editingUser.value = user
  editRole.value = user.role || 'user'
}

const saveRole = async () => {
  if (!editingUser.value) return
  saving.value = true
  try {
    await api(`/api/admin/users/${editingUser.value.id}`, {
      method: 'PUT',
      body: JSON.stringify({ role: editRole.value }),
    })
    editingUser.value.role = editRole.value
    editingUser.value = null
  } catch { /* */ }
  saving.value = false
}

const deleteUser = async (user) => {
  if (!confirm(`Supprimer ${user.name} ?`)) return
  try {
    await api(`/api/admin/users/${user.id}`, { method: 'DELETE' })
    users.value = users.value.filter(u => u.id !== user.id)
  } catch { /* */ }
}

const doSearch = () => { currentPage.value = 1; fetchUsers(1) }

onMounted(() => fetchUsers())
watch([roleFilter], () => doSearch())
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-bold">Utilisateurs</h1>
    </div>

    <div class="flex gap-3 mb-4 flex-wrap">
      <input v-model="search" @keyup.enter="doSearch" placeholder="Rechercher..." class="flex-1 min-w-[200px] px-4 py-2.5 rounded-lg border text-sm" style="background: var(--bg-secondary); border-color: var(--border-color); color: var(--text-primary)" />
      <select v-model="roleFilter" class="px-4 py-2.5 rounded-lg border text-sm" style="background: var(--bg-secondary); border-color: var(--border-color); color: var(--text-primary)">
        <option value="">Tous les rôles</option>
        <option value="user">User</option>
        <option value="admin">Admin</option>
        <option value="superadmin">Superadmin</option>
      </select>
    </div>

    <div class="rounded-xl border overflow-hidden" style="background: var(--bg-secondary); border-color: var(--border-color)">
      <div v-if="loading" class="p-8 text-center">
        <div class="w-6 h-6 border-2 border-red-500 border-t-transparent rounded-full animate-spin mx-auto" />
      </div>
      <table v-else class="w-full text-sm">
        <thead>
          <tr class="border-b text-left opacity-50" style="border-color: var(--border-color)">
            <th class="px-4 py-3 font-medium">Utilisateur</th>
            <th class="px-4 py-3 font-medium">Boutique</th>
            <th class="px-4 py-3 font-medium">Rôle</th>
            <th class="px-4 py-3 font-medium">Inscrit le</th>
            <th class="px-4 py-3 font-medium">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in users" :key="u.id" class="border-t" style="border-color: var(--border-color)">
            <td class="px-4 py-3">
              <div class="font-medium">{{ u.name }}</div>
              <div class="text-xs opacity-40">{{ u.email }}</div>
            </td>
            <td class="px-4 py-3">
              <span v-if="u.shop" class="text-xs">{{ u.shop.name }} <span class="opacity-40">({{ u.shop.plan || 'free' }})</span></span>
              <span v-else class="text-xs opacity-30">—</span>
            </td>
            <td class="px-4 py-3">
              <span class="px-2 py-0.5 rounded-full text-[10px] font-bold text-white" :style="{ background: u.role === 'superadmin' ? '#EF4444' : u.role === 'admin' ? '#F59E0B' : '#6B7280' }">
                {{ u.role || 'user' }}
              </span>
            </td>
            <td class="px-4 py-3 text-xs opacity-50">{{ new Date(u.created_at).toLocaleDateString('fr-FR') }}</td>
            <td class="px-4 py-3">
              <div class="flex gap-2">
                <button @click="openEdit(u)" class="text-xs text-blue-500 hover:underline">Modifier</button>
                <button v-if="u.role !== 'superadmin'" @click="deleteUser(u)" class="text-xs text-red-500 hover:underline">Supprimer</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="flex justify-center gap-2 mt-4">
      <button v-for="p in totalPages" :key="p" @click="fetchUsers(p)" class="w-8 h-8 rounded-lg text-xs font-medium" :class="p === currentPage ? 'bg-red-500 text-white' : 'opacity-50'" :style="p !== currentPage ? { background: 'var(--bg-secondary)' } : {}">{{ p }}</button>
    </div>

    <!-- Edit modal -->
    <div v-if="editingUser" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" @click.self="editingUser = null">
      <div class="rounded-xl border p-6 max-w-sm w-full" style="background: var(--bg-secondary); border-color: var(--border-color)">
        <h3 class="font-bold mb-4">Modifier {{ editingUser.name }}</h3>
        <label class="text-xs font-medium opacity-50 block mb-2">Rôle</label>
        <select v-model="editRole" class="w-full px-4 py-2.5 rounded-lg border text-sm mb-4" style="background: var(--bg-primary); border-color: var(--border-color); color: var(--text-primary)">
          <option value="user">User</option>
          <option value="admin">Admin</option>
          <option value="superadmin">Superadmin</option>
        </select>
        <div class="flex gap-3">
          <button @click="saveRole" :disabled="saving" class="flex-1 py-2.5 rounded-lg text-sm font-medium text-white bg-red-500 hover:bg-red-600 disabled:opacity-50">{{ saving ? '...' : 'Enregistrer' }}</button>
          <button @click="editingUser = null" class="flex-1 py-2.5 rounded-lg text-sm font-medium border" style="border-color: var(--border-color)">Annuler</button>
        </div>
      </div>
    </div>
  </div>
</template>
