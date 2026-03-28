<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { productImageUrl, shopLogoUrl, storageUrl } from '../../composables/useStorage'

const route = useRoute()
const router = useRouter()

const shopSlug = computed(() => route.params.shopSlug)
const productSlug = computed(() => route.params.productSlug)

const product = ref(null)
const shop = ref(null)
const related = ref([])
const loading = ref(true)
const error = ref(false)
const selectedImageIndex = ref(0)

// Order form
const showOrderForm = ref(false)
const orderForm = ref({ customer_name: '', customer_phone: '', customer_email: '', quantity: 1, note: '' })
const ordering = ref(false)
const orderSuccess = ref(false)
const orderError = ref('')
const paymentMethod = ref('flutterwave') // flutterwave or cod
const createdOrder = ref(null)

// Payment modal
const showPaymentModal = ref(false)
const paymentStep = ref('processing') // processing, waiting, success, error
const paymentMessage = ref('')

// Chat form
const showChatForm = ref(false)
const chatForm = ref({ customer_name: '', customer_phone: '', customer_email: '', body: '' })
const chatSending = ref(false)
const chatSent = ref(false)
const chatError = ref('')

const fmt = (n) => new Intl.NumberFormat('fr-FR').format(n)

const images = computed(() => {
  if (!product.value) return []
  const imgs = []
  if (Array.isArray(product.value.images)) {
    product.value.images.forEach(img => {
      if (img.startsWith('http')) imgs.push(img)
      else imgs.push(storageUrl(img))
    })
  }
  if (!imgs.length) {
    const s = productImageUrl(product.value)
    if (s) imgs.push(s)
  }
  return imgs
})

const hasDiscount = computed(() => product.value?.compare_price && product.value.compare_price > product.value.price)
const discountPercent = computed(() => {
  if (!hasDiscount.value) return 0
  return Math.round((1 - product.value.price / product.value.compare_price) * 100)
})

const accent = computed(() => {
  if (!shop.value?.customization) return '#FF6B2C'
  try {
    const c = typeof shop.value.customization === 'string' ? JSON.parse(shop.value.customization) : shop.value.customization
    return c?.accent_color || c?.primaryColor || '#FF6B2C'
  } catch { return '#FF6B2C' }
})

onMounted(async () => {
  try {
    const res = await fetch(`/api/shops/${shopSlug.value}/products/${productSlug.value}`)
    if (!res.ok) throw new Error('Not found')
    const data = await res.json()
    product.value = data.product
    shop.value = data.shop
    related.value = data.related || []
  } catch {
    error.value = true
  } finally {
    loading.value = false
  }
})

const openWhatsApp = () => {
  if (!shop.value?.whatsapp) return
  const phone = shop.value.whatsapp.replace(/[^0-9+]/g, '')
  const msg = `Bonjour ! Je suis intéressé(e) par "${product.value.name}" à ${fmt(product.value.price)} FCFA sur ${shop.value.name}.`
  window.open(`https://wa.me/${phone}?text=${encodeURIComponent(msg)}`, '_blank')
}

const submitOrder = async () => {
  ordering.value = true
  orderError.value = ''
  try {
    const res = await fetch('/api/orders', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({
        product_id: product.value.id,
        ...orderForm.value,
        payment_method: paymentMethod.value,
      }),
    })
    const data = await res.json()
    if (!res.ok) throw new Error(data.message || 'Erreur')
    createdOrder.value = data.order

    // If online payment, use Flutterwave Inline modal
    if (paymentMethod.value === 'flutterwave') {
      showOrderForm.value = false
      showPaymentModal.value = true
      paymentStep.value = 'processing'

      const payRes = await fetch('/api/payments/order', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ order_id: data.order.id }),
      })
      const payData = await payRes.json()
      if (!payRes.ok) throw new Error(payData.message || 'Erreur de paiement')

      paymentStep.value = 'waiting'

      // Use Flutterwave Inline (modal popup)
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
            logo: '/icons/icon-192.png',
          },
          callback: async (response) => {
            paymentStep.value = 'processing'
            try {
              const verifyRes = await fetch('/api/payments/verify', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({
                  transaction_id: String(response.transaction_id),
                  tx_ref: response.tx_ref,
                }),
              })
              const verifyData = await verifyRes.json()
              if (verifyRes.ok && (verifyData.status === 'success' || verifyData.status === 'already_processed')) {
                paymentStep.value = 'success'
              } else {
                paymentStep.value = 'error'
                paymentMessage.value = verifyData.message || 'Paiement non confirmé.'
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
        // Fallback: redirect if Flutterwave JS not loaded
        window.location.href = payData.payment_link
      }
      return
    }

    // COD — show success
    orderSuccess.value = true
    showOrderForm.value = false
  } catch (e) {
    orderError.value = e.message
    showPaymentModal.value = false
  } finally {
    ordering.value = false
  }
}

const submitChat = async () => {
  if (!chatForm.value.customer_name || !chatForm.value.customer_phone || !chatForm.value.body) {
    chatError.value = 'Remplissez tous les champs obligatoires.'
    return
  }
  chatSending.value = true
  chatError.value = ''
  try {
    const res = await fetch(`/api/shops/${shopSlug.value}/chat`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
      body: JSON.stringify({
        ...chatForm.value,
        product_id: product.value.id,
      }),
    })
    if (!res.ok) { const d = await res.json(); throw new Error(d.message || 'Erreur') }
    chatSent.value = true
  } catch (e) {
    chatError.value = e.message
  } finally {
    chatSending.value = false
  }
}

const goToRelated = (p) => {
  router.push(`/${shopSlug.value}/${p.slug}`)
}
</script>

<template>
  <div class="min-h-screen" style="background: var(--bg-primary)">
    <!-- Loading -->
    <div v-if="loading" class="min-h-screen flex items-center justify-center">
      <div class="w-8 h-8 border-2 border-brand border-t-transparent rounded-full animate-spin" />
    </div>

    <!-- Error -->
    <div v-else-if="error" class="min-h-screen flex flex-col items-center justify-center gap-4">
      <h1 class="font-display font-bold text-2xl text-white">Produit introuvable</h1>
      <router-link :to="'/' + shopSlug" class="text-brand text-sm hover:underline">Retour à la boutique</router-link>
    </div>

    <!-- Product page -->
    <div v-else>
    <!-- Top bar -->
    <header class="sticky top-0 z-40 backdrop-blur-xl border-b" :style="{ background: 'var(--bg-secondary)', borderColor: 'var(--border-default)' }">
      <div class="max-w-5xl mx-auto px-4 py-3 flex items-center gap-3">
        <router-link :to="'/' + shopSlug" class="flex items-center gap-2 no-underline">
          <div class="w-8 h-8 rounded-lg overflow-hidden bg-white/10 flex items-center justify-center">
            <img v-if="shopLogoUrl(shop)" :src="shopLogoUrl(shop)" class="w-full h-full object-cover" />
            <span v-else class="text-xs font-bold text-white">{{ shop?.name?.[0] }}</span>
          </div>
          <span class="text-sm font-semibold" :style="{ color: 'var(--text-primary)' }">{{ shop?.name }}</span>
        </router-link>
        <span v-if="shop?.verified" class="w-4 h-4 rounded-full bg-blue-500 flex items-center justify-center">
          <svg width="10" height="10" viewBox="0 0 24 24" fill="white"><polyline points="20 6 9 17 4 12" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </span>
      </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 py-6 md:py-10">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12">
        <!-- Images -->
        <div>
          <div class="aspect-square rounded-2xl overflow-hidden relative" :style="{ background: 'var(--bg-secondary)' }">
            <img v-if="images.length" :src="images[selectedImageIndex]" :alt="product.name" class="w-full h-full object-contain" />
            <div v-else class="w-full h-full flex items-center justify-center">
              <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="text-white/10" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
            </div>
            <div v-if="hasDiscount" class="absolute top-3 left-3 px-3 py-1.5 rounded-lg text-sm font-bold text-white" :style="{ background: accent }">
              -{{ discountPercent }}%
            </div>
            <div v-if="product.is_preorder" class="absolute top-3 right-3 px-3 py-1.5 rounded-lg text-sm font-bold bg-amber-500/90 text-white">
              Précommande
            </div>
          </div>

          <!-- Thumbnails -->
          <div v-if="images.length > 1" class="flex gap-2 mt-3 overflow-x-auto pb-1">
            <button
              v-for="(img, i) in images"
              :key="i"
              @click="selectedImageIndex = i"
              class="w-16 h-16 rounded-lg overflow-hidden border-2 shrink-0 transition-all"
              :class="i === selectedImageIndex ? 'border-brand' : 'border-transparent opacity-60'"
            >
              <img :src="img" class="w-full h-full object-cover" />
            </button>
          </div>
        </div>

        <!-- Info -->
        <div>
          <span v-if="product.category" class="text-xs font-semibold uppercase tracking-wider" :style="{ color: accent }">
            {{ product.category }}
            <span v-if="product.subcategory" class="text-white/30"> / {{ product.subcategory }}</span>
          </span>
          <h1 class="font-display font-bold text-2xl md:text-3xl text-white mt-1">{{ product.name }}</h1>

          <!-- Price -->
          <div class="flex items-baseline gap-3 mt-4">
            <span class="text-3xl font-display font-black" :style="{ color: accent }">
              {{ fmt(product.price) }} <small class="text-sm font-semibold opacity-70">F CFA</small>
            </span>
            <span v-if="hasDiscount" class="text-base line-through text-white/30">{{ fmt(product.compare_price) }}</span>
          </div>

          <!-- Preorder info -->
          <div v-if="product.is_preorder" class="mt-3 p-3 rounded-xl bg-amber-500/10 border border-amber-500/20">
            <p class="text-sm text-amber-400 font-medium">Précommande</p>
            <p v-if="product.preorder_available_at" class="text-xs text-white/50 mt-0.5">
              Disponible le {{ new Date(product.preorder_available_at).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) }}
            </p>
            <p v-if="product.preorder_deposit_percent" class="text-xs text-white/50 mt-0.5">
              Acompte de {{ product.preorder_deposit_percent }}% requis
            </p>
          </div>

          <!-- Stock status -->
          <div class="mt-4">
            <span v-if="product.product_type === 'digital'" class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-lg bg-purple-500/10 text-purple-400">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              Produit digital
            </span>
            <span v-else-if="product.stock === 0" class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-lg bg-red-500/10 text-red-400">
              <span class="w-1.5 h-1.5 rounded-full bg-red-400" />Rupture de stock
            </span>
            <span v-else-if="product.stock && product.stock <= 5" class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-lg bg-amber-500/10 text-amber-400">
              <span class="w-1.5 h-1.5 rounded-full bg-amber-400" />Plus que {{ product.stock }}
            </span>
          </div>

          <!-- Description -->
          <p v-if="product.description" class="text-sm text-white/55 leading-relaxed mt-5 whitespace-pre-line">{{ product.description }}</p>

          <!-- Long description -->
          <div v-if="product.long_description" class="mt-5 pt-5 border-t" :style="{ borderColor: 'var(--border-default)' }">
            <h3 class="text-sm font-semibold text-white/70 mb-2">Description détaillée</h3>
            <p class="text-sm text-white/45 leading-relaxed whitespace-pre-line">{{ product.long_description }}</p>
          </div>

          <!-- Actions -->
          <div class="mt-6 space-y-3">
            <button
              @click="showOrderForm = true"
              :disabled="product.stock === 0 && product.product_type !== 'digital'"
              class="w-full py-3.5 rounded-xl text-sm font-bold text-white transition-all disabled:opacity-30 hover:shadow-lg"
              :style="{ background: accent }"
            >
              {{ product.is_preorder ? 'Précommander' : 'Commander' }} · {{ fmt(product.price) }} F CFA
            </button>

            <!-- Message seller -->
            <button
              @click="showChatForm = true"
              class="w-full py-3.5 rounded-xl text-sm font-bold transition-all border"
              style="color: rgba(255,255,255,0.7); border-color: rgba(255,255,255,0.1); background: rgba(255,255,255,0.03);"
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="inline mr-2 -mt-0.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
              Envoyer un message au vendeur
            </button>

            <button
              v-if="shop?.whatsapp"
              @click="openWhatsApp"
              class="w-full py-3.5 rounded-xl text-sm font-bold transition-all border"
              style="color: #25D366; border-color: rgba(37,211,102,0.3); background: rgba(37,211,102,0.05);"
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="#25D366" class="inline mr-2 -mt-0.5"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492l4.629-1.467A11.96 11.96 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75c-2.14 0-4.12-.669-5.75-1.806l-.412-.248-4.272 1.12 1.14-4.163-.272-.432A9.71 9.71 0 012.25 12 9.75 9.75 0 0112 2.25 9.75 9.75 0 0121.75 12 9.75 9.75 0 0112 21.75z"/></svg>
              Contacter par WhatsApp
            </button>
          </div>

          <!-- Order success -->
          <div v-if="orderSuccess" class="mt-4 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
            <p class="text-sm text-emerald-400 font-medium">Commande envoyée avec succès !</p>
            <p class="text-xs text-white/40 mt-1">Le vendeur vous contactera bientôt.</p>
          </div>

          <!-- Chat sent success -->
          <div v-if="chatSent" class="mt-4 p-4 rounded-xl bg-blue-500/10 border border-blue-500/20">
            <p class="text-sm text-blue-400 font-medium">Message envoyé !</p>
            <p class="text-xs text-white/40 mt-1">Le vendeur recevra votre message et vous répondra bientôt.</p>
          </div>
        </div>
      </div>

      <!-- Related products -->
      <div v-if="related.length" class="mt-16">
        <h2 class="font-display font-bold text-lg text-white mb-6">Produits similaires</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <div
            v-for="p in related"
            :key="p.id"
            @click="goToRelated(p)"
            class="rounded-xl overflow-hidden cursor-pointer group border"
            :style="{ background: 'var(--bg-secondary)', borderColor: 'var(--border-default)' }"
          >
            <div class="aspect-square overflow-hidden">
              <img v-if="productImageUrl(p)" :src="productImageUrl(p)" :alt="p.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
              <div v-else class="w-full h-full flex items-center justify-center bg-white/5">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" class="text-white/10" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/></svg>
              </div>
            </div>
            <div class="p-3">
              <h3 class="text-sm font-semibold text-white/80 truncate">{{ p.name }}</h3>
              <p class="text-sm font-bold mt-1" :style="{ color: accent }">{{ fmt(p.price) }} F</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Shop footer -->
      <div class="mt-16 pt-6 border-t text-center" :style="{ borderColor: 'var(--border-default)' }">
        <router-link :to="'/' + shopSlug" class="text-sm font-medium no-underline" :style="{ color: accent }">
          Voir toute la boutique {{ shop?.name }}
        </router-link>
      </div>
    </main>

    <!-- Order form modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showOrderForm" class="fixed inset-0 z-[60] flex items-end md:items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="showOrderForm = false" />
          <div class="relative w-full max-w-md rounded-t-3xl md:rounded-3xl overflow-hidden max-h-[90vh] overflow-y-auto" :style="{ background: 'var(--bg-secondary)' }">
            <div class="p-6">
              <div class="flex items-center justify-between mb-5">
                <h3 class="font-display font-bold text-lg text-white">
                  {{ product.is_preorder ? 'Précommander' : 'Commander' }}
                </h3>
                <button @click="showOrderForm = false" class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-white/40 hover:text-white transition">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
              </div>

              <!-- Product summary -->
              <div class="flex items-center gap-3 p-3 rounded-xl mb-5" :style="{ background: 'var(--bg-primary)' }">
                <div class="w-12 h-12 rounded-lg overflow-hidden shrink-0">
                  <img v-if="images.length" :src="images[0]" class="w-full h-full object-cover" />
                </div>
                <div class="min-w-0">
                  <p class="text-sm font-semibold text-white truncate">{{ product.name }}</p>
                  <p class="text-sm font-bold" :style="{ color: accent }">{{ fmt(product.price) }} F CFA</p>
                </div>
              </div>

              <div v-if="orderError" class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">{{ orderError }}</div>

              <form @submit.prevent="submitOrder" class="space-y-4">
                <div>
                  <label class="block text-xs font-medium mb-1" :style="{ color: 'var(--text-muted)' }">Nom complet *</label>
                  <input v-model="orderForm.customer_name" type="text" required class="w-full px-4 py-3 rounded-xl border text-sm outline-none focus:border-brand" :style="{ background: 'var(--bg-primary)', borderColor: 'var(--border-default)', color: 'var(--text-primary)' }" />
                </div>
                <div>
                  <label class="block text-xs font-medium mb-1" :style="{ color: 'var(--text-muted)' }">Téléphone *</label>
                  <input v-model="orderForm.customer_phone" type="tel" required class="w-full px-4 py-3 rounded-xl border text-sm outline-none focus:border-brand" :style="{ background: 'var(--bg-primary)', borderColor: 'var(--border-default)', color: 'var(--text-primary)' }" />
                </div>
                <div>
                  <label class="block text-xs font-medium mb-1" :style="{ color: 'var(--text-muted)' }">Email (optionnel)</label>
                  <input v-model="orderForm.customer_email" type="email" class="w-full px-4 py-3 rounded-xl border text-sm outline-none focus:border-brand" :style="{ background: 'var(--bg-primary)', borderColor: 'var(--border-default)', color: 'var(--text-primary)' }" />
                </div>
                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="block text-xs font-medium mb-1" :style="{ color: 'var(--text-muted)' }">Quantité</label>
                    <input v-model.number="orderForm.quantity" type="number" min="1" class="w-full px-4 py-3 rounded-xl border text-sm outline-none focus:border-brand" :style="{ background: 'var(--bg-primary)', borderColor: 'var(--border-default)', color: 'var(--text-primary)' }" />
                  </div>
                  <div class="flex items-end">
                    <p class="text-sm font-bold pb-3" :style="{ color: accent }">
                      Total: {{ fmt(product.price * (orderForm.quantity || 1)) }} F
                    </p>
                  </div>
                </div>
                <div>
                  <label class="block text-xs font-medium mb-1" :style="{ color: 'var(--text-muted)' }">Note (optionnel)</label>
                  <textarea v-model="orderForm.note" rows="2" maxlength="500" class="w-full px-4 py-3 rounded-xl border text-sm outline-none focus:border-brand resize-none" :style="{ background: 'var(--bg-primary)', borderColor: 'var(--border-default)', color: 'var(--text-primary)' }" />
                </div>

                <!-- Deposit info for preorder -->
                <div v-if="product.is_preorder && product.preorder_deposit_percent" class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-400">
                  Acompte de {{ product.preorder_deposit_percent }}% soit {{ fmt(Math.ceil(product.price * (orderForm.quantity || 1) * product.preorder_deposit_percent / 100)) }} F CFA
                </div>

                <!-- Payment method choice -->
                <div v-if="product.product_type !== 'digital'">
                  <label class="block text-xs font-medium mb-2" :style="{ color: 'var(--text-muted)' }">Mode de paiement</label>
                  <div class="grid grid-cols-2 gap-2">
                    <button
                      type="button"
                      @click="paymentMethod = 'flutterwave'"
                      class="p-3 rounded-xl border text-center transition-all"
                      :class="paymentMethod === 'flutterwave' ? 'border-brand bg-brand/10' : 'border-white/10 bg-white/[0.02]'"
                    >
                      <svg class="w-5 h-5 mx-auto mb-1.5" :class="paymentMethod === 'flutterwave' ? 'text-brand' : 'text-white/40'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                      <p class="text-xs font-semibold" :class="paymentMethod === 'flutterwave' ? 'text-brand' : 'text-white/60'">Payer en ligne</p>
                      <p class="text-[10px] mt-0.5" :class="paymentMethod === 'flutterwave' ? 'text-white/50' : 'text-white/25'">Mobile Money / Carte</p>
                    </button>
                    <button
                      type="button"
                      @click="paymentMethod = 'cod'"
                      class="p-3 rounded-xl border text-center transition-all"
                      :class="paymentMethod === 'cod' ? 'border-brand bg-brand/10' : 'border-white/10 bg-white/[0.02]'"
                    >
                      <svg class="w-5 h-5 mx-auto mb-1.5" :class="paymentMethod === 'cod' ? 'text-brand' : 'text-white/40'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z"/><circle cx="14" cy="14" r="2"/></svg>
                      <p class="text-xs font-semibold" :class="paymentMethod === 'cod' ? 'text-brand' : 'text-white/60'">À la livraison</p>
                      <p class="text-[10px] mt-0.5" :class="paymentMethod === 'cod' ? 'text-white/50' : 'text-white/25'">Le vendeur vous contacte</p>
                    </button>
                  </div>
                </div>

                <button
                  type="submit"
                  :disabled="ordering"
                  class="w-full py-3.5 rounded-xl text-sm font-bold text-white transition-all disabled:opacity-50"
                  :style="{ background: accent }"
                >
                  {{ ordering ? 'Traitement...' : (paymentMethod === 'flutterwave' ? `Payer ${fmt(product.price * (orderForm.quantity || 1))} F CFA` : (product.is_preorder ? 'Confirmer la précommande' : 'Confirmer la commande')) }}
                </button>
              </form>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Chat form modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showChatForm" class="fixed inset-0 z-[60] flex items-end md:items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="showChatForm = false" />
          <div class="relative w-full max-w-md rounded-t-3xl md:rounded-3xl overflow-hidden" :style="{ background: 'var(--bg-secondary)' }">
            <div class="p-6">
              <!-- Chat sent success -->
              <div v-if="chatSent" class="text-center py-6">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center" style="background: rgba(59,130,246,0.1)">
                  <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#3B82F6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <h3 class="font-display font-bold text-lg text-white mb-2">Message envoyé !</h3>
                <p class="text-sm text-white/40 mb-5">Le vendeur recevra votre message dans son tableau de bord.</p>
                <button @click="showChatForm = false" class="px-8 py-2.5 rounded-xl text-sm font-bold text-white" :style="{ background: accent }">Fermer</button>
              </div>

              <!-- Chat form -->
              <div v-else>
                <div class="flex items-center justify-between mb-5">
                  <h3 class="font-display font-bold text-lg text-white">Envoyer un message</h3>
                  <button @click="showChatForm = false" class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center text-white/40 hover:text-white transition">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                  </button>
                </div>

                <!-- Product context -->
                <div class="flex items-center gap-3 p-3 rounded-xl mb-5" :style="{ background: 'var(--bg-primary)' }">
                  <div class="w-10 h-10 rounded-lg overflow-hidden shrink-0">
                    <img v-if="images.length" :src="images[0]" class="w-full h-full object-cover" />
                  </div>
                  <div class="min-w-0">
                    <p class="text-xs text-white/40">À propos de</p>
                    <p class="text-sm font-semibold text-white truncate">{{ product.name }}</p>
                  </div>
                </div>

                <div v-if="chatError" class="mb-4 p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm">{{ chatError }}</div>

                <form @submit.prevent="submitChat" class="space-y-4">
                  <div>
                    <label class="block text-xs font-medium mb-1" :style="{ color: 'var(--text-muted)' }">Votre nom *</label>
                    <input v-model="chatForm.customer_name" type="text" required placeholder="Jean Kamga" class="w-full px-4 py-3 rounded-xl border text-sm outline-none focus:border-brand" :style="{ background: 'var(--bg-primary)', borderColor: 'var(--border-default)', color: 'var(--text-primary)' }" />
                  </div>
                  <div>
                    <label class="block text-xs font-medium mb-1" :style="{ color: 'var(--text-muted)' }">Téléphone *</label>
                    <input v-model="chatForm.customer_phone" type="tel" required placeholder="+237 6XX XXX XXX" class="w-full px-4 py-3 rounded-xl border text-sm outline-none focus:border-brand" :style="{ background: 'var(--bg-primary)', borderColor: 'var(--border-default)', color: 'var(--text-primary)' }" />
                  </div>
                  <div>
                    <label class="block text-xs font-medium mb-1" :style="{ color: 'var(--text-muted)' }">Email (optionnel)</label>
                    <input v-model="chatForm.customer_email" type="email" class="w-full px-4 py-3 rounded-xl border text-sm outline-none focus:border-brand" :style="{ background: 'var(--bg-primary)', borderColor: 'var(--border-default)', color: 'var(--text-primary)' }" />
                  </div>
                  <div>
                    <label class="block text-xs font-medium mb-1" :style="{ color: 'var(--text-muted)' }">Message *</label>
                    <textarea v-model="chatForm.body" rows="3" required maxlength="5000" placeholder="Bonjour, j'ai une question sur ce produit..." class="w-full px-4 py-3 rounded-xl border text-sm outline-none focus:border-brand resize-none" :style="{ background: 'var(--bg-primary)', borderColor: 'var(--border-default)', color: 'var(--text-primary)' }" />
                  </div>
                  <button
                    type="submit"
                    :disabled="chatSending"
                    class="w-full py-3.5 rounded-xl text-sm font-bold text-white transition-all disabled:opacity-50"
                    :style="{ background: accent }"
                  >
                    {{ chatSending ? 'Envoi...' : 'Envoyer le message' }}
                  </button>
                </form>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- Payment status modal -->
    <Teleport to="body">
      <Transition name="modal">
        <div v-if="showPaymentModal" class="fixed inset-0 z-[70] flex items-center justify-center p-4">
          <div class="absolute inset-0 bg-black/80 backdrop-blur-md" />
          <div class="relative w-full max-w-sm rounded-3xl overflow-hidden" style="background: linear-gradient(145deg, #12121a, #1a1a2e); border: 1px solid rgba(255,255,255,0.08)">
            <div class="p-8 text-center">

              <!-- Processing / Initializing -->
              <template v-if="paymentStep === 'processing'">
                <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center" style="background: rgba(255,107,44,0.1)">
                  <svg class="w-10 h-10 text-brand animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.2"/>
                    <path d="M12 2a10 10 0 019.95 9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                  </svg>
                </div>
                <h3 class="font-display font-bold text-lg text-white mb-2">Préparation du paiement...</h3>
                <p class="text-sm text-white/40">Un instant, nous initialisons votre transaction.</p>
              </template>

              <!-- Waiting for payment -->
              <template v-else-if="paymentStep === 'waiting'">
                <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center" style="background: rgba(59,130,246,0.1)">
                  <svg class="w-10 h-10 text-blue-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/>
                  </svg>
                </div>
                <h3 class="font-display font-bold text-lg text-white mb-2">Finalisez votre paiement</h3>
                <p class="text-sm text-white/40 mb-4">Complétez le paiement dans la fenêtre Flutterwave.</p>
                <div class="flex items-center justify-center gap-2">
                  <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse" />
                  <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse" style="animation-delay: 0.2s" />
                  <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse" style="animation-delay: 0.4s" />
                </div>
              </template>

              <!-- Success -->
              <template v-else-if="paymentStep === 'success'">
                <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center" style="background: rgba(34,197,94,0.1)">
                  <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#22C55E" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                </div>
                <h3 class="font-display font-bold text-xl text-white mb-2">Paiement confirmé !</h3>
                <p class="text-sm text-white/50 mb-6">Votre commande a été payée et confirmée. Le vendeur va préparer votre produit.</p>
                <button @click="showPaymentModal = false; orderSuccess = true" class="px-8 py-3 rounded-xl text-sm font-bold text-white transition-all" :style="{ background: accent }">
                  Fermer
                </button>
              </template>

              <!-- Error -->
              <template v-else-if="paymentStep === 'error'">
                <div class="w-20 h-20 mx-auto mb-6 rounded-full flex items-center justify-center" style="background: rgba(239,68,68,0.1)">
                  <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                  </svg>
                </div>
                <h3 class="font-display font-bold text-xl text-white mb-2">Paiement échoué</h3>
                <p class="text-sm text-white/50 mb-6">{{ paymentMessage || 'Le paiement n\'a pas abouti.' }}</p>
                <div class="flex gap-3 justify-center">
                  <button @click="showPaymentModal = false" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white/60 border border-white/10 hover:bg-white/5 transition-all">
                    Fermer
                  </button>
                  <button @click="showPaymentModal = false; showOrderForm = true" class="px-6 py-2.5 rounded-xl text-sm font-bold text-white transition-all" :style="{ background: accent }">
                    Réessayer
                  </button>
                </div>
              </template>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
    </div>
  </div>
</template>

<style scoped>
.modal-enter-active { transition: all 0.3s ease; }
.modal-leave-active { transition: all 0.2s ease; }
.modal-enter-from { opacity: 0; }
.modal-enter-from > div:last-child { transform: translateY(20px); }
.modal-leave-to { opacity: 0; }
</style>
