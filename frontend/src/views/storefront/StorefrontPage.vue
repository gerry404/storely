<script setup>
import { ref, onMounted, computed, watch, onUnmounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { productImageUrl, shopLogoUrl, shopBannerUrl, storageUrl, apiUrl } from '../../composables/useStorage'

const route = useRoute()
const router = useRouter()
const slug = computed(() => route.params.slug)

// ─── State ─────────────────────────────────────
const store = ref(null)
const products = ref([])
const reviews = ref([])
const promotions = ref([])
const loading = ref(true)
const error = ref(false)
const selectedProduct = ref(null)
const searchQuery = ref('')
const activeCategory = ref('all')
const mobileMenuOpen = ref(false)
const headerScrolled = ref(false)
const selectedImageIndex = ref(0)

// ─── Promo Carousel ───────────────────────────
const carouselIndex = ref(0)
const carouselPaused = ref(false)
let carouselTimer = null
const carouselRef = ref(null)

function startCarousel() {
  stopCarousel()
  carouselTimer = setInterval(() => {
    if (!carouselPaused.value && activePromos.value.length > 1) {
      carouselIndex.value = (carouselIndex.value + 1) % activePromos.value.length
    }
  }, 5000)
}
function stopCarousel() { if (carouselTimer) { clearInterval(carouselTimer); carouselTimer = null } }
function goToSlide(i) { carouselIndex.value = i; startCarousel() }
function nextSlide() { carouselIndex.value = (carouselIndex.value + 1) % activePromos.value.length; startCarousel() }
function prevSlide() { carouselIndex.value = (carouselIndex.value - 1 + activePromos.value.length) % activePromos.value.length; startCarousel() }

// Touch swipe support
let touchStartX = 0
function onTouchStart(e) { touchStartX = e.touches[0].clientX; carouselPaused.value = true }
function onTouchEnd(e) {
  const diff = touchStartX - e.changedTouches[0].clientX
  if (Math.abs(diff) > 50) { diff > 0 ? nextSlide() : prevSlide() }
  carouselPaused.value = false
}

// ─── Customization ─────────────────────────────
const customization = ref({})
const accent = computed(() => customization.value.primaryColor || '#FF6B2C')
const gridCols = computed(() => customization.value.gridColumns || null)

const gridClass = computed(() => {
  const c = gridCols.value
  if (c === 2) return 'grid-cols-2'
  if (c === 3) return 'grid-cols-2 sm:grid-cols-3'
  return 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4'
})

// ─── Computed ──────────────────────────────────
const categories = computed(() => {
  const cats = new Set()
  products.value.forEach(p => { if (p.active !== false && p.category) cats.add(p.category) })
  return Array.from(cats)
})

const filteredProducts = computed(() => {
  let list = products.value.filter(p => p.active !== false)
  if (activeCategory.value !== 'all') list = list.filter(p => p.category === activeCategory.value)
  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase().trim()
    list = list.filter(p => p.name.toLowerCase().includes(q) || (p.description || '').toLowerCase().includes(q))
  }
  return list
})

const promoProducts = computed(() => {
  return products.value.filter(p => p.active !== false && p.compare_price && p.compare_price > p.price)
})

const featuredProducts = computed(() => {
  const fIds = store.value?.featured_products
  if (!fIds || !Array.isArray(fIds) || fIds.length === 0) return []
  return products.value.filter(p => fIds.includes(p.id) && p.active !== false)
})

const activePromos = computed(() => promotions.value.filter(p => p.active))

const avgRating = computed(() => {
  if (!reviews.value?.length) return 0
  return Math.round(reviews.value.reduce((s, r) => s + (r.rating || 0), 0) / reviews.value.length * 10) / 10
})

// ─── Visibility toggles from customization ────
const vis = computed(() => {
  const c = customization.value
  return {
    phone: c.showPhone !== false,
    whatsapp: c.showWhatsapp !== false,
    email: c.showEmail !== false,
    address: c.showAddress !== false,
    city: c.showCity !== false,
    hours: c.showHours !== false,
    desc: c.showDescription !== false,
    rating: c.showRating !== false,
    productCount: c.showProductCount !== false,
    category: c.showCategory !== false,
  }
})

const socialLinks = computed(() => {
  const c = customization.value
  const l = []
  if (c.instagram) l.push({ name: 'Instagram', url: `https://instagram.com/${c.instagram}`, icon: 'ig' })
  if (c.facebook) l.push({ name: 'Facebook', url: `https://facebook.com/${c.facebook}`, icon: 'fb' })
  if (c.tiktok) l.push({ name: 'TikTok', url: `https://tiktok.com/@${c.tiktok}`, icon: 'tt' })
  if (c.twitter) l.push({ name: 'Twitter', url: `https://twitter.com/${c.twitter}`, icon: 'tw' })
  return l
})

const productImages = computed(() => {
  if (!selectedProduct.value) return []
  const imgs = []
  if (Array.isArray(selectedProduct.value.images)) {
    selectedProduct.value.images.forEach(img => {
      if (typeof img === 'string') {
        imgs.push(img.startsWith('http') || img.startsWith('data:') || img.startsWith('/storage/') ? img : `/storage/${img}`)
      }
    })
  }
  if (!imgs.length) { const s = productImageUrl(selectedProduct.value); if (s) imgs.push(s) }
  return imgs
})

// ─── Fetch ─────────────────────────────────────
async function fetchShop() {
  loading.value = true
  error.value = false
  try {
    const res = await fetch(apiUrl(`/api/shops/${slug.value}`))
    if (!res.ok) throw new Error('Not found')
    const data = await res.json()
    const sd = data.shop || data
    store.value = sd
    products.value = data.products || sd.products || []
    reviews.value = data.reviews || sd.reviews || []
    promotions.value = data.promotions || sd.promotions || []

    if (sd.customization) {
      try { customization.value = typeof sd.customization === 'string' ? JSON.parse(sd.customization) : sd.customization }
      catch { customization.value = {} }
    }
    if (sd.featured_products && typeof sd.featured_products === 'string') {
      try { sd.featured_products = JSON.parse(sd.featured_products) } catch { sd.featured_products = [] }
    }
  } catch { error.value = true }
  finally { loading.value = false }
}

onMounted(() => {
  fetchShop().then(() => { if (activePromos.value.length > 1) startCarousel() })
  window.addEventListener('scroll', onScroll)
})
onUnmounted(() => { window.removeEventListener('scroll', onScroll); stopCarousel() })

function onScroll() { headerScrolled.value = window.scrollY > 60 }

// ─── Helpers ───────────────────────────────────
const fmt = (p) => typeof p === 'number' ? p.toLocaleString('fr-FR') : p
const hasDiscount = (p) => p.compare_price && p.compare_price > p.price
const discPct = (p) => hasDiscount(p) ? Math.round((1 - p.price / p.compare_price) * 100) : 0
const promoForProduct = (p) => {
  return activePromos.value.find(pr => pr.product_ids && pr.product_ids.includes(p.id))
}

const openWhatsApp = (product = null) => {
  if (!store.value?.whatsapp) return
  const msg = product
    ? `Bonjour ! Je suis intéressé(e) par "${product.name}" à ${fmt(product.price)} FCFA sur ${store.value.name}.`
    : `Bonjour ! J'ai vu votre boutique ${store.value.name} sur Storely.`
  window.open(`https://wa.me/${store.value.whatsapp}?text=${encodeURIComponent(msg)}`, '_blank')
}

const openProduct = (product) => {
  if (product.slug) {
    router.push(`/${slug.value}/${product.slug}`)
  } else {
    selectedProduct.value = product
  }
}

const scrollToProducts = () => {
  document.getElementById('sf-products')?.scrollIntoView({ behavior: 'smooth' })
}

const shareShop = async () => {
  const url = window.location.href
  if (navigator.share) {
    try { await navigator.share({ title: store.value.name, url }) } catch {}
  } else {
    await navigator.clipboard.writeText(url)
    alert('Lien copié !')
  }
}

// ─── Order Form ────────────────────────────────
const showOrderForm = ref(false)
const orderProduct = ref(null)
const orderForm = ref({ name: '', phone: '', note: '', address: '' })
const orderQty = ref(1)
const orderSubmitting = ref(false)
const orderSuccess = ref(false)
const orderError = ref('')
const orderPaymentMethod = ref('flutterwave')
const orderDeliveryZoneId = ref(null)

const deliveryZones = computed(() => {
  const list = store.value?.delivery_zones || []
  return list.filter(z => z.active !== false)
})
const selectedDeliveryZone = computed(() => {
  if (!orderDeliveryZoneId.value) return null
  return deliveryZones.value.find(z => z.id === orderDeliveryZoneId.value) || null
})
const deliveryFee = computed(() => selectedDeliveryZone.value?.price || 0)

// Payment modal
const showPaymentModal = ref(false)
const paymentStep = ref('processing')
const paymentMessage = ref('')

const openOrder = (product) => {
  orderProduct.value = product
  selectedProduct.value = null
  orderForm.value = { name: '', phone: '', note: '', address: '' }
  orderQty.value = 1
  orderError.value = ''
  orderSuccess.value = false
  orderPaymentMethod.value = 'flutterwave'
  // Pre-select default zone if any
  const defaultZone = deliveryZones.value.find(z => z.is_default) || deliveryZones.value[0]
  orderDeliveryZoneId.value = defaultZone ? defaultZone.id : null
  showOrderForm.value = true
}

const orderSubtotal = computed(() => orderProduct.value ? orderProduct.value.price * orderQty.value : 0)
const orderTotal = computed(() => orderSubtotal.value + deliveryFee.value)

const submitOrder = async () => {
  if (!orderForm.value.name || !orderForm.value.phone) { orderError.value = 'Remplissez votre nom et téléphone.'; return }
  orderSubmitting.value = true; orderError.value = ''
  try {
    const res = await fetch(apiUrl('/api/orders'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({
        product_id: orderProduct.value.id,
        customer_name: orderForm.value.name,
        customer_phone: orderForm.value.phone,
        quantity: orderQty.value,
        note: orderForm.value.note || undefined,
        payment_method: orderPaymentMethod.value,
        delivery_zone_id: orderDeliveryZoneId.value || undefined,
        delivery_address: orderForm.value.address || undefined,
      }),
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.message || 'Erreur')

    if (orderPaymentMethod.value === 'flutterwave' && data.order) {
      showOrderForm.value = false
      showPaymentModal.value = true
      paymentStep.value = 'processing'

      const payRes = await fetch(apiUrl('/api/payments/order'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ order_id: data.order.id }),
      })
      const payData = await payRes.json()
      if (!payRes.ok) throw new Error(payData.message || 'Erreur de paiement')

      paymentStep.value = 'waiting'

      if (window.FlutterwaveCheckout) {
        window.FlutterwaveCheckout({
          public_key: payData.public_key,
          tx_ref: payData.tx_ref,
          amount: payData.amount,
          currency: payData.currency,
          customer: {
            email: payData.customer_email,
            name: payData.customer_name,
            phone_number: payData.customer_phone,
          },
          customizations: {
            title: 'Storely',
            description: `Commande #${payData.order_id}`,
            logo: '/logo.png',
          },
          callback: async (response) => {
            paymentStep.value = 'processing'
            try {
              const vRes = await fetch(apiUrl('/api/payments/verify'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
                body: JSON.stringify({ transaction_id: String(response.transaction_id), tx_ref: response.tx_ref }),
              })
              const vData = await vRes.json()
              if (vRes.ok && (vData.status === 'success' || vData.status === 'already_processed')) {
                paymentStep.value = 'success'
              } else {
                paymentStep.value = 'error'
                paymentMessage.value = vData.message || 'Paiement non confirmé.'
              }
            } catch {
              paymentStep.value = 'error'
              paymentMessage.value = 'Erreur de vérification.'
            }
          },
          onclose: () => {
            if (paymentStep.value === 'waiting') {
              paymentStep.value = 'error'
              paymentMessage.value = 'Paiement annulé.'
            }
          },
        })
      } else {
        window.location.href = payData.payment_link
      }
      return
    }

    orderSuccess.value = true
  } catch (e) {
    orderError.value = e.message
    showPaymentModal.value = false
  }
  finally { orderSubmitting.value = false }
}

// ─── Chat Form ─────────────────────────────────
const showChatForm = ref(false)
const chatProduct = ref(null)
const chatForm = ref({ customer_name: '', customer_phone: '', customer_email: '', body: '' })
const chatSending = ref(false)
const chatSent = ref(false)
const chatError = ref('')

const openChat = (product = null) => {
  chatProduct.value = product
  chatForm.value = { customer_name: '', customer_phone: '', customer_email: '', body: '' }
  chatSent.value = false
  chatError.value = ''
  showChatForm.value = true
}

const submitChat = async () => {
  if (!chatForm.value.customer_name || !chatForm.value.customer_phone || !chatForm.value.body) {
    chatError.value = 'Remplissez tous les champs obligatoires.'
    return
  }
  chatSending.value = true; chatError.value = ''
  try {
    const res = await fetch(apiUrl(`/api/shops/${slug.value}/chat`), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ ...chatForm.value, product_id: chatProduct.value?.id || undefined }),
    })
    if (!res.ok) { const d = await res.json(); throw new Error(d.message || 'Erreur') }
    chatSent.value = true
  } catch (e) { chatError.value = e.message }
  finally { chatSending.value = false }
}

watch(selectedProduct, () => { selectedImageIndex.value = 0 })
</script>

<template>
<div v-if="loading" class="sf min-h-screen" style="background: #0a0a12">
  <!-- Skeleton -->
  <div class="h-16 border-b border-white/5 animate-pulse" style="background: rgba(255,255,255,0.02)"></div>
  <div class="h-[400px] animate-pulse" style="background: linear-gradient(180deg, rgba(255,255,255,0.03), transparent)"></div>
  <div class="max-w-7xl mx-auto px-4 py-8">
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
      <div v-for="n in 8" :key="n" class="rounded-2xl overflow-hidden animate-pulse" style="background: rgba(255,255,255,0.03)">
        <div class="aspect-square" style="background: rgba(255,255,255,0.04)"></div>
        <div class="p-4"><div class="h-4 rounded w-3/4 mb-2" style="background: rgba(255,255,255,0.05)"></div><div class="h-5 rounded w-1/2" style="background: rgba(255,255,255,0.05)"></div></div>
      </div>
    </div>
  </div>
</div>

<div v-else-if="error" class="sf min-h-screen flex items-center justify-center" style="background: #0a0a12">
  <div class="text-center px-6">
    <div class="w-20 h-20 rounded-full mx-auto mb-6 flex items-center justify-center" style="background: rgba(255,107,44,0.08)">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#FF6B2C" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    </div>
    <h1 class="text-2xl font-bold text-white mb-3">Boutique introuvable</h1>
    <p class="text-sm text-white/50 mb-8">Cette boutique n'existe pas ou est désactivée.</p>
    <router-link to="/" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold text-white" style="background: #FF6B2C">Retour à l'accueil</router-link>
  </div>
</div>

<!-- ============================================================ -->
<!-- ====================== MAIN STOREFRONT ===================== -->
<!-- ============================================================ -->
<div v-else-if="store" class="sf min-h-screen" :style="{ '--a': accent, background: '#0a0a12' }">

  <!-- ═══════ STICKY HEADER / NAV ═══════ -->
  <header
    class="sf-header"
    :class="{ 'sf-header--scrolled': headerScrolled }"
  >
    <div class="max-w-7xl mx-auto px-4 h-full flex items-center gap-4">
      <!-- Logo + Name -->
      <router-link :to="'/' + store.slug" class="flex items-center gap-3 shrink-0 no-underline">
        <div class="sf-nav-logo">
          <img v-if="shopLogoUrl(store)" :src="shopLogoUrl(store)" class="w-full h-full object-cover" :alt="store.name" />
          <span v-else class="text-base font-bold text-white">{{ store.name?.[0] }}</span>
        </div>
        <span class="font-display font-bold text-white text-base hidden sm:block">{{ store.name }}</span>
      </router-link>

      <!-- Search (desktop) -->
      <div class="hidden md:flex flex-1 max-w-md mx-4 relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.35)" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input v-model="searchQuery" type="text" :placeholder="`Rechercher...`" class="sf-nav-search" />
      </div>

      <div class="flex items-center gap-2 ml-auto">
        <!-- Share -->
        <button @click="shareShop" class="sf-nav-btn" title="Partager">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
        </button>
        <!-- WhatsApp -->
        <button v-if="store.whatsapp && vis.whatsapp" @click="openWhatsApp()" class="sf-nav-wa">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
          <span class="hidden sm:inline text-sm font-semibold">Contacter</span>
        </button>
      </div>
    </div>
  </header>

  <!-- ═══════ ANNOUNCEMENT BAR ═══════ -->
  <div
    v-if="customization.announcementEnabled && customization.announcementText"
    class="sf-announce"
    :style="{ background: accent }"
  >
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
    {{ customization.announcementText }}
  </div>

  <!-- ═══════ HERO SECTION ═══════ -->
  <section class="sf-hero">
    <!-- Banner -->
    <img v-if="shopBannerUrl(store)" :src="shopBannerUrl(store)" :alt="store.name" class="absolute inset-0 w-full h-full object-cover" />
    <div v-else class="absolute inset-0" :style="{ background: `radial-gradient(ellipse at 30% 20%, ${accent}25 0%, transparent 60%), radial-gradient(ellipse at 70% 80%, ${accent}15 0%, transparent 50%), linear-gradient(180deg, #0d0d1a, #0a0a12)` }"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-[#0a0a12] via-[#0a0a12]/60 to-transparent"></div>
    <div class="absolute inset-0" style="background: radial-gradient(circle at 50% 30%, rgba(255,255,255,0.02) 0%, transparent 70%)"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 h-full flex flex-col justify-end pb-12 md:pb-16">
      <div class="flex flex-col md:flex-row md:items-end gap-6">
        <!-- Logo large -->
        <div class="sf-hero-logo shrink-0">
          <img v-if="shopLogoUrl(store)" :src="shopLogoUrl(store)" class="w-full h-full object-cover" :alt="store.name" />
          <span v-else class="text-4xl font-bold text-white">{{ store.name?.[0] }}</span>
        </div>
        <div class="flex-1">
          <div class="flex items-center gap-3 flex-wrap">
            <h1 class="text-3xl md:text-5xl font-display font-black text-white leading-tight">{{ store.name }}</h1>
            <span v-if="store.verified && store.effective_plan === 'pro'" class="sf-verified">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
              Vérifié
            </span>
          </div>
          <div class="flex flex-wrap items-center gap-3 mt-3">
            <span v-if="store.category && vis.category" class="sf-chip">{{ store.category }}</span>
            <span v-if="store.city && vis.city" class="sf-chip"><svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>{{ store.city }}</span>
            <span v-if="reviews.length && vis.rating" class="sf-chip"><svg width="11" height="11" viewBox="0 0 24 24" fill="#FFAA33" stroke="#FFAA33" stroke-width="1"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>{{ avgRating }} ({{ reviews.length }} avis)</span>
            <span v-if="vis.productCount" class="sf-chip">{{ products.filter(p=>p.active!==false).length }} produits</span>
          </div>
          <p v-if="store.description && vis.desc" class="text-sm md:text-base text-white/60 mt-4 max-w-xl leading-relaxed">{{ store.description }}</p>
          <p v-if="customization.welcomeMessage" class="text-sm text-white/50 italic mt-2 max-w-xl">{{ customization.welcomeMessage }}</p>
          <div class="flex items-center gap-3 mt-5">
            <button @click="scrollToProducts" class="sf-cta-btn" :style="{ background: accent }">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
              Voir les produits
            </button>
            <button v-if="store.whatsapp && vis.whatsapp" @click="openWhatsApp()" class="sf-cta-outline">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
              WhatsApp
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══════ PROMO CAROUSEL ═══════ -->
  <section v-if="activePromos.length" class="max-w-7xl mx-auto px-4 -mt-6 relative z-10 mb-8">
    <div
      ref="carouselRef"
      class="carousel"
      @mouseenter="carouselPaused = true"
      @mouseleave="carouselPaused = false"
      @touchstart="onTouchStart"
      @touchend="onTouchEnd"
    >
      <!-- Track -->
      <div class="carousel-track" :style="{ transform: `translateX(-${carouselIndex * 100}%)` }">
        <div
          v-for="promo in activePromos"
          :key="promo.id"
          class="carousel-slide"
        >
          <div class="carousel-card" :style="{ '--promo-color': promo.badge_color || accent }">
            <!-- Background image (full bleed) -->
            <div v-if="promo.banner_image" class="carousel-bg">
              <img :src="storageUrl(promo.banner_image)" class="w-full h-full object-cover" />
              <div class="carousel-bg-overlay"></div>
            </div>
            <div v-else class="carousel-bg">
              <div class="carousel-bg-gradient" :style="{ background: `radial-gradient(ellipse at 70% 30%, ${promo.badge_color || accent}30 0%, transparent 60%), radial-gradient(ellipse at 20% 80%, ${promo.badge_color || accent}20 0%, transparent 50%), linear-gradient(135deg, rgba(255,255,255,0.02), transparent)` }"></div>
              <!-- Decorative shapes -->
              <div class="carousel-deco-1" :style="{ background: (promo.badge_color || accent) + '08' }"></div>
              <div class="carousel-deco-2" :style="{ borderColor: (promo.badge_color || accent) + '10' }"></div>
            </div>

            <!-- Content -->
            <div class="carousel-content">
              <div class="flex-1 min-w-0">
                <span class="carousel-type-badge" :style="{ background: promo.badge_color || accent }">
                  <svg v-if="promo.type==='discount'" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                  <svg v-else-if="promo.type==='flash_sale'" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
                  <svg v-else-if="promo.type==='free_shipping'" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                  <svg v-else width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                  {{ promo.badge_text || promo.type.replace('_',' ').toUpperCase() }}
                </span>
                <h3 class="carousel-title">{{ promo.title }}</h3>
                <p v-if="promo.description" class="carousel-desc">{{ promo.description }}</p>
                <div class="flex items-center gap-3 mt-3">
                  <p v-if="promo.discount_percent" class="carousel-discount" :style="{ color: promo.badge_color || accent }">
                    -{{ promo.discount_percent }}%
                  </p>
                  <p v-if="promo.discount_amount" class="carousel-discount" :style="{ color: promo.badge_color || accent }">
                    -{{ fmt(promo.discount_amount) }} F
                  </p>
                  <span v-if="promo.ends_at" class="carousel-timer">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    Jusqu'au {{ new Date(promo.ends_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }) }}
                  </span>
                </div>
                <p v-if="promo.product_ids && promo.product_ids.length" class="carousel-products-count">
                  {{ promo.product_ids.length }} produit{{ promo.product_ids.length > 1 ? 's' : '' }} concerné{{ promo.product_ids.length > 1 ? 's' : '' }}
                </p>
              </div>

              <!-- Big discount display (right side on desktop) -->
              <div v-if="promo.discount_percent" class="carousel-big-discount hidden md:flex" :style="{ color: promo.badge_color || accent }">
                <span class="carousel-big-number">{{ promo.discount_percent }}</span>
                <span class="carousel-big-percent">%</span>
                <span class="carousel-big-off">OFF</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Navigation arrows -->
      <template v-if="activePromos.length > 1">
        <button @click="prevSlide" class="carousel-arrow carousel-arrow-left">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <button @click="nextSlide" class="carousel-arrow carousel-arrow-right">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
      </template>

      <!-- Dots -->
      <div v-if="activePromos.length > 1" class="carousel-dots">
        <button
          v-for="(_, i) in activePromos"
          :key="i"
          @click="goToSlide(i)"
          class="carousel-dot"
          :class="{ active: i === carouselIndex }"
        >
          <span class="carousel-dot-fill" :style="i === carouselIndex ? { background: accent } : {}"></span>
        </button>
      </div>

      <!-- Progress bar (auto-play indicator) -->
      <div v-if="activePromos.length > 1" class="carousel-progress">
        <div
          class="carousel-progress-bar"
          :class="{ paused: carouselPaused }"
          :style="{ background: accent }"
          :key="carouselIndex"
        ></div>
      </div>
    </div>
  </section>

  <!-- ═══════ PROMO PRODUCTS (if any discounts) ═══════ -->
  <section v-if="promoProducts.length" class="max-w-7xl mx-auto px-4 mb-10">
    <div class="flex items-center gap-3 mb-5">
      <div class="sf-section-icon" :style="{ background: accent + '15', color: accent }">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
      </div>
      <div>
        <h2 class="text-lg font-display font-bold text-white">Offres spéciales</h2>
        <p class="text-xs text-white/40">Profitez de nos promotions en cours</p>
      </div>
    </div>
    <div class="sf-scroll-row">
      <div
        v-for="product in promoProducts"
        :key="'promo-' + product.id"
        class="sf-scroll-card group"
        @click="openProduct(product)"
      >
        <div class="sf-scroll-img">
          <img v-if="productImageUrl(product)" :src="productImageUrl(product)" :alt="product.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" />
          <div v-else class="w-full h-full flex items-center justify-center"><svg width="24" height="24" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>
          <div class="sf-discount-tag">-{{ discPct(product) }}%</div>
        </div>
        <div class="p-3">
          <p class="text-xs text-white/80 font-semibold truncate">{{ product.name }}</p>
          <div class="flex items-baseline gap-2 mt-1">
            <span class="text-sm font-bold" :style="{ color: accent }">{{ fmt(product.price) }} F</span>
            <span class="text-xs line-through text-white/30">{{ fmt(product.compare_price) }}</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══════ FEATURED PRODUCTS ═══════ -->
  <section v-if="featuredProducts.length" class="max-w-7xl mx-auto px-4 mb-10">
    <div class="flex items-center gap-3 mb-5">
      <div class="sf-section-icon" style="background: rgba(255,170,51,0.1); color: #FFAA33">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
      </div>
      <div>
        <h2 class="text-lg font-display font-bold text-white">Produits vedettes</h2>
        <p class="text-xs text-white/40">Sélectionnés par le vendeur</p>
      </div>
    </div>
    <div class="sf-scroll-row">
      <div
        v-for="product in featuredProducts"
        :key="'feat-' + product.id"
        class="sf-scroll-card group"
        @click="openProduct(product)"
      >
        <div class="sf-scroll-img">
          <img v-if="productImageUrl(product)" :src="productImageUrl(product)" :alt="product.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy" />
          <div v-else class="w-full h-full flex items-center justify-center"><svg width="24" height="24" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/></svg></div>
          <div class="absolute top-2 left-2 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-500/90 text-white">VEDETTE</div>
        </div>
        <div class="p-3">
          <p class="text-xs text-white/80 font-semibold truncate">{{ product.name }}</p>
          <span class="text-sm font-bold mt-1 block" :style="{ color: accent }">{{ fmt(product.price) }} <small class="text-[10px] text-white/40">FCFA</small></span>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══════ SEARCH + CATEGORY FILTERS (mobile search) ═══════ -->
  <div id="sf-products" class="max-w-7xl mx-auto px-4 mb-6 scroll-mt-20">
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
      <!-- Mobile search -->
      <div class="md:hidden relative flex-1">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.3)" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        <input v-model="searchQuery" type="text" placeholder="Rechercher un produit..." class="sf-mob-search" />
      </div>
      <!-- Categories -->
      <div v-if="categories.length" class="flex items-center gap-2 overflow-x-auto sf-no-scroll pb-1">
        <button @click="activeCategory = 'all'" class="sf-cat-pill" :class="{ active: activeCategory === 'all' }">Tout <span class="sf-cat-count">{{ products.filter(p=>p.active!==false).length }}</span></button>
        <button v-for="cat in categories" :key="cat" @click="activeCategory = cat" class="sf-cat-pill" :class="{ active: activeCategory === cat }">{{ cat }}</button>
      </div>
    </div>
  </div>

  <!-- ═══════ ALL PRODUCTS GRID ═══════ -->
  <section class="max-w-7xl mx-auto px-4 pb-24">
    <div class="flex items-center justify-between mb-5">
      <h2 class="text-lg font-display font-bold text-white">
        {{ activeCategory === 'all' ? 'Tous les produits' : activeCategory }}
        <span class="text-sm font-normal text-white/40 ml-1">({{ filteredProducts.length }})</span>
      </h2>
    </div>

    <div v-if="filteredProducts.length" class="grid gap-4 md:gap-5" :class="gridClass">
      <div
        v-for="product in filteredProducts"
        :key="product.id"
        class="sf-card group"
        @click="openProduct(product)"
      >
        <div class="sf-card-img">
          <img v-if="productImageUrl(product)" :src="productImageUrl(product)" :alt="product.name" class="w-full h-full object-cover group-hover:scale-[1.06] transition-transform duration-500" loading="lazy" />
          <div v-else class="w-full h-full flex items-center justify-center">
            <svg width="28" height="28" fill="none" stroke="rgba(255,255,255,0.12)" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
          </div>
          <!-- Badges -->
          <div v-if="hasDiscount(product)" class="sf-badge-discount">-{{ discPct(product) }}%</div>
          <div v-if="promoForProduct(product)" class="sf-badge-promo" :style="{ background: promoForProduct(product).badge_color || accent }">{{ promoForProduct(product).badge_text || 'PROMO' }}</div>
          <div v-if="product.stock === 0" class="sf-badge-oos">Rupture</div>
          <div v-else-if="product.stock && product.stock <= 3" class="sf-badge-low">Plus que {{ product.stock }}</div>
          <!-- Hover overlay -->
          <div class="sf-card-hover">
            <span class="sf-card-hover-btn">Voir le produit</span>
          </div>
        </div>
        <div class="sf-card-body">
          <p v-if="product.category" class="text-[10px] text-white/35 uppercase tracking-wider font-semibold mb-1">{{ product.category }}</p>
          <h3 class="text-sm font-semibold text-white/90 truncate leading-tight">{{ product.name }}</h3>
          <div class="flex items-baseline gap-2 mt-2">
            <span class="text-base font-display font-extrabold" :style="{ color: accent }">{{ fmt(product.price) }} <small class="text-[10px] font-semibold opacity-60">FCFA</small></span>
            <span v-if="hasDiscount(product)" class="text-xs line-through text-white/30">{{ fmt(product.compare_price) }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty -->
    <div v-else class="text-center py-20">
      <svg width="40" height="40" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="1.5" viewBox="0 0 24 24" class="mx-auto mb-4"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <p class="text-sm text-white/40">{{ searchQuery || activeCategory !== 'all' ? 'Aucun produit trouvé.' : 'Aucun produit disponible.' }}</p>
      <button v-if="searchQuery || activeCategory !== 'all'" @click="searchQuery = ''; activeCategory = 'all'" class="mt-4 text-xs font-semibold px-4 py-2 rounded-lg text-white/60 border border-white/10 hover:bg-white/5 transition">Tout afficher</button>
    </div>
  </section>

  <!-- ═══════ REVIEWS SECTION ═══════ -->
  <section v-if="reviews.length && vis.rating" class="border-t border-white/5 py-12">
    <div class="max-w-7xl mx-auto px-4">
      <div class="flex items-center gap-3 mb-6">
        <div class="sf-section-icon" style="background: rgba(255,170,51,0.08); color: #FFAA33">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
        <div>
          <h2 class="text-lg font-display font-bold text-white">Avis clients</h2>
          <p class="text-xs text-white/40">{{ avgRating }}/5 basé sur {{ reviews.length }} avis</p>
        </div>
      </div>
      <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
        <div v-for="review in reviews.slice(0, 6)" :key="review.id" class="sf-review-card">
          <div class="flex items-center gap-2 mb-2">
            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white" :style="{ background: accent + '25', color: accent }">{{ (review.author_name || '?')[0].toUpperCase() }}</div>
            <div>
              <p class="text-sm font-semibold text-white/90">{{ review.author_name }}</p>
              <div class="flex gap-0.5">
                <svg v-for="s in 5" :key="s" width="10" height="10" viewBox="0 0 24 24" :fill="s <= review.rating ? '#FFAA33' : 'none'" :stroke="s <= review.rating ? '#FFAA33' : 'rgba(255,255,255,0.15)'" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
              </div>
            </div>
          </div>
          <p v-if="review.comment" class="text-xs text-white/50 leading-relaxed">{{ review.comment }}</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══════ SHOP INFO / FOOTER ═══════ -->
  <footer class="sf-footer">
    <div class="max-w-7xl mx-auto px-4">
      <div class="grid gap-8 md:grid-cols-3 mb-8">
        <!-- About -->
        <div>
          <div class="flex items-center gap-3 mb-4">
            <div class="sf-nav-logo">
              <img v-if="shopLogoUrl(store)" :src="shopLogoUrl(store)" class="w-full h-full object-cover" />
              <span v-else class="text-sm font-bold text-white">{{ store.name?.[0] }}</span>
            </div>
            <span class="font-display font-bold text-white">{{ store.name }}</span>
          </div>
          <p v-if="store.description" class="text-xs text-white/40 leading-relaxed">{{ store.description }}</p>
        </div>
        <!-- Contact -->
        <div>
          <h4 class="text-xs font-display font-bold uppercase tracking-wider text-white/60 mb-4">Contact</h4>
          <div class="space-y-3">
            <a v-if="store.phone && vis.phone" :href="'tel:' + store.phone" class="sf-footer-link">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6A19.79 19.79 0 014.11 2h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/></svg>
              {{ store.phone }}
            </a>
            <a v-if="store.whatsapp && vis.whatsapp" :href="'https://wa.me/' + store.whatsapp" target="_blank" class="sf-footer-link">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" style="color: #25D366"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
              WhatsApp
            </a>
            <p v-if="store.email && vis.email" class="sf-footer-link">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
              {{ store.email }}
            </p>
            <p v-if="store.address && vis.address" class="sf-footer-link">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
              {{ store.address }}<template v-if="store.city">, {{ store.city }}</template>
            </p>
            <p v-if="store.open_hours && vis.hours" class="sf-footer-link">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              {{ store.open_hours }}
            </p>
          </div>
        </div>
        <!-- Social -->
        <div>
          <h4 class="text-xs font-display font-bold uppercase tracking-wider text-white/60 mb-4">Suivez-nous</h4>
          <div v-if="socialLinks.length" class="flex flex-wrap gap-2">
            <a v-for="link in socialLinks" :key="link.name" :href="link.url" target="_blank" class="sf-social-pill">
              <svg v-if="link.icon==='ig'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
              <svg v-if="link.icon==='fb'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
              <svg v-if="link.icon==='tt'" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-2.88 2.5 2.89 2.89 0 01-2.89-2.89 2.89 2.89 0 012.89-2.89c.28 0 .54.04.79.1v-3.5a6.37 6.37 0 00-.79-.05A6.34 6.34 0 003.15 15.2a6.34 6.34 0 0010.86 4.44V13a8.18 8.18 0 005.58 2.18V11.7a4.83 4.83 0 01-3.77-1.24V6.69z"/></svg>
              <svg v-if="link.icon==='tw'" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5 0-.83-.08-.83-.08-.83A7.72 7.72 0 0023 3z"/></svg>
              {{ link.name }}
            </a>
          </div>
          <p v-else class="text-xs text-white/30">Aucun réseau social</p>
          <button @click="shareShop" class="sf-share-btn mt-4">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
            Partager la boutique
          </button>
        </div>
      </div>
      <!-- Bottom bar — branding based on plan -->
      <div class="border-t border-white/5 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
        <!-- Free: full branding -->
        <a v-if="!store.effective_plan || store.effective_plan === 'free'" href="/" class="flex items-center gap-2 text-xs text-white/30 no-underline hover:text-white/50 transition font-medium">
          <img src="/logo.png" alt="Storely" class="w-5 h-5 object-contain" />
          Propulsé par Storely
        </a>
        <!-- Starter: small discrete link -->
        <a v-else-if="store.effective_plan === 'starter'" href="/" class="text-[10px] text-white/15 no-underline hover:text-white/30 transition">
          storely.app
        </a>
        <!-- Pro: no branding -->
        <span v-else></span>
        <p class="text-[10px] text-white/20">&copy; {{ new Date().getFullYear() }} {{ store.name }}. Tous droits réservés.</p>
      </div>
    </div>
  </footer>

  <!-- ═══════ FLOATING WHATSAPP ═══════ -->
  <button v-if="store.whatsapp && vis.whatsapp && store.effective_plan && store.effective_plan !== 'free'" @click="openWhatsApp()" class="sf-fab">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492l4.625-1.452A11.93 11.93 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>
  </button>

  <!-- ═══════ PRODUCT DETAIL MODAL ═══════ -->
  <teleport to="body">
    <transition enter-active-class="transition duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="selectedProduct" class="fixed inset-0 z-[60] flex items-end md:items-center justify-center" @click.self="selectedProduct = null">
        <div class="absolute inset-0 bg-black/85 backdrop-blur-lg" @click="selectedProduct = null"></div>
        <div class="sf-modal md:flex">
          <!-- Images -->
          <div class="sf-modal-img md:w-1/2 shrink-0">
            <div class="aspect-[4/3] md:aspect-auto md:h-full relative" style="background: #08080f">
              <img v-if="productImages.length" :src="productImages[selectedImageIndex]" :alt="selectedProduct.name" class="w-full h-full object-contain" />
              <div v-else class="w-full h-full flex items-center justify-center"><svg width="48" height="48" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></div>
              <div v-if="productImages.length > 1" class="absolute bottom-3 left-1/2 -translate-x-1/2 flex gap-1.5">
                <button v-for="(_, i) in productImages" :key="i" @click.stop="selectedImageIndex = i" class="w-2 h-2 rounded-full transition" :style="{ background: i === selectedImageIndex ? accent : 'rgba(255,255,255,0.25)' }"></button>
              </div>
              <div v-if="hasDiscount(selectedProduct)" class="absolute top-3 left-3 sf-badge-discount text-sm px-3 py-1.5">-{{ discPct(selectedProduct) }}%</div>
            </div>
          </div>
          <!-- Info -->
          <div class="sf-modal-body md:w-1/2">
            <button @click="selectedProduct = null" class="absolute top-3 right-3 w-9 h-9 rounded-xl flex items-center justify-center bg-white/5 text-white/50 hover:bg-white/10 hover:text-white transition z-10">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <div class="flex-1 overflow-y-auto p-5 md:p-6">
              <span v-if="selectedProduct.category" class="text-[11px] font-semibold uppercase tracking-wider" :style="{ color: accent }">{{ selectedProduct.category }}</span>
              <h2 class="text-xl md:text-2xl font-display font-bold text-white mt-1">{{ selectedProduct.name }}</h2>
              <div class="flex items-baseline gap-3 mt-3">
                <span class="text-2xl md:text-3xl font-display font-black" :style="{ color: accent }">{{ fmt(selectedProduct.price) }} <small class="text-sm font-semibold opacity-70">FCFA</small></span>
                <span v-if="hasDiscount(selectedProduct)" class="text-sm line-through text-white/35">{{ fmt(selectedProduct.compare_price) }}</span>
              </div>
              <div class="mt-3">
                <span v-if="selectedProduct.stock === 0" class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-lg" style="background: rgba(239,68,68,0.1); color: #EF4444"><span class="w-1.5 h-1.5 rounded-full bg-red-400"></span>Rupture de stock</span>
                <span v-else-if="selectedProduct.stock && selectedProduct.stock <= 5" class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-lg" style="background: rgba(245,158,11,0.1); color: #F59E0B"><span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>Plus que {{ selectedProduct.stock }}</span>
                <span v-else class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-lg" style="background: rgba(34,197,94,0.08); color: #22C55E"><span class="w-1.5 h-1.5 rounded-full bg-green-400"></span>En stock</span>
              </div>
              <p v-if="selectedProduct.description" class="text-sm text-white/55 leading-relaxed mt-4 whitespace-pre-line">{{ selectedProduct.description }}</p>
            </div>
            <div class="p-5 md:p-6 pt-0 space-y-2.5">
              <button @click="openOrder(selectedProduct)" :disabled="selectedProduct.stock === 0" class="sf-btn-accent w-full disabled:opacity-30">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                Commander · {{ fmt(selectedProduct.price) }} FCFA
              </button>
              <button @click="selectedProduct = null; openChat(selectedProduct)" class="sf-cta-outline w-full">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                Envoyer un message
              </button>
              <button v-if="store.whatsapp" @click="openWhatsApp(selectedProduct)" class="sf-btn-wa-outline w-full">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                Contacter via WhatsApp
              </button>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </teleport>

  <!-- ═══════ ORDER FORM MODAL ═══════ -->
  <teleport to="body">
    <transition enter-active-class="transition duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showOrderForm" class="fixed inset-0 z-[60] flex items-end md:items-center justify-center" @click.self="showOrderForm = false">
        <div class="absolute inset-0 bg-black/85 backdrop-blur-lg" @click="showOrderForm = false"></div>
        <div class="relative w-full max-w-md max-h-[90vh] overflow-y-auto rounded-t-2xl md:rounded-2xl" style="background: #141420; border: 1px solid rgba(255,255,255,0.06)">
          <!-- Success -->
          <div v-if="orderSuccess" class="p-8 text-center">
            <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-5" style="background: rgba(34,197,94,0.1)">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#22C55E" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h3 class="text-xl font-display font-bold text-white mb-2">Commande confirmée !</h3>
            <p class="text-sm text-white/50 mb-1">Votre commande a été envoyée à <strong class="text-white">{{ store.name }}</strong>.</p>
            <p class="text-sm text-white/50 mb-6">On vous contactera au <strong class="text-white">{{ orderForm.phone }}</strong>.</p>
            <button @click="showOrderForm = false" class="sf-btn-accent px-8">Fermer</button>
          </div>
          <!-- Form -->
          <div v-else class="p-5 md:p-6">
            <div class="flex items-center justify-between mb-5">
              <h3 class="text-lg font-display font-bold text-white">Passer commande</h3>
              <button @click="showOrderForm = false" class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-white/40 hover:text-white transition">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </div>
            <!-- Product -->
            <div v-if="orderProduct" class="flex items-center gap-3 p-3 rounded-xl mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06)">
              <div class="w-14 h-14 rounded-lg overflow-hidden shrink-0" style="background: rgba(255,255,255,0.04)">
                <img v-if="productImageUrl(orderProduct)" :src="productImageUrl(orderProduct)" class="w-full h-full object-cover" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-white truncate">{{ orderProduct.name }}</p>
                <p class="text-sm font-bold mt-0.5" :style="{ color: accent }">{{ fmt(orderProduct.price) }} FCFA</p>
              </div>
            </div>
            <!-- Qty -->
            <div class="flex items-center justify-between py-3 border-b border-white/5">
              <span class="text-xs font-semibold uppercase tracking-wider text-white/35">Quantité</span>
              <div class="flex items-center border border-white/10 rounded-lg overflow-hidden">
                <button @click="orderQty = Math.max(1, orderQty - 1)" class="w-9 h-9 flex items-center justify-center text-white/70 hover:bg-white/5 transition text-base font-semibold">-</button>
                <span class="w-10 text-center text-sm font-bold text-white">{{ orderQty }}</span>
                <button @click="orderQty++" class="w-9 h-9 flex items-center justify-center text-white/70 hover:bg-white/5 transition text-base font-semibold">+</button>
              </div>
            </div>
            <!-- Delivery zone (if configured and physical product) -->
            <div v-if="deliveryZones.length && orderProduct && orderProduct.product_type !== 'digital'" class="py-3 border-b border-white/5">
              <label class="sf-form-label block mb-2">Zone de livraison</label>
              <div class="grid grid-cols-1 gap-1.5 max-h-44 overflow-y-auto pr-1">
                <button
                  v-for="zone in deliveryZones"
                  :key="zone.id"
                  type="button"
                  @click="orderDeliveryZoneId = zone.id"
                  class="flex items-center justify-between p-2.5 rounded-lg border text-left transition-all"
                  :class="orderDeliveryZoneId === zone.id ? 'border-[var(--a)] bg-[color-mix(in_srgb,var(--a)_10%,transparent)]' : 'border-white/10 bg-white/[0.02] hover:bg-white/5'"
                >
                  <div class="min-w-0">
                    <p class="text-sm font-semibold text-white truncate">{{ zone.name }}</p>
                    <p v-if="zone.estimated_hours" class="text-[10px] text-white/35 mt-0.5">
                      Livraison sous {{ zone.estimated_hours < 24 ? zone.estimated_hours + 'h' : Math.round(zone.estimated_hours / 24) + ' j' }}
                    </p>
                  </div>
                  <span class="text-xs font-display font-bold ml-2 shrink-0" :style="{ color: accent }">
                    {{ zone.price === 0 ? 'Gratuit' : fmt(zone.price) + ' F' }}
                  </span>
                </button>
              </div>
            </div>
            <!-- Totals -->
            <div class="py-3 mb-3 space-y-1.5">
              <div v-if="deliveryFee > 0" class="flex items-center justify-between text-xs text-white/50">
                <span>Sous-total</span><span>{{ fmt(orderSubtotal) }} FCFA</span>
              </div>
              <div v-if="deliveryFee > 0" class="flex items-center justify-between text-xs text-white/50">
                <span>Livraison {{ selectedDeliveryZone ? '(' + selectedDeliveryZone.name + ')' : '' }}</span>
                <span>{{ fmt(deliveryFee) }} FCFA</span>
              </div>
              <div class="flex items-center justify-between pt-1">
                <span class="text-sm text-white/70">Total</span>
                <span class="text-lg font-display font-bold" :style="{ color: accent }">{{ fmt(orderTotal) }} FCFA</span>
              </div>
            </div>
            <div v-if="orderError" class="mb-3 p-3 rounded-lg text-xs font-medium" style="background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.15); color: #EF4444">{{ orderError }}</div>
            <form @submit.prevent="submitOrder" class="space-y-3">
              <div><label class="sf-form-label">Nom complet</label><input v-model="orderForm.name" type="text" required placeholder="Jean Kamga" class="sf-form-input" /></div>
              <div><label class="sf-form-label">Téléphone</label><input v-model="orderForm.phone" type="tel" required placeholder="+237 6XX XXX XXX" class="sf-form-input" /></div>
              <div v-if="selectedDeliveryZone">
                <label class="sf-form-label">Adresse de livraison <span class="text-white/20">(précisez votre quartier / point de repère)</span></label>
                <input v-model="orderForm.address" type="text" placeholder="Ex: Akwa, Rue de la Joie, face pharmacie" class="sf-form-input" />
              </div>
              <div><label class="sf-form-label">Note <span class="text-white/20">(optionnel)</span></label><textarea v-model="orderForm.note" rows="2" placeholder="Taille, couleur, détails..." class="sf-form-input resize-none"></textarea></div>
              <!-- Payment method -->
              <div v-if="orderProduct && orderProduct.product_type !== 'digital'">
                <label class="sf-form-label">Mode de paiement</label>
                <div class="grid grid-cols-2 gap-2 mt-1">
                  <button type="button" @click="orderPaymentMethod = 'flutterwave'" class="p-2.5 rounded-xl border text-center transition-all" :class="orderPaymentMethod === 'flutterwave' ? 'border-[var(--a)] bg-[color-mix(in_srgb,var(--a)_10%,transparent)]' : 'border-white/10 bg-white/[0.02]'">
                    <svg class="w-4 h-4 mx-auto mb-1" :class="orderPaymentMethod === 'flutterwave' ? 'text-white' : 'text-white/40'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                    <p class="text-[11px] font-semibold" :class="orderPaymentMethod === 'flutterwave' ? 'text-white' : 'text-white/50'">Payer en ligne</p>
                    <p class="text-[9px] text-white/25 mt-0.5">Mobile Money / Carte</p>
                  </button>
                  <button type="button" @click="orderPaymentMethod = 'cod'" class="p-2.5 rounded-xl border text-center transition-all" :class="orderPaymentMethod === 'cod' ? 'border-[var(--a)] bg-[color-mix(in_srgb,var(--a)_10%,transparent)]' : 'border-white/10 bg-white/[0.02]'">
                    <svg class="w-4 h-4 mx-auto mb-1" :class="orderPaymentMethod === 'cod' ? 'text-white' : 'text-white/40'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/><circle cx="14" cy="14" r="2"/></svg>
                    <p class="text-[11px] font-semibold" :class="orderPaymentMethod === 'cod' ? 'text-white' : 'text-white/50'">À la livraison</p>
                    <p class="text-[9px] text-white/25 mt-0.5">Le vendeur vous contacte</p>
                  </button>
                </div>
              </div>
              <button type="submit" :disabled="orderSubmitting" class="sf-btn-accent w-full !mt-4 disabled:opacity-50">
                <svg v-if="orderSubmitting" width="16" height="16" class="animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.25"/><path d="M12 2a10 10 0 019.95 9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                {{ orderSubmitting ? 'Traitement...' : (orderPaymentMethod === 'flutterwave' ? `Payer ${fmt(orderTotal)} FCFA` : `Confirmer · ${fmt(orderTotal)} FCFA`) }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </transition>
  </teleport>

  <!-- ═══════ CHAT FORM MODAL ═══════ -->
  <teleport to="body">
    <transition enter-active-class="transition duration-300" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
      <div v-if="showChatForm" class="fixed inset-0 z-[60] flex items-end md:items-center justify-center" @click.self="showChatForm = false">
        <div class="absolute inset-0 bg-black/85 backdrop-blur-lg" @click="showChatForm = false"></div>
        <div class="relative w-full max-w-md max-h-[90vh] overflow-y-auto rounded-t-2xl md:rounded-2xl" style="background: #141420; border: 1px solid rgba(255,255,255,0.06)">
          <!-- Chat sent success -->
          <div v-if="chatSent" class="p-8 text-center">
            <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-5" style="background: rgba(59,130,246,0.1)">
              <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h3 class="text-xl font-display font-bold text-white mb-2">Message envoyé !</h3>
            <p class="text-sm text-white/50 mb-1">Votre message a été envoyé à <strong class="text-white">{{ store.name }}</strong>.</p>
            <p class="text-sm text-white/40 mb-6">Le vendeur vous répondra bientôt.</p>
            <button @click="showChatForm = false" class="sf-btn-accent px-8">Fermer</button>
          </div>
          <!-- Chat form -->
          <div v-else class="p-5 md:p-6">
            <div class="flex items-center justify-between mb-5">
              <h3 class="text-lg font-display font-bold text-white">Envoyer un message</h3>
              <button @click="showChatForm = false" class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-white/40 hover:text-white transition">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </button>
            </div>
            <!-- Product context -->
            <div v-if="chatProduct" class="flex items-center gap-3 p-3 rounded-xl mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06)">
              <div class="w-10 h-10 rounded-lg overflow-hidden shrink-0" style="background: rgba(255,255,255,0.04)">
                <img v-if="productImageUrl(chatProduct)" :src="productImageUrl(chatProduct)" class="w-full h-full object-cover" />
              </div>
              <div class="min-w-0">
                <p class="text-[10px] text-white/30">À propos de</p>
                <p class="text-sm font-semibold text-white truncate">{{ chatProduct.name }}</p>
              </div>
            </div>
            <div v-if="chatError" class="mb-3 p-3 rounded-lg text-xs font-medium" style="background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.15); color: #EF4444">{{ chatError }}</div>
            <form @submit.prevent="submitChat" class="space-y-3">
              <div><label class="sf-form-label">Votre nom *</label><input v-model="chatForm.customer_name" type="text" required placeholder="Jean Kamga" class="sf-form-input" /></div>
              <div><label class="sf-form-label">Téléphone *</label><input v-model="chatForm.customer_phone" type="tel" required placeholder="+237 6XX XXX XXX" class="sf-form-input" /></div>
              <div><label class="sf-form-label">Email <span class="text-white/20">(optionnel)</span></label><input v-model="chatForm.customer_email" type="email" class="sf-form-input" /></div>
              <div><label class="sf-form-label">Message *</label><textarea v-model="chatForm.body" rows="3" required maxlength="5000" placeholder="Bonjour, j'ai une question..." class="sf-form-input resize-none"></textarea></div>
              <button type="submit" :disabled="chatSending" class="sf-btn-accent w-full !mt-4 disabled:opacity-50">
                <svg v-if="chatSending" width="16" height="16" class="animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.25"/><path d="M12 2a10 10 0 019.95 9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                {{ chatSending ? 'Envoi...' : 'Envoyer le message' }}
              </button>
            </form>
          </div>
        </div>
      </div>
    </transition>
  </teleport>

  <!-- ══���════ PAYMENT STATUS MODAL ═══════ -->
  <teleport to="body">
    <transition enter-active-class="transition duration-300" enter-from-class="opacity-0" leave-active-class="transition duration-200" leave-to-class="opacity-0">
      <div v-if="showPaymentModal" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/80 backdrop-blur-md"></div>
        <div class="relative w-full max-w-sm rounded-3xl overflow-hidden" style="background: linear-gradient(145deg, #12121a, #1a1a2e); border: 1px solid rgba(255,255,255,0.08)">
          <div class="p-8 text-center">
            <!-- Processing -->
            <template v-if="paymentStep === 'processing'">
              <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center" style="background: rgba(255,107,44,0.1)">
                <svg class="w-10 h-10 text-brand animate-spin" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.2"/><path d="M12 2a10 10 0 019.95 9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
              </div>
              <h3 class="text-lg font-display font-bold text-white mb-2">Préparation du paiement...</h3>
              <p class="text-sm text-white/40">Un instant, nous initialisons votre transaction.</p>
            </template>
            <!-- Waiting -->
            <template v-else-if="paymentStep === 'waiting'">
              <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center" style="background: rgba(59,130,246,0.1)">
                <svg class="w-10 h-10 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
              </div>
              <h3 class="text-lg font-display font-bold text-white mb-2">Finalisez votre paiement</h3>
              <p class="text-sm text-white/40 mb-4">Complétez le paiement dans la fenêtre Flutterwave.</p>
              <div class="flex items-center justify-center gap-2">
                <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse" /><span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse" style="animation-delay:0.2s" /><span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse" style="animation-delay:0.4s" />
              </div>
            </template>
            <!-- Success -->
            <template v-else-if="paymentStep === 'success'">
              <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center" style="background: rgba(34,197,94,0.1)">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#22C55E" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
              </div>
              <h3 class="text-xl font-display font-bold text-white mb-2">Paiement confirmé !</h3>
              <p class="text-sm text-white/50 mb-6">Votre commande a été payée. Le vendeur prépare votre produit.</p>
              <button @click="showPaymentModal = false; orderSuccess = true" class="sf-btn-accent px-8">Fermer</button>
            </template>
            <!-- Error -->
            <template v-else-if="paymentStep === 'error'">
              <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center" style="background: rgba(239,68,68,0.1)">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
              </div>
              <h3 class="text-xl font-display font-bold text-white mb-2">Paiement échoué</h3>
              <p class="text-sm text-white/50 mb-6">{{ paymentMessage || 'Le paiement n\'a pas abouti.' }}</p>
              <div class="flex gap-3 justify-center">
                <button @click="showPaymentModal = false" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white/60 border border-white/10 hover:bg-white/5 transition-all">Fermer</button>
                <button @click="showPaymentModal = false; showOrderForm = true" class="sf-btn-accent px-6">Réessayer</button>
              </div>
            </template>
          </div>
        </div>
      </div>
    </transition>
  </teleport>

  <!-- Floating chat button (mobile) -->
  <button v-if="!loading && !error && store" @click="openChat()" class="sf-fab-chat">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
  </button>

</div>
</template>

<style scoped>
.sf { --a: #FF6B2C; font-family: var(--font-body, 'DM Sans', system-ui, sans-serif); }

/* ── Header ── */
.sf-header {
  position: fixed; top: 0; left: 0; right: 0; z-index: 50;
  height: 60px;
  background: transparent;
  transition: all 0.3s ease;
}
.sf-header--scrolled {
  background: rgba(10,10,18,0.92);
  backdrop-filter: blur(16px) saturate(1.5);
  border-bottom: 1px solid rgba(255,255,255,0.05);
  box-shadow: 0 4px 30px rgba(0,0,0,0.3);
}
.sf-nav-logo {
  width: 36px; height: 36px; border-radius: 10px; overflow: hidden;
  background: linear-gradient(135deg, var(--a), color-mix(in srgb, var(--a), #000 25%));
  display: flex; align-items: center; justify-content: center;
  border: 2px solid rgba(255,255,255,0.08);
}
.sf-nav-search {
  width: 100%; padding: 0.55rem 0.9rem 0.55rem 2.2rem; border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.06); background: rgba(255,255,255,0.04);
  color: white; font-size: 0.82rem; outline: none; transition: all 0.2s;
}
.sf-nav-search:focus { border-color: var(--a); box-shadow: 0 0 0 3px color-mix(in srgb, var(--a) 12%, transparent); }
.sf-nav-search::placeholder { color: rgba(255,255,255,0.3); }
.sf-nav-btn {
  width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center;
  background: rgba(255,255,255,0.05); color: rgba(255,255,255,0.6); transition: all 0.2s;
}
.sf-nav-btn:hover { background: rgba(255,255,255,0.08); color: white; }
.sf-nav-wa {
  display: inline-flex; align-items: center; gap: 6px; padding: 0.5rem 0.8rem; border-radius: 10px;
  background: #25D366; color: white; font-size: 0.82rem; transition: all 0.2s;
}
.sf-nav-wa:hover { background: #20BD5A; }

/* ── Announcement ── */
.sf-announce {
  margin-top: 60px; padding: 0.55rem 1rem; display: flex; align-items: center; justify-content: center; gap: 6px;
  font-size: 0.78rem; font-weight: 600; color: white; text-align: center;
}

/* ── Hero ── */
.sf-hero {
  position: relative; min-height: 420px; overflow: hidden;
  margin-top: 60px;
}
.sf-hero:not(:has(+ .sf-announce)) { margin-top: 60px; }
.sf-announce + .sf-hero { margin-top: 0; }
.sf-hero-logo {
  width: 80px; height: 80px; border-radius: 1.25rem; overflow: hidden;
  background: linear-gradient(135deg, var(--a), color-mix(in srgb, var(--a), #000 25%));
  display: flex; align-items: center; justify-content: center;
  border: 3px solid rgba(255,255,255,0.1); box-shadow: 0 8px 40px rgba(0,0,0,0.5);
}
@media (min-width: 768px) { .sf-hero { min-height: 480px; } .sf-hero-logo { width: 96px; height: 96px; } }

.sf-verified {
  display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 8px;
  font-size: 0.68rem; font-weight: 700; background: rgba(34,197,94,0.15); color: #22C55E;
}
.sf-chip {
  display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 8px;
  font-size: 0.68rem; font-weight: 500; background: rgba(255,255,255,0.07); color: rgba(255,255,255,0.65);
}
.sf-cta-btn {
  display: inline-flex; align-items: center; gap: 8px; padding: 0.7rem 1.4rem; border-radius: 12px;
  font-size: 0.85rem; font-weight: 700; color: white; transition: all 0.2s;
  box-shadow: 0 4px 20px color-mix(in srgb, var(--a) 35%, transparent);
}
.sf-cta-btn:hover { filter: brightness(1.1); transform: translateY(-1px); }
.sf-cta-outline {
  display: inline-flex; align-items: center; gap: 6px; padding: 0.7rem 1.2rem; border-radius: 12px;
  font-size: 0.85rem; font-weight: 600; color: rgba(255,255,255,0.8);
  border: 1px solid rgba(255,255,255,0.12); background: rgba(255,255,255,0.04); transition: all 0.2s;
}
.sf-cta-outline:hover { background: rgba(255,255,255,0.08); color: white; }

/* ── Carousel ── */
.carousel {
  position: relative;
  border-radius: 1.25rem;
  overflow: hidden;
  background: rgba(255,255,255,0.02);
  border: 1px solid rgba(255,255,255,0.06);
}

.carousel-track {
  display: flex;
  transition: transform 0.6s cubic-bezier(0.25, 1, 0.5, 1);
  will-change: transform;
}

.carousel-slide {
  min-width: 100%;
  flex-shrink: 0;
}

.carousel-card {
  position: relative;
  min-height: 180px;
  overflow: hidden;
}
@media (min-width: 768px) { .carousel-card { min-height: 220px; } }

.carousel-bg {
  position: absolute;
  inset: 0;
  overflow: hidden;
}
.carousel-bg img { transition: transform 8s ease; }
.carousel-slide:hover .carousel-bg img { transform: scale(1.05); }
.carousel-bg-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, rgba(10,10,18,0.92) 0%, rgba(10,10,18,0.6) 50%, rgba(10,10,18,0.3) 100%);
}
.carousel-bg-gradient {
  position: absolute;
  inset: 0;
}
.carousel-deco-1 {
  position: absolute;
  width: 300px; height: 300px;
  border-radius: 50%;
  top: -100px; right: -50px;
  filter: blur(60px);
}
.carousel-deco-2 {
  position: absolute;
  width: 200px; height: 200px;
  border-radius: 50%;
  border: 2px solid;
  bottom: -80px; left: 10%;
  opacity: 0.5;
}

.carousel-content {
  position: relative;
  z-index: 1;
  display: flex;
  align-items: center;
  gap: 2rem;
  padding: 1.5rem 1.5rem;
  height: 100%;
}
@media (min-width: 768px) { .carousel-content { padding: 2rem 2.5rem; } }

.carousel-type-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 0.6rem;
  font-weight: 800;
  color: white;
  letter-spacing: 0.06em;
  text-transform: uppercase;
}

.carousel-title {
  font-family: var(--font-display);
  font-weight: 800;
  font-size: 1.25rem;
  color: white;
  margin-top: 0.6rem;
  line-height: 1.3;
}
@media (min-width: 768px) { .carousel-title { font-size: 1.6rem; } }

.carousel-desc {
  font-size: 0.78rem;
  color: rgba(255,255,255,0.5);
  margin-top: 0.3rem;
  max-width: 400px;
  line-height: 1.5;
}

.carousel-discount {
  font-family: var(--font-display);
  font-weight: 900;
  font-size: 1.5rem;
}
@media (min-width: 768px) { .carousel-discount { font-size: 1.8rem; } }

.carousel-timer {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 3px 8px;
  border-radius: 6px;
  font-size: 0.65rem;
  font-weight: 600;
  background: rgba(255,255,255,0.06);
  color: rgba(255,255,255,0.5);
}

.carousel-products-count {
  font-size: 0.68rem;
  color: rgba(255,255,255,0.3);
  margin-top: 0.5rem;
}

.carousel-big-discount {
  flex-direction: column;
  align-items: center;
  justify-content: center;
  position: relative;
  flex-shrink: 0;
}
.carousel-big-number {
  font-family: var(--font-display);
  font-weight: 900;
  font-size: 4.5rem;
  line-height: 1;
  letter-spacing: -0.04em;
  opacity: 0.9;
}
.carousel-big-percent {
  font-family: var(--font-display);
  font-weight: 800;
  font-size: 1.8rem;
  margin-top: -8px;
  opacity: 0.7;
}
.carousel-big-off {
  font-family: var(--font-display);
  font-weight: 800;
  font-size: 0.9rem;
  letter-spacing: 0.15em;
  opacity: 0.5;
  margin-top: -2px;
}

/* Arrows */
.carousel-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  width: 36px; height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255,255,255,0.08);
  backdrop-filter: blur(8px);
  color: rgba(255,255,255,0.7);
  border: 1px solid rgba(255,255,255,0.1);
  transition: all 0.2s;
  z-index: 5;
  opacity: 0;
}
.carousel:hover .carousel-arrow { opacity: 1; }
.carousel-arrow:hover {
  background: rgba(255,255,255,0.15);
  color: white;
  transform: translateY(-50%) scale(1.08);
}
.carousel-arrow-left { left: 12px; }
.carousel-arrow-right { right: 12px; }
@media (max-width: 767px) {
  .carousel-arrow { display: none; }
}

/* Dots */
.carousel-dots {
  position: absolute;
  bottom: 12px;
  left: 50%;
  transform: translateX(-50%);
  display: flex;
  gap: 6px;
  z-index: 5;
}
.carousel-dot {
  width: 24px; height: 4px;
  border-radius: 2px;
  overflow: hidden;
  background: rgba(255,255,255,0.12);
  cursor: pointer;
  transition: all 0.3s;
  padding: 0;
}
.carousel-dot.active { width: 32px; }
.carousel-dot-fill {
  display: block;
  width: 100%;
  height: 100%;
  border-radius: 2px;
  background: rgba(255,255,255,0.25);
  transition: background 0.3s;
}

/* Progress bar */
.carousel-progress {
  position: absolute;
  bottom: 0;
  left: 0; right: 0;
  height: 3px;
  background: rgba(255,255,255,0.05);
  z-index: 5;
}
.carousel-progress-bar {
  height: 100%;
  width: 0%;
  border-radius: 0 2px 2px 0;
  animation: carouselProgress 5s linear forwards;
}
.carousel-progress-bar.paused {
  animation-play-state: paused;
}
@keyframes carouselProgress {
  from { width: 0%; }
  to { width: 100%; }
}

/* ── Section icon ── */
.sf-section-icon {
  width: 40px; height: 40px; border-radius: 12px;
  display: flex; align-items: center; justify-content: center; flex-shrink: 0;
}

/* ── Horizontal scroll row ── */
.sf-scroll-row {
  display: flex; gap: 12px; overflow-x: auto; padding-bottom: 8px;
  -ms-overflow-style: none; scrollbar-width: none;
}
.sf-scroll-row::-webkit-scrollbar { display: none; }
.sf-scroll-card {
  min-width: 160px; max-width: 200px; flex-shrink: 0;
  border-radius: 1rem; overflow: hidden; cursor: pointer;
  background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); transition: all 0.3s;
}
.sf-scroll-card:hover { background: rgba(255,255,255,0.06); transform: translateY(-3px); box-shadow: 0 12px 30px rgba(0,0,0,0.3); }
.sf-scroll-img { aspect-ratio: 1; position: relative; overflow: hidden; background: rgba(255,255,255,0.02); }
.sf-discount-tag {
  position: absolute; top: 8px; left: 8px; padding: 2px 8px; border-radius: 6px;
  font-size: 0.65rem; font-weight: 800; background: #EF4444; color: white;
}

/* ── Search / Filters ── */
.sf-mob-search {
  width: 100%; padding: 0.6rem 0.9rem 0.6rem 2.2rem; border-radius: 10px;
  border: 1px solid rgba(255,255,255,0.06); background: rgba(255,255,255,0.03);
  color: white; font-size: 0.82rem; outline: none; transition: all 0.2s;
}
.sf-mob-search:focus { border-color: var(--a); }
.sf-mob-search::placeholder { color: rgba(255,255,255,0.25); }
.sf-no-scroll { -ms-overflow-style: none; scrollbar-width: none; }
.sf-no-scroll::-webkit-scrollbar { display: none; }
.sf-cat-pill {
  white-space: nowrap; padding: 0.4rem 0.9rem; border-radius: 2rem;
  font-size: 0.75rem; font-weight: 600; border: 1px solid rgba(255,255,255,0.08);
  background: transparent; color: rgba(255,255,255,0.45); cursor: pointer; transition: all 0.2s;
}
.sf-cat-pill:hover { background: rgba(255,255,255,0.05); color: white; }
.sf-cat-pill.active { background: var(--a); border-color: var(--a); color: white; }
.sf-cat-count { margin-left: 4px; opacity: 0.6; font-size: 0.7rem; }

/* ── Product Card ── */
.sf-card {
  border-radius: 1rem; overflow: hidden; cursor: pointer;
  background: rgba(255,255,255,0.025); border: 1px solid rgba(255,255,255,0.05);
  transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}
.sf-card:hover {
  background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1);
  transform: translateY(-5px); box-shadow: 0 20px 50px rgba(0,0,0,0.35);
}
.sf-card-img { aspect-ratio: 1; position: relative; overflow: hidden; background: rgba(255,255,255,0.02); }
.sf-card-body { padding: 0.85rem 1rem 1rem; }
.sf-badge-discount { position: absolute; top: 8px; left: 8px; padding: 3px 8px; border-radius: 8px; font-size: 0.68rem; font-weight: 800; background: #EF4444; color: white; }
.sf-badge-promo { position: absolute; top: 8px; left: 8px; padding: 3px 8px; border-radius: 8px; font-size: 0.6rem; font-weight: 800; color: white; text-transform: uppercase; letter-spacing: 0.03em; }
.sf-badge-oos { position: absolute; top: 8px; right: 8px; padding: 3px 8px; border-radius: 8px; font-size: 0.62rem; font-weight: 700; background: rgba(239,68,68,0.2); color: #EF4444; }
.sf-badge-low { position: absolute; top: 8px; right: 8px; padding: 3px 8px; border-radius: 8px; font-size: 0.62rem; font-weight: 700; background: rgba(245,158,11,0.15); color: #F59E0B; }
.sf-card-hover {
  position: absolute; inset: 0; display: flex; align-items: flex-end; justify-content: center; padding-bottom: 12px;
  background: linear-gradient(to top, rgba(0,0,0,0.6), transparent 50%);
  opacity: 0; transition: opacity 0.3s;
}
.sf-card:hover .sf-card-hover { opacity: 1; }
.sf-card-hover-btn {
  padding: 6px 14px; border-radius: 8px; font-size: 0.7rem; font-weight: 600;
  background: rgba(255,255,255,0.12); backdrop-filter: blur(8px); color: white;
}

/* ── Reviews ── */
.sf-review-card {
  padding: 1rem; border-radius: 0.85rem;
  background: rgba(255,255,255,0.025); border: 1px solid rgba(255,255,255,0.05);
}

/* ── Footer ── */
.sf-footer {
  border-top: 1px solid rgba(255,255,255,0.05); padding: 3rem 0 2rem;
  background: rgba(255,255,255,0.01);
}
.sf-footer-link {
  display: flex; align-items: center; gap: 8px;
  font-size: 0.78rem; color: rgba(255,255,255,0.45); text-decoration: none; transition: color 0.2s;
}
.sf-footer-link:hover { color: rgba(255,255,255,0.7); }
.sf-social-pill {
  display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 8px;
  font-size: 0.72rem; font-weight: 500; background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.5);
  text-decoration: none; transition: all 0.2s; border: 1px solid rgba(255,255,255,0.06);
}
.sf-social-pill:hover { background: rgba(255,255,255,0.08); color: white; }
.sf-share-btn {
  display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 8px;
  font-size: 0.72rem; font-weight: 500; background: rgba(255,255,255,0.04); color: rgba(255,255,255,0.5);
  border: 1px solid rgba(255,255,255,0.06); transition: all 0.2s;
}
.sf-share-btn:hover { background: rgba(255,255,255,0.08); color: white; }

/* ── FAB ── */
.sf-fab {
  position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 40;
  width: 56px; height: 56px; border-radius: 50%;
  background: #25D366; color: white; display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 24px rgba(37,211,102,0.4); transition: transform 0.2s;
}
.sf-fab:active { transform: scale(0.92); }
@media (min-width: 768px) { .sf-fab { display: none; } }

.sf-fab-chat {
  position: fixed; bottom: 5rem; right: 1.5rem; z-index: 39;
  width: 48px; height: 48px; border-radius: 50%;
  background: var(--a, #FF6B2C); color: white; display: flex; align-items: center; justify-content: center;
  box-shadow: 0 4px 20px rgba(255,107,44,0.35); transition: transform 0.2s;
  border: 2px solid rgba(255,255,255,0.1);
}
.sf-fab-chat:active { transform: scale(0.92); }
@media (min-width: 768px) { .sf-fab-chat { display: none; } }

/* ── Modal ── */
.sf-modal {
  position: relative; width: 100%; max-width: 800px; max-height: 90vh;
  background: #141420; border-radius: 1rem 1rem 0 0; overflow: hidden;
  animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
@media (min-width: 768px) { .sf-modal { border-radius: 1.25rem; max-height: 80vh; } }
.sf-modal-img { overflow: hidden; }
.sf-modal-body { flex: 1; display: flex; flex-direction: column; overflow: hidden; position: relative; }

/* ── Buttons ── */
.sf-btn-accent {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  padding: 0.8rem 1.5rem; border-radius: 0.75rem; font-size: 0.85rem; font-weight: 700;
  color: white; background: var(--a); transition: all 0.2s;
  box-shadow: 0 4px 16px color-mix(in srgb, var(--a) 30%, transparent);
}
.sf-btn-accent:hover { filter: brightness(1.1); transform: translateY(-1px); }
.sf-btn-wa-outline {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  padding: 0.65rem 1rem; border-radius: 0.75rem; font-size: 0.82rem; font-weight: 600;
  color: #25D366; background: rgba(37,211,102,0.08); border: 1px solid rgba(37,211,102,0.15); transition: all 0.2s;
}
.sf-btn-wa-outline:hover { background: #25D366; color: white; }

/* ── Form ── */
.sf-form-label {
  display: block; margin-bottom: 5px;
  font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: rgba(255,255,255,0.3);
}
.sf-form-input {
  width: 100%; padding: 0.65rem 0.85rem; border-radius: 0.65rem;
  border: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.03);
  color: white; font-size: 0.85rem; outline: none; transition: all 0.2s;
}
.sf-form-input:focus { border-color: var(--a); box-shadow: 0 0 0 3px color-mix(in srgb, var(--a) 12%, transparent); }
.sf-form-input::placeholder { color: rgba(255,255,255,0.2); }

@keyframes slideUp { from { transform: translateY(30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
</style>
