<script setup>
import { ref, onMounted } from 'vue'

const loaded = ref(false)
onMounted(() => {
  requestAnimationFrame(() => { loaded.value = true })
})

const trustPoints = [
  'Gratuit pour démarrer',
  'Prêt en 10 minutes',
  'Paiement Mobile Money',
]
</script>

<template>
  <section class="relative overflow-hidden pt-28 pb-20 md:pt-36 md:pb-28">
    <!-- Background : soft warm halo + subtle dot grid -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="halo-brand" style="top: -15%; right: -10%;" />
      <div class="absolute inset-0 dots-bg opacity-60" style="mask-image: radial-gradient(ellipse at center top, black 40%, transparent 75%); -webkit-mask-image: radial-gradient(ellipse at center top, black 40%, transparent 75%);" />
    </div>

    <div class="container-max relative">
      <div class="grid lg:grid-cols-12 gap-12 lg:gap-10 items-center">
        <!-- Left : text -->
        <div class="lg:col-span-6 xl:col-span-6">
          <div
            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full mb-6 transition-all duration-700"
            :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-2'"
            style="background: var(--highlight-bg); border: 1px solid var(--highlight-border);"
          >
            <span class="inline-flex w-1.5 h-1.5 rounded-full animate-soft-pulse" style="background: var(--color-brand)" />
            <span class="text-xs font-semibold font-display tracking-wide" style="color: var(--color-brand-dark)">Nouveau · Réconciliation Mobile Money automatique</span>
          </div>

          <h1
            class="display-hero mb-6 transition-all duration-700 delay-100"
            :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
          >
            La boutique en ligne
            <span class="text-gradient">pensée pour vous.</span>
          </h1>

          <p
            class="text-lg md:text-xl leading-relaxed mb-8 max-w-xl transition-all duration-700 delay-200"
            :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
            style="color: var(--text-muted)"
          >
            Créez une vitrine professionnelle, recevez des paiements Mobile Money, gérez vos commandes et livraisons — le tout depuis votre téléphone. Sans développeur, sans technique.
          </p>

          <div
            class="flex flex-col sm:flex-row items-start gap-3 mb-10 transition-all duration-700 delay-300"
            :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'"
          >
            <router-link to="/register" class="btn-primary btn-lg">
              Créer ma boutique — Gratuit
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
              </svg>
            </router-link>
            <router-link to="/marketplace" class="btn-secondary btn-lg">Voir des exemples</router-link>
          </div>

          <div
            class="flex flex-wrap items-center gap-x-6 gap-y-2 transition-all duration-700 delay-500"
            :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0'"
          >
            <div v-for="t in trustPoints" :key="t" class="flex items-center gap-2 text-sm" style="color: var(--text-muted)">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2DD4A8" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
              <span>{{ t }}</span>
            </div>
          </div>
        </div>

        <!-- Right : layered mockup (real-looking storefront, no live animation) -->
        <div class="lg:col-span-6 xl:col-span-6 relative">
          <div
            class="relative transition-all duration-1000 delay-200"
            :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
          >
            <!-- Desktop browser mockup -->
            <div class="relative rounded-[20px] overflow-hidden" style="background: var(--bg-secondary); border: 1px solid var(--border-default); box-shadow: var(--card-shadow-xl);">
              <!-- browser chrome -->
              <div class="flex items-center gap-2 px-4 py-3 border-b" style="background: var(--bg-tertiary); border-color: var(--border-default)">
                <div class="flex items-center gap-1.5">
                  <div class="w-3 h-3 rounded-full" style="background: #FF5F57" />
                  <div class="w-3 h-3 rounded-full" style="background: #FFBD2E" />
                  <div class="w-3 h-3 rounded-full" style="background: #28C840" />
                </div>
                <div class="flex-1 mx-3 flex items-center gap-2 px-3 py-1 rounded-md text-xs font-mono" style="background: var(--bg-secondary); border: 1px solid var(--border-default); color: var(--text-muted)">
                  <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#2DD4A8" stroke-width="3"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                  storely.app/elegance-douala
                </div>
              </div>
              <!-- storefront mockup content -->
              <div class="relative p-0">
                <!-- shop banner -->
                <div class="h-24 relative overflow-hidden" style="background: linear-gradient(135deg, #FF6B2C, #FFAA33 80%)">
                  <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 20% 50%, rgba(255,255,255,0.3), transparent 50%), radial-gradient(circle at 80% 80%, rgba(255,255,255,0.2), transparent 40%);" />
                </div>
                <!-- shop identity -->
                <div class="px-5 -mt-8 relative">
                  <div class="flex items-end gap-3 mb-4">
                    <div class="w-16 h-16 rounded-2xl bg-white flex items-center justify-center shadow-lg text-xl font-display font-bold" style="color: var(--color-brand)">É</div>
                    <div class="pb-1">
                      <div class="flex items-center gap-1.5">
                        <span class="font-display font-bold text-sm" style="color: var(--text-primary)">Élégance Douala</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" fill="#2DD4A8"/><polyline points="8 12 11 15 16 9" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                      </div>
                      <div class="flex items-center gap-1.5 mt-0.5 text-[10px]" style="color: var(--text-muted)">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Douala · Akwa
                        <span style="color: var(--color-brand)">★ 4.9</span>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- products grid -->
                <div class="px-5 pb-5 grid grid-cols-3 gap-2">
                  <div v-for="(p, i) in [
                    { name: 'Robe Wax', price: '15 000', color: '#FF6B2C', emoji: '👗' },
                    { name: 'Sac cuir', price: '8 500', color: '#6C5CE7', emoji: '👜' },
                    { name: 'Boucles', price: '5 200', color: '#2DD4A8', emoji: '💎' },
                    { name: 'Sandales', price: '12 000', color: '#FF4D6A', emoji: '👡' },
                    { name: 'Bracelet', price: '3 800', color: '#FFAA33', emoji: '📿' },
                    { name: 'Chemise', price: '9 500', color: '#38BDF8', emoji: '👔' },
                  ]" :key="i" class="rounded-lg overflow-hidden" style="background: var(--bg-tertiary); border: 1px solid var(--border-default)">
                    <div class="aspect-square flex items-center justify-center text-2xl" :style="{ background: `linear-gradient(145deg, ${p.color}18, ${p.color}06)` }">{{ p.emoji }}</div>
                    <div class="px-1.5 py-1">
                      <p class="text-[9px] truncate" style="color: var(--text-muted)">{{ p.name }}</p>
                      <p class="text-[10px] font-bold" :style="{ color: p.color }">{{ p.price }} F</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Floating order notification card (premium, not glass) -->
            <div class="hidden md:flex absolute -bottom-6 -left-6 items-center gap-3 px-4 py-3 rounded-2xl animate-fade-up delay-700" style="background: var(--card-bg); border: 1px solid var(--border-default); box-shadow: var(--card-shadow-xl); max-width: 280px;">
              <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background: rgba(45, 212, 168, 0.12)">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2DD4A8" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
              </div>
              <div class="min-w-0">
                <p class="text-xs font-bold font-display" style="color: var(--text-primary)">Nouvelle commande !</p>
                <p class="text-[11px]" style="color: var(--text-muted)">Amina K. · <span class="font-semibold" style="color: #2DD4A8">+30 000 F</span></p>
              </div>
            </div>

            <!-- Floating momo payment card -->
            <div class="hidden md:flex absolute -top-5 -right-4 items-center gap-2.5 px-3.5 py-2.5 rounded-xl animate-fade-up delay-500" style="background: var(--card-bg); border: 1px solid var(--border-default); box-shadow: var(--card-shadow-xl);">
              <div class="w-8 h-8 rounded-lg flex items-center justify-center text-[10px] font-bold font-display text-white" style="background: #FFC000">MTN</div>
              <div>
                <p class="text-[10px]" style="color: var(--text-muted)">Paiement reçu</p>
                <p class="text-xs font-bold font-display" style="color: var(--text-primary)">15 000 FCFA</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
