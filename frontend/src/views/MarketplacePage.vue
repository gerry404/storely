<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const shops = ref([])
const featured = ref([])
const cities = ref([])
const categories = ref([])
const loading = ref(true)
const searchQuery = ref('')
const selectedCategory = ref('')
const selectedCity = ref('')
const currentPage = ref(1)
const totalPages = ref(1)
const showFilters = ref(false)

const fetchShops = async (page = 1) => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    params.set('page', page)
    if (searchQuery.value) params.set('q', searchQuery.value)
    if (selectedCategory.value) params.set('category', selectedCategory.value)
    if (selectedCity.value) params.set('city', selectedCity.value)

    const res = await fetch(`/api/marketplace?${params}`)
    const data = await res.json()
    shops.value = data.data || []
    currentPage.value = data.current_page || 1
    totalPages.value = data.last_page || 1
  } catch {
    shops.value = []
  }
  loading.value = false
}

const fetchMeta = async () => {
  try {
    const [featuredRes, citiesRes, categoriesRes] = await Promise.all([
      fetch('/api/marketplace/featured'),
      fetch('/api/marketplace/cities'),
      fetch('/api/marketplace/categories'),
    ])
    featured.value = await featuredRes.json()
    cities.value = await citiesRes.json()
    categories.value = await categoriesRes.json()
  } catch {
    // silent
  }
}

const search = () => {
  currentPage.value = 1
  fetchShops(1)
}

const clearFilters = () => {
  searchQuery.value = ''
  selectedCategory.value = ''
  selectedCity.value = ''
  fetchShops(1)
}

const goToShop = (slug) => {
  router.push(`/${slug}`)
}

const avgRating = (shop) => {
  const r = shop.reviews_avg_rating
  return r ? parseFloat(r).toFixed(1) : null
}

const shopImage = (shop) => {
  if (shop.logo) return `/storage/${shop.logo}`
  return null
}

const formatCount = (n) => {
  if (!n) return '0'
  if (n >= 1000) return `${(n / 1000).toFixed(1)}k`
  return n.toString()
}

onMounted(() => {
  fetchShops()
  fetchMeta()
})

watch([selectedCategory, selectedCity], () => search())
</script>

<template>
  <div class="min-h-screen" style="background: var(--bg-primary); color: var(--text-primary)">
    <!-- Hero -->
    <section class="relative overflow-hidden py-16 md:py-24">
      <div class="absolute inset-0 opacity-[0.03]" style="background: radial-gradient(circle at 30% 50%, #f97316 0%, transparent 50%), radial-gradient(circle at 70% 50%, #8b5cf6 0%, transparent 50%)" />
      <div class="relative max-w-6xl mx-auto px-4 text-center">
        <h1 class="text-3xl md:text-5xl font-bold mb-4">
          Découvrez les meilleures
          <span class="bg-gradient-to-r from-orange-500 to-purple-600 bg-clip-text text-transparent">boutiques</span>
        </h1>
        <p class="text-lg opacity-70 mb-8 max-w-2xl mx-auto">
          Explorez des milliers de boutiques et trouvez exactement ce que vous cherchez
        </p>

        <!-- Search bar -->
        <div class="max-w-2xl mx-auto flex gap-2">
          <div class="flex-1 relative">
            <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
              v-model="searchQuery"
              @keyup.enter="search"
              type="text"
              placeholder="Rechercher une boutique, un produit, une ville..."
              class="w-full pl-12 pr-4 py-3.5 rounded-xl border transition-all focus:outline-none focus:ring-2 focus:ring-orange-500/30"
              style="background: var(--bg-secondary); border-color: var(--border-color); color: var(--text-primary)"
            />
          </div>
          <button
            @click="search"
            class="px-6 py-3.5 rounded-xl font-medium text-white transition-all hover:scale-[1.02]"
            style="background: linear-gradient(135deg, #f97316, #ea580c)"
          >
            Rechercher
          </button>
          <button
            @click="showFilters = !showFilters"
            class="px-4 py-3.5 rounded-xl border transition-all"
            :style="{ background: showFilters ? 'var(--bg-tertiary)' : 'var(--bg-secondary)', borderColor: 'var(--border-color)' }"
          >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
          </button>
        </div>

        <!-- Filters -->
        <transition name="slide">
          <div v-if="showFilters" class="max-w-2xl mx-auto mt-4 flex gap-3 flex-wrap justify-center">
            <select
              v-model="selectedCategory"
              class="px-4 py-2.5 rounded-lg border text-sm"
              style="background: var(--bg-secondary); border-color: var(--border-color); color: var(--text-primary)"
            >
              <option value="">Toutes les catégories</option>
              <option v-for="cat in categories" :key="cat.category" :value="cat.category">
                {{ cat.category }} ({{ cat.shop_count }})
              </option>
            </select>
            <select
              v-model="selectedCity"
              class="px-4 py-2.5 rounded-lg border text-sm"
              style="background: var(--bg-secondary); border-color: var(--border-color); color: var(--text-primary)"
            >
              <option value="">Toutes les villes</option>
              <option v-for="city in cities" :key="city.city" :value="city.city">
                {{ city.city }} ({{ city.shop_count }})
              </option>
            </select>
            <button
              v-if="selectedCategory || selectedCity || searchQuery"
              @click="clearFilters"
              class="px-4 py-2.5 rounded-lg text-sm text-orange-500 hover:text-orange-400 transition-colors"
            >
              Effacer les filtres
            </button>
          </div>
        </transition>
      </div>
    </section>

    <!-- Featured shops -->
    <section v-if="featured.length && !searchQuery && !selectedCategory && !selectedCity" class="max-w-6xl mx-auto px-4 mb-12">
      <h2 class="text-xl font-bold mb-6 flex items-center gap-2">
        <svg class="w-5 h-5 text-orange-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
        Boutiques en vedette
      </h2>
      <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <div
          v-for="shop in featured.slice(0, 8)"
          :key="shop.id"
          @click="goToShop(shop.slug)"
          class="group relative rounded-xl border p-4 cursor-pointer transition-all hover:scale-[1.02] hover:shadow-lg"
          style="background: var(--bg-secondary); border-color: var(--border-color)"
        >
          <div v-if="shop.is_boosted" class="absolute -top-2 -right-2 px-2 py-0.5 text-[10px] font-bold text-white rounded-full bg-gradient-to-r from-orange-500 to-amber-500">
            BOOST
          </div>
          <div class="flex items-center gap-3 mb-3">
            <div v-if="shopImage(shop)" class="w-12 h-12 rounded-full overflow-hidden flex-shrink-0">
              <img :src="shopImage(shop)" class="w-full h-full object-cover" />
            </div>
            <div v-else class="w-12 h-12 rounded-full flex-shrink-0 flex items-center justify-center text-lg font-bold text-white" style="background: linear-gradient(135deg, #f97316, #8b5cf6)">
              {{ shop.name?.[0] }}
            </div>
            <div class="min-w-0">
              <div class="font-semibold text-sm truncate flex items-center gap-1">
                {{ shop.name }}
                <svg v-if="shop.effective_plan === 'pro'" class="w-4 h-4 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
              </div>
              <div class="text-xs opacity-50">{{ shop.city || 'Cameroun' }}</div>
            </div>
          </div>
          <!-- Badges -->
          <div v-if="shop.badges?.length" class="flex gap-1 mb-2 flex-wrap">
            <span
              v-for="badge in shop.badges.slice(0, 3)"
              :key="badge.id"
              class="px-1.5 py-0.5 text-[9px] font-medium rounded-full text-white"
              :style="{ background: badge.color }"
            >
              {{ badge.name }}
            </span>
          </div>
          <div class="flex items-center gap-3 text-xs opacity-60">
            <span>{{ formatCount(shop.products_count) }} produits</span>
            <span v-if="avgRating(shop)" class="flex items-center gap-0.5">
              <svg class="w-3 h-3 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
              {{ avgRating(shop) }}
            </span>
          </div>
        </div>
      </div>
    </section>

    <!-- Results -->
    <section class="max-w-6xl mx-auto px-4 pb-20">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold">
          {{ searchQuery || selectedCategory || selectedCity ? 'Résultats' : 'Toutes les boutiques' }}
        </h2>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <div v-for="i in 8" :key="i" class="rounded-xl border p-4 animate-pulse" style="background: var(--bg-secondary); border-color: var(--border-color)">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 rounded-full" style="background: var(--bg-tertiary)" />
            <div class="flex-1">
              <div class="h-4 rounded w-3/4 mb-2" style="background: var(--bg-tertiary)" />
              <div class="h-3 rounded w-1/2" style="background: var(--bg-tertiary)" />
            </div>
          </div>
        </div>
      </div>

      <!-- Results grid -->
      <div v-else-if="shops.length" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        <div
          v-for="shop in shops"
          :key="shop.id"
          @click="goToShop(shop.slug)"
          class="group relative rounded-xl border p-4 cursor-pointer transition-all hover:scale-[1.02] hover:shadow-lg"
          style="background: var(--bg-secondary); border-color: var(--border-color)"
        >
          <div v-if="shop.is_boosted" class="absolute -top-2 -right-2 px-2 py-0.5 text-[10px] font-bold text-white rounded-full bg-gradient-to-r from-orange-500 to-amber-500">
            BOOST
          </div>
          <div class="flex items-center gap-3 mb-3">
            <div v-if="shopImage(shop)" class="w-12 h-12 rounded-full overflow-hidden flex-shrink-0">
              <img :src="shopImage(shop)" class="w-full h-full object-cover" />
            </div>
            <div v-else class="w-12 h-12 rounded-full flex-shrink-0 flex items-center justify-center text-lg font-bold text-white" style="background: linear-gradient(135deg, #f97316, #8b5cf6)">
              {{ shop.name?.[0] }}
            </div>
            <div class="min-w-0">
              <div class="font-semibold text-sm truncate flex items-center gap-1">
                {{ shop.name }}
                <svg v-if="shop.effective_plan === 'pro'" class="w-4 h-4 text-blue-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>
              </div>
              <div class="text-xs opacity-50">{{ shop.category || '' }} · {{ shop.city || 'Cameroun' }}</div>
            </div>
          </div>
          <div v-if="shop.badges?.length" class="flex gap-1 mb-2 flex-wrap">
            <span
              v-for="badge in shop.badges.slice(0, 3)"
              :key="badge.id"
              class="px-1.5 py-0.5 text-[9px] font-medium rounded-full text-white"
              :style="{ background: badge.color }"
            >
              {{ badge.name }}
            </span>
          </div>
          <div v-if="shop.description" class="text-xs opacity-50 mb-2 line-clamp-2">{{ shop.description }}</div>
          <div class="flex items-center gap-3 text-xs opacity-60">
            <span>{{ formatCount(shop.products_count) }} produits</span>
            <span>{{ formatCount(shop.orders_count) }} ventes</span>
            <span v-if="avgRating(shop)" class="flex items-center gap-0.5">
              <svg class="w-3 h-3 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
              {{ avgRating(shop) }}
            </span>
          </div>
        </div>
      </div>

      <!-- Empty -->
      <div v-else class="text-center py-20 opacity-50">
        <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <p class="text-lg font-medium mb-1">Aucune boutique trouvée</p>
        <p class="text-sm">Essayez de modifier vos critères de recherche</p>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="flex justify-center gap-2 mt-8">
        <button
          v-for="page in totalPages"
          :key="page"
          @click="fetchShops(page)"
          class="w-10 h-10 rounded-lg text-sm font-medium transition-all"
          :class="page === currentPage ? 'text-white' : 'opacity-60 hover:opacity-100'"
          :style="page === currentPage ? { background: 'linear-gradient(135deg, #f97316, #ea580c)' } : { background: 'var(--bg-secondary)' }"
        >
          {{ page }}
        </button>
      </div>
    </section>
  </div>
</template>

<style scoped>
.slide-enter-active, .slide-leave-active {
  transition: all 0.3s ease;
}
.slide-enter-from, .slide-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
