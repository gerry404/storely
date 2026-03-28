<script setup>
import { ref } from 'vue'

const activeFilter = ref('all')

const filters = [
  { id: 'all', label: 'Toutes' },
  { id: 'mode', label: 'Mode' },
  { id: 'tech', label: 'Électronique' },
  { id: 'beauty', label: 'Beauté' },
  { id: 'food', label: 'Alimentation' },
  { id: 'artisan', label: 'Artisanat' },
]

const stores = [
  { name: 'Élégance Mode', slug: 'elegance-mode', category: 'mode', location: 'Douala, Akwa', products: 45, views: '12.3k', gradient: 'from-brand to-brand-amber', letter: 'E', verified: true },
  { name: 'TechZone CM', slug: 'techzone-cm', category: 'tech', location: 'Yaoundé, Bastos', products: 120, views: '28.7k', gradient: 'from-electric to-sky', letter: 'T', verified: true },
  { name: 'Beauty Palace', slug: 'beauty-palace', category: 'beauty', location: 'Douala, Bonapriso', products: 78, views: '9.1k', gradient: 'from-brand-coral to-brand', letter: 'B', verified: true },
  { name: 'Saveurs du Littoral', slug: 'saveurs-littoral', category: 'food', location: 'Douala, Deido', products: 32, views: '5.6k', gradient: 'from-mint to-electric', letter: 'S', verified: false },
  { name: 'Atelier Nkam', slug: 'atelier-nkam', category: 'artisan', location: 'Yaoundé, Nsimeyong', products: 56, views: '7.2k', gradient: 'from-brand-amber to-brand', letter: 'A', verified: true },
  { name: 'Chic & Tendance', slug: 'chic-tendance', category: 'mode', location: 'Douala, Bonamoussadi', products: 89, views: '15.4k', gradient: 'from-brand-coral to-electric', letter: 'C', verified: true },
  { name: 'PhoneFix Pro', slug: 'phonefix-pro', category: 'tech', location: 'Yaoundé, Mvan', products: 64, views: '11.0k', gradient: 'from-sky to-mint', letter: 'P', verified: false },
  { name: 'Glow Cosmetics', slug: 'glow-cosmetics', category: 'beauty', location: 'Douala, Makepe', products: 42, views: '8.3k', gradient: 'from-electric to-brand-coral', letter: 'G', verified: true },
  { name: 'Épices du Cameroun', slug: 'epices-cameroun', category: 'food', location: 'Bafoussam', products: 28, views: '3.9k', gradient: 'from-brand to-mint', letter: 'É', verified: false },
]

const filteredStores = () => {
  if (activeFilter.value === 'all') return stores
  return stores.filter(s => s.category === activeFilter.value)
}
</script>

<template>
  <main class="min-h-screen pt-28 pb-20">
    <div class="max-w-6xl mx-auto px-6">
      <!-- Header -->
      <div class="text-center mb-12">
        <span class="inline-block px-4 py-1.5 rounded-full glass text-xs font-display font-medium text-brand-amber uppercase tracking-wider mb-6">
          Vitrines
        </span>
        <h1 class="font-display font-bold text-4xl md:text-6xl text-white tracking-tight mb-4">
          Découvrez nos<br />
          <span class="text-gradient">boutiques actives</span>
        </h1>
        <p class="text-white/40 max-w-lg mx-auto">
          Voici quelques exemples de commerçants qui utilisent Storely au quotidien. Votre boutique pourrait être la prochaine.
        </p>
      </div>

      <!-- Filters -->
      <div class="flex flex-wrap justify-center gap-2 mb-12">
        <button
          v-for="f in filters"
          :key="f.id"
          @click="activeFilter = f.id"
          class="px-4 py-2 rounded-xl text-sm font-display font-medium transition-all duration-300"
          :class="activeFilter === f.id
            ? 'bg-white/10 text-white border border-white/10'
            : 'text-white/40 hover:text-white/60 border border-transparent'"
        >
          {{ f.label }}
        </button>
      </div>

      <!-- Store grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <router-link
          v-for="(store, i) in filteredStores()"
          :key="store.slug"
          :to="`/${store.slug}`"
          class="glass-card rounded-2xl p-6 group no-underline block transition-all duration-500"
          :style="{ animationDelay: `${i * 60}ms` }"
        >
          <!-- Store header -->
          <div class="flex items-center gap-3 mb-5">
            <div :class="`w-12 h-12 rounded-2xl bg-gradient-to-br ${store.gradient} flex items-center justify-center text-white font-display font-bold text-lg shrink-0`">
              {{ store.letter }}
            </div>
            <div class="flex-1 min-w-0">
              <div class="flex items-center gap-1.5">
                <span class="font-display font-bold text-white truncate">{{ store.name }}</span>
                <svg v-if="store.verified" width="14" height="14" viewBox="0 0 24 24" fill="#2DD4A8" class="shrink-0"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              </div>
              <p class="text-xs text-white/30">{{ store.location }}</p>
            </div>
          </div>

          <!-- Fake product grid -->
          <div class="grid grid-cols-3 gap-1.5 mb-5">
            <div v-for="j in 3" :key="j" class="aspect-square rounded-lg bg-white/[0.03] flex items-center justify-center">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="text-white/10"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/></svg>
            </div>
          </div>

          <!-- Stats -->
          <div class="flex items-center justify-between text-xs">
            <span class="text-white/30">{{ store.products }} produits</span>
            <span class="text-white/30">{{ store.views }} vues/mois</span>
          </div>

          <!-- Visit link -->
          <div class="mt-4 pt-4 border-t border-white/5 flex items-center justify-between">
            <span class="text-xs text-white/20 font-mono">storely.app/{{ store.slug }}</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-white/20 group-hover:text-brand group-hover:translate-x-1 transition-all"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </div>
        </router-link>
      </div>

      <!-- CTA -->
      <div class="mt-16 text-center">
        <p class="text-white/30 mb-4">Vous voulez apparaître ici ?</p>
        <router-link to="/register" class="btn-primary">
          Créer ma vitrine
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </router-link>
      </div>
    </div>
  </main>
</template>
