<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '../../composables/useAuth'
import { usePlan } from '../../composables/usePlan'
import { useCategories } from '../../composables/useCategories'

const route = useRoute()
const router = useRouter()
const { api } = useAuth()
const { imageLimit, canUseFeature, isPro, canAddProduct, productLimit, productCount } = usePlan()
const { categoryList, getSubcategories } = useCategories()

const isEdit = computed(() => !!route.params.id)
const step = ref(1)
const totalSteps = 4
const loading = ref(false)
const saving = ref(false)
const error = ref('')
const fieldErrors = ref({})

const form = ref({
  name: '',
  description: '',
  long_description: '',
  category: '',
  subcategory: '',
  product_type: 'physical',
  price: '',
  compare_price: '',
  stock: '0',
  active: true,
  is_preorder: false,
  preorder_available_at: '',
  preorder_deposit_percent: '',
  digital_download_limit: '5',
})

const existingImages = ref([])
const newImageFiles = ref([])
const newImagePreviews = ref([])
const digitalFile = ref(null)
const digitalFileName = ref('')

const subcategories = computed(() => getSubcategories(form.value.category))

const totalImageCount = computed(() => existingImages.value.length + newImageFiles.value.length)
const canAddMoreImages = computed(() => totalImageCount.value < imageLimit.value)

// Load product for edit
onMounted(async () => {
  if (isEdit.value) {
    loading.value = true
    try {
      const data = await api('/api/products')
      const products = data.products || data
      const product = (Array.isArray(products) ? products : []).find(p => p.id == route.params.id)
      if (product) {
        form.value = {
          name: product.name || '',
          description: product.description || '',
          long_description: product.long_description || '',
          category: product.category || '',
          subcategory: product.subcategory || '',
          product_type: product.product_type || 'physical',
          price: product.price || '',
          compare_price: product.compare_price || '',
          stock: product.stock ?? '0',
          active: product.active ?? true,
          is_preorder: product.is_preorder || false,
          preorder_available_at: product.preorder_available_at ? product.preorder_available_at.split('T')[0] : '',
          preorder_deposit_percent: product.preorder_deposit_percent || '',
          digital_download_limit: product.digital_download_limit || '5',
        }
        existingImages.value = product.images || []
        if (product.digital_file_path) digitalFileName.value = 'Fichier existant'
      }
    } catch (e) {
      error.value = 'Impossible de charger le produit'
    } finally {
      loading.value = false
    }
  }
})

// Reset subcategory when category changes
watch(() => form.value.category, () => {
  form.value.subcategory = ''
})

const onImageSelect = (e) => {
  const files = Array.from(e.target.files || [])
  const remaining = imageLimit.value - totalImageCount.value
  const toAdd = files.slice(0, remaining)
  toAdd.forEach(file => {
    newImageFiles.value.push(file)
    const reader = new FileReader()
    reader.onload = (ev) => newImagePreviews.value.push(ev.target.result)
    reader.readAsDataURL(file)
  })
  e.target.value = ''
}

const removeExistingImage = (index) => {
  existingImages.value.splice(index, 1)
}

const removeNewImage = (index) => {
  newImageFiles.value.splice(index, 1)
  newImagePreviews.value.splice(index, 1)
}

const onDigitalFileSelect = (e) => {
  const file = e.target.files?.[0]
  if (file) {
    digitalFile.value = file
    digitalFileName.value = file.name
  }
}

const nextStep = () => {
  if (step.value < totalSteps) step.value++
}

const prevStep = () => {
  if (step.value > 1) step.value--
}

const stepValid = computed(() => {
  if (step.value === 1) return form.value.name.trim() && form.value.product_type
  if (step.value === 2) return true
  if (step.value === 3) return form.value.price !== '' && Number(form.value.price) >= 0
  return true
})

const submit = async () => {
  saving.value = true
  error.value = ''
  fieldErrors.value = {}

  try {
    const formData = new FormData()

    // Append all form fields
    formData.append('name', form.value.name)
    formData.append('description', form.value.description)
    formData.append('long_description', form.value.long_description)
    formData.append('category', form.value.category)
    formData.append('subcategory', form.value.subcategory)
    formData.append('product_type', form.value.product_type)
    formData.append('price', form.value.price)
    if (form.value.compare_price) formData.append('compare_price', form.value.compare_price)
    formData.append('stock', form.value.stock || '0')
    formData.append('active', form.value.active ? '1' : '0')
    formData.append('is_preorder', form.value.is_preorder ? '1' : '0')
    if (form.value.is_preorder) {
      if (form.value.preorder_available_at) formData.append('preorder_available_at', form.value.preorder_available_at)
      if (form.value.preorder_deposit_percent) formData.append('preorder_deposit_percent', form.value.preorder_deposit_percent)
    }
    if (form.value.product_type === 'digital' && form.value.digital_download_limit) {
      formData.append('digital_download_limit', form.value.digital_download_limit)
    }

    // Images
    if (isEdit.value) {
      formData.append('existing_images', JSON.stringify(existingImages.value))
      formData.append('_method', 'PUT')
    }
    newImageFiles.value.forEach(file => {
      formData.append('images[]', file)
    })

    // Digital file
    if (digitalFile.value) {
      formData.append('digital_file', digitalFile.value)
    }

    const url = isEdit.value ? `/api/products/${route.params.id}` : '/api/products'
    const data = await api(url, {
      method: 'POST',
      body: formData,
    })

    router.push('/dashboard/products')
  } catch (e) {
    if (e.errors) {
      fieldErrors.value = e.errors
      error.value = e.message || 'Veuillez corriger les erreurs'
    } else {
      error.value = e.message || 'Erreur réseau'
    }
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <div class="max-w-3xl mx-auto px-4 md:px-8 py-8">
    <!-- Header -->
    <div class="flex items-center gap-3 mb-8">
      <button @click="router.push('/dashboard/products')" class="w-9 h-9 flex items-center justify-center rounded-xl border transition-colors hover:bg-white/5" :style="{ borderColor: 'var(--border-default)', color: 'var(--text-muted)' }">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
      </button>
      <div>
        <h1 class="font-display font-bold text-xl" :style="{ color: 'var(--text-primary)' }">
          {{ isEdit ? 'Modifier le produit' : 'Nouveau produit' }}
        </h1>
        <p class="text-xs" :style="{ color: 'var(--text-muted)' }">Étape {{ step }} sur {{ totalSteps }}</p>
      </div>
    </div>

    <!-- Plan limit warning -->
    <div v-if="!isEdit && !canAddProduct" class="mb-6 p-4 rounded-xl bg-amber-500/10 border border-amber-500/20">
      <p class="text-sm text-amber-400 font-medium">Limite de {{ productLimit === Infinity ? '' : productLimit }} produits atteinte ({{ productCount }}/{{ productLimit }})</p>
      <router-link to="/dashboard/upgrade" class="text-xs text-brand hover:underline">Passer au plan supérieur</router-link>
    </div>

    <!-- Progress bar -->
    <div class="flex gap-2 mb-8">
      <div
        v-for="s in totalSteps"
        :key="s"
        class="h-1 flex-1 rounded-full transition-all duration-300"
        :class="s <= step ? 'bg-brand' : 'bg-white/10'"
      />
    </div>

    <!-- Error -->
    <div v-if="error" class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">
      {{ error }}
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-20" :style="{ color: 'var(--text-muted)' }">
      Chargement...
    </div>

    <template v-else>
      <!-- Step 1: Info -->
      <div v-show="step === 1" class="space-y-5">
        <h2 class="font-display font-semibold text-lg" :style="{ color: 'var(--text-primary)' }">Informations</h2>

        <!-- Product type -->
        <div>
          <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-secondary)' }">Type de produit</label>
          <div class="flex gap-3">
            <button
              v-for="t in [{id: 'physical', label: 'Physique', icon: 'box'}, {id: 'digital', label: 'Digital', icon: 'download'}]"
              :key="t.id"
              @click="form.product_type = t.id"
              class="flex-1 flex items-center gap-3 p-4 rounded-xl border-2 transition-all"
              :class="form.product_type === t.id ? 'border-brand bg-brand/5' : ''"
              :style="form.product_type !== t.id ? { borderColor: 'var(--border-default)', background: 'var(--bg-secondary)' } : {}"
            >
              <svg v-if="t.icon === 'box'" width="20" height="20" viewBox="0 0 24 24" fill="none" :stroke="form.product_type === t.id ? '#FF6B2C' : 'currentColor'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
              <svg v-if="t.icon === 'download'" width="20" height="20" viewBox="0 0 24 24" fill="none" :stroke="form.product_type === t.id ? '#FF6B2C' : 'currentColor'" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              <div>
                <p class="text-sm font-semibold" :style="{ color: form.product_type === t.id ? '#FF6B2C' : 'var(--text-primary)' }">{{ t.label }}</p>
              </div>
            </button>
          </div>
        </div>

        <!-- Name -->
        <div>
          <label class="block text-sm font-medium mb-1.5" :style="{ color: 'var(--text-secondary)' }">Nom du produit *</label>
          <input v-model="form.name" type="text" maxlength="255" class="w-full px-4 py-3 rounded-xl border text-sm outline-none transition-colors focus:border-brand" :style="{ background: 'var(--bg-secondary)', borderColor: fieldErrors.name ? '#ef4444' : 'var(--border-default)', color: 'var(--text-primary)' }" placeholder="Ex: Robe en wax Ankara" />
          <p v-if="fieldErrors.name" class="text-xs text-red-400 mt-1">{{ fieldErrors.name[0] }}</p>
        </div>

        <!-- Category -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1.5" :style="{ color: 'var(--text-secondary)' }">Catégorie</label>
            <select v-model="form.category" class="w-full px-4 py-3 rounded-xl border text-sm outline-none transition-colors focus:border-brand" :style="{ background: 'var(--bg-secondary)', borderColor: 'var(--border-default)', color: 'var(--text-primary)' }">
              <option value="">Sélectionner</option>
              <option v-for="cat in categoryList" :key="cat" :value="cat">{{ cat }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1.5" :style="{ color: 'var(--text-secondary)' }">Sous-catégorie</label>
            <select v-model="form.subcategory" :disabled="!subcategories.length" class="w-full px-4 py-3 rounded-xl border text-sm outline-none transition-colors focus:border-brand disabled:opacity-50" :style="{ background: 'var(--bg-secondary)', borderColor: 'var(--border-default)', color: 'var(--text-primary)' }">
              <option value="">Sélectionner</option>
              <option v-for="sub in subcategories" :key="sub" :value="sub">{{ sub }}</option>
            </select>
          </div>
        </div>

        <!-- Description -->
        <div>
          <label class="block text-sm font-medium mb-1.5" :style="{ color: 'var(--text-secondary)' }">Description courte</label>
          <textarea v-model="form.description" rows="3" maxlength="1000" class="w-full px-4 py-3 rounded-xl border text-sm outline-none transition-colors focus:border-brand resize-none" :style="{ background: 'var(--bg-secondary)', borderColor: 'var(--border-default)', color: 'var(--text-primary)' }" placeholder="Description visible dans la liste" />
        </div>

        <!-- Long description -->
        <div>
          <label class="block text-sm font-medium mb-1.5" :style="{ color: 'var(--text-secondary)' }">Description détaillée</label>
          <textarea v-model="form.long_description" rows="5" maxlength="5000" class="w-full px-4 py-3 rounded-xl border text-sm outline-none transition-colors focus:border-brand resize-none" :style="{ background: 'var(--bg-secondary)', borderColor: 'var(--border-default)', color: 'var(--text-primary)' }" placeholder="Description complète visible sur la page produit" />
        </div>
      </div>

      <!-- Step 2: Images -->
      <div v-show="step === 2" class="space-y-5">
        <h2 class="font-display font-semibold text-lg" :style="{ color: 'var(--text-primary)' }">
          Images
          <span class="text-xs font-normal ml-2" :style="{ color: 'var(--text-muted)' }">{{ totalImageCount }}/{{ imageLimit }} (plan actuel)</span>
        </h2>

        <!-- Image grid -->
        <div class="grid grid-cols-3 md:grid-cols-4 gap-3">
          <!-- Existing images -->
          <div v-for="(img, i) in existingImages" :key="'e'+i" class="relative aspect-square rounded-xl overflow-hidden border" :style="{ borderColor: 'var(--border-default)' }">
            <img :src="'/storage/' + img" class="w-full h-full object-cover" />
            <button @click="removeExistingImage(i)" class="absolute top-1 right-1 w-6 h-6 rounded-full bg-black/60 text-white flex items-center justify-center text-xs hover:bg-red-500 transition-colors">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
          </div>

          <!-- New image previews -->
          <div v-for="(preview, i) in newImagePreviews" :key="'n'+i" class="relative aspect-square rounded-xl overflow-hidden border border-brand/30">
            <img :src="preview" class="w-full h-full object-cover" />
            <button @click="removeNewImage(i)" class="absolute top-1 right-1 w-6 h-6 rounded-full bg-black/60 text-white flex items-center justify-center text-xs hover:bg-red-500 transition-colors">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <span class="absolute bottom-1 left-1 px-1.5 py-0.5 rounded text-[10px] bg-brand/80 text-white">Nouveau</span>
          </div>

          <!-- Add button -->
          <label v-if="canAddMoreImages" class="aspect-square rounded-xl border-2 border-dashed flex flex-col items-center justify-center cursor-pointer transition-colors hover:border-brand/50" :style="{ borderColor: 'var(--border-default)', color: 'var(--text-faint)' }">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            <span class="text-xs mt-1">Ajouter</span>
            <input type="file" accept="image/*" multiple class="hidden" @change="onImageSelect" />
          </label>
        </div>

        <!-- Digital file upload -->
        <div v-if="form.product_type === 'digital'" class="mt-6">
          <label class="block text-sm font-medium mb-2" :style="{ color: 'var(--text-secondary)' }">Fichier digital</label>
          <label class="flex items-center gap-3 p-4 rounded-xl border-2 border-dashed cursor-pointer transition-colors hover:border-brand/50" :style="{ borderColor: 'var(--border-default)', background: 'var(--bg-secondary)' }">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" :style="{ color: 'var(--text-muted)' }"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            <div>
              <p class="text-sm font-medium" :style="{ color: 'var(--text-primary)' }">{{ digitalFileName || 'Choisir un fichier' }}</p>
              <p class="text-xs" :style="{ color: 'var(--text-muted)' }">PDF, ZIP, MP3, etc. (max 100 Mo)</p>
            </div>
            <input type="file" class="hidden" @change="onDigitalFileSelect" />
          </label>
        </div>
      </div>

      <!-- Step 3: Price & Stock -->
      <div v-show="step === 3" class="space-y-5">
        <h2 class="font-display font-semibold text-lg" :style="{ color: 'var(--text-primary)' }">Prix & Stock</h2>

        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1.5" :style="{ color: 'var(--text-secondary)' }">Prix (F CFA) *</label>
            <input v-model="form.price" type="number" min="0" class="w-full px-4 py-3 rounded-xl border text-sm outline-none transition-colors focus:border-brand" :style="{ background: 'var(--bg-secondary)', borderColor: fieldErrors.price ? '#ef4444' : 'var(--border-default)', color: 'var(--text-primary)' }" placeholder="5000" />
            <p v-if="fieldErrors.price" class="text-xs text-red-400 mt-1">{{ fieldErrors.price[0] }}</p>
          </div>
          <div>
            <label class="block text-sm font-medium mb-1.5" :style="{ color: 'var(--text-secondary)' }">
              Ancien prix
              <span class="text-xs font-normal" :style="{ color: 'var(--text-faint)' }">(barré)</span>
            </label>
            <input v-model="form.compare_price" type="number" min="0" class="w-full px-4 py-3 rounded-xl border text-sm outline-none transition-colors focus:border-brand" :style="{ background: 'var(--bg-secondary)', borderColor: 'var(--border-default)', color: 'var(--text-primary)' }" placeholder="8000" />
          </div>
        </div>

        <div v-if="form.product_type === 'physical'" class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium mb-1.5" :style="{ color: 'var(--text-secondary)' }">Stock</label>
            <input v-model="form.stock" type="number" min="0" class="w-full px-4 py-3 rounded-xl border text-sm outline-none transition-colors focus:border-brand" :style="{ background: 'var(--bg-secondary)', borderColor: 'var(--border-default)', color: 'var(--text-primary)' }" placeholder="0" />
          </div>
        </div>

        <div v-if="form.product_type === 'digital'">
          <label class="block text-sm font-medium mb-1.5" :style="{ color: 'var(--text-secondary)' }">Limite de téléchargements</label>
          <input v-model="form.digital_download_limit" type="number" min="1" class="w-full px-4 py-3 rounded-xl border text-sm outline-none transition-colors focus:border-brand" :style="{ background: 'var(--bg-secondary)', borderColor: 'var(--border-default)', color: 'var(--text-primary)' }" placeholder="5" />
          <p class="text-xs mt-1" :style="{ color: 'var(--text-faint)' }">Nombre de fois qu'un client peut télécharger le fichier</p>
        </div>

        <!-- Compare price preview -->
        <div v-if="form.price && form.compare_price && Number(form.compare_price) > Number(form.price)" class="p-3 rounded-xl border" :style="{ borderColor: 'var(--border-default)', background: 'var(--bg-secondary)' }">
          <p class="text-xs" :style="{ color: 'var(--text-muted)' }">Aperçu prix :</p>
          <div class="flex items-baseline gap-2 mt-1">
            <span class="font-bold text-lg" :style="{ color: 'var(--text-primary)' }">{{ Number(form.price).toLocaleString('fr') }} F</span>
            <span class="text-sm line-through" :style="{ color: 'var(--text-faint)' }">{{ Number(form.compare_price).toLocaleString('fr') }} F</span>
            <span class="text-xs font-semibold text-emerald-400">
              -{{ Math.round((1 - Number(form.price) / Number(form.compare_price)) * 100) }}%
            </span>
          </div>
        </div>
      </div>

      <!-- Step 4: Options -->
      <div v-show="step === 4" class="space-y-5">
        <h2 class="font-display font-semibold text-lg" :style="{ color: 'var(--text-primary)' }">Options</h2>

        <!-- Active toggle -->
        <div class="flex items-center justify-between p-4 rounded-xl border" :style="{ borderColor: 'var(--border-default)', background: 'var(--bg-secondary)' }">
          <div>
            <p class="text-sm font-medium" :style="{ color: 'var(--text-primary)' }">Produit actif</p>
            <p class="text-xs" :style="{ color: 'var(--text-muted)' }">Visible dans votre boutique</p>
          </div>
          <button @click="form.active = !form.active" class="relative w-11 h-6 rounded-full transition-colors" :class="form.active ? 'bg-brand' : 'bg-white/10'">
            <span class="absolute top-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform" :class="form.active ? 'translate-x-5' : 'translate-x-0.5'" />
          </button>
        </div>

        <!-- Preorder (Pro) -->
        <div class="p-4 rounded-xl border" :style="{ borderColor: 'var(--border-default)', background: 'var(--bg-secondary)', opacity: canUseFeature('preorders') ? 1 : 0.5 }">
          <div class="flex items-center justify-between mb-3">
            <div>
              <p class="text-sm font-medium" :style="{ color: 'var(--text-primary)' }">
                Précommande
                <span v-if="!canUseFeature('preorders')" class="ml-1 text-xs text-brand font-normal">Pro</span>
              </p>
              <p class="text-xs" :style="{ color: 'var(--text-muted)' }">Permettre les commandes avant disponibilité</p>
            </div>
            <button
              @click="canUseFeature('preorders') && (form.is_preorder = !form.is_preorder)"
              class="relative w-11 h-6 rounded-full transition-colors"
              :class="form.is_preorder ? 'bg-brand' : 'bg-white/10'"
              :disabled="!canUseFeature('preorders')"
            >
              <span class="absolute top-0.5 w-5 h-5 rounded-full bg-white shadow transition-transform" :class="form.is_preorder ? 'translate-x-5' : 'translate-x-0.5'" />
            </button>
          </div>

          <div v-if="form.is_preorder" class="grid grid-cols-2 gap-3 pt-3 border-t" :style="{ borderColor: 'var(--border-default)' }">
            <div>
              <label class="block text-xs font-medium mb-1" :style="{ color: 'var(--text-muted)' }">Date de disponibilité</label>
              <input v-model="form.preorder_available_at" type="date" class="w-full px-3 py-2 rounded-lg border text-sm outline-none focus:border-brand" :style="{ background: 'var(--bg-primary)', borderColor: 'var(--border-default)', color: 'var(--text-primary)' }" />
            </div>
            <div>
              <label class="block text-xs font-medium mb-1" :style="{ color: 'var(--text-muted)' }">Acompte (%)</label>
              <input v-model="form.preorder_deposit_percent" type="number" min="10" max="100" placeholder="30" class="w-full px-3 py-2 rounded-lg border text-sm outline-none focus:border-brand" :style="{ background: 'var(--bg-primary)', borderColor: 'var(--border-default)', color: 'var(--text-primary)' }" />
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation buttons -->
      <div class="flex items-center justify-between mt-8 pt-6 border-t" :style="{ borderColor: 'var(--border-default)' }">
        <button
          v-if="step > 1"
          @click="prevStep"
          class="px-5 py-2.5 rounded-xl text-sm font-medium border transition-colors hover:bg-white/5"
          :style="{ color: 'var(--text-muted)', borderColor: 'var(--border-default)' }"
        >
          Précédent
        </button>
        <div v-else />

        <button
          v-if="step < totalSteps"
          @click="nextStep"
          :disabled="!stepValid"
          class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all disabled:opacity-40"
          :class="stepValid ? 'bg-brand hover:bg-brand/90' : 'bg-brand/40'"
        >
          Suivant
        </button>
        <button
          v-else
          @click="submit"
          :disabled="saving || (!isEdit && !canAddProduct)"
          class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-brand transition-all disabled:opacity-40 hover:bg-brand/90"
        >
          {{ saving ? 'Enregistrement...' : (isEdit ? 'Modifier' : 'Créer le produit') }}
        </button>
      </div>
    </template>
  </div>
</template>
