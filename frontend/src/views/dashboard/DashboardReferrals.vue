<script setup>
import { ref, onMounted } from 'vue'
import { useAuth } from '../../composables/useAuth'

const { api } = useAuth()

const referralData = ref(null)
const loading = ref(true)
const copied = ref(false)
const applyCode = ref('')
const applyLoading = ref(false)
const applyMessage = ref('')
const applyError = ref(false)

const fetchReferrals = async () => {
  loading.value = true
  try {
    referralData.value = await api('/api/referrals')
  } catch {
    referralData.value = null
  }
  loading.value = false
}

const copyLink = () => {
  if (!referralData.value?.referral_link) return
  navigator.clipboard.writeText(referralData.value.referral_link)
  copied.value = true
  setTimeout(() => copied.value = false, 2000)
}

const copyCode = () => {
  if (!referralData.value?.referral_code) return
  navigator.clipboard.writeText(referralData.value.referral_code)
  copied.value = true
  setTimeout(() => copied.value = false, 2000)
}

const submitApply = async () => {
  if (!applyCode.value.trim()) return
  applyLoading.value = true
  applyMessage.value = ''
  applyError.value = false
  try {
    const data = await api('/api/referrals/apply', {
      method: 'POST',
      body: JSON.stringify({ referral_code: applyCode.value.trim().toUpperCase() }),
    })
    applyMessage.value = data.message
    applyCode.value = ''
  } catch (e) {
    applyMessage.value = e.message || 'Erreur lors de l\'application du code'
    applyError.value = true
  }
  applyLoading.value = false
}

const statusLabel = (status) => {
  const map = {
    pending: 'En attente',
    qualified: 'Qualifié',
    rewarded: 'Récompensé',
  }
  return map[status] || status
}

const statusColor = (status) => {
  const map = {
    pending: '#F59E0B',
    qualified: '#3B82F6',
    rewarded: '#10B981',
  }
  return map[status] || '#6B7280'
}

onMounted(fetchReferrals)
</script>

<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold">Programme de parrainage</h1>
    <p class="opacity-60 text-sm">Invitez des commerçants et gagnez 1 mois gratuit chacun pour chaque inscription</p>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="w-8 h-8 border-2 border-orange-500 border-t-transparent rounded-full animate-spin" />
    </div>

    <template v-else-if="referralData">
      <!-- Share card -->
      <div class="rounded-xl border p-6 relative overflow-hidden" style="background: var(--bg-secondary); border-color: var(--border-color)">
        <div class="absolute inset-0 opacity-[0.03]" style="background: radial-gradient(circle at 80% 20%, #f97316, transparent 60%)" />
        <div class="relative">
          <div class="flex flex-col sm:flex-row gap-6">
            <!-- Code -->
            <div class="flex-1">
              <label class="text-xs font-medium opacity-50 uppercase tracking-wider">Votre code</label>
              <div class="mt-2 flex items-center gap-2">
                <div class="flex-1 px-4 py-3 rounded-lg border font-mono text-xl font-bold tracking-[0.3em] text-center" style="background: var(--bg-primary); border-color: var(--border-color)">
                  {{ referralData.referral_code }}
                </div>
                <button
                  @click="copyCode"
                  class="px-4 py-3 rounded-lg border transition-all hover:opacity-80"
                  style="border-color: var(--border-color); background: var(--bg-primary)"
                >
                  <svg v-if="!copied" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3" /></svg>
                  <svg v-else class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                </button>
              </div>
            </div>

            <!-- Link -->
            <div class="flex-1">
              <label class="text-xs font-medium opacity-50 uppercase tracking-wider">Lien d'invitation</label>
              <div class="mt-2 flex items-center gap-2">
                <div class="flex-1 px-4 py-3 rounded-lg border text-sm truncate opacity-70" style="background: var(--bg-primary); border-color: var(--border-color)">
                  {{ referralData.referral_link }}
                </div>
                <button
                  @click="copyLink"
                  class="px-4 py-3 rounded-lg text-white font-medium transition-all hover:scale-[1.02]"
                  style="background: linear-gradient(135deg, #f97316, #ea580c)"
                >
                  Copier
                </button>
              </div>
            </div>
          </div>

          <!-- Share buttons -->
          <div class="flex items-center gap-3 mt-4">
            <span class="text-xs opacity-50">Partager via :</span>
            <a
              :href="`https://wa.me/?text=Rejoins%20Storely%20avec%20mon%20code%20${referralData.referral_code}%20et%20on%20gagne%20chacun%201%20mois%20gratuit%20!%20${encodeURIComponent(referralData.referral_link)}`"
              target="_blank"
              class="p-2 rounded-lg transition-colors hover:opacity-80"
              style="background: #25D366; color: white"
            >
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
            </a>
            <a
              :href="`https://t.me/share/url?url=${encodeURIComponent(referralData.referral_link)}&text=Rejoins%20Storely%20!%20Code%20${referralData.referral_code}`"
              target="_blank"
              class="p-2 rounded-lg transition-colors hover:opacity-80"
              style="background: #0088cc; color: white"
            >
              <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 000 12a12 12 0 0012 12 12 12 0 0012-12A12 12 0 0012 0a12 12 0 00-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 01.171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.479.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>
            </a>
          </div>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="rounded-xl border p-4 text-center" style="background: var(--bg-secondary); border-color: var(--border-color)">
          <div class="text-2xl font-bold">{{ referralData.stats.total }}</div>
          <div class="text-xs opacity-50 mt-1">Invitations</div>
        </div>
        <div class="rounded-xl border p-4 text-center" style="background: var(--bg-secondary); border-color: var(--border-color)">
          <div class="text-2xl font-bold text-blue-500">{{ referralData.stats.qualified }}</div>
          <div class="text-xs opacity-50 mt-1">Qualifiés</div>
        </div>
        <div class="rounded-xl border p-4 text-center" style="background: var(--bg-secondary); border-color: var(--border-color)">
          <div class="text-2xl font-bold text-green-500">{{ referralData.stats.rewarded }}</div>
          <div class="text-xs opacity-50 mt-1">Récompensés</div>
        </div>
        <div class="rounded-xl border p-4 text-center" style="background: var(--bg-secondary); border-color: var(--border-color)">
          <div class="text-2xl font-bold text-orange-500">{{ referralData.stats.total_reward_months }}</div>
          <div class="text-xs opacity-50 mt-1">Mois gagnés</div>
        </div>
      </div>

      <!-- Apply a code -->
      <div class="rounded-xl border p-6" style="background: var(--bg-secondary); border-color: var(--border-color)">
        <h3 class="font-semibold mb-3">Vous avez un code de parrainage ?</h3>
        <form @submit.prevent="submitApply" class="flex gap-2">
          <input
            v-model="applyCode"
            type="text"
            placeholder="Entrez le code (8 caractères)"
            maxlength="8"
            class="flex-1 px-4 py-2.5 rounded-lg border text-sm font-mono uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-orange-500/30"
            style="background: var(--bg-primary); border-color: var(--border-color); color: var(--text-primary)"
          />
          <button
            type="submit"
            :disabled="applyLoading || applyCode.length < 8"
            class="px-5 py-2.5 rounded-lg text-sm font-medium text-white transition-all hover:scale-[1.02] disabled:opacity-50"
            style="background: linear-gradient(135deg, #8b5cf6, #7c3aed)"
          >
            {{ applyLoading ? '...' : 'Appliquer' }}
          </button>
        </form>
        <p v-if="applyMessage" class="text-sm mt-2" :class="applyError ? 'text-red-500' : 'text-green-500'">
          {{ applyMessage }}
        </p>
      </div>

      <!-- Referral list -->
      <div v-if="referralData.referrals?.length" class="rounded-xl border overflow-hidden" style="background: var(--bg-secondary); border-color: var(--border-color)">
        <div class="p-4 border-b font-semibold" style="border-color: var(--border-color)">
          Historique des parrainages
        </div>
        <div class="divide-y" style="--tw-divide-opacity: 1; border-color: var(--border-color)">
          <div
            v-for="ref in referralData.referrals"
            :key="ref.id"
            class="flex items-center gap-4 p-4"
          >
            <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold text-white" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9)">
              {{ ref.referred?.name?.[0] || '?' }}
            </div>
            <div class="flex-1 min-w-0">
              <div class="font-medium text-sm truncate">{{ ref.referred?.name || 'Utilisateur' }}</div>
              <div class="text-xs opacity-40">{{ ref.referred?.email }}</div>
            </div>
            <span
              class="px-2.5 py-1 text-xs font-medium rounded-full text-white"
              :style="{ background: statusColor(ref.status) }"
            >
              {{ statusLabel(ref.status) }}
            </span>
          </div>
        </div>
      </div>

      <!-- How it works -->
      <div class="rounded-xl border p-6" style="background: var(--bg-secondary); border-color: var(--border-color)">
        <h3 class="font-semibold mb-4">Comment ça marche ?</h3>
        <div class="grid sm:grid-cols-3 gap-6">
          <div class="text-center">
            <div class="w-12 h-12 rounded-full mx-auto mb-3 flex items-center justify-center text-white font-bold" style="background: linear-gradient(135deg, #f97316, #ea580c)">1</div>
            <div class="text-sm font-medium">Partagez votre code</div>
            <div class="text-xs opacity-50 mt-1">Envoyez votre code ou lien d'invitation à un commerçant</div>
          </div>
          <div class="text-center">
            <div class="w-12 h-12 rounded-full mx-auto mb-3 flex items-center justify-center text-white font-bold" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed)">2</div>
            <div class="text-sm font-medium">Il s'inscrit</div>
            <div class="text-xs opacity-50 mt-1">Votre filleul crée son compte avec votre code</div>
          </div>
          <div class="text-center">
            <div class="w-12 h-12 rounded-full mx-auto mb-3 flex items-center justify-center text-white font-bold" style="background: linear-gradient(135deg, #10B981, #059669)">3</div>
            <div class="text-sm font-medium">Vous gagnez tous les deux</div>
            <div class="text-xs opacity-50 mt-1">1 mois gratuit chacun dès son premier abonnement</div>
          </div>
        </div>
      </div>
    </template>
  </div>
</template>
