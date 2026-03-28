<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuth } from '../../composables/useAuth'

const { api } = useAuth()

const promos = ref([])
const loading = ref(true)
const showForm = ref(false)
const editing = ref(null)
const saving = ref(false)
const msg = ref('')
const msgError = ref(false)

const defaultForm = () => ({
  name: '',
  description: '',
  type: 'auto_register',
  plan: 'pro',
  duration_days: 30,
  max_uses: 50,
  promo_code: '',
  active: true,
  starts_at: '',
  expires_at: '',
})

const form = ref(defaultForm())

const fetchPromos = async () => {
  loading.value = true
  try { promos.value = await api('/api/admin/promotions') } catch { promos.value = [] }
  loading.value = false
}

const openCreate = () => {
  editing.value = null
  form.value = defaultForm()
  showForm.value = true
}

const openEdit = (p) => {
  editing.value = p
  form.value = {
    name: p.name,
    description: p.description || '',
    type: p.type,
    plan: p.plan,
    duration_days: p.duration_days,
    max_uses: p.max_uses || '',
    promo_code: p.promo_code || '',
    active: p.active,
    starts_at: p.starts_at ? p.starts_at.slice(0, 10) : '',
    expires_at: p.expires_at ? p.expires_at.slice(0, 10) : '',
  }
  showForm.value = true
}

const save = async () => {
  saving.value = true
  msg.value = ''
  msgError.value = false
  try {
    const body = { ...form.value }
    if (!body.max_uses) body.max_uses = null
    if (!body.promo_code) body.promo_code = null
    if (!body.starts_at) body.starts_at = null
    if (!body.expires_at) body.expires_at = null

    if (editing.value) {
      await api(`/api/admin/promotions/${editing.value.id}`, {
        method: 'PUT',
        body: JSON.stringify(body),
      })
      msg.value = 'Promotion mise à jour.'
    } else {
      await api('/api/admin/promotions', {
        method: 'POST',
        body: JSON.stringify(body),
      })
      msg.value = 'Promotion créée.'
    }
    showForm.value = false
    fetchPromos()
  } catch (e) {
    msg.value = e.message
    msgError.value = true
  }
  saving.value = false
}

const deletePromo = async (p) => {
  if (!confirm(`Supprimer la promotion "${p.name}" ?`)) return
  try {
    await api(`/api/admin/promotions/${p.id}`, { method: 'DELETE' })
    fetchPromos()
  } catch {}
}

const toggleActive = async (p) => {
  try {
    await api(`/api/admin/promotions/${p.id}`, {
      method: 'PUT',
      body: JSON.stringify({ active: !p.active }),
    })
    fetchPromos()
  } catch {}
}

const typeLabels = { auto_register: 'Auto inscription', manual: 'Manuel', code: 'Code promo' }
const planColors = { starter: '#F59E0B', pro: '#EF4444' }

const totalUsed = computed(() => promos.value.reduce((s, p) => s + p.used_count, 0))
const activeCount = computed(() => promos.value.filter(p => p.is_available).length)

onMounted(fetchPromos)
</script>

<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-2xl font-bold">Promotions</h1>
        <p class="text-sm opacity-50 mt-1">Gérez les offres promotionnelles de la plateforme</p>
      </div>
      <button
        @click="openCreate"
        class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:scale-[1.02]"
        style="background: linear-gradient(135deg, #EF4444, #DC2626)"
      >
        + Nouvelle promo
      </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4 mb-8">
      <div class="rounded-xl border p-4" style="background: var(--bg-secondary); border-color: var(--border-color)">
        <div class="text-2xl font-bold">{{ promos.length }}</div>
        <div class="text-xs opacity-50">Promotions</div>
      </div>
      <div class="rounded-xl border p-4" style="background: var(--bg-secondary); border-color: var(--border-color)">
        <div class="text-2xl font-bold text-green-500">{{ activeCount }}</div>
        <div class="text-xs opacity-50">Actives</div>
      </div>
      <div class="rounded-xl border p-4" style="background: var(--bg-secondary); border-color: var(--border-color)">
        <div class="text-2xl font-bold text-blue-500">{{ totalUsed }}</div>
        <div class="text-xs opacity-50">Utilisations totales</div>
      </div>
    </div>

    <!-- Message -->
    <transition name="fade">
      <div v-if="msg" class="mb-4 px-4 py-3 rounded-xl text-sm" :class="msgError ? 'bg-red-500/10 text-red-400' : 'bg-green-500/10 text-green-400'">
        {{ msg }}
      </div>
    </transition>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-16">
      <div class="w-8 h-8 border-2 border-red-500 border-t-transparent rounded-full animate-spin" />
    </div>

    <!-- Promos list -->
    <div v-else class="space-y-4">
      <div v-if="!promos.length" class="text-center py-16 opacity-40">
        <p class="text-lg mb-2">Aucune promotion</p>
        <p class="text-sm">Créez votre première offre promotionnelle</p>
      </div>

      <div
        v-for="p in promos"
        :key="p.id"
        class="rounded-2xl border overflow-hidden transition-all"
        style="background: var(--bg-secondary); border-color: var(--border-color)"
      >
        <!-- Accent bar -->
        <div class="h-1" :style="{ background: p.is_available ? 'linear-gradient(90deg, #10B981, #34D399)' : 'linear-gradient(90deg, #6B7280, #9CA3AF)' }" />

        <div class="p-5">
          <div class="flex items-start justify-between gap-4">
            <!-- Left: info -->
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-2.5 mb-1">
                <h3 class="font-bold text-base truncate">{{ p.name }}</h3>
                <!-- Status -->
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                  :class="p.is_available ? 'bg-green-500/10 text-green-400' : 'bg-gray-500/10 text-gray-400'"
                >
                  {{ p.is_available ? 'Active' : 'Inactive' }}
                </span>
              </div>
              <p v-if="p.description" class="text-sm opacity-50 mb-3">{{ p.description }}</p>

              <!-- Tags -->
              <div class="flex flex-wrap gap-2">
                <!-- Type -->
                <span class="px-2.5 py-1 rounded-lg text-[11px] font-medium" style="background: var(--bg-primary)">
                  <span class="opacity-40">Type :</span>
                  <span class="ml-1 font-semibold">{{ typeLabels[p.type] || p.type }}</span>
                </span>
                <!-- Plan -->
                <span
                  class="px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase"
                  :style="{ background: planColors[p.plan] + '15', color: planColors[p.plan] }"
                >
                  {{ p.plan }}
                </span>
                <!-- Duration -->
                <span class="px-2.5 py-1 rounded-lg text-[11px] font-medium" style="background: var(--bg-primary)">
                  {{ p.duration_days }} jours
                </span>
                <!-- Code -->
                <span v-if="p.promo_code" class="px-2.5 py-1 rounded-lg text-[11px] font-mono font-bold bg-purple-500/10 text-purple-400">
                  {{ p.promo_code }}
                </span>
              </div>
            </div>

            <!-- Right: progress + actions -->
            <div class="flex flex-col items-end gap-3 shrink-0">
              <!-- Usage counter -->
              <div class="text-right">
                <div class="text-lg font-bold">
                  {{ p.used_count }}<span v-if="p.max_uses" class="text-sm font-normal opacity-40"> / {{ p.max_uses }}</span>
                </div>
                <div class="text-[10px] opacity-40 uppercase tracking-wider">Utilisations</div>
              </div>

              <!-- Progress bar -->
              <div v-if="p.max_uses" class="w-28 h-2 rounded-full overflow-hidden" style="background: var(--bg-primary)">
                <div
                  class="h-full rounded-full transition-all"
                  :style="{
                    width: `${Math.min(100, (p.used_count / p.max_uses) * 100)}%`,
                    background: p.used_count >= p.max_uses
                      ? '#EF4444'
                      : p.used_count >= p.max_uses * 0.8
                        ? '#F59E0B'
                        : '#10B981'
                  }"
                />
              </div>

              <!-- Actions -->
              <div class="flex gap-1.5">
                <button
                  @click="toggleActive(p)"
                  class="p-1.5 rounded-lg transition-colors"
                  :class="p.active ? 'hover:bg-amber-500/10 text-amber-500' : 'hover:bg-green-500/10 text-green-500'"
                  :title="p.active ? 'Désactiver' : 'Activer'"
                >
                  <svg v-if="p.active" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </button>
                <button @click="openEdit(p)" class="p-1.5 rounded-lg hover:bg-blue-500/10 text-blue-500 transition-colors" title="Modifier">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                </button>
                <button @click="deletePromo(p)" class="p-1.5 rounded-lg hover:bg-red-500/10 text-red-500 transition-colors" title="Supprimer">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                </button>
              </div>
            </div>
          </div>

          <!-- Dates -->
          <div v-if="p.starts_at || p.expires_at" class="flex gap-4 mt-3 pt-3 border-t text-[11px] opacity-40" style="border-color: var(--border-color)">
            <span v-if="p.starts_at">Début : {{ new Date(p.starts_at).toLocaleDateString('fr-FR') }}</span>
            <span v-if="p.expires_at">Fin : {{ new Date(p.expires_at).toLocaleDateString('fr-FR') }}</span>
            <span v-if="p.creator" class="ml-auto">Par {{ p.creator.name }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal form -->
    <Teleport to="body">
      <transition name="modal">
        <div v-if="showForm" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showForm = false" />
          <div class="relative w-full max-w-lg rounded-2xl overflow-hidden max-h-[90vh] overflow-y-auto" style="background: var(--bg-secondary)">
            <!-- Header -->
            <div class="sticky top-0 z-10 flex items-center justify-between p-5 border-b" style="background: var(--bg-secondary); border-color: var(--border-color)">
              <h3 class="font-bold text-lg">{{ editing ? 'Modifier la promo' : 'Nouvelle promotion' }}</h3>
              <button @click="showForm = false" class="w-8 h-8 rounded-lg flex items-center justify-center opacity-40 hover:opacity-100 hover:bg-white/5 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </div>

            <form @submit.prevent="save" class="p-5 space-y-4">
              <!-- Name -->
              <div>
                <label class="text-[11px] font-semibold uppercase tracking-wider opacity-40 block mb-1.5">Nom de la promotion *</label>
                <input v-model="form.name" required class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20" style="background: var(--bg-primary); border-color: var(--border-color); color: var(--text-primary)" placeholder="1 mois Pro offert — 50 premiers" />
              </div>

              <!-- Description -->
              <div>
                <label class="text-[11px] font-semibold uppercase tracking-wider opacity-40 block mb-1.5">Description</label>
                <textarea v-model="form.description" rows="2" class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 resize-none" style="background: var(--bg-primary); border-color: var(--border-color); color: var(--text-primary)" placeholder="Les 50 premiers inscrits bénéficient d'un mois Pro gratuit" />
              </div>

              <!-- Type + Plan -->
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="text-[11px] font-semibold uppercase tracking-wider opacity-40 block mb-1.5">Type *</label>
                  <select v-model="form.type" class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none" style="background: var(--bg-primary); border-color: var(--border-color); color: var(--text-primary)">
                    <option value="auto_register">Auto inscription</option>
                    <option value="code">Code promo</option>
                    <option value="manual">Manuel</option>
                  </select>
                </div>
                <div>
                  <label class="text-[11px] font-semibold uppercase tracking-wider opacity-40 block mb-1.5">Plan offert *</label>
                  <select v-model="form.plan" class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none" style="background: var(--bg-primary); border-color: var(--border-color); color: var(--text-primary)">
                    <option value="starter">Starter</option>
                    <option value="pro">Pro</option>
                  </select>
                </div>
              </div>

              <!-- Duration + Max uses -->
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="text-[11px] font-semibold uppercase tracking-wider opacity-40 block mb-1.5">Durée (jours) *</label>
                  <input v-model.number="form.duration_days" type="number" min="1" max="365" required class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none" style="background: var(--bg-primary); border-color: var(--border-color); color: var(--text-primary)" />
                </div>
                <div>
                  <label class="text-[11px] font-semibold uppercase tracking-wider opacity-40 block mb-1.5">Limite utilisations</label>
                  <input v-model.number="form.max_uses" type="number" min="1" class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none" style="background: var(--bg-primary); border-color: var(--border-color); color: var(--text-primary)" placeholder="Illimité" />
                </div>
              </div>

              <!-- Promo code (if type = code) -->
              <div v-if="form.type === 'code'">
                <label class="text-[11px] font-semibold uppercase tracking-wider opacity-40 block mb-1.5">Code promo</label>
                <input v-model="form.promo_code" class="w-full px-4 py-2.5 rounded-xl border text-sm font-mono uppercase focus:outline-none" style="background: var(--bg-primary); border-color: var(--border-color); color: var(--text-primary)" placeholder="LAUNCH50" />
              </div>

              <!-- Dates -->
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="text-[11px] font-semibold uppercase tracking-wider opacity-40 block mb-1.5">Date début</label>
                  <input v-model="form.starts_at" type="date" class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none" style="background: var(--bg-primary); border-color: var(--border-color); color: var(--text-primary)" />
                </div>
                <div>
                  <label class="text-[11px] font-semibold uppercase tracking-wider opacity-40 block mb-1.5">Date fin</label>
                  <input v-model="form.expires_at" type="date" class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none" style="background: var(--bg-primary); border-color: var(--border-color); color: var(--text-primary)" />
                </div>
              </div>

              <!-- Active toggle -->
              <div class="flex items-center gap-3">
                <button
                  type="button"
                  @click="form.active = !form.active"
                  class="relative w-11 h-6 rounded-full transition-colors"
                  :class="form.active ? 'bg-green-500' : 'bg-gray-600'"
                >
                  <div
                    class="absolute top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform"
                    :class="form.active ? 'translate-x-5.5' : 'translate-x-0.5'"
                  />
                </button>
                <span class="text-sm">{{ form.active ? 'Active' : 'Inactive' }}</span>
              </div>

              <!-- Info box for auto_register -->
              <div v-if="form.type === 'auto_register'" class="p-3 rounded-xl bg-blue-500/10 border border-blue-500/20 text-xs text-blue-400 leading-relaxed">
                <strong>Auto inscription :</strong> Cette promo s'applique automatiquement à chaque nouvel inscrit. Si vous définissez une limite de {{ form.max_uses || '∞' }} utilisations, seuls les {{ form.max_uses || '' }} premiers inscrits en bénéficient.
              </div>

              <!-- Submit -->
              <button
                type="submit"
                :disabled="saving || !form.name"
                class="w-full py-3 rounded-xl text-sm font-bold text-white transition-all hover:scale-[1.01] disabled:opacity-40"
                style="background: linear-gradient(135deg, #EF4444, #DC2626)"
              >
                {{ saving ? 'Enregistrement...' : (editing ? 'Mettre à jour' : 'Créer la promotion') }}
              </button>
            </form>
          </div>
        </div>
      </transition>
    </Teleport>
  </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: all 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-4px); }
.modal-enter-active { transition: all 0.3s ease; }
.modal-leave-active { transition: all 0.2s ease; }
.modal-enter-from { opacity: 0; }
.modal-enter-from > div:last-child { transform: translateY(20px) scale(0.95); }
.modal-leave-to { opacity: 0; }
</style>
