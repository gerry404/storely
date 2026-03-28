<script setup>
import { ref, onMounted } from 'vue'
import { useAuth } from '../../composables/useAuth'
const { api } = useAuth()

const settings = ref([])
const loading = ref(true)
const newKey = ref('')
const newValue = ref('')
const newType = ref('string')
const saving = ref(false)

const fetchSettings = async () => {
  loading.value = true
  try { settings.value = await api('/api/admin/settings') } catch { settings.value = [] }
  loading.value = false
}

const saveSetting = async (key, value, type) => {
  saving.value = true
  try {
    await api('/api/admin/settings', {
      method: 'POST',
      body: JSON.stringify({ key, value, type }),
    })
    await fetchSettings()
  } catch { /* */ }
  saving.value = false
}

const addSetting = async () => {
  if (!newKey.value) return
  await saveSetting(newKey.value, newValue.value, newType.value)
  newKey.value = ''
  newValue.value = ''
}

onMounted(fetchSettings)
</script>

<template>
  <div>
    <h1 class="text-2xl font-bold mb-6">Paramètres plateforme</h1>

    <!-- Add new -->
    <div class="rounded-xl border p-5 mb-6" style="background: var(--bg-secondary); border-color: var(--border-color)">
      <h3 class="font-semibold mb-3">Ajouter un paramètre</h3>
      <div class="flex gap-3 flex-wrap">
        <input v-model="newKey" placeholder="Clé" class="px-4 py-2.5 rounded-lg border text-sm flex-1 min-w-[150px]" style="background: var(--bg-primary); border-color: var(--border-color); color: var(--text-primary)" />
        <input v-model="newValue" placeholder="Valeur" class="px-4 py-2.5 rounded-lg border text-sm flex-1 min-w-[150px]" style="background: var(--bg-primary); border-color: var(--border-color); color: var(--text-primary)" />
        <select v-model="newType" class="px-4 py-2.5 rounded-lg border text-sm" style="background: var(--bg-primary); border-color: var(--border-color); color: var(--text-primary)">
          <option value="string">String</option>
          <option value="boolean">Boolean</option>
          <option value="integer">Integer</option>
          <option value="json">JSON</option>
        </select>
        <button @click="addSetting" :disabled="saving" class="px-5 py-2.5 rounded-lg text-sm font-medium text-white bg-red-500 hover:bg-red-600 disabled:opacity-50">Ajouter</button>
      </div>
    </div>

    <!-- Existing settings -->
    <div class="rounded-xl border overflow-hidden" style="background: var(--bg-secondary); border-color: var(--border-color)">
      <div v-if="loading" class="p-8 text-center"><div class="w-6 h-6 border-2 border-red-500 border-t-transparent rounded-full animate-spin mx-auto" /></div>
      <div v-else-if="!settings.length" class="p-8 text-center text-sm opacity-40">Aucun paramètre configuré</div>
      <div v-else class="divide-y" style="border-color: var(--border-color)">
        <div v-for="s in settings" :key="s.id" class="flex items-center gap-4 p-4">
          <div class="flex-1 min-w-0">
            <div class="font-mono text-sm font-medium">{{ s.key }}</div>
            <div class="text-xs opacity-40">Type: {{ s.type }}</div>
          </div>
          <input
            :value="s.value"
            @change="saveSetting(s.key, $event.target.value, s.type)"
            class="px-3 py-2 rounded-lg border text-sm w-48"
            style="background: var(--bg-primary); border-color: var(--border-color); color: var(--text-primary)"
          />
        </div>
      </div>
    </div>
  </div>
</template>
