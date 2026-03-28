<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useAuth } from '../../composables/useAuth'
import { storageUrl } from '../../composables/useStorage'

const { shop, api, fetchUser } = useAuth()

const activeTab = ref('colors')
const saving = ref(false)
const saved = ref(false)
const errorMessage = ref('')
const bannerUploading = ref(false)
const bannerPreview = ref(null)
const showPreview = ref(false)

const tabs = [
  { id: 'colors', label: 'Couleurs', icon: 'palette' },
  { id: 'banner', label: 'Bannière', icon: 'image' },
  { id: 'layout', label: 'Mise en page', icon: 'grid' },
  { id: 'social', label: 'Réseaux', icon: 'share' },
  { id: 'text', label: 'Texte', icon: 'type' },
  { id: 'advanced', label: 'Avancé', icon: 'code' },
]

const form = ref({
  primaryColor: '#FF6B2C',
  backgroundStyle: 'dark',
  bannerStyle: 'gradient',
  bannerHeight: 'normal',
  gridColumns: 3,
  cardStyle: 'detailed',
  showDescription: true,
  showRating: true,
  showHours: true,
  showLocation: true,
  instagram: '',
  facebook: '',
  tiktok: '',
  twitter: '',
  welcomeMessage: '',
  announcementText: '',
  announcementEnabled: false,
  customCss: '',
  analyticsId: '',
})

const backgroundOptions = [
  { value: 'dark', label: 'Sombre' },
  { value: 'light', label: 'Clair' },
  { value: 'custom', label: 'Dégradé' },
]

const bannerHeightOptions = [
  { value: 'compact', label: 'Compact', h: 'h-6' },
  { value: 'normal', label: 'Normal', h: 'h-10' },
  { value: 'tall', label: 'Grand', h: 'h-14' },
]

const cardStyleOptions = [
  { value: 'minimal', label: 'Minimal', desc: 'Image + prix' },
  { value: 'detailed', label: 'Détaillé', desc: 'Titre, description, prix' },
  { value: 'large', label: 'Grand visuel', desc: 'Images plein format' },
]

const previewBg = computed(() => {
  if (form.value.backgroundStyle === 'light') return '#F8F9FA'
  if (form.value.backgroundStyle === 'custom') return `linear-gradient(135deg, #1a1a2e, ${form.value.primaryColor}22)`
  return '#0A0A0F'
})

const previewText = computed(() => form.value.backgroundStyle === 'light' ? '#1a1a2e' : '#fff')
const previewMuted = computed(() => form.value.backgroundStyle === 'light' ? '#666' : 'rgba(255,255,255,0.5)')

const socialCount = computed(() => {
  return [form.value.instagram, form.value.facebook, form.value.tiktok, form.value.twitter].filter(Boolean).length
})

function loadFromShop() {
  if (shop.value?.customization) {
    try {
      const data = typeof shop.value.customization === 'string'
        ? JSON.parse(shop.value.customization)
        : shop.value.customization
      Object.keys(form.value).forEach((key) => {
        if (data[key] !== undefined) form.value[key] = data[key]
      })
      if (data.bannerUrl) bannerPreview.value = data.bannerUrl
    } catch { /* use defaults */ }
  }
  const rawBanner = shop.value?.banner_url || shop.value?.banner
  if (rawBanner) bannerPreview.value = storageUrl(rawBanner)
}

onMounted(loadFromShop)
watch(shop, loadFromShop)

const save = async () => {
  saving.value = true
  errorMessage.value = ''
  try {
    await api('/api/shop', {
      method: 'PUT',
      body: JSON.stringify({ customization: JSON.stringify(form.value) }),
    })
    await fetchUser()
    saved.value = true
    setTimeout(() => saved.value = false, 3000)
  } catch (err) {
    errorMessage.value = err.message || 'Erreur lors de la sauvegarde.'
  } finally {
    saving.value = false
  }
}

const uploadBanner = async (event) => {
  const file = event.target.files[0]
  if (!file) return
  bannerUploading.value = true
  errorMessage.value = ''
  try {
    const fd = new FormData()
    fd.append('banner', file)
    const token = localStorage.getItem('storely-token')
    const res = await fetch('/api/shop/banner', {
      method: 'POST',
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
      body: fd,
    })
    const data = await res.json()
    if (res.ok) bannerPreview.value = storageUrl(data.banner_url || data.banner)
    else errorMessage.value = data.message || "Erreur lors de l'upload."
  } catch {
    errorMessage.value = "Erreur lors de l'upload de la bannière."
  } finally {
    bannerUploading.value = false
  }
}
</script>

<template>
  <div class="p-4 sm:p-6 md:p-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6 sm:mb-8">
      <div>
        <h1 class="font-display font-bold text-xl sm:text-2xl" style="color: var(--text-primary)">Personnalisation</h1>
        <p class="text-sm mt-1" style="color: var(--text-muted)">Personnalisez l'apparence de votre vitrine</p>
      </div>
      <button
        @click="showPreview = !showPreview"
        class="hidden lg:flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium transition-all"
        :class="showPreview ? 'bg-brand/10 text-brand border border-brand/20' : 'hover:bg-white/5 border border-transparent'"
        :style="!showPreview ? { color: 'var(--text-muted)' } : {}"
      >
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        {{ showPreview ? 'Masquer l\'aperçu' : 'Aperçu en direct' }}
      </button>
    </div>

    <div class="flex gap-6">
      <!-- Main content -->
      <div class="flex-1 min-w-0" :class="showPreview ? 'max-w-2xl' : 'max-w-4xl'">

        <!-- Tab Bar -->
        <div class="mb-6 -mx-4 px-4 sm:mx-0 sm:px-0">
          <div class="flex gap-1 overflow-x-auto pb-2 no-scrollbar">
            <button
              v-for="tab in tabs"
              :key="tab.id"
              @click="activeTab = tab.id"
              class="flex items-center gap-2 px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl text-xs sm:text-sm font-display font-medium whitespace-nowrap transition-all shrink-0"
              :class="activeTab === tab.id
                ? 'bg-brand/15 text-brand border border-brand/30'
                : 'border border-transparent hover:bg-white/5'"
              :style="activeTab !== tab.id ? { color: 'var(--text-muted)' } : {}"
            >
              <svg v-if="tab.icon === 'palette'" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="10.5" r="2.5" opacity=".6"/><circle cx="8.5" cy="7.5" r="2.5" opacity=".8"/><circle cx="6.5" cy="12" r="2.5" opacity=".5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.9 0 1.5-.7 1.5-1.5 0-.4-.1-.7-.4-1-.3-.3-.4-.7-.4-1.1 0-.8.7-1.5 1.5-1.5H16c3.3 0 6-2.7 6-6 0-5.5-4.5-9.9-10-9.9z"/></svg>
              <svg v-if="tab.icon === 'image'" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
              <svg v-if="tab.icon === 'grid'" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
              <svg v-if="tab.icon === 'share'" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
              <svg v-if="tab.icon === 'type'" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>
              <svg v-if="tab.icon === 'code'" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
              <span class="hidden sm:inline">{{ tab.label }}</span>
            </button>
          </div>
        </div>

        <!-- ======================== COULEURS ======================== -->
        <div v-show="activeTab === 'colors'" class="space-y-5">
          <div class="glass-card rounded-2xl p-5 sm:p-6">
            <h2 class="font-display font-bold text-sm mb-1" style="color: var(--text-primary)">Couleur principale</h2>
            <p class="text-xs mb-5" style="color: var(--text-muted)">Boutons, liens et accents de votre vitrine.</p>

            <div class="flex items-center gap-3 mb-5">
              <label class="relative w-11 h-11 rounded-xl overflow-hidden cursor-pointer shrink-0 ring-2 ring-white/10 hover:ring-white/20 transition-all">
                <input type="color" v-model="form.primaryColor" class="absolute inset-0 w-full h-full cursor-pointer opacity-0" />
                <div class="w-full h-full" :style="{ background: form.primaryColor }"></div>
              </label>
              <input type="text" v-model="form.primaryColor" class="px-3 py-2.5 rounded-xl text-sm font-mono w-28 t-input" />
              <div class="flex-1 h-10 rounded-xl transition-colors hidden sm:block" :style="{ background: `linear-gradient(135deg, ${form.primaryColor}, ${form.primaryColor}66)` }"></div>
            </div>

            <!-- Quick colors -->
            <div class="flex flex-wrap gap-2">
              <button
                v-for="c in ['#FF6B2C', '#6C5CE7', '#2DD4A8', '#FF4D6A', '#FFAA33', '#3B82F6', '#EC4899', '#14B8A6']"
                :key="c"
                @click="form.primaryColor = c"
                class="w-8 h-8 rounded-lg transition-all hover:scale-110"
                :class="form.primaryColor === c ? 'ring-2 ring-white ring-offset-2 ring-offset-[var(--bg-primary)]' : ''"
                :style="{ background: c }"
              />
            </div>
          </div>

          <div class="glass-card rounded-2xl p-5 sm:p-6">
            <h2 class="font-display font-bold text-sm mb-1" style="color: var(--text-primary)">Ambiance</h2>
            <p class="text-xs mb-5" style="color: var(--text-muted)">Style de fond de votre vitrine.</p>

            <div class="grid grid-cols-3 gap-3">
              <button
                v-for="opt in backgroundOptions"
                :key="opt.value"
                @click="form.backgroundStyle = opt.value"
                class="p-3 sm:p-4 rounded-xl border-2 transition-all text-center"
                :class="form.backgroundStyle === opt.value ? 'border-brand bg-brand/5' : 'border-white/10 hover:border-white/20'"
              >
                <div
                  class="w-full h-10 sm:h-12 rounded-lg mb-2"
                  :class="{
                    'bg-gradient-to-br from-[#0A0A0F] to-[#1A1A2E]': opt.value === 'dark',
                    'bg-gradient-to-br from-gray-100 to-white': opt.value === 'light',
                    'bg-gradient-to-br from-purple-900 via-indigo-900 to-slate-900': opt.value === 'custom',
                  }"
                ></div>
                <span class="text-xs sm:text-sm font-display font-medium" style="color: var(--text-primary)">{{ opt.label }}</span>
              </button>
            </div>
          </div>
        </div>

        <!-- ======================== BANNIERE ======================== -->
        <div v-show="activeTab === 'banner'" class="space-y-5">
          <div class="glass-card rounded-2xl p-5 sm:p-6">
            <h2 class="font-display font-bold text-sm mb-1" style="color: var(--text-primary)">Image de bannière</h2>
            <p class="text-xs mb-5" style="color: var(--text-muted)">En-tête de votre vitrine. Format 1200x400px recommandé.</p>

            <div
              class="relative w-full rounded-xl overflow-hidden border transition-all"
              :class="{
                'h-20': form.bannerHeight === 'compact',
                'h-36': form.bannerHeight === 'normal',
                'h-52': form.bannerHeight === 'tall',
              }"
              style="border-color: var(--border-default)"
            >
              <img v-if="bannerPreview" :src="bannerPreview" class="w-full h-full object-cover" alt="Bannière" />
              <div
                v-else
                class="w-full h-full flex items-center justify-center"
                :style="form.bannerStyle === 'gradient' ? { background: `linear-gradient(135deg, ${form.primaryColor}33, ${form.primaryColor}11)` } : {}"
                :class="form.bannerStyle !== 'gradient' ? 'bg-white/5' : ''"
              >
                <div class="text-center">
                  <svg class="mx-auto mb-1" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="color: var(--text-faint)"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                  <p class="text-xs" style="color: var(--text-faint)">Aucune bannière</p>
                </div>
              </div>

              <label class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 hover:opacity-100 transition-opacity cursor-pointer">
                <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-white/20 backdrop-blur text-white text-sm font-medium">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                  {{ bannerUploading ? 'Envoi...' : 'Importer' }}
                </div>
                <input type="file" accept="image/*" class="hidden" @change="uploadBanner" :disabled="bannerUploading" />
              </label>
            </div>

            <!-- Banner options -->
            <div class="grid grid-cols-2 gap-4 mt-5">
              <div>
                <label class="block text-xs font-display font-medium mb-2 uppercase tracking-wider" style="color: var(--text-faint)">Type</label>
                <div class="flex gap-2">
                  <button
                    v-for="s in [{v:'image',l:'Image'},{v:'gradient',l:'Dégradé'}]"
                    :key="s.v"
                    @click="form.bannerStyle = s.v"
                    class="flex-1 py-2 rounded-lg border text-xs font-medium transition-all text-center"
                    :class="form.bannerStyle === s.v ? 'border-brand bg-brand/5 text-brand' : 'border-white/10 hover:border-white/20'"
                    :style="form.bannerStyle !== s.v ? { color: 'var(--text-muted)' } : {}"
                  >{{ s.l }}</button>
                </div>
              </div>
              <div>
                <label class="block text-xs font-display font-medium mb-2 uppercase tracking-wider" style="color: var(--text-faint)">Hauteur</label>
                <div class="flex gap-2">
                  <button
                    v-for="opt in bannerHeightOptions"
                    :key="opt.value"
                    @click="form.bannerHeight = opt.value"
                    class="flex-1 py-2 rounded-lg border text-xs font-medium transition-all text-center"
                    :class="form.bannerHeight === opt.value ? 'border-brand bg-brand/5 text-brand' : 'border-white/10 hover:border-white/20'"
                    :style="form.bannerHeight !== opt.value ? { color: 'var(--text-muted)' } : {}"
                  >{{ opt.label }}</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ======================== MISE EN PAGE ======================== -->
        <div v-show="activeTab === 'layout'" class="space-y-5">
          <div class="glass-card rounded-2xl p-5 sm:p-6">
            <h2 class="font-display font-bold text-sm mb-1" style="color: var(--text-primary)">Grille produits</h2>
            <p class="text-xs mb-5" style="color: var(--text-muted)">Produits par ligne sur votre vitrine.</p>

            <div class="grid grid-cols-3 gap-3">
              <button
                v-for="cols in [2, 3, 4]"
                :key="cols"
                @click="form.gridColumns = cols"
                class="p-3 sm:p-4 rounded-xl border-2 transition-all"
                :class="form.gridColumns === cols ? 'border-brand bg-brand/5' : 'border-white/10 hover:border-white/20'"
              >
                <div class="grid gap-1" :style="{ gridTemplateColumns: `repeat(${cols}, 1fr)` }">
                  <div v-for="n in cols * 2" :key="n" class="aspect-square rounded bg-white/10"></div>
                </div>
                <span class="block text-xs sm:text-sm font-display font-medium mt-2" style="color: var(--text-primary)">{{ cols }} col.</span>
              </button>
            </div>
          </div>

          <div class="glass-card rounded-2xl p-5 sm:p-6">
            <h2 class="font-display font-bold text-sm mb-1" style="color: var(--text-primary)">Style des fiches</h2>
            <p class="text-xs mb-5" style="color: var(--text-muted)">Présentation de vos produits.</p>

            <div class="grid grid-cols-3 gap-3">
              <button
                v-for="s in cardStyleOptions"
                :key="s.value"
                @click="form.cardStyle = s.value"
                class="p-3 sm:p-4 rounded-xl border-2 transition-all text-left"
                :class="form.cardStyle === s.value ? 'border-brand bg-brand/5' : 'border-white/10 hover:border-white/20'"
              >
                <div v-if="s.value === 'minimal'" class="space-y-1 mb-2"><div class="aspect-video rounded bg-white/10"></div><div class="h-1.5 rounded bg-white/10 w-10"></div></div>
                <div v-if="s.value === 'detailed'" class="space-y-1 mb-2"><div class="aspect-video rounded bg-white/10"></div><div class="h-1.5 rounded bg-white/10 w-14"></div><div class="h-1 rounded bg-white/5 w-full"></div><div class="h-1.5 rounded bg-white/10 w-8"></div></div>
                <div v-if="s.value === 'large'" class="space-y-1 mb-2"><div class="aspect-[3/4] rounded bg-white/10"></div><div class="h-1.5 rounded bg-white/10 w-10"></div></div>
                <span class="text-xs font-medium block" style="color: var(--text-primary)">{{ s.label }}</span>
              </button>
            </div>
          </div>

          <div class="glass-card rounded-2xl p-5 sm:p-6">
            <h2 class="font-display font-bold text-sm mb-1" style="color: var(--text-primary)">Éléments visibles</h2>
            <p class="text-xs mb-5" style="color: var(--text-muted)">Informations affichées sur votre vitrine.</p>

            <div class="space-y-2">
              <label
                v-for="item in [
                  { model: 'showDescription', label: 'Description', sub: 'Description des produits' },
                  { model: 'showRating', label: 'Évaluations', sub: 'Notes et avis' },
                  { model: 'showHours', label: 'Horaires', sub: 'Heures d\'ouverture' },
                  { model: 'showLocation', label: 'Localisation', sub: 'Adresse' },
                ]"
                :key="item.model"
                class="flex items-center justify-between p-3 rounded-xl hover:bg-white/[0.02] transition-all cursor-pointer"
                style="border: 1px solid var(--border-default)"
              >
                <div>
                  <span class="text-sm font-medium" style="color: var(--text-primary)">{{ item.label }}</span>
                  <span class="block text-xs" style="color: var(--text-faint)">{{ item.sub }}</span>
                </div>
                <div class="relative shrink-0 ml-3">
                  <input type="checkbox" v-model="form[item.model]" class="sr-only peer" />
                  <div class="w-10 h-[22px] rounded-full bg-white/10 peer-checked:bg-brand/60 transition-colors"></div>
                  <div class="absolute left-0.5 top-[1px] w-5 h-5 rounded-full bg-white shadow-sm transition-transform peer-checked:translate-x-[18px]"></div>
                </div>
              </label>
            </div>
          </div>
        </div>

        <!-- ======================== RESEAUX SOCIAUX ======================== -->
        <div v-show="activeTab === 'social'" class="space-y-5">
          <div class="glass-card rounded-2xl p-5 sm:p-6">
            <h2 class="font-display font-bold text-sm mb-1" style="color: var(--text-primary)">Réseaux sociaux</h2>
            <p class="text-xs mb-5" style="color: var(--text-muted)">Liens affichés sur votre vitrine. {{ socialCount > 0 ? `${socialCount} réseau(x) configuré(s).` : '' }}</p>

            <div class="space-y-4">
              <div v-for="net in [
                { model: 'instagram', label: 'Instagram', placeholder: 'https://instagram.com/...', iconPath: 'M2 2h20v20H2zM16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37zM17.5 6.5h.01', rect: true },
                { model: 'facebook', label: 'Facebook', placeholder: 'https://facebook.com/...', iconPath: 'M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z' },
                { model: 'tiktok', label: 'TikTok', placeholder: 'https://tiktok.com/@...', iconPath: 'M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5' },
                { model: 'twitter', label: 'X (Twitter)', placeholder: 'https://x.com/...', iconPath: 'M4 4l11.7 16h4.3M4 20L20 4' },
              ]" :key="net.model">
                <label class="block text-xs font-display font-medium mb-1.5 uppercase tracking-wider" style="color: var(--text-faint)">{{ net.label }}</label>
                <div class="flex items-center gap-0">
                  <span class="flex items-center justify-center w-10 h-10 rounded-l-xl shrink-0" style="background: var(--bg-primary); border: 1px solid var(--border-default); border-right: 0">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--text-muted)">
                      <rect v-if="net.rect" x="2" y="2" width="20" height="20" rx="5" ry="5"/>
                      <path :d="net.iconPath"/>
                    </svg>
                  </span>
                  <input
                    v-model="form[net.model]"
                    type="url"
                    :placeholder="net.placeholder"
                    class="flex-1 px-3 py-2.5 rounded-r-xl text-sm t-input !rounded-l-none"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ======================== TEXTE ======================== -->
        <div v-show="activeTab === 'text'" class="space-y-5">
          <div class="glass-card rounded-2xl p-5 sm:p-6">
            <h2 class="font-display font-bold text-sm mb-1" style="color: var(--text-primary)">Message de bienvenue</h2>
            <p class="text-xs mb-4" style="color: var(--text-muted)">Affiché sous la description de votre boutique.</p>
            <textarea
              v-model="form.welcomeMessage"
              rows="3"
              placeholder="Bienvenue ! Découvrez nos créations uniques..."
              class="t-input w-full resize-none"
            ></textarea>
          </div>

          <div class="glass-card rounded-2xl p-5 sm:p-6">
            <div class="flex items-center justify-between mb-4">
              <div>
                <h2 class="font-display font-bold text-sm" style="color: var(--text-primary)">Barre d'annonce</h2>
                <p class="text-xs mt-0.5" style="color: var(--text-muted)">Bandeau promotionnel en haut de la vitrine.</p>
              </div>
              <div class="relative shrink-0 ml-3">
                <input type="checkbox" v-model="form.announcementEnabled" class="sr-only peer" />
                <div class="w-10 h-[22px] rounded-full bg-white/10 peer-checked:bg-brand/60 transition-colors cursor-pointer" @click="form.announcementEnabled = !form.announcementEnabled"></div>
                <div class="absolute left-0.5 top-[1px] w-5 h-5 rounded-full bg-white shadow-sm transition-transform peer-checked:translate-x-[18px] pointer-events-none"></div>
              </div>
            </div>

            <input
              v-model="form.announcementText"
              type="text"
              placeholder="Livraison gratuite dès 10 000 F CFA !"
              :disabled="!form.announcementEnabled"
              class="t-input w-full disabled:opacity-40 disabled:cursor-not-allowed"
            />

            <Transition name="slide">
              <div v-if="form.announcementEnabled && form.announcementText" class="mt-4">
                <p class="text-[10px] font-display font-medium uppercase tracking-wider mb-1.5" style="color: var(--text-faint)">Aperçu</p>
                <div class="w-full py-2 px-4 rounded-lg text-center text-xs font-medium text-white" :style="{ backgroundColor: form.primaryColor }">
                  {{ form.announcementText }}
                </div>
              </div>
            </Transition>
          </div>
        </div>

        <!-- ======================== AVANCE ======================== -->
        <div v-show="activeTab === 'advanced'" class="space-y-5">
          <div class="glass-card rounded-2xl p-5 sm:p-6">
            <div class="flex items-center gap-2 mb-1">
              <h2 class="font-display font-bold text-sm" style="color: var(--text-primary)">CSS personnalisé</h2>
              <span class="px-2 py-0.5 rounded-md bg-brand/10 text-brand text-[10px] font-bold uppercase">Pro</span>
            </div>
            <p class="text-xs mb-4" style="color: var(--text-muted)">Code CSS injecté dans votre vitrine.</p>
            <textarea
              v-model="form.customCss"
              rows="8"
              placeholder=".product-card:hover {
  transform: scale(1.02);
}"
              class="w-full px-4 py-3 rounded-xl bg-[#0D0D14] text-sm font-mono leading-relaxed resize-y t-input"
              spellcheck="false"
            ></textarea>
          </div>

          <div class="glass-card rounded-2xl p-5 sm:p-6">
            <h2 class="font-display font-bold text-sm mb-1" style="color: var(--text-primary)">Google Analytics</h2>
            <p class="text-xs mb-4" style="color: var(--text-muted)">Suivez vos visiteurs et conversions.</p>
            <input v-model="form.analyticsId" type="text" placeholder="G-XXXXXXXXXX" class="t-input w-full font-mono" />
          </div>
        </div>

        <!-- ======================== SAVE BAR ======================== -->
        <div class="mt-6 sm:mt-8 flex items-center gap-3 pb-4">
          <button
            @click="save"
            :disabled="saving"
            class="btn-primary flex items-center gap-2"
            :class="saving ? 'opacity-70 pointer-events-none' : ''"
          >
            <svg v-if="saving" width="16" height="16" viewBox="0 0 24 24" class="animate-spin" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.25"/><path d="M12 2a10 10 0 019.95 9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
            <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
            {{ saving ? 'Enregistrement...' : 'Enregistrer' }}
          </button>

          <Transition name="slide">
            <span v-if="saved" class="flex items-center gap-1.5 text-sm font-medium text-mint">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              Enregistré
            </span>
          </Transition>

          <span v-if="errorMessage" class="text-sm text-brand-coral">{{ errorMessage }}</span>
        </div>
      </div>

      <!-- ======================== LIVE PREVIEW PANEL ======================== -->
      <Transition name="preview">
        <div v-if="showPreview" class="hidden lg:block w-80 xl:w-96 shrink-0">
          <div class="sticky top-4">
            <p class="text-xs font-display font-medium uppercase tracking-wider mb-3" style="color: var(--text-faint)">Aperçu en direct</p>
            <div
              class="rounded-2xl overflow-hidden border shadow-2xl"
              style="border-color: var(--border-default)"
              :style="{ background: typeof previewBg === 'string' && previewBg.startsWith('linear') ? undefined : previewBg, backgroundImage: typeof previewBg === 'string' && previewBg.startsWith('linear') ? previewBg : undefined }"
            >
              <!-- Mini announcement bar -->
              <div
                v-if="form.announcementEnabled && form.announcementText"
                class="text-center py-1.5 px-3 text-[9px] font-medium text-white truncate"
                :style="{ background: form.primaryColor }"
              >
                {{ form.announcementText }}
              </div>

              <!-- Mini banner -->
              <div
                class="relative overflow-hidden"
                :class="{ 'h-12': form.bannerHeight === 'compact', 'h-20': form.bannerHeight === 'normal', 'h-28': form.bannerHeight === 'tall' }"
              >
                <img v-if="bannerPreview" :src="bannerPreview" class="w-full h-full object-cover" />
                <div v-else class="w-full h-full" :style="{ background: `linear-gradient(135deg, ${form.primaryColor}33, ${form.primaryColor}11)` }"></div>
              </div>

              <!-- Mini shop card -->
              <div class="p-4 -mt-6 relative z-10">
                <div class="rounded-xl p-3" :style="{ background: form.backgroundStyle === 'light' ? 'rgba(255,255,255,0.9)' : 'rgba(255,255,255,0.05)', border: '1px solid ' + (form.backgroundStyle === 'light' ? 'rgba(0,0,0,0.1)' : 'rgba(255,255,255,0.1)') }">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold shrink-0" :style="{ background: form.primaryColor }">
                      {{ shop?.name?.[0] || 'S' }}
                    </div>
                    <div class="min-w-0">
                      <p class="text-xs font-bold truncate" :style="{ color: previewText }">{{ shop?.name || 'Ma Boutique' }}</p>
                      <p class="text-[9px] truncate" :style="{ color: previewMuted }">{{ shop?.category || 'Mode' }}</p>
                    </div>
                  </div>
                  <p v-if="form.welcomeMessage" class="text-[9px] italic mt-2 truncate" :style="{ color: previewMuted }">{{ form.welcomeMessage }}</p>
                </div>

                <!-- Mini products grid -->
                <div class="grid gap-1.5 mt-3" :style="{ gridTemplateColumns: `repeat(${form.gridColumns}, 1fr)` }">
                  <div v-for="n in Math.min(form.gridColumns * 2, 8)" :key="n" class="rounded-lg overflow-hidden" :style="{ background: form.backgroundStyle === 'light' ? 'rgba(0,0,0,0.05)' : 'rgba(255,255,255,0.05)' }">
                    <div :class="form.cardStyle === 'large' ? 'aspect-[3/4]' : 'aspect-square'" class="bg-white/5"></div>
                    <div v-if="form.cardStyle !== 'minimal'" class="p-1.5">
                      <div class="h-1 rounded-full bg-white/10 w-3/4 mb-1"></div>
                      <div v-if="form.cardStyle === 'detailed'" class="h-0.5 rounded-full bg-white/5 w-full mb-1"></div>
                      <div class="h-1 rounded-full w-1/2" :style="{ background: form.primaryColor + '44' }"></div>
                    </div>
                    <div v-else class="p-1">
                      <div class="h-1 rounded-full w-1/2" :style="{ background: form.primaryColor + '44' }"></div>
                    </div>
                  </div>
                </div>

                <!-- Social icons preview -->
                <div v-if="socialCount > 0" class="flex gap-2 mt-3 justify-center">
                  <div v-for="n in socialCount" :key="n" class="w-5 h-5 rounded-full" :style="{ background: form.primaryColor + '22' }"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </div>
  </div>
</template>

<style scoped>
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
.no-scrollbar::-webkit-scrollbar { display: none; }

.slide-enter-active, .slide-leave-active { transition: all 0.3s ease; }
.slide-enter-from, .slide-leave-to { opacity: 0; transform: translateY(-8px); }

.preview-enter-active { transition: all 0.3s ease; }
.preview-leave-active { transition: all 0.2s ease; }
.preview-enter-from, .preview-leave-to { opacity: 0; transform: translateX(20px); }

input[type='color'] { -webkit-appearance: none; appearance: none; border: none; }
input[type='color']::-webkit-color-swatch-wrapper { padding: 0; }
input[type='color']::-webkit-color-swatch { border: none; border-radius: 0.75rem; }
input[type='color']::-moz-color-swatch { border: none; border-radius: 0.75rem; }
</style>
