<script setup>
const year = new Date().getFullYear()

const columns = [
  {
    title: 'Produit',
    links: [
      { to: '/pricing', label: 'Tarifs' },
      { to: '/#features', label: 'Fonctionnalités' },
      { to: '/marketplace', label: 'Marketplace' },
      { to: '/examples', label: 'Exemples de boutiques' },
    ],
  },
  {
    title: 'Entreprise',
    links: [
      { to: '/help', label: 'Centre d\'aide' },
      { to: '/contact', label: 'Contact' },
      { to: '/help#faq', label: 'FAQ' },
    ],
  },
  {
    title: 'Légal',
    links: [
      { to: '/terms', label: 'Conditions d\'utilisation' },
      { to: '/privacy', label: 'Confidentialité' },
    ],
  },
]

const paymentLogos = [
  { name: 'MTN MoMo', color: '#FFC000', letters: 'MTN' },
  { name: 'Orange Money', color: '#FF7900', letters: 'OM' },
  { name: 'Flutterwave', color: '#F5A623', letters: 'FLW' },
  { name: 'Visa', color: '#1A1F71', letters: 'VISA' },
]
</script>

<template>
  <footer class="relative" style="background: var(--bg-secondary); border-top: 1px solid var(--border-default)">
    <div class="container-max py-16 md:py-20">
      <div class="grid grid-cols-2 md:grid-cols-12 gap-8 md:gap-12">
        <!-- Brand column -->
        <div class="col-span-2 md:col-span-5">
          <router-link to="/" class="flex items-center gap-2.5 no-underline mb-4">
            <img src="/logo.png" alt="Storely" class="w-9 h-9 object-contain" />
            <span class="font-display text-xl font-bold" style="color: var(--logo-text)">Storely</span>
          </router-link>
          <p class="text-sm leading-relaxed max-w-sm mb-6" style="color: var(--text-muted)">
            La plateforme e-commerce tout-en-un pour les entrepreneurs africains. Créez, vendez, encaissez — en quelques minutes.
          </p>

          <div class="flex flex-wrap items-center gap-2 mb-6">
            <span
              v-for="p in paymentLogos"
              :key="p.name"
              class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold font-display"
              :style="{ background: `${p.color}12`, color: p.color, border: `1px solid ${p.color}28` }"
            >
              {{ p.letters }}
            </span>
          </div>

          <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full" style="background: var(--bg-tertiary); border: 1px solid var(--border-default)">
            <span class="relative flex w-2 h-2">
              <span class="absolute inset-0 rounded-full animate-soft-pulse" style="background: #2DD4A8" />
              <span class="relative rounded-full w-2 h-2" style="background: #2DD4A8" />
            </span>
            <span class="text-xs font-medium" style="color: var(--text-secondary)">Tous les systèmes opérationnels</span>
          </div>
        </div>

        <!-- Link columns -->
        <div v-for="col in columns" :key="col.title" class="md:col-span-2">
          <h4 class="font-display text-xs font-bold mb-4 uppercase tracking-[0.12em]" style="color: var(--text-muted)">{{ col.title }}</h4>
          <ul class="space-y-2.5">
            <li v-for="l in col.links" :key="l.label">
              <router-link :to="l.to" class="text-sm transition-colors no-underline hover:!text-[var(--color-brand)]" style="color: var(--text-secondary)">
                {{ l.label }}
              </router-link>
            </li>
          </ul>
        </div>

        <!-- Newsletter -->
        <div class="col-span-2 md:col-span-1">
          <h4 class="font-display text-xs font-bold mb-4 uppercase tracking-[0.12em]" style="color: var(--text-muted)">Réseaux</h4>
          <ul class="space-y-2.5">
            <li><a href="#" class="text-sm no-underline transition-colors hover:!text-[var(--color-brand)]" style="color: var(--text-secondary)">Twitter</a></li>
            <li><a href="#" class="text-sm no-underline transition-colors hover:!text-[var(--color-brand)]" style="color: var(--text-secondary)">LinkedIn</a></li>
            <li><a href="#" class="text-sm no-underline transition-colors hover:!text-[var(--color-brand)]" style="color: var(--text-secondary)">Instagram</a></li>
          </ul>
        </div>
      </div>

      <div class="mt-14 pt-6 flex flex-col md:flex-row md:items-center justify-between gap-4" style="border-top: 1px solid var(--border-default)">
        <p class="text-xs" style="color: var(--text-faint)">© {{ year }} Storely. Fait avec soin à Douala 🇨🇲</p>
        <div class="flex items-center gap-6 text-xs" style="color: var(--text-faint)">
          <router-link to="/terms" class="no-underline transition-colors hover:!text-[var(--color-brand)]" style="color: inherit">Conditions</router-link>
          <router-link to="/privacy" class="no-underline transition-colors hover:!text-[var(--color-brand)]" style="color: inherit">Confidentialité</router-link>
          <span>v2.0</span>
        </div>
      </div>
    </div>
  </footer>
</template>
