<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuth } from '../../composables/useAuth'
import { usePlan } from '../../composables/usePlan'
import { productImageUrl } from '../../composables/useStorage'

const router = useRouter()
const { api } = useAuth()
const { canAddProduct, productLimit, productCount, planLabel } = usePlan()

const products = ref([])
const planInfo = ref(null)
const loading = ref(true)

const formatPrice = (value) => {
  return new Intl.NumberFormat('fr-FR').format(value)
}

const fetchProducts = async () => {
  loading.value = true
  try {
    const data = await api('/api/products')
    products.value = data.products || data
    if (data.plan_info) planInfo.value = data.plan_info
  } catch (e) {
    console.error('Failed to fetch products:', e)
    products.value = []
  } finally {
    loading.value = false
  }
}

const openCreateForm = () => {
  router.push('/dashboard/products/new')
}

const openEditForm = (product) => {
  router.push(`/dashboard/products/${product.id}/edit`)
}

const deleteProduct = async (product) => {
  if (!confirm(`Supprimer "${product.name}" ? Cette action est irréversible.`)) return
  try {
    await api(`/api/products/${product.id}`, { method: 'DELETE' })
    await fetchProducts()
  } catch (e) {
    console.error('Delete error:', e)
  }
}

onMounted(() => {
  fetchProducts()
})
</script>

<template>
  <div class="p-6 md:p-8 max-w-6xl">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="font-display font-bold text-2xl text-white mb-1">Produits</h1>
        <p class="text-sm text-white/40">
          {{ products.length }} produit{{ products.length !== 1 ? 's' : '' }}
          <span v-if="productLimit !== Infinity" class="text-white/20"> · {{ productCount }}/{{ productLimit }} ({{ planLabel }})</span>
        </p>
      </div>
      <button @click="openCreateForm" class="btn-primary !py-2.5 !px-5 !text-sm">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Ajouter
      </button>
    </div>

    <!-- Loading skeletons -->
    <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="i in 6" :key="i" class="glass-card rounded-2xl overflow-hidden animate-pulse">
        <div class="aspect-[4/3] bg-white/5"></div>
        <div class="p-4 space-y-3">
          <div class="h-4 bg-white/10 rounded w-3/4"></div>
          <div class="flex justify-between">
            <div class="h-4 bg-white/10 rounded w-1/3"></div>
            <div class="h-3 bg-white/10 rounded w-1/4"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else-if="products.length === 0" class="flex flex-col items-center justify-center py-20">
      <div class="w-20 h-20 rounded-2xl bg-white/5 flex items-center justify-center mb-6">
        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="text-white/20" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
        </svg>
      </div>
      <h3 class="font-display font-semibold text-lg text-white mb-2">Aucun produit</h3>
      <p class="text-sm text-white/40 mb-6 text-center max-w-xs">Commencez par ajouter votre premier produit à votre vitrine.</p>
      <button @click="openCreateForm" class="btn-primary !py-2.5 !px-5 !text-sm">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Ajouter un produit
      </button>
    </div>

    <!-- Products grid -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div
        v-for="product in products"
        :key="product.id"
        class="glass-card rounded-2xl overflow-hidden group"
      >
        <!-- Image area -->
        <div class="aspect-[4/3] bg-gradient-to-br from-white/5 to-white/[0.02] flex items-center justify-center relative overflow-hidden">
          <img
            v-if="productImageUrl(product)"
            :src="productImageUrl(product)"
            :alt="product.name"
            class="w-full h-full object-cover"
          />
          <svg v-else class="w-10 h-10 text-white/10" fill="currentColor" viewBox="0 0 24 24">
            <path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/>
          </svg>

          <!-- Badges -->
          <div class="absolute top-3 right-3 flex gap-1.5">
            <span v-if="product.product_type === 'digital'" class="px-2 py-1 rounded-lg text-[10px] font-bold bg-purple-500/15 text-purple-400">Digital</span>
            <span v-if="product.is_preorder" class="px-2 py-1 rounded-lg text-[10px] font-bold bg-amber-500/15 text-amber-400">Précommande</span>
            <span
              class="px-2 py-1 rounded-lg text-[10px] font-bold"
              :class="product.active ? 'bg-mint/10 text-mint' : 'bg-white/10 text-white/40'"
            >
              {{ product.active ? 'Actif' : 'Inactif' }}
            </span>
          </div>

          <!-- Hover overlay -->
          <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
            <button @click="openEditForm(product)" class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-white hover:bg-white/20 transition-all">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            </button>
            <button @click="deleteProduct(product)" class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center text-brand-coral hover:bg-brand-coral/20 transition-all">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
            </button>
          </div>
        </div>

        <div class="p-4">
          <h3 class="font-display font-semibold text-sm text-white truncate">{{ product.name }}</h3>
          <div class="flex items-center justify-between mt-2">
            <span class="font-display font-bold text-brand">{{ formatPrice(product.price) }} F</span>
            <span class="text-xs" :class="product.stock === 0 ? 'text-brand-coral' : 'text-white/30'">
              {{ product.stock === 0 ? 'Rupture' : `${product.stock} en stock` }}
            </span>
          </div>
        </div>
      </div>

      <!-- Add product card -->
      <button
        @click="openCreateForm"
        class="border-2 border-dashed border-white/10 rounded-2xl flex flex-col items-center justify-center gap-3 min-h-[250px] hover:border-brand/30 hover:bg-brand/5 transition-all group"
      >
        <div class="w-12 h-12 rounded-xl bg-white/5 flex items-center justify-center group-hover:bg-brand/10 transition-all">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="text-white/30 group-hover:text-brand transition-colors" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
        </div>
        <span class="text-sm text-white/30 group-hover:text-white/50 transition-colors">Ajouter un produit</span>
      </button>
    </div>

  </div>
</template>
