<script setup>
import { ref, onMounted, computed } from 'vue'
import { useAuth } from '../../composables/useAuth'
const { api } = useAuth()

const badges = ref([])
const loading = ref(true)
const awardForm = ref({ shop_id: '', badge_id: '' })
const awardMsg = ref('')
const awardError = ref(false)
const awarding = ref(false)
const shopSearch = ref('')
const shopResults = ref([])
const searchingShop = ref(false)
const selectedShop = ref(null)

const fetchBadges = async () => {
  loading.value = true
  try { badges.value = await api('/api/admin/badges') } catch { badges.value = [] }
  loading.value = false
}

const searchShop = async () => {
  if (!shopSearch.value || shopSearch.value.length < 2) { shopResults.value = []; return }
  searchingShop.value = true
  try {
    const data = await api(`/api/admin/shops?q=${encodeURIComponent(shopSearch.value)}`)
    shopResults.value = (data.data || []).slice(0, 6)
  } catch { shopResults.value = [] }
  searchingShop.value = false
}

const selectShop = (shop) => {
  selectedShop.value = shop
  awardForm.value.shop_id = shop.id
  shopSearch.value = ''
  shopResults.value = []
}

const awardBadge = async () => {
  if (!awardForm.value.shop_id || !awardForm.value.badge_id) return
  awardMsg.value = ''
  awardError.value = false
  awarding.value = true
  try {
    const data = await api('/api/admin/badges/award', {
      method: 'POST',
      body: JSON.stringify({ shop_id: Number(awardForm.value.shop_id), badge_id: Number(awardForm.value.badge_id) }),
    })
    awardMsg.value = data.message
    selectedShop.value = null
    awardForm.value = { shop_id: '', badge_id: '' }
    fetchBadges()
  } catch (e) {
    awardMsg.value = e.message
    awardError.value = true
  }
  awarding.value = false
}

const badgeIcons = {
  verified: '<path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
  star: '<path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>',
  lightning: '<path d="M13 10V3L4 14h7v7l9-11h-7z"/>',
  heart: '<path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>',
  rocket: '<path d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.58-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>',
  shield: '<path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
  download: '<path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>',
}

const criteriaLabels = {
  manual: 'Attribution manuelle',
  orders_count: 'Nombre de commandes',
  reviews_avg: 'Note moyenne',
  monthly_orders: 'Commandes / 30 jours',
  account_age_months: 'Ancienneté (mois)',
  digital_sales_count: 'Ventes digitales',
  response_time: 'Temps de réponse (min)',
}

onMounted(fetchBadges)
</script>

<template>
  <div>
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-2xl font-bold">Badges</h1>
        <p class="text-sm opacity-50 mt-1">Gérez les badges de confiance attribués aux boutiques</p>
      </div>
      <div class="text-sm opacity-40">{{ badges.length }} badges</div>
    </div>

    <!-- Award section -->
    <div class="rounded-2xl border p-6 mb-8 relative" style="background: var(--bg-secondary); border-color: var(--border-color)">
      <div class="absolute top-0 right-0 w-40 h-40 opacity-[0.03]" style="background: radial-gradient(circle, #EF4444, transparent 70%)" />
      <div class="relative">
        <h3 class="font-bold text-lg mb-1">Attribuer un badge</h3>
        <p class="text-xs opacity-40 mb-5">Sélectionnez une boutique et un badge à lui attribuer manuellement</p>

        <div class="flex gap-4 flex-wrap items-end">
          <!-- Shop selector -->
          <div class="flex-1 min-w-[220px] relative">
            <label class="text-[11px] font-semibold uppercase tracking-wider opacity-40 block mb-1.5">Boutique</label>
            <div v-if="selectedShop" class="flex items-center gap-2 px-4 py-2.5 rounded-xl border" style="background: var(--bg-primary); border-color: var(--border-color)">
              <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0" style="background: linear-gradient(135deg, #f97316, #8b5cf6)">{{ selectedShop.name?.[0] }}</div>
              <span class="text-sm font-medium truncate">{{ selectedShop.name }}</span>
              <span class="text-[10px] opacity-30 ml-auto">#{{ selectedShop.id }}</span>
              <button @click="selectedShop = null; awardForm.shop_id = ''" class="ml-1 opacity-40 hover:opacity-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
            <div v-else>
              <input
                v-model="shopSearch"
                @input="searchShop"
                placeholder="Rechercher une boutique..."
                class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 transition-all"
                style="background: var(--bg-primary); border-color: var(--border-color); color: var(--text-primary)"
              />
              <!-- Dropdown -->
              <div v-if="shopResults.length" class="absolute left-0 right-0 top-full mt-1 rounded-xl border shadow-xl z-50 overflow-hidden" style="background: var(--bg-secondary); border-color: var(--border-color)">
                <div
                  v-for="s in shopResults"
                  :key="s.id"
                  @click="selectShop(s)"
                  class="flex items-center gap-2 px-4 py-2.5 cursor-pointer transition-colors hover:brightness-95"
                >
                  <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-[10px] font-bold" style="background: linear-gradient(135deg, #f97316, #8b5cf6)">{{ s.name?.[0] }}</div>
                  <div class="min-w-0 flex-1">
                    <div class="text-sm font-medium truncate">{{ s.name }}</div>
                    <div class="text-[10px] opacity-30">{{ s.slug }} · {{ s.plan || 'free' }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Badge selector -->
          <div class="flex-1 min-w-[220px]">
            <label class="text-[11px] font-semibold uppercase tracking-wider opacity-40 block mb-1.5">Badge</label>
            <select
              v-model="awardForm.badge_id"
              class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 transition-all"
              style="background: var(--bg-primary); border-color: var(--border-color); color: var(--text-primary)"
            >
              <option value="">Sélectionner un badge</option>
              <option v-for="b in badges" :key="b.id" :value="b.id">{{ b.name }} — {{ b.name_en }}</option>
            </select>
          </div>

          <button
            @click="awardBadge"
            :disabled="!awardForm.shop_id || !awardForm.badge_id || awarding"
            class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:scale-[1.02] disabled:opacity-40 disabled:hover:scale-100"
            style="background: linear-gradient(135deg, #EF4444, #DC2626)"
          >
            {{ awarding ? 'Attribution...' : 'Attribuer' }}
          </button>
        </div>

        <transition name="fade">
          <p v-if="awardMsg" class="text-sm mt-3 px-3 py-2 rounded-lg inline-block" :class="awardError ? 'bg-red-500/10 text-red-400' : 'bg-green-500/10 text-green-400'">
            {{ awardMsg }}
          </p>
        </transition>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex items-center justify-center py-16">
      <div class="w-8 h-8 border-2 border-red-500 border-t-transparent rounded-full animate-spin" />
    </div>

    <!-- Badges grid -->
    <div v-else class="grid gap-5 md:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="b in badges"
        :key="b.id"
        class="group relative rounded-2xl border overflow-hidden transition-all hover:scale-[1.01] hover:shadow-lg"
        style="background: var(--bg-secondary); border-color: var(--border-color)"
      >
        <!-- Colored accent bar -->
        <div class="h-1" :style="{ background: `linear-gradient(90deg, ${b.color}, ${b.color}80)` }" />

        <div class="p-5">
          <!-- Header -->
          <div class="flex items-start gap-4 mb-4">
            <!-- Badge icon circle with glow -->
            <div class="relative">
              <div
                class="absolute inset-0 rounded-2xl blur-xl opacity-20 transition-opacity group-hover:opacity-40"
                :style="{ background: b.color }"
              />
              <div
                class="relative w-14 h-14 rounded-2xl flex items-center justify-center"
                :style="{ background: `linear-gradient(135deg, ${b.color}, ${b.color}CC)`, boxShadow: `0 4px 14px ${b.color}30` }"
              >
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" v-html="badgeIcons[b.icon] || badgeIcons.star" />
              </div>
            </div>
            <div class="flex-1 min-w-0">
              <h3 class="font-bold text-base">{{ b.name }}</h3>
              <p class="text-xs opacity-40 mt-0.5">{{ b.name_en }}</p>
            </div>
            <!-- Count pill -->
            <div
              class="px-3 py-1 rounded-full text-xs font-bold flex items-center gap-1"
              :style="{ background: `${b.color}15`, color: b.color }"
            >
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
              {{ b.shops_count || 0 }}
            </div>
          </div>

          <!-- Description -->
          <p class="text-sm opacity-60 mb-4 leading-relaxed">{{ b.description }}</p>

          <!-- Criteria tag -->
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <div class="px-2.5 py-1 rounded-lg text-[11px] font-medium" style="background: var(--bg-primary)">
                <span class="opacity-40">Critère :</span>
                <span class="ml-1 font-semibold">{{ criteriaLabels[b.criteria_type] || b.criteria_type }}</span>
              </div>
              <div v-if="b.criteria_type !== 'manual'" class="px-2.5 py-1 rounded-lg text-[11px] font-bold" style="background: var(--bg-primary)">
                {{ b.criteria_value }}{{ b.criteria_type === 'reviews_avg' ? '/50' : '' }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: all 0.3s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-4px); }
</style>
