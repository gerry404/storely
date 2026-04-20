<script setup>
const products = [
  { name: 'Robe Ankara', price: '25 000', img: '👗', accent: '#FF6B2C' },
  { name: 'Sac main', price: '18 500', img: '👜', accent: '#6C5CE7' },
  { name: 'Parfum oud', price: '42 000', img: '🕊️', accent: '#2DD4A8' },
  { name: 'Bracelet or', price: '12 800', img: '📿', accent: '#FFAA33' },
]
</script>

<template>
  <section class="section-lg relative overflow-hidden">
    <div class="halo-brand" style="bottom: -20%; left: -10%;" />

    <div class="container-max relative">
      <div class="grid lg:grid-cols-12 gap-12 items-center">
        <!-- Left : text -->
        <div class="lg:col-span-5">
          <span class="eyebrow">Storefront public</span>
          <h2 class="display-xl mt-4 mb-5">Une boutique qui inspire confiance.</h2>
          <p class="text-lg mb-8" style="color: var(--text-muted)">
            Vos clients voient une page professionnelle, rapide, adaptée au mobile. Photos produits, description claire, ajout au panier, paiement Mobile Money — tout est fluide.
          </p>

          <ul class="space-y-4">
            <li v-for="point in [
              'URL courte personnalisée : storely.app/votre-boutique',
              'Design responsive mobile d\'abord',
              'Chargement ultra-rapide (&lt; 2 secondes)',
              'SEO optimisé, indexé par Google',
              'Partage facile sur WhatsApp, Instagram, TikTok',
            ]" :key="point" class="flex items-start gap-3">
              <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 mt-0.5" style="background: rgba(45,212,168,0.15); color: #2DD4A8">
                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              </div>
              <span class="text-sm" style="color: var(--text-secondary)" v-html="point" />
            </li>
          </ul>

          <div class="mt-8">
            <router-link to="/marketplace" class="btn-secondary">
              Voir des boutiques réelles
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
            </router-link>
          </div>
        </div>

        <!-- Right : mobile + desktop mockups -->
        <div class="lg:col-span-7 relative">
          <div class="relative flex items-end justify-center gap-4">
            <!-- Desktop mockup (behind) -->
            <div class="hidden md:block flex-1 max-w-lg rounded-2xl overflow-hidden" style="background: var(--bg-secondary); border: 1px solid var(--border-default); box-shadow: var(--card-shadow-xl); transform: translateY(-10px);">
              <div class="flex items-center gap-1.5 px-3 py-2 border-b" style="background: var(--bg-tertiary); border-color: var(--border-default)">
                <div class="w-2 h-2 rounded-full" style="background: #FF5F57" />
                <div class="w-2 h-2 rounded-full" style="background: #FFBD2E" />
                <div class="w-2 h-2 rounded-full" style="background: #28C840" />
              </div>
              <!-- banner -->
              <div class="h-16" style="background: linear-gradient(135deg, #FF6B2C, #FFAA33)" />
              <!-- content -->
              <div class="px-4 -mt-6 pb-4">
                <div class="flex items-end gap-2 mb-3">
                  <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center font-display font-bold text-sm shadow-md" style="color: #FF6B2C">M</div>
                  <div class="pb-1">
                    <p class="font-display font-bold text-xs" style="color: var(--text-primary)">Maison Amina</p>
                    <p class="text-[9px]" style="color: var(--text-muted)">Mode · Douala</p>
                  </div>
                </div>
                <div class="grid grid-cols-4 gap-1.5">
                  <div v-for="(p, i) in products" :key="i" class="rounded-lg overflow-hidden" style="background: var(--bg-primary); border: 1px solid var(--border-default)">
                    <div class="aspect-square flex items-center justify-center text-lg" :style="{ background: `${p.accent}18` }">{{ p.img }}</div>
                    <div class="px-1 py-1">
                      <p class="text-[8px] truncate" style="color: var(--text-muted)">{{ p.name }}</p>
                      <p class="text-[9px] font-bold" :style="{ color: p.accent }">{{ p.price }}F</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Mobile mockup (front, right) -->
            <div class="relative w-[220px] md:w-[240px] shrink-0" style="filter: drop-shadow(0 30px 60px rgba(15,15,20,0.25))">
              <div class="rounded-[32px] overflow-hidden p-2" style="background: #0F0F14">
                <div class="rounded-[24px] overflow-hidden" style="background: var(--bg-secondary)">
                  <!-- notch -->
                  <div class="h-5 flex items-center justify-center" style="background: #0F0F14">
                    <div class="w-16 h-4 rounded-full" style="background: #000" />
                  </div>
                  <!-- banner -->
                  <div class="h-14 relative" style="background: linear-gradient(135deg, #FF6B2C, #FFAA33)" />
                  <!-- identity -->
                  <div class="px-3 -mt-5">
                    <div class="flex items-end gap-2">
                      <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center font-display font-bold text-sm shadow-md" style="color: #FF6B2C">M</div>
                    </div>
                    <p class="font-display font-bold text-sm mt-1.5" style="color: var(--text-primary)">Maison Amina</p>
                    <p class="text-[9px] mb-2" style="color: var(--text-muted)">Mode femme · Douala</p>
                  </div>
                  <!-- product tiles -->
                  <div class="px-3 pb-3 grid grid-cols-2 gap-1.5">
                    <div v-for="(p, i) in products.slice(0, 4)" :key="i" class="rounded-lg overflow-hidden" style="background: var(--bg-primary); border: 1px solid var(--border-default)">
                      <div class="aspect-square flex items-center justify-center text-xl" :style="{ background: `${p.accent}18` }">{{ p.img }}</div>
                      <div class="px-1.5 py-1">
                        <p class="text-[9px] truncate" style="color: var(--text-muted)">{{ p.name }}</p>
                        <p class="text-[10px] font-bold" :style="{ color: p.accent }">{{ p.price }} F</p>
                      </div>
                    </div>
                  </div>
                  <!-- CTA bar -->
                  <div class="px-3 pb-3">
                    <div class="w-full py-2 rounded-lg text-center text-[10px] font-bold font-display text-white" style="background: linear-gradient(135deg, #FF6B2C, #FF8F5C)">
                      Voir toute la collection
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
