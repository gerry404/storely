<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuth } from '../../composables/useAuth'

const { api } = useAuth()

const zones = ref([])
const loading = ref(true)
const showModal = ref(false)
const editing = ref(null)
const submitting = ref(false)
const fieldErrors = ref({})
const showDeleteConfirm = ref(null)
const deleting = ref(false)
const togglingId = ref(null)

const defaultForm = () => ({
  name: '',
  description: '',
  price: '',
  estimated_hours: '',
  is_default: false,
  active: true,
  sort_order: 0,
})

const form = ref(defaultForm())

const formatPrice = (p) => {
  if (p === 0 || p === null || p === undefined) return 'Gratuit'
  return `${new Intl.NumberFormat('fr-FR').format(p)} F`
}

const formatDelay = (h) => {
  if (!h) return '—'
  if (h < 24) return `${h}h`
  const d = Math.round(h / 24)
  return `${d} jour${d > 1 ? 's' : ''}`
}

const sortedZones = computed(() => {
  return [...zones.value].sort((a, b) => (a.sort_order || 0) - (b.sort_order || 0) || a.id - b.id)
})

const fetchZones = async () => {
  loading.value = true
  try {
    const data = await api('/api/delivery-zones')
    zones.value = data || []
  } catch (e) {
    console.error('Failed to fetch delivery zones:', e)
    zones.value = []
  } finally {
    loading.value = false
  }
}

const openCreate = () => {
  editing.value = null
  form.value = defaultForm()
  fieldErrors.value = {}
  showModal.value = true
}

const openEdit = (zone) => {
  editing.value = zone
  form.value = {
    name: zone.name || '',
    description: zone.description || '',
    price: zone.price ?? '',
    estimated_hours: zone.estimated_hours ?? '',
    is_default: !!zone.is_default,
    active: !!zone.active,
    sort_order: zone.sort_order || 0,
  }
  fieldErrors.value = {}
  showModal.value = true
}

const submit = async () => {
  submitting.value = true
  fieldErrors.value = {}
  try {
    const payload = {
      name: form.value.name,
      description: form.value.description || null,
      price: parseInt(form.value.price || 0, 10),
      estimated_hours: form.value.estimated_hours ? parseInt(form.value.estimated_hours, 10) : null,
      is_default: form.value.is_default,
      active: form.value.active,
      sort_order: parseInt(form.value.sort_order || 0, 10),
    }
    if (editing.value) {
      await api(`/api/delivery-zones/${editing.value.id}`, { method: 'PUT', body: JSON.stringify(payload) })
    } else {
      await api('/api/delivery-zones', { method: 'POST', body: JSON.stringify(payload) })
    }
    showModal.value = false
    await fetchZones()
  } catch (e) {
    if (e.errors) fieldErrors.value = e.errors
    console.error('Submit error:', e)
  } finally {
    submitting.value = false
  }
}

const toggleActive = async (zone) => {
  togglingId.value = zone.id
  try {
    await api(`/api/delivery-zones/${zone.id}`, {
      method: 'PUT',
      body: JSON.stringify({ active: !zone.active }),
    })
    zone.active = !zone.active
  } catch (e) {
    console.error(e)
  } finally {
    togglingId.value = null
  }
}

const setDefault = async (zone) => {
  try {
    await api(`/api/delivery-zones/${zone.id}`, {
      method: 'PUT',
      body: JSON.stringify({ is_default: true }),
    })
    zones.value.forEach(z => { z.is_default = z.id === zone.id })
  } catch (e) {
    console.error(e)
  }
}

const confirmDelete = (zone) => { showDeleteConfirm.value = zone }
const cancelDelete = () => { showDeleteConfirm.value = null }

const doDelete = async () => {
  if (!showDeleteConfirm.value) return
  deleting.value = true
  try {
    await api(`/api/delivery-zones/${showDeleteConfirm.value.id}`, { method: 'DELETE' })
    showDeleteConfirm.value = null
    await fetchZones()
  } catch (e) {
    console.error(e)
  } finally {
    deleting.value = false
  }
}

onMounted(fetchZones)
</script>

<template>
  <div class="p-6 md:p-8 max-w-5xl">
    <!-- Header -->
    <div class="flex items-start justify-between gap-4 mb-8 flex-wrap">
      <div>
        <span class="eyebrow">Zones de livraison</span>
        <h1 class="display-lg mt-2">Configurez vos zones et frais</h1>
        <p class="text-sm mt-2 max-w-xl" style="color: var(--text-muted)">
          Définissez les quartiers / villes que vous livrez et leur prix. Vos clients choisiront leur zone au checkout et verront le tarif automatiquement.
        </p>
      </div>
      <button @click="openCreate" class="btn-primary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Ajouter une zone
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="space-y-3">
      <div v-for="n in 3" :key="n" class="card-flat p-5 animate-pulse">
        <div class="h-5 w-40 rounded mb-3" style="background: var(--bg-tertiary)"></div>
        <div class="h-4 w-64 rounded" style="background: var(--bg-tertiary)"></div>
      </div>
    </div>

    <!-- Empty -->
    <div v-else-if="zones.length === 0" class="card-flat p-10 text-center">
      <div class="w-14 h-14 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background: var(--bg-tertiary)">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-muted)"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
      </div>
      <h3 class="font-display font-bold text-lg mb-2" style="color: var(--text-primary)">Aucune zone configurée</h3>
      <p class="text-sm mb-6 max-w-sm mx-auto" style="color: var(--text-muted)">
        Ajoutez les zones que vous livrez (quartiers, villes) avec leur tarif. Exemples&nbsp;: Akwa 1&nbsp;000&nbsp;F, Bonabéri 2&nbsp;000&nbsp;F.
      </p>
      <button @click="openCreate" class="btn-primary">
        Créer ma première zone
      </button>
    </div>

    <!-- List -->
    <div v-else class="space-y-3">
      <div v-for="zone in sortedZones" :key="zone.id" class="card-flat p-5 transition-all" :style="{ opacity: zone.active ? 1 : 0.55 }">
        <div class="flex flex-col md:flex-row md:items-center gap-4">
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-1.5">
              <h3 class="font-display font-bold text-base truncate" style="color: var(--text-primary)">{{ zone.name }}</h3>
              <span v-if="zone.is_default" class="badge badge-mint">Par défaut</span>
              <span v-if="!zone.active" class="badge" style="background: var(--bg-tertiary); color: var(--text-muted)">Inactive</span>
            </div>
            <p v-if="zone.description" class="text-sm mb-2" style="color: var(--text-muted)">{{ zone.description }}</p>
            <div class="flex flex-wrap items-center gap-x-5 gap-y-1 text-xs" style="color: var(--text-muted)">
              <span class="flex items-center gap-1.5">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                <span class="font-display font-bold" style="color: var(--text-primary)">{{ formatPrice(zone.price) }}</span>
              </span>
              <span class="flex items-center gap-1.5">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                {{ formatDelay(zone.estimated_hours) }}
              </span>
            </div>
          </div>

          <div class="flex items-center gap-2 shrink-0">
            <button
              v-if="!zone.is_default"
              @click="setDefault(zone)"
              class="btn-ghost text-xs !py-1.5 !px-2.5"
              title="Définir comme zone par défaut"
            >Définir par défaut</button>

            <button
              type="button"
              @click="toggleActive(zone)"
              :disabled="togglingId === zone.id"
              class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors shrink-0"
              :style="zone.active ? { background: 'var(--color-brand)' } : { background: 'var(--bg-tertiary)' }"
            >
              <span
                class="inline-block h-4 w-4 rounded-full bg-white transition-transform"
                :style="{ transform: zone.active ? 'translateX(22px)' : 'translateX(4px)' }"
              />
            </button>

            <button @click="openEdit(zone)" class="btn-icon" title="Modifier">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </button>
            <button @click="confirmDelete(zone)" class="btn-icon" title="Supprimer" style="color: #EF4444">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <teleport to="body">
      <transition
        enter-active-class="transition-all duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-all duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-end md:items-center justify-center p-4">
          <div class="absolute inset-0" style="background: rgba(11,11,16,0.55)" @click="showModal = false" />
          <div class="relative w-full max-w-md rounded-t-3xl md:rounded-3xl p-6 md:p-7 max-h-[90vh] overflow-y-auto"
               style="background: var(--card-bg); border: 1px solid var(--card-border); box-shadow: var(--card-shadow-xl)">
            <div class="flex items-center justify-between mb-5">
              <h2 class="font-display font-bold text-xl" style="color: var(--text-primary)">
                {{ editing ? 'Modifier la zone' : 'Nouvelle zone' }}
              </h2>
              <button @click="showModal = false" class="btn-icon">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
              <div>
                <label class="label-input">Nom de la zone</label>
                <input v-model="form.name" type="text" required placeholder="Ex: Akwa, Bonabéri..." class="input" />
                <p v-if="fieldErrors.name" class="text-xs mt-1" style="color: #EF4444">{{ fieldErrors.name[0] }}</p>
              </div>

              <div>
                <label class="label-input">Description (optionnel)</label>
                <input v-model="form.description" type="text" placeholder="Ex: Uniquement avant 18h" class="input" />
              </div>

              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="label-input">Prix livraison (F)</label>
                  <input v-model="form.price" type="number" min="0" required placeholder="1000" class="input" />
                  <p v-if="fieldErrors.price" class="text-xs mt-1" style="color: #EF4444">{{ fieldErrors.price[0] }}</p>
                </div>
                <div>
                  <label class="label-input">Délai (heures)</label>
                  <input v-model="form.estimated_hours" type="number" min="1" placeholder="48" class="input" />
                </div>
              </div>

              <div class="flex items-center justify-between pt-1">
                <div>
                  <p class="text-sm font-display font-semibold" style="color: var(--text-primary)">Zone par défaut</p>
                  <p class="text-xs" style="color: var(--text-muted)">Sélectionnée automatiquement au checkout</p>
                </div>
                <button
                  type="button"
                  @click="form.is_default = !form.is_default"
                  class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                  :style="form.is_default ? { background: 'var(--color-brand)' } : { background: 'var(--bg-tertiary)' }"
                >
                  <span class="inline-block h-4 w-4 rounded-full bg-white transition-transform"
                        :style="{ transform: form.is_default ? 'translateX(22px)' : 'translateX(4px)' }" />
                </button>
              </div>

              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-display font-semibold" style="color: var(--text-primary)">Zone active</p>
                  <p class="text-xs" style="color: var(--text-muted)">Visible pour vos clients</p>
                </div>
                <button
                  type="button"
                  @click="form.active = !form.active"
                  class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                  :style="form.active ? { background: 'var(--color-brand)' } : { background: 'var(--bg-tertiary)' }"
                >
                  <span class="inline-block h-4 w-4 rounded-full bg-white transition-transform"
                        :style="{ transform: form.active ? 'translateX(22px)' : 'translateX(4px)' }" />
                </button>
              </div>

              <button type="submit" :disabled="submitting" class="btn-primary w-full justify-center mt-2" style="width: 100%">
                <svg v-if="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                {{ submitting ? 'Enregistrement...' : (editing ? 'Enregistrer' : 'Créer la zone') }}
              </button>
            </form>
          </div>
        </div>
      </transition>
    </teleport>

    <!-- Delete confirm -->
    <teleport to="body">
      <transition
        enter-active-class="transition-all duration-150"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-all duration-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="showDeleteConfirm" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
          <div class="absolute inset-0" style="background: rgba(11,11,16,0.7)" @click="cancelDelete" />
          <div class="relative w-full max-w-sm rounded-2xl p-6 text-center"
               style="background: var(--card-bg); border: 1px solid var(--card-border); box-shadow: var(--card-shadow-xl)">
            <div class="w-12 h-12 rounded-2xl mx-auto mb-4 flex items-center justify-center" style="background: rgba(239,68,68,0.1)">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" style="color: #EF4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
              </svg>
            </div>
            <h3 class="font-display font-bold text-lg mb-2" style="color: var(--text-primary)">Supprimer cette zone ?</h3>
            <p class="text-sm mb-5" style="color: var(--text-muted)">
              "<span style="color: var(--text-primary); font-weight: 600">{{ showDeleteConfirm.name }}</span>" sera supprimée. Les commandes existantes conservent leur frais.
            </p>
            <div class="flex gap-2.5">
              <button @click="cancelDelete" class="btn-secondary flex-1" style="flex: 1">Annuler</button>
              <button @click="doDelete" :disabled="deleting" class="flex-1 px-4 py-2.5 rounded-xl text-sm font-display font-bold"
                      style="flex: 1; background: rgba(239,68,68,0.12); color: #EF4444; border: 1px solid rgba(239,68,68,0.25)">
                {{ deleting ? 'Suppression...' : 'Supprimer' }}
              </button>
            </div>
          </div>
        </div>
      </transition>
    </teleport>
  </div>
</template>

<style scoped>
.label-input {
  display: block;
  font-size: 11px;
  font-family: var(--font-display);
  font-weight: 600;
  color: var(--text-muted);
  margin-bottom: 6px;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}
.input {
  width: 100%;
  padding: 0.75rem 1rem;
  border-radius: 12px;
  background: var(--bg-secondary);
  border: 1px solid var(--border-default);
  color: var(--text-primary);
  font-size: 0.875rem;
  transition: all 0.15s ease;
}
.input:focus {
  outline: none;
  border-color: var(--color-brand);
  box-shadow: 0 0 0 3px rgba(255,107,44,0.12);
}
</style>
