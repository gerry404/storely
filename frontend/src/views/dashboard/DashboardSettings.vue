<script setup>
import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue'
import { useAuth } from '../../composables/useAuth'
import { shopLogoUrl, apiUrl } from '../../composables/useStorage'
import { useCategories } from '../../composables/useCategories'
import { usePlan } from '../../composables/usePlan'
import { getCitiesByCountry } from '../../data/cities-by-country'
import { getCountryByCode } from '../../data/countries'

const { user, shop, api, fetchUser } = useAuth()
const { categoryList } = useCategories()
const { currentPlan, planLabel, isFree, isPro } = usePlan()

const form = ref({
  name: '',
  slug: '',
  category: '',
  description: '',
  phone: '',
  whatsapp: '',
  email: '',
  address: '',
  city: '',
  openHours: '',
})

// Visibility toggles (stored in customization JSON)
const visibility = ref({
  showPhone: true,
  showWhatsapp: true,
  showEmail: true,
  showAddress: true,
  showCity: true,
  showHours: true,
  showDescription: true,
  showRating: true,
  showProductCount: true,
  showCategory: true,
})

const saving = ref(false)
const saved = ref(false)
const errorMessage = ref('')
const fieldErrors = ref({})
const logoUploading = ref(false)
const logoInput = ref(null)
const linkCopied = ref(false)

// Category icons mapping
const categoryIcons = {
  'Mode & Vêtements': 'M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z',
  'Beauté & Soins': 'M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z',
  'Électronique': 'M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6A19.79 19.79 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z',
  'Maison & Déco': 'M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z',
  'Alimentation': 'M18 8h1a4 4 0 010 8h-1M2 8h16v9a4 4 0 01-4 4H6a4 4 0 01-4-4V8zM6 1v3M10 1v3M14 1v3',
  'Art & Artisanat': 'M12 19l7-7 3 3-7 7-3-3zM18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5zM2 2l7.586 7.586',
  'Services': 'M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z',
  'Produits digitaux': 'M13 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V9z',
  'Sport & Loisirs': 'M5.2 7.5L12 2l6.8 5.5M12 2v7M3 14l9 8 9-8',
  'Santé & Bien-être': 'M22 12h-4l-3 9L9 3l-3 9H2',
}

// Dynamic cities based on shop country
const shopCountry = computed(() => shop.value?.country || 'CM')
const shopCountryInfo = computed(() => getCountryByCode(shopCountry.value))
const cities = computed(() => {
  const list = getCitiesByCountry(shopCountry.value)
  return list.length ? list : getCitiesByCountry('CM')
})

// Custom dropdown state
const catDropdownOpen = ref(false)
const cityDropdownOpen = ref(false)
const catSearch = ref('')
const citySearch = ref('')
const catDropdownRef = ref(null)
const cityDropdownRef = ref(null)
const catSearchRef = ref(null)
const citySearchRef = ref(null)
const catHighlight = ref(-1)
const cityHighlight = ref(-1)

const filteredCategories = computed(() => {
  if (!catSearch.value) return categoryList
  const q = catSearch.value.toLowerCase()
  return categoryList.filter(c => c.toLowerCase().includes(q))
})

const filteredCities = computed(() => {
  const all = [...cities.value, 'Autre']
  if (!citySearch.value) return all
  const q = citySearch.value.toLowerCase()
  return all.filter(c => c.toLowerCase().includes(q))
})

const openCatDropdown = () => {
  catDropdownOpen.value = true
  catSearch.value = ''
  catHighlight.value = -1
  nextTick(() => catSearchRef.value?.focus())
}

const openCityDropdown = () => {
  cityDropdownOpen.value = true
  citySearch.value = ''
  cityHighlight.value = -1
  nextTick(() => citySearchRef.value?.focus())
}

const selectCategory = (cat) => {
  form.value.category = cat
  catDropdownOpen.value = false
  catSearch.value = ''
}

const selectCity = (city) => {
  form.value.city = city
  cityDropdownOpen.value = false
  citySearch.value = ''
}

const handleCatKey = (e) => {
  const list = filteredCategories.value
  if (e.key === 'ArrowDown') { e.preventDefault(); catHighlight.value = Math.min(catHighlight.value + 1, list.length - 1) }
  else if (e.key === 'ArrowUp') { e.preventDefault(); catHighlight.value = Math.max(catHighlight.value - 1, 0) }
  else if (e.key === 'Enter' && catHighlight.value >= 0) { e.preventDefault(); selectCategory(list[catHighlight.value]) }
  else if (e.key === 'Escape') { catDropdownOpen.value = false }
}

const handleCityKey = (e) => {
  const list = filteredCities.value
  if (e.key === 'ArrowDown') { e.preventDefault(); cityHighlight.value = Math.min(cityHighlight.value + 1, list.length - 1) }
  else if (e.key === 'ArrowUp') { e.preventDefault(); cityHighlight.value = Math.max(cityHighlight.value - 1, 0) }
  else if (e.key === 'Enter' && cityHighlight.value >= 0) { e.preventDefault(); selectCity(list[cityHighlight.value]) }
  else if (e.key === 'Escape') { cityDropdownOpen.value = false }
}

// Close dropdowns on outside click
const handleOutsideClick = (e) => {
  if (catDropdownRef.value && !catDropdownRef.value.contains(e.target)) catDropdownOpen.value = false
  if (cityDropdownRef.value && !cityDropdownRef.value.contains(e.target)) cityDropdownOpen.value = false
}

const siteBase = import.meta.env.PROD ? 'https://storely.app' : window.location.origin
const shopUrl = computed(() => `${siteBase}/${form.value.slug || shop.value?.slug || ''}`)

onMounted(() => {
  document.addEventListener('mousedown', handleOutsideClick)
  if (shop.value) {
    form.value.name = shop.value.name || ''
    form.value.slug = shop.value.slug || ''
    form.value.category = shop.value.category || ''
    form.value.description = shop.value.description || ''
    form.value.phone = shop.value.phone || ''
    form.value.whatsapp = shop.value.whatsapp || ''
    form.value.email = shop.value.email || ''
    form.value.address = shop.value.address || ''
    form.value.city = shop.value.city || ''
    form.value.openHours = shop.value.open_hours || ''

    // Load visibility from customization
    const cust = shop.value.customization
    if (cust) {
      const data = typeof cust === 'string' ? (() => { try { return JSON.parse(cust) } catch { return {} } })() : cust
      Object.keys(visibility.value).forEach(k => {
        if (data[k] !== undefined) visibility.value[k] = data[k]
      })
    }
  }
})

onUnmounted(() => {
  document.removeEventListener('mousedown', handleOutsideClick)
})

const copyLink = async () => {
  try { await navigator.clipboard.writeText(shopUrl.value) } catch {
    const i = document.createElement('input'); i.value = shopUrl.value
    document.body.appendChild(i); i.select(); document.execCommand('copy'); document.body.removeChild(i)
  }
  linkCopied.value = true
  setTimeout(() => linkCopied.value = false, 2000)
}

const shareLink = async () => {
  if (navigator.share) {
    try { await navigator.share({ title: shop.value?.name, text: `Découvrez ${shop.value?.name} sur Storely !`, url: shopUrl.value }) } catch {}
  } else copyLink()
}

const shareWhatsApp = () => {
  window.open(`https://wa.me/?text=${encodeURIComponent(`Découvrez ${shop.value?.name} sur Storely ! ${shopUrl.value}`)}`, '_blank')
}

const shareFacebook = () => {
  window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shopUrl.value)}`, '_blank', 'width=600,height=400')
}

const uploadLogo = async (event) => {
  const file = event.target.files[0]
  if (!file) return
  logoUploading.value = true
  errorMessage.value = ''
  try {
    const fd = new FormData()
    fd.append('logo', file)
    const token = localStorage.getItem('storely-token')
    const res = await fetch(apiUrl('/api/shop/logo'), {
      method: 'POST',
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
      body: fd,
    })
    if (!res.ok) { const d = await res.json(); throw new Error(d.message || 'Erreur') }
    await fetchUser()
  } catch (e) { errorMessage.value = e.message }
  finally { logoUploading.value = false }
}

const save = async () => {
  saving.value = true
  errorMessage.value = ''
  fieldErrors.value = {}
  try {
    // Merge visibility into existing customization
    let existingCust = {}
    if (shop.value?.customization) {
      existingCust = typeof shop.value.customization === 'string'
        ? (() => { try { return JSON.parse(shop.value.customization) } catch { return {} } })()
        : { ...shop.value.customization }
    }
    const mergedCust = { ...existingCust, ...visibility.value }

    await api('/api/shop', {
      method: 'PUT',
      body: JSON.stringify({
        name: form.value.name,
        category: form.value.category,
        description: form.value.description,
        phone: form.value.phone,
        whatsapp: form.value.whatsapp,
        email: form.value.email,
        address: form.value.address,
        city: form.value.city,
        open_hours: form.value.openHours,
        customization: JSON.stringify(mergedCust),
      }),
    })
    await fetchUser()
    saved.value = true
    setTimeout(() => saved.value = false, 3000)
  } catch (err) {
    errorMessage.value = err.message
    fieldErrors.value = err.errors || {}
  } finally { saving.value = false }
}

const visibilityItems = [
  { key: 'showPhone', label: 'Téléphone', icon: 'phone', description: 'Afficher votre numéro de téléphone' },
  { key: 'showWhatsapp', label: 'WhatsApp', icon: 'whatsapp', description: 'Bouton WhatsApp et contact' },
  { key: 'showEmail', label: 'Email', icon: 'email', description: 'Afficher votre adresse email' },
  { key: 'showAddress', label: 'Adresse', icon: 'map', description: 'Afficher votre adresse physique' },
  { key: 'showCity', label: 'Ville', icon: 'city', description: 'Afficher votre ville' },
  { key: 'showHours', label: 'Horaires', icon: 'clock', description: "Afficher vos horaires d'ouverture" },
  { key: 'showDescription', label: 'Description', icon: 'text', description: 'Afficher la description de la boutique' },
  { key: 'showRating', label: 'Notes & Avis', icon: 'star', description: 'Afficher les étoiles et avis clients' },
  { key: 'showProductCount', label: 'Nombre de produits', icon: 'count', description: 'Afficher le nombre de produits' },
  { key: 'showCategory', label: 'Catégorie', icon: 'tag', description: 'Afficher le badge de catégorie' },
]
</script>

<template>
  <div class="p-4 sm:p-6 md:p-8 max-w-3xl mx-auto md:mx-0">
    <div class="mb-8">
      <h1 class="font-display font-bold text-2xl" style="color: var(--text-primary)">Paramètres</h1>
      <p class="text-sm mt-1" style="color: var(--text-muted)">Gérez toutes les informations de votre boutique</p>
    </div>

    <!-- Toasts -->
    <Transition name="slide">
      <div v-if="saved" class="mb-6 px-4 py-3 rounded-xl bg-mint/10 border border-mint/20 text-mint text-sm flex items-center gap-2">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        Modifications enregistrées !
      </div>
    </Transition>
    <div v-if="errorMessage" class="mb-6 px-4 py-3 rounded-xl bg-brand-coral/10 border border-brand-coral/20 text-brand-coral text-sm flex items-start gap-2">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 mt-0.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
      {{ errorMessage }}
    </div>

    <!-- ══════ LIEN BOUTIQUE ══════ -->
    <div class="glass-card rounded-2xl p-5 sm:p-6 mb-6">
      <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-xl bg-brand/10 flex items-center justify-center shrink-0">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FF6B2C" stroke-width="2"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
        </div>
        <div>
          <h2 class="font-display font-bold text-sm" style="color: var(--text-primary)">Lien de votre vitrine</h2>
          <p class="text-xs" style="color: var(--text-muted)">Partagez ce lien avec vos clients</p>
        </div>
      </div>
      <div class="flex items-center gap-2 mb-4">
        <div class="flex-1 flex items-center min-w-0">
          <div class="px-3 py-2.5 rounded-l-xl text-xs font-mono shrink-0" style="background: var(--bg-primary); border: 1px solid var(--border-default); border-right: 0; color: var(--text-faint)">storely.app/</div>
          <div class="flex-1 px-3 py-2.5 rounded-r-xl text-sm font-mono font-medium truncate" style="background: var(--bg-primary); border: 1px solid var(--border-default); color: var(--text-primary)">{{ form.slug || '...' }}</div>
        </div>
        <button @click="copyLink" class="shrink-0 px-4 py-2.5 rounded-xl text-sm font-medium transition-all flex items-center gap-2" :class="linkCopied ? 'bg-mint/10 text-mint border border-mint/20' : 'bg-brand/10 text-brand border border-brand/20 hover:bg-brand/15'">
          <svg v-if="!linkCopied" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
          <svg v-else width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          {{ linkCopied ? 'Copié !' : 'Copier' }}
        </button>
      </div>
      <div class="flex flex-wrap gap-2">
        <button @click="shareLink" class="set-share-btn"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>Partager</button>
        <button @click="shareWhatsApp" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-[#25D366]/10 text-[#25D366] text-xs font-medium hover:bg-[#25D366]/15 transition"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>WhatsApp</button>
        <button @click="shareFacebook" class="flex items-center gap-2 px-4 py-2 rounded-xl bg-[#1877F2]/10 text-[#1877F2] text-xs font-medium hover:bg-[#1877F2]/15 transition"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>Facebook</button>
      </div>
    </div>

    <!-- ══════ INFORMATIONS BOUTIQUE ══════ -->
    <div class="glass-card rounded-2xl p-5 sm:p-6 mb-6">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 rounded-xl bg-electric/10 flex items-center justify-center shrink-0">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#818CF8" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        </div>
        <div>
          <h2 class="font-display font-bold text-sm" style="color: var(--text-primary)">Informations de la boutique</h2>
          <p class="text-xs" style="color: var(--text-muted)">Ces infos seront visibles sur votre vitrine</p>
        </div>
      </div>

      <div class="space-y-5">
        <!-- Logo -->
        <div class="flex items-center gap-4">
          <div class="w-16 h-16 rounded-2xl shrink-0 cursor-pointer relative overflow-hidden group" @click="logoInput?.click()">
            <img v-if="shopLogoUrl(shop)" :src="shopLogoUrl(shop)" alt="Logo" class="w-full h-full object-cover" />
            <div v-else class="w-full h-full bg-gradient-to-br from-brand to-brand-amber flex items-center justify-center text-white font-display font-bold text-2xl">{{ shop?.name?.charAt(0)?.toUpperCase() || 'S' }}</div>
            <div class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/><circle cx="12" cy="13" r="4"/></svg>
            </div>
            <div v-if="logoUploading" class="absolute inset-0 bg-black/60 flex items-center justify-center">
              <svg class="w-5 h-5 text-white animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.25"/><path d="M12 2a10 10 0 019.95 9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
            </div>
          </div>
          <div>
            <button class="text-sm text-brand hover:text-brand-light transition font-medium" @click="logoInput?.click()" :disabled="logoUploading">{{ logoUploading ? 'Envoi...' : 'Changer le logo' }}</button>
            <p class="text-xs mt-1" style="color: var(--text-faint)">JPG, PNG. Max 2MB.</p>
          </div>
          <input ref="logoInput" type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="uploadLogo" />
        </div>

        <!-- Name + Slug -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="set-label">Nom de la boutique</label>
            <input v-model="form.name" type="text" class="t-input w-full" placeholder="Ma Boutique" />
            <p v-if="fieldErrors.name" class="text-xs text-brand-coral mt-1">{{ fieldErrors.name[0] }}</p>
          </div>
          <div>
            <label class="set-label">URL personnalisée</label>
            <div class="flex items-center">
              <span class="px-3 py-2.5 rounded-l-xl text-xs shrink-0" style="background: var(--bg-primary); border: 1px solid var(--border-default); border-right: 0; color: var(--text-faint)">storely.app/</span>
              <input v-model="form.slug" type="text" class="t-input flex-1 !rounded-l-none" />
            </div>
          </div>
        </div>

        <!-- Category + City (Premium Dropdowns) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- Category Dropdown -->
          <div ref="catDropdownRef" class="relative">
            <label class="set-label">Catégorie</label>
            <button
              type="button"
              @click="catDropdownOpen ? (catDropdownOpen = false) : openCatDropdown()"
              class="prem-select w-full"
              :class="{ 'prem-select--open': catDropdownOpen }"
            >
              <span v-if="form.category" class="flex items-center gap-2 min-w-0">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="shrink-0 text-brand"><path :d="categoryIcons[form.category] || categoryIcons['Services']" /></svg>
                <span class="truncate">{{ form.category }}</span>
              </span>
              <span v-else style="color: var(--text-faint)">Choisir une catégorie...</span>
              <svg class="prem-select-chevron shrink-0" :class="{ 'rotate-180': catDropdownOpen }" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
            </button>

            <Transition name="dropdown">
              <div v-if="catDropdownOpen" class="prem-dropdown">
                <div class="prem-dropdown-search">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 opacity-40"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                  <input
                    ref="catSearchRef"
                    v-model="catSearch"
                    @keydown="handleCatKey"
                    type="text"
                    placeholder="Rechercher..."
                    class="prem-dropdown-search-input"
                  />
                </div>
                <div class="prem-dropdown-list">
                  <button
                    v-for="(cat, idx) in filteredCategories"
                    :key="cat"
                    type="button"
                    @click="selectCategory(cat)"
                    @mouseenter="catHighlight = idx"
                    class="prem-dropdown-item"
                    :class="{ 'prem-dropdown-item--active': form.category === cat, 'prem-dropdown-item--highlight': catHighlight === idx }"
                  >
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="shrink-0" :class="form.category === cat ? 'text-brand' : 'opacity-40'"><path :d="categoryIcons[cat] || categoryIcons['Services']" /></svg>
                    <span class="flex-1 text-left truncate">{{ cat }}</span>
                    <svg v-if="form.category === cat" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#FF6B2C" stroke-width="2.5" class="shrink-0"><polyline points="20 6 9 17 4 12"/></svg>
                  </button>
                  <div v-if="!filteredCategories.length" class="px-4 py-6 text-center text-xs" style="color: var(--text-faint)">Aucun résultat</div>
                </div>
              </div>
            </Transition>
          </div>

          <!-- City Dropdown -->
          <div ref="cityDropdownRef" class="relative">
            <label class="set-label">
              Ville
              <span v-if="shopCountryInfo" class="ml-1.5 text-[10px] font-normal normal-case tracking-normal px-1.5 py-0.5 rounded-md inline-flex items-center gap-1" style="background: rgba(255,255,255,0.05); color: var(--text-muted)">
                {{ shopCountryInfo.flag }} {{ shopCountryInfo.name }}
              </span>
            </label>
            <button
              type="button"
              @click="cityDropdownOpen ? (cityDropdownOpen = false) : openCityDropdown()"
              class="prem-select w-full"
              :class="{ 'prem-select--open': cityDropdownOpen }"
            >
              <span v-if="form.city && form.city !== 'Autre'" class="flex items-center gap-2 min-w-0">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="shrink-0 text-mint"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                <span class="truncate">{{ form.city }}</span>
              </span>
              <span v-else style="color: var(--text-faint)">Sélectionner une ville...</span>
              <svg class="prem-select-chevron shrink-0" :class="{ 'rotate-180': cityDropdownOpen }" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
            </button>

            <Transition name="dropdown">
              <div v-if="cityDropdownOpen" class="prem-dropdown">
                <div class="prem-dropdown-search">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="shrink-0 opacity-40"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                  <input
                    ref="citySearchRef"
                    v-model="citySearch"
                    @keydown="handleCityKey"
                    type="text"
                    placeholder="Rechercher une ville..."
                    class="prem-dropdown-search-input"
                  />
                </div>
                <div class="prem-dropdown-list">
                  <button
                    v-for="(city, idx) in filteredCities"
                    :key="city"
                    type="button"
                    @click="selectCity(city)"
                    @mouseenter="cityHighlight = idx"
                    class="prem-dropdown-item"
                    :class="{ 'prem-dropdown-item--active': form.city === city, 'prem-dropdown-item--highlight': cityHighlight === idx }"
                  >
                    <svg v-if="city !== 'Autre'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="shrink-0" :class="form.city === city ? 'text-mint' : 'opacity-30'"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="shrink-0 opacity-30"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span class="flex-1 text-left truncate">{{ city }}</span>
                    <svg v-if="form.city === city" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#2DD4A8" stroke-width="2.5" class="shrink-0"><polyline points="20 6 9 17 4 12"/></svg>
                  </button>
                  <div v-if="!filteredCities.length" class="px-4 py-6 text-center text-xs" style="color: var(--text-faint)">Aucune ville trouvée</div>
                </div>
              </div>
            </Transition>

            <!-- Custom city input when "Autre" is selected -->
            <input v-if="form.city === 'Autre'" v-model="form.city" type="text" class="t-input w-full mt-2" placeholder="Entrez votre ville..." />
          </div>
        </div>

        <!-- Description -->
        <div>
          <label class="set-label">Description</label>
          <textarea v-model="form.description" rows="3" class="t-input w-full resize-none" placeholder="Décrivez votre boutique en quelques mots..." />
          <p class="text-xs mt-1" style="color: var(--text-faint)">{{ (form.description || '').length }}/1000 caractères</p>
        </div>

        <!-- Divider -->
        <div class="border-t pt-5" style="border-color: var(--border-default)">
          <h3 class="font-display font-semibold text-sm mb-4" style="color: var(--text-primary)">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="inline mr-1"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6A19.79 19.79 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
            Contact & Localisation
          </h3>
        </div>

        <!-- Phone + WhatsApp -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="set-label">Téléphone</label>
            <input v-model="form.phone" type="tel" placeholder="+237 6XX XXX XXX" class="t-input w-full" />
          </div>
          <div>
            <label class="set-label">WhatsApp</label>
            <input v-model="form.whatsapp" type="tel" placeholder="237699000000" class="t-input w-full" />
            <p class="text-xs mt-1" style="color: var(--text-faint)">Format international sans + ni espaces</p>
          </div>
        </div>

        <!-- Email -->
        <div>
          <label class="set-label">Email de la boutique</label>
          <input v-model="form.email" type="email" placeholder="contact@maboutique.com" class="t-input w-full" />
          <p v-if="fieldErrors.email" class="text-xs text-brand-coral mt-1">{{ fieldErrors.email[0] }}</p>
        </div>

        <!-- Address -->
        <div>
          <label class="set-label">Adresse complète</label>
          <input v-model="form.address" type="text" placeholder="Ex: Rue de la Joie, Akwa, Douala" class="t-input w-full" />
        </div>

        <!-- Opening hours -->
        <div>
          <label class="set-label">Horaires d'ouverture</label>
          <input v-model="form.openHours" type="text" placeholder="Lun-Sam: 8h-19h, Dim: Fermé" class="t-input w-full" />
        </div>
      </div>
    </div>

    <!-- ══════ ÉLÉMENTS VISIBLES ══════ -->
    <div class="glass-card rounded-2xl p-5 sm:p-6 mb-6">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-mint/10 flex items-center justify-center shrink-0">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2DD4A8" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
        </div>
        <div>
          <h2 class="font-display font-bold text-sm" style="color: var(--text-primary)">Éléments visibles</h2>
          <p class="text-xs" style="color: var(--text-muted)">Choisissez ce qui apparaît sur votre vitrine publique</p>
        </div>
      </div>

      <div class="mt-4 space-y-1">
        <div
          v-for="item in visibilityItems"
          :key="item.key"
          class="set-visibility-row"
          @click="visibility[item.key] = !visibility[item.key]"
        >
          <div class="flex items-center gap-3 flex-1 min-w-0">
            <!-- Icon -->
            <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0" :style="{ background: visibility[item.key] ? 'rgba(45,212,168,0.1)' : 'rgba(255,255,255,0.03)' }">
              <svg v-if="item.icon==='phone'" width="14" height="14" viewBox="0 0 24 24" fill="none" :stroke="visibility[item.key] ? '#2DD4A8' : 'rgba(255,255,255,0.25)'" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6A19.79 19.79 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
              <svg v-if="item.icon==='whatsapp'" width="14" height="14" viewBox="0 0 24 24" :fill="visibility[item.key] ? '#25D366' : 'rgba(255,255,255,0.25)'" stroke="none"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
              <svg v-if="item.icon==='email'" width="14" height="14" viewBox="0 0 24 24" fill="none" :stroke="visibility[item.key] ? '#2DD4A8' : 'rgba(255,255,255,0.25)'" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
              <svg v-if="item.icon==='map'" width="14" height="14" viewBox="0 0 24 24" fill="none" :stroke="visibility[item.key] ? '#2DD4A8' : 'rgba(255,255,255,0.25)'" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
              <svg v-if="item.icon==='city'" width="14" height="14" viewBox="0 0 24 24" fill="none" :stroke="visibility[item.key] ? '#2DD4A8' : 'rgba(255,255,255,0.25)'" stroke-width="2"><rect x="1" y="6" width="15" height="14" rx="2"/><path d="M16 2h4a2 2 0 012 2v16a2 2 0 01-2 2h-4"/><line x1="8" y1="10" x2="10" y2="10"/><line x1="8" y1="14" x2="10" y2="14"/></svg>
              <svg v-if="item.icon==='clock'" width="14" height="14" viewBox="0 0 24 24" fill="none" :stroke="visibility[item.key] ? '#2DD4A8' : 'rgba(255,255,255,0.25)'" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <svg v-if="item.icon==='text'" width="14" height="14" viewBox="0 0 24 24" fill="none" :stroke="visibility[item.key] ? '#2DD4A8' : 'rgba(255,255,255,0.25)'" stroke-width="2"><line x1="17" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="17" y1="18" x2="3" y2="18"/></svg>
              <svg v-if="item.icon==='star'" width="14" height="14" viewBox="0 0 24 24" :fill="visibility[item.key] ? '#FFAA33' : 'none'" :stroke="visibility[item.key] ? '#FFAA33' : 'rgba(255,255,255,0.25)'" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
              <svg v-if="item.icon==='count'" width="14" height="14" viewBox="0 0 24 24" fill="none" :stroke="visibility[item.key] ? '#2DD4A8' : 'rgba(255,255,255,0.25)'" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
              <svg v-if="item.icon==='tag'" width="14" height="14" viewBox="0 0 24 24" fill="none" :stroke="visibility[item.key] ? '#2DD4A8' : 'rgba(255,255,255,0.25)'" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
            </div>
            <div class="min-w-0">
              <p class="text-sm font-medium" style="color: var(--text-primary)">{{ item.label }}</p>
              <p class="text-xs" style="color: var(--text-faint)">{{ item.description }}</p>
            </div>
          </div>
          <!-- Toggle -->
          <div class="set-toggle" :class="{ active: visibility[item.key] }">
            <div class="set-toggle-dot"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- ══════ SAVE ══════ -->
    <div class="flex items-center gap-3 mb-6">
      <button class="btn-primary flex items-center gap-2" :disabled="saving" :class="saving ? 'opacity-70 pointer-events-none' : ''" @click="save">
        <svg v-if="saving" width="16" height="16" viewBox="0 0 24 24" class="animate-spin" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.25"/><path d="M12 2a10 10 0 019.95 9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
        {{ saving ? 'Enregistrement...' : 'Enregistrer les modifications' }}
      </button>
      <Transition name="slide">
        <span v-if="saved" class="flex items-center gap-1.5 text-sm font-medium text-mint">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          Enregistré
        </span>
      </Transition>
    </div>

    <!-- ══════ ABONNEMENT ══════ -->
    <div class="glass-card rounded-2xl p-5 sm:p-6 mb-6">
      <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" :class="isPro ? 'bg-brand-amber/10' : isFree ? 'bg-white/5' : 'bg-brand/10'">
          <svg v-if="isPro" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FFAA33" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" :stroke="isFree ? 'rgba(255,255,255,0.4)' : '#FF6B2C'" stroke-width="2"><path d="M20.24 12.24a6 6 0 00-8.49-8.49L5 10.5V19h8.5z"/><line x1="16" y1="8" x2="2" y2="22"/><line x1="17.5" y1="15" x2="9" y2="15"/></svg>
        </div>
        <div>
          <h2 class="font-display font-bold text-sm" style="color: var(--text-primary)">Abonnement</h2>
          <p class="text-xs" style="color: var(--text-muted)">Votre plan actuel et ses avantages</p>
        </div>
      </div>

      <!-- Current plan card -->
      <div class="p-4 rounded-xl border" :class="isPro ? 'bg-gradient-to-r from-brand-amber/5 to-brand/5 border-brand-amber/20' : isFree ? 'bg-white/[0.02] border-white/[0.06]' : 'bg-brand/5 border-brand/20'">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center font-display font-bold text-sm" :class="isPro ? 'bg-gradient-to-br from-brand-amber to-brand text-white' : isFree ? 'bg-white/5 text-white/40' : 'bg-brand/10 text-brand'">
              {{ isPro ? 'P' : isFree ? 'F' : 'S' }}
            </div>
            <div>
              <div class="flex items-center gap-2">
                <span class="font-display font-bold text-sm" style="color: var(--text-primary)">Plan {{ planLabel }}</span>
                <span v-if="isPro" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-brand-amber/10 text-brand-amber border border-brand-amber/20">Premium</span>
                <span v-else-if="isFree" class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-white/5 text-white/40 border border-white/[0.06]">Gratuit</span>
                <span v-else class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-brand/10 text-brand border border-brand/20">Actif</span>
              </div>
              <p class="text-xs mt-0.5" style="color: var(--text-muted)">
                <template v-if="isFree">5 produits, 1 image, branding Storely</template>
                <template v-else-if="isPro">Produits illimites, 10 images, sans branding</template>
                <template v-else>20 produits, 3 images, commandes en ligne</template>
              </p>
            </div>
          </div>

          <router-link
            v-if="!isPro"
            to="/dashboard/upgrade"
            class="shrink-0 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all flex items-center gap-2"
            :class="isFree ? 'bg-gradient-to-r from-brand to-brand-amber text-white hover:shadow-lg hover:shadow-brand/20' : 'bg-gradient-to-r from-brand-amber to-yellow-500 text-white hover:shadow-lg hover:shadow-brand-amber/20'"
          >
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
            {{ isFree ? 'Passer au Starter' : 'Passer au Pro' }}
          </router-link>
          <div v-else class="shrink-0 flex items-center gap-1.5 text-sm text-mint font-medium">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Plan maximum
          </div>
        </div>
      </div>

      <!-- Plan comparison hint for free/starter -->
      <p v-if="!isPro" class="text-xs mt-3 text-center" style="color: var(--text-faint)">
        <router-link to="/dashboard/upgrade" class="text-brand hover:underline">Comparer les plans</router-link> pour voir toutes les fonctionnalites disponibles
      </p>
    </div>

    <!-- ══════ COMPTE ══════ -->
    <div class="glass-card rounded-2xl p-5 sm:p-6">
      <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-xl bg-electric/10 flex items-center justify-center shrink-0">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#818CF8" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
        <h2 class="font-display font-bold text-sm" style="color: var(--text-primary)">Compte</h2>
      </div>
      <div class="flex items-center gap-4 p-4 rounded-xl" style="background: var(--bg-primary); border: 1px solid var(--border-default)">
        <div class="w-12 h-12 rounded-xl bg-electric/10 flex items-center justify-center text-electric font-display font-bold shrink-0">{{ user?.name?.[0]?.toUpperCase() || 'U' }}</div>
        <div class="min-w-0 flex-1">
          <p class="font-display font-semibold text-sm truncate" style="color: var(--text-primary)">{{ user?.name || '-' }}</p>
          <p class="text-xs truncate" style="color: var(--text-muted)">{{ user?.email || '-' }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.slide-enter-active, .slide-leave-active { transition: all 0.3s ease; }
.slide-enter-from, .slide-leave-to { opacity: 0; transform: translateY(-8px); }

.set-label {
  display: block;
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--text-faint);
  margin-bottom: 6px;
}

.set-share-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 0.5rem 1rem;
  border-radius: 0.75rem;
  font-size: 0.75rem;
  font-weight: 500;
  background: var(--bg-primary);
  border: 1px solid var(--border-default);
  color: var(--text-secondary);
  transition: all 0.2s;
}
.set-share-btn:hover { background: rgba(255,255,255,0.05); }

.set-visibility-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 10px 12px;
  border-radius: 12px;
  cursor: pointer;
  transition: background 0.15s;
}
.set-visibility-row:hover {
  background: rgba(255,255,255,0.02);
}

.set-toggle {
  width: 44px;
  height: 24px;
  border-radius: 12px;
  background: rgba(255,255,255,0.08);
  position: relative;
  transition: background 0.25s;
  flex-shrink: 0;
}
.set-toggle.active {
  background: #2DD4A8;
}
.set-toggle-dot {
  position: absolute;
  top: 2px;
  left: 2px;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  background: white;
  transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 1px 4px rgba(0,0,0,0.2);
}
.set-toggle.active .set-toggle-dot {
  transform: translateX(20px);
}

/* ── Premium Dropdowns ── */
.prem-select {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
  padding: 10px 14px;
  border-radius: 12px;
  font-size: 0.875rem;
  color: var(--text-primary);
  background: var(--bg-primary);
  border: 1px solid var(--border-default);
  cursor: pointer;
  transition: all 0.2s;
  text-align: left;
  min-height: 44px;
}
.prem-select:hover {
  border-color: rgba(255,255,255,0.12);
  background: rgba(255,255,255,0.03);
}
.prem-select--open {
  border-color: rgba(255, 107, 44, 0.4);
  box-shadow: 0 0 0 3px rgba(255, 107, 44, 0.08);
}
.prem-select-chevron {
  transition: transform 0.2s ease;
  color: var(--text-faint);
}

.prem-dropdown {
  position: absolute;
  top: calc(100% + 6px);
  left: 0;
  right: 0;
  z-index: 50;
  border-radius: 14px;
  border: 1px solid rgba(255,255,255,0.08);
  background: var(--bg-secondary);
  box-shadow: 0 16px 48px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.04) inset;
  overflow: hidden;
  backdrop-filter: blur(20px);
}

.prem-dropdown-search {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 10px 14px;
  border-bottom: 1px solid rgba(255,255,255,0.06);
}
.prem-dropdown-search-input {
  flex: 1;
  background: transparent;
  border: none;
  outline: none;
  font-size: 0.8rem;
  color: var(--text-primary);
}
.prem-dropdown-search-input::placeholder {
  color: var(--text-faint);
}

.prem-dropdown-list {
  max-height: 240px;
  overflow-y: auto;
  padding: 4px;
  scrollbar-width: thin;
  scrollbar-color: rgba(255,255,255,0.08) transparent;
}
.prem-dropdown-list::-webkit-scrollbar { width: 4px; }
.prem-dropdown-list::-webkit-scrollbar-track { background: transparent; }
.prem-dropdown-list::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }

.prem-dropdown-item {
  display: flex;
  align-items: center;
  gap: 10px;
  width: 100%;
  padding: 9px 12px;
  border-radius: 10px;
  font-size: 0.8rem;
  color: var(--text-secondary);
  transition: all 0.12s;
  cursor: pointer;
  border: none;
  background: transparent;
}
.prem-dropdown-item:hover,
.prem-dropdown-item--highlight {
  background: rgba(255,255,255,0.04);
  color: var(--text-primary);
}
.prem-dropdown-item--active {
  background: rgba(255, 107, 44, 0.06);
  color: var(--text-primary);
}

/* Dropdown transition */
.dropdown-enter-active { transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1); }
.dropdown-leave-active { transition: all 0.15s ease-in; }
.dropdown-enter-from { opacity: 0; transform: translateY(-6px) scale(0.97); }
.dropdown-leave-to { opacity: 0; transform: translateY(-4px) scale(0.98); }
</style>
