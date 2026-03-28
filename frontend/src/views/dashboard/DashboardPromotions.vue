<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useAuth } from '../../composables/useAuth'
import { apiUrl } from '../../composables/useStorage'

const { api } = useAuth()

const promotions = ref([])
const products = ref([])
const loading = ref(true)
const showModal = ref(false)
const editingPromo = ref(null)
const submitting = ref(false)
const fieldErrors = ref({})
const showDeleteConfirm = ref(null)
const deleting = ref(false)
const togglingId = ref(null)
const bannerFile = ref(null)
const bannerPreview = ref(null)
const productSearch = ref('')

const defaultForm = () => ({
  title: '',
  description: '',
  type: 'discount',
  discount_percent: '',
  discount_amount: '',
  badge_text: '',
  badge_color: '#8B5CF6',
  product_ids: [],
  active: true,
  starts_at: '',
  ends_at: '',
})

const form = ref(defaultForm())

const promoTypes = [
  { key: 'discount', label: 'Reduction', icon: 'percent', color: '#8B5CF6' },
  { key: 'banner', label: 'Banniere', icon: 'image', color: '#3B82F6' },
  { key: 'flash_sale', label: 'Vente flash', icon: 'zap', color: '#F59E0B' },
  { key: 'free_shipping', label: 'Livraison gratuite', icon: 'truck', color: '#10B981' },
]

const badgeColors = [
  '#8B5CF6', '#3B82F6', '#10B981', '#F59E0B', '#EF4444',
  '#EC4899', '#06B6D4', '#F97316', '#6366F1', '#14B8A6',
]

const filteredProducts = computed(() => {
  if (!productSearch.value) return products.value
  const q = productSearch.value.toLowerCase()
  return products.value.filter(p => p.name.toLowerCase().includes(q))
})

const getPromoStatus = (promo) => {
  if (!promo.active) return { label: 'Inactif', class: 'promo-status-inactive' }
  const now = new Date()
  if (promo.ends_at && new Date(promo.ends_at) < now) return { label: 'Expire', class: 'promo-status-expired' }
  if (promo.starts_at && new Date(promo.starts_at) > now) return { label: 'Planifie', class: 'promo-status-scheduled' }
  return { label: 'Actif', class: 'promo-status-active' }
}

const getTypeInfo = (type) => {
  return promoTypes.find(t => t.key === type) || promoTypes[0]
}

const formatDate = (date) => {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('fr-FR', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

const formatDateInput = (date) => {
  if (!date) return ''
  return date.substring(0, 16)
}

const showsDiscount = computed(() => {
  return form.value.type === 'discount' || form.value.type === 'flash_sale'
})

// ---- API calls ----

const fetchPromotions = async () => {
  loading.value = true
  try {
    const data = await api('/api/promotions')
    promotions.value = data
  } catch (e) {
    console.error('Failed to fetch promotions:', e)
    promotions.value = []
  } finally {
    loading.value = false
  }
}

const fetchProducts = async () => {
  try {
    const data = await api('/api/products')
    products.value = data
  } catch (e) {
    console.error('Failed to fetch products:', e)
    products.value = []
  }
}

const openCreateModal = () => {
  editingPromo.value = null
  form.value = defaultForm()
  bannerFile.value = null
  bannerPreview.value = null
  fieldErrors.value = {}
  productSearch.value = ''
  showModal.value = true
}

const openEditModal = (promo) => {
  editingPromo.value = promo
  form.value = {
    title: promo.title || '',
    description: promo.description || '',
    type: promo.type || 'discount',
    discount_percent: promo.discount_percent || '',
    discount_amount: promo.discount_amount || '',
    badge_text: promo.badge_text || '',
    badge_color: promo.badge_color || '#8B5CF6',
    product_ids: promo.product_ids ? [...promo.product_ids] : (promo.products ? promo.products.map(p => p.id) : []),
    active: !!promo.active,
    starts_at: formatDateInput(promo.starts_at),
    ends_at: formatDateInput(promo.ends_at),
  }
  bannerFile.value = null
  bannerPreview.value = promo.banner_image ? `/storage/${promo.banner_image}` : null
  fieldErrors.value = {}
  productSearch.value = ''
  showModal.value = true
}

const onBannerChange = (e) => {
  const file = e.target.files[0]
  if (!file) return
  bannerFile.value = file
  const reader = new FileReader()
  reader.onload = (ev) => {
    bannerPreview.value = ev.target.result
  }
  reader.readAsDataURL(file)
}

const toggleProductId = (id) => {
  const idx = form.value.product_ids.indexOf(id)
  if (idx === -1) {
    form.value.product_ids.push(id)
  } else {
    form.value.product_ids.splice(idx, 1)
  }
}

const submitForm = async () => {
  submitting.value = true
  fieldErrors.value = {}
  try {
    const payload = {
      title: form.value.title,
      description: form.value.description,
      type: form.value.type,
      discount_percent: form.value.discount_percent || null,
      discount_amount: form.value.discount_amount || null,
      badge_text: form.value.badge_text,
      badge_color: form.value.badge_color,
      product_ids: form.value.product_ids,
      active: form.value.active ? 1 : 0,
      starts_at: form.value.starts_at || null,
      ends_at: form.value.ends_at || null,
    }

    if (editingPromo.value) {
      await api(`/api/promotions/${editingPromo.value.id}`, {
        method: 'PUT',
        body: JSON.stringify(payload),
      })
    } else {
      const data = await api('/api/promotions', {
        method: 'POST',
        body: JSON.stringify(payload),
      })
      // If a banner file is selected and we just created, upload it
      if (bannerFile.value && data && data.id) {
        await uploadBanner(data.id)
      }
    }

    // Upload banner for edit mode
    if (editingPromo.value && bannerFile.value) {
      await uploadBanner(editingPromo.value.id)
    }

    showModal.value = false
    await fetchPromotions()
  } catch (e) {
    if (e.errors) {
      fieldErrors.value = e.errors
    }
    console.error('Submit error:', e)
  } finally {
    submitting.value = false
  }
}

const uploadBanner = async (promoId) => {
  const formData = new FormData()
  formData.append('banner', bannerFile.value)
  const token = localStorage.getItem('storely-token')
  const res = await fetch(apiUrl(`/api/promotions/${promoId}/banner`), {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      Authorization: `Bearer ${token}`,
    },
    body: formData,
  })
  if (!res.ok) {
    const data = await res.json()
    console.error('Banner upload failed:', data)
  }
}

const toggleActive = async (promo) => {
  togglingId.value = promo.id
  try {
    await api(`/api/promotions/${promo.id}`, {
      method: 'PUT',
      body: JSON.stringify({ active: !promo.active }),
    })
    promo.active = !promo.active
  } catch (e) {
    console.error('Toggle error:', e)
  } finally {
    togglingId.value = null
  }
}

const confirmDelete = (promo) => {
  showDeleteConfirm.value = promo
}

const cancelDelete = () => {
  showDeleteConfirm.value = null
}

const deletePromo = async () => {
  if (!showDeleteConfirm.value) return
  deleting.value = true
  try {
    await api(`/api/promotions/${showDeleteConfirm.value.id}`, { method: 'DELETE' })
    showDeleteConfirm.value = null
    await fetchPromotions()
  } catch (e) {
    console.error('Delete error:', e)
  } finally {
    deleting.value = false
  }
}

onMounted(() => {
  fetchPromotions()
  fetchProducts()
})
</script>

<template>
  <div class="p-6 md:p-8 max-w-6xl">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="font-display font-bold text-2xl text-white mb-1">Promotions</h1>
        <p class="text-sm text-white/40">{{ promotions.length }} promotion{{ promotions.length !== 1 ? 's' : '' }} configuree{{ promotions.length !== 1 ? 's' : '' }}</p>
      </div>
      <button @click="openCreateModal" class="btn-primary !py-2.5 !px-5 !text-sm">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Creer une promo
      </button>
    </div>

    <!-- Loading skeletons -->
    <div v-if="loading" class="space-y-3">
      <div v-for="n in 4" :key="n" class="glass-card rounded-2xl p-5 animate-pulse">
        <div class="flex flex-col md:flex-row md:items-center gap-4">
          <div class="flex-1 space-y-3">
            <div class="flex items-center gap-3">
              <div class="h-6 w-20 bg-white/10 rounded-lg"></div>
              <div class="h-5 w-40 bg-white/10 rounded"></div>
              <div class="h-5 w-16 bg-white/10 rounded-lg"></div>
            </div>
            <div class="flex items-center gap-4">
              <div class="h-4 w-24 bg-white/5 rounded"></div>
              <div class="h-4 w-32 bg-white/5 rounded"></div>
              <div class="h-4 w-20 bg-white/5 rounded"></div>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <div class="h-6 w-11 bg-white/10 rounded-full"></div>
            <div class="h-8 w-8 bg-white/5 rounded-lg"></div>
            <div class="h-8 w-8 bg-white/5 rounded-lg"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else-if="promotions.length === 0" class="flex flex-col items-center justify-center py-20">
      <div class="w-20 h-20 rounded-2xl bg-white/5 flex items-center justify-center mb-6">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="text-white/20" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
          <line x1="7" y1="7" x2="7.01" y2="7"/>
        </svg>
      </div>
      <h3 class="font-display font-semibold text-lg text-white mb-2">Aucune promotion</h3>
      <p class="text-sm text-white/40 mb-6 text-center max-w-xs">Creez votre premiere promotion pour booster vos ventes.</p>
      <button @click="openCreateModal" class="btn-primary !py-2.5 !px-5 !text-sm">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Creer une promotion
      </button>
    </div>

    <!-- Promotions list -->
    <div v-else class="space-y-3">
      <div
        v-for="promo in promotions"
        :key="promo.id"
        class="glass-card rounded-2xl p-5 transition-all hover:bg-white/[0.04]"
      >
        <div class="flex flex-col md:flex-row md:items-center gap-4">
          <!-- Left: info -->
          <div class="flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2 mb-2">
              <!-- Type badge -->
              <span
                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[11px] font-bold uppercase tracking-wide"
                :style="{ backgroundColor: getTypeInfo(promo.type).color + '15', color: getTypeInfo(promo.type).color }"
              >
                <!-- Percent icon -->
                <svg v-if="promo.type === 'discount'" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>
                <!-- Image icon -->
                <svg v-else-if="promo.type === 'banner'" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                <!-- Zap icon -->
                <svg v-else-if="promo.type === 'flash_sale'" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                <!-- Truck icon -->
                <svg v-else width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                {{ getTypeInfo(promo.type).label }}
              </span>

              <!-- Title -->
              <h3 class="font-display font-semibold text-white text-sm truncate">{{ promo.title }}</h3>

              <!-- Status -->
              <span
                class="px-2 py-0.5 rounded-lg text-[10px] font-bold uppercase tracking-wide"
                :class="getPromoStatus(promo).class"
              >
                {{ getPromoStatus(promo).label }}
              </span>
            </div>

            <!-- Details row -->
            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-white/40">
              <!-- Discount info -->
              <span v-if="promo.discount_percent" class="flex items-center gap-1">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>
                -{{ promo.discount_percent }}%
              </span>
              <span v-if="promo.discount_amount" class="flex items-center gap-1">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                -{{ new Intl.NumberFormat('fr-FR').format(promo.discount_amount) }} F
              </span>

              <!-- Date range -->
              <span v-if="promo.starts_at || promo.ends_at" class="flex items-center gap-1">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                {{ formatDate(promo.starts_at) }} &rarr; {{ formatDate(promo.ends_at) }}
              </span>

              <!-- Products count -->
              <span class="flex items-center gap-1">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
                {{ (promo.product_ids || promo.products || []).length }} produit{{ (promo.product_ids || promo.products || []).length !== 1 ? 's' : '' }}
              </span>

              <!-- Badge preview -->
              <span
                v-if="promo.badge_text"
                class="px-2 py-0.5 rounded text-[10px] font-bold text-white"
                :style="{ backgroundColor: promo.badge_color || '#8B5CF6' }"
              >
                {{ promo.badge_text }}
              </span>
            </div>
          </div>

          <!-- Right: actions -->
          <div class="flex items-center gap-2 shrink-0">
            <!-- Active toggle -->
            <button
              type="button"
              @click="toggleActive(promo)"
              :disabled="togglingId === promo.id"
              class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 disabled:opacity-50"
              :class="promo.active ? 'bg-emerald-500' : 'bg-white/10'"
            >
              <span
                class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200"
                :class="promo.active ? 'translate-x-6' : 'translate-x-1'"
              />
            </button>

            <!-- Edit -->
            <button
              @click="openEditModal(promo)"
              class="w-9 h-9 rounded-xl bg-white/5 flex items-center justify-center text-white/40 hover:text-white hover:bg-white/10 transition-all"
            >
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </button>

            <!-- Delete -->
            <button
              @click="confirmDelete(promo)"
              class="w-9 h-9 rounded-xl bg-white/5 flex items-center justify-center text-white/40 hover:text-red-400 hover:bg-red-400/10 transition-all"
            >
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <teleport to="body">
      <transition
        enter-active-class="transition-all duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-all duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-end md:items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="showModal = false" />
          <div class="relative w-full max-w-lg glass-light rounded-t-3xl md:rounded-3xl p-6 md:p-8 animate-slide-up max-h-[90vh] overflow-y-auto">
            <!-- Modal header -->
            <div class="flex items-center justify-between mb-6">
              <h2 class="font-display font-bold text-xl text-white">
                {{ editingPromo ? 'Modifier la promotion' : 'Nouvelle promotion' }}
              </h2>
              <button @click="showModal = false" class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-white/40 hover:text-white hover:bg-white/10 transition-all">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </div>

            <form @submit.prevent="submitForm" class="space-y-5">
              <!-- Type selector -->
              <div>
                <label class="block text-xs font-display font-medium text-white/50 mb-2 uppercase tracking-wider">Type de promotion</label>
                <div class="grid grid-cols-2 gap-2">
                  <button
                    v-for="t in promoTypes"
                    :key="t.key"
                    type="button"
                    @click="form.type = t.key"
                    class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl border text-sm font-medium transition-all"
                    :class="form.type === t.key ? 'border-white/20 bg-white/10 text-white' : 'border-white/5 bg-white/[0.02] text-white/40 hover:bg-white/5'"
                  >
                    <!-- Percent -->
                    <svg v-if="t.key === 'discount'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :style="{ color: t.color }"><line x1="19" y1="5" x2="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>
                    <!-- Image -->
                    <svg v-else-if="t.key === 'banner'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :style="{ color: t.color }"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    <!-- Zap -->
                    <svg v-else-if="t.key === 'flash_sale'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :style="{ color: t.color }"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                    <!-- Truck -->
                    <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :style="{ color: t.color }"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                    {{ t.label }}
                  </button>
                </div>
              </div>

              <!-- Title -->
              <div>
                <label class="block text-xs font-display font-medium text-white/50 mb-1.5 uppercase tracking-wider">Titre</label>
                <input
                  v-model="form.title"
                  type="text"
                  placeholder="Ex: Soldes d'ete -30%"
                  class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-white/20 text-sm focus:outline-none focus:border-brand/50 focus:ring-1 focus:ring-brand/20 transition-all"
                />
                <p v-if="fieldErrors.title" class="text-xs text-red-400 mt-1">{{ fieldErrors.title[0] }}</p>
              </div>

              <!-- Description -->
              <div>
                <label class="block text-xs font-display font-medium text-white/50 mb-1.5 uppercase tracking-wider">Description</label>
                <textarea
                  v-model="form.description"
                  placeholder="Decrivez votre promotion..."
                  rows="2"
                  class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-white/20 text-sm focus:outline-none focus:border-brand/50 focus:ring-1 focus:ring-brand/20 transition-all resize-none"
                />
                <p v-if="fieldErrors.description" class="text-xs text-red-400 mt-1">{{ fieldErrors.description[0] }}</p>
              </div>

              <!-- Banner upload (all types) -->
              <div>
                <label class="block text-xs font-display font-medium text-white/50 mb-1.5 uppercase tracking-wider">Banniere</label>
                <div
                  @click="$refs.bannerInput.click()"
                  class="border-2 border-dashed border-white/10 rounded-xl h-32 flex items-center justify-center hover:border-brand/30 transition-all cursor-pointer overflow-hidden relative"
                >
                  <img v-if="bannerPreview" :src="bannerPreview" class="w-full h-full object-cover" />
                  <div v-else class="flex flex-col items-center">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="text-white/20 mb-2" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/>
                    </svg>
                    <p class="text-xs text-white/30">Cliquer pour ajouter une banniere</p>
                  </div>
                  <input ref="bannerInput" type="file" accept="image/*" class="hidden" @change="onBannerChange" />
                </div>
              </div>

              <!-- Discount fields (conditional) -->
              <div v-if="showsDiscount" class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-display font-medium text-white/50 mb-1.5 uppercase tracking-wider">Reduction (%)</label>
                  <input
                    v-model="form.discount_percent"
                    type="number"
                    placeholder="30"
                    min="0"
                    max="100"
                    class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-white/20 text-sm focus:outline-none focus:border-brand/50 focus:ring-1 focus:ring-brand/20 transition-all"
                  />
                  <p v-if="fieldErrors.discount_percent" class="text-xs text-red-400 mt-1">{{ fieldErrors.discount_percent[0] }}</p>
                </div>
                <div>
                  <label class="block text-xs font-display font-medium text-white/50 mb-1.5 uppercase tracking-wider">Montant (F)</label>
                  <input
                    v-model="form.discount_amount"
                    type="number"
                    placeholder="5000"
                    min="0"
                    class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-white/20 text-sm focus:outline-none focus:border-brand/50 focus:ring-1 focus:ring-brand/20 transition-all"
                  />
                  <p v-if="fieldErrors.discount_amount" class="text-xs text-red-400 mt-1">{{ fieldErrors.discount_amount[0] }}</p>
                </div>
              </div>

              <!-- Badge text + color -->
              <div>
                <label class="block text-xs font-display font-medium text-white/50 mb-1.5 uppercase tracking-wider">Badge</label>
                <div class="flex gap-3">
                  <input
                    v-model="form.badge_text"
                    type="text"
                    placeholder="Ex: -30%"
                    class="flex-1 px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white placeholder-white/20 text-sm focus:outline-none focus:border-brand/50 focus:ring-1 focus:ring-brand/20 transition-all"
                  />
                  <div class="flex items-center gap-1.5 px-2 py-1 rounded-xl bg-white/5 border border-white/10">
                    <button
                      v-for="c in badgeColors"
                      :key="c"
                      type="button"
                      @click="form.badge_color = c"
                      class="w-5 h-5 rounded-full transition-all shrink-0"
                      :style="{ backgroundColor: c }"
                      :class="form.badge_color === c ? 'ring-2 ring-white ring-offset-1 ring-offset-transparent scale-110' : 'opacity-60 hover:opacity-100'"
                    />
                  </div>
                </div>
                <!-- Badge preview -->
                <div v-if="form.badge_text" class="mt-2">
                  <span class="text-xs text-white/30 mr-2">Apercu:</span>
                  <span
                    class="px-2.5 py-1 rounded-md text-[11px] font-bold text-white"
                    :style="{ backgroundColor: form.badge_color }"
                  >
                    {{ form.badge_text }}
                  </span>
                </div>
              </div>

              <!-- Product selector -->
              <div>
                <label class="block text-xs font-display font-medium text-white/50 mb-1.5 uppercase tracking-wider">
                  Produits concernes
                  <span v-if="form.product_ids.length" class="text-white/30 normal-case">({{ form.product_ids.length }} selectionne{{ form.product_ids.length !== 1 ? 's' : '' }})</span>
                </label>
                <!-- Search -->
                <input
                  v-model="productSearch"
                  type="text"
                  placeholder="Rechercher un produit..."
                  class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-white/20 text-sm focus:outline-none focus:border-brand/50 focus:ring-1 focus:ring-brand/20 transition-all mb-2"
                />
                <!-- Products list -->
                <div class="max-h-40 overflow-y-auto rounded-xl bg-white/[0.02] border border-white/5 divide-y divide-white/5">
                  <div v-if="products.length === 0" class="px-4 py-3 text-xs text-white/30 text-center">
                    Aucun produit disponible
                  </div>
                  <label
                    v-for="product in filteredProducts"
                    :key="product.id"
                    class="flex items-center gap-3 px-4 py-2.5 cursor-pointer hover:bg-white/5 transition-colors"
                  >
                    <input
                      type="checkbox"
                      :checked="form.product_ids.includes(product.id)"
                      @change="toggleProductId(product.id)"
                      class="promo-checkbox"
                    />
                    <span class="text-sm text-white/70 truncate flex-1">{{ product.name }}</span>
                    <span v-if="product.price" class="text-xs text-white/30 shrink-0">{{ new Intl.NumberFormat('fr-FR').format(product.price) }} F</span>
                  </label>
                </div>
              </div>

              <!-- Date pickers -->
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block text-xs font-display font-medium text-white/50 mb-1.5 uppercase tracking-wider">Debut</label>
                  <input
                    v-model="form.starts_at"
                    type="datetime-local"
                    class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm focus:outline-none focus:border-brand/50 focus:ring-1 focus:ring-brand/20 transition-all"
                  />
                </div>
                <div>
                  <label class="block text-xs font-display font-medium text-white/50 mb-1.5 uppercase tracking-wider">Fin</label>
                  <input
                    v-model="form.ends_at"
                    type="datetime-local"
                    class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white text-sm focus:outline-none focus:border-brand/50 focus:ring-1 focus:ring-brand/20 transition-all"
                  />
                </div>
              </div>

              <!-- Active toggle -->
              <div class="flex items-center justify-between">
                <label class="text-xs font-display font-medium text-white/50 uppercase tracking-wider">Promotion active</label>
                <button
                  type="button"
                  @click="form.active = !form.active"
                  class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200"
                  :class="form.active ? 'bg-emerald-500' : 'bg-white/10'"
                >
                  <span
                    class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200"
                    :class="form.active ? 'translate-x-6' : 'translate-x-1'"
                  />
                </button>
              </div>

              <!-- Submit button -->
              <button
                type="submit"
                :disabled="submitting"
                class="btn-primary !w-full !justify-center !mt-6 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <svg v-if="submitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                {{ submitting ? 'En cours...' : (editingPromo ? 'Enregistrer les modifications' : 'Creer la promotion') }}
              </button>
            </form>
          </div>
        </div>
      </transition>
    </teleport>

    <!-- Delete confirmation modal -->
    <teleport to="body">
      <transition
        enter-active-class="transition-all duration-200"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-all duration-150"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="showDeleteConfirm" class="fixed inset-0 z-[60] flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="cancelDelete" />
          <div class="relative w-full max-w-sm glass-light rounded-2xl p-6 text-center">
            <!-- Warning icon -->
            <div class="w-14 h-14 rounded-2xl bg-red-400/10 flex items-center justify-center mx-auto mb-4">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="text-red-400" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
              </svg>
            </div>
            <h3 class="font-display font-bold text-lg text-white mb-2">Supprimer cette promotion ?</h3>
            <p class="text-sm text-white/40 mb-6">
              La promotion <span class="text-white/70 font-medium">"{{ showDeleteConfirm.title }}"</span> sera definitivement supprimee. Cette action est irreversible.
            </p>
            <div class="flex gap-3">
              <button
                @click="cancelDelete"
                class="flex-1 px-4 py-2.5 rounded-xl text-sm font-medium bg-white/5 text-white/60 hover:bg-white/10 hover:text-white transition-all border border-white/10"
              >
                Annuler
              </button>
              <button
                @click="deletePromo"
                :disabled="deleting"
                class="flex-1 px-4 py-2.5 rounded-xl text-sm font-medium bg-red-500/20 text-red-400 hover:bg-red-500/30 transition-all border border-red-500/20 disabled:opacity-50"
              >
                <svg v-if="deleting" class="animate-spin -ml-1 mr-1.5 h-3.5 w-3.5 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
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
/* Status badges */
.promo-status-active {
  background-color: rgba(16, 185, 129, 0.1);
  color: #10b981;
}
.promo-status-inactive {
  background-color: rgba(255, 255, 255, 0.05);
  color: rgba(255, 255, 255, 0.4);
}
.promo-status-expired {
  background-color: rgba(239, 68, 68, 0.1);
  color: #ef4444;
}
.promo-status-scheduled {
  background-color: rgba(59, 130, 246, 0.1);
  color: #3b82f6;
}

/* Custom checkbox */
.promo-checkbox {
  appearance: none;
  width: 16px;
  height: 16px;
  border: 1.5px solid rgba(255, 255, 255, 0.15);
  border-radius: 4px;
  background: rgba(255, 255, 255, 0.03);
  cursor: pointer;
  position: relative;
  flex-shrink: 0;
  transition: all 0.15s ease;
}
.promo-checkbox:checked {
  background: var(--color-brand, #8B5CF6);
  border-color: var(--color-brand, #8B5CF6);
}
.promo-checkbox:checked::after {
  content: '';
  position: absolute;
  left: 4px;
  top: 1px;
  width: 5px;
  height: 9px;
  border: solid white;
  border-width: 0 2px 2px 0;
  transform: rotate(45deg);
}
.promo-checkbox:hover {
  border-color: rgba(255, 255, 255, 0.3);
}

/* Date input styling for dark theme */
input[type="datetime-local"]::-webkit-calendar-picker-indicator {
  filter: invert(0.6);
  cursor: pointer;
}

/* Scrollbar styling for product list */
.max-h-40::-webkit-scrollbar {
  width: 4px;
}
.max-h-40::-webkit-scrollbar-track {
  background: transparent;
}
.max-h-40::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 2px;
}
.max-h-40::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.2);
}
</style>
