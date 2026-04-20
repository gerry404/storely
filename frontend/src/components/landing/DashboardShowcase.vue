<script setup>
const highlights = [
  {
    title: 'Tableau de bord clair',
    text: 'Revenus du jour, commandes à traiter, vues de la boutique — les chiffres clés affichés en premier, sans surcharge.',
  },
  {
    title: 'Commandes en 1 clic',
    text: 'Acceptez, marquez comme expédiée, contactez le client par WhatsApp sans quitter l\'écran.',
  },
  {
    title: 'Insights IA',
    text: 'Storely analyse vos données et vous suggère des actions : "vos ventes sont 3x plus fortes le jeudi, programmez une promotion."',
  },
]
</script>

<template>
  <section class="section-lg relative" style="background: var(--section-alt-bg)">
    <div class="container-max">
      <div class="grid lg:grid-cols-12 gap-12 items-center">
        <!-- Left : Dashboard mockup -->
        <div class="lg:col-span-7 order-2 lg:order-1 relative">
          <!-- Dashboard window -->
          <div class="relative rounded-2xl overflow-hidden" style="background: var(--bg-secondary); border: 1px solid var(--border-default); box-shadow: var(--card-shadow-xl);">
            <!-- Header bar -->
            <div class="flex items-center justify-between px-5 py-3 border-b" style="background: var(--bg-tertiary); border-color: var(--border-default)">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand to-brand-amber flex items-center justify-center text-white font-bold text-xs">É</div>
                <div>
                  <p class="text-xs font-bold" style="color: var(--text-primary)">Élégance Douala</p>
                  <p class="text-[10px]" style="color: var(--text-muted)">Dashboard</p>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg" style="background: var(--bg-secondary); border: 1px solid var(--border-default)" />
                <div class="w-7 h-7 rounded-lg" style="background: var(--bg-secondary); border: 1px solid var(--border-default)" />
              </div>
            </div>

            <div class="p-5">
              <!-- KPI grid -->
              <div class="grid grid-cols-3 gap-3 mb-5">
                <div v-for="kpi in [
                  { label: 'Revenus aujourd\'hui', value: '127 500 F', diff: '+32%', color: '#2DD4A8' },
                  { label: 'Commandes', value: '8', diff: '+3', color: '#FF6B2C' },
                  { label: 'Vues boutique', value: '1 420', diff: '+18%', color: '#6C5CE7' },
                ]" :key="kpi.label" class="p-3 rounded-xl" style="background: var(--bg-primary); border: 1px solid var(--border-default)">
                  <p class="text-[10px] mb-1" style="color: var(--text-muted)">{{ kpi.label }}</p>
                  <div class="flex items-baseline gap-1.5">
                    <span class="font-display font-bold text-base" style="color: var(--text-primary)">{{ kpi.value }}</span>
                    <span class="text-[10px] font-bold" :style="{ color: kpi.color }">{{ kpi.diff }}</span>
                  </div>
                </div>
              </div>

              <!-- Chart placeholder -->
              <div class="rounded-xl mb-5 p-4 relative overflow-hidden" style="background: var(--bg-primary); border: 1px solid var(--border-default); height: 140px;">
                <p class="text-[10px] font-semibold mb-2" style="color: var(--text-muted)">Revenus des 7 derniers jours</p>
                <svg viewBox="0 0 300 80" class="w-full h-[80px]" preserveAspectRatio="none">
                  <defs>
                    <linearGradient id="chartGrad" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" stop-color="#FF6B2C" stop-opacity="0.25" />
                      <stop offset="100%" stop-color="#FF6B2C" stop-opacity="0" />
                    </linearGradient>
                  </defs>
                  <path d="M0,60 L40,45 L80,52 L120,30 L160,35 L200,18 L240,22 L280,10 L300,5 L300,80 L0,80 Z" fill="url(#chartGrad)" />
                  <path d="M0,60 L40,45 L80,52 L120,30 L160,35 L200,18 L240,22 L280,10 L300,5" stroke="#FF6B2C" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </div>

              <!-- Orders list -->
              <div class="rounded-xl overflow-hidden" style="background: var(--bg-primary); border: 1px solid var(--border-default)">
                <div class="px-3 py-2 border-b flex items-center justify-between" style="border-color: var(--border-default)">
                  <p class="text-xs font-bold font-display" style="color: var(--text-primary)">Dernières commandes</p>
                  <span class="text-[10px]" style="color: var(--color-brand)">Voir tout →</span>
                </div>
                <div class="divide-y" style="--tw-divide-opacity: 1; border-color: var(--border-default)">
                  <div v-for="(o, i) in [
                    { name: 'Amina K.', item: 'Robe Wax', price: '30 000 F', status: 'Payée', color: '#2DD4A8' },
                    { name: 'Joseph M.', item: 'Sac cuir', price: '8 500 F', status: 'En cours', color: '#FFAA33' },
                    { name: 'Claire T.', item: 'Boucles', price: '10 400 F', status: 'Payée', color: '#2DD4A8' },
                  ]" :key="i" class="flex items-center gap-3 px-3 py-2.5" style="border-color: var(--border-default)">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-[11px] font-bold text-white" style="background: #FF6B2C">
                      {{ o.name[0] }}
                    </div>
                    <div class="flex-1 min-w-0">
                      <p class="text-xs font-semibold" style="color: var(--text-primary)">{{ o.name }}</p>
                      <p class="text-[10px]" style="color: var(--text-muted)">{{ o.item }}</p>
                    </div>
                    <span class="text-xs font-bold font-display" style="color: var(--text-primary)">{{ o.price }}</span>
                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold" :style="{ background: `${o.color}15`, color: o.color }">{{ o.status }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Floating insight card -->
          <div class="hidden md:flex absolute -right-4 -bottom-6 items-start gap-3 p-4 rounded-2xl max-w-[280px]" style="background: var(--card-bg); border: 1px solid var(--border-default); box-shadow: var(--card-shadow-xl)">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background: linear-gradient(135deg, #6C5CE7, #38BDF8)">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><path d="M12 2l2.4 7.4H22l-6 4.5 2.3 7.3L12 16.6l-6.3 4.6L8 13.9 2 9.4h7.6z"/></svg>
            </div>
            <div>
              <p class="text-[10px] font-bold font-display mb-0.5" style="color: #6C5CE7">INSIGHT IA</p>
              <p class="text-xs leading-snug" style="color: var(--text-primary)">Vos ventes sont 3× plus fortes le jeudi. Programmez une promo ce jeudi.</p>
            </div>
          </div>
        </div>

        <!-- Right : text -->
        <div class="lg:col-span-5 order-1 lg:order-2">
          <span class="eyebrow">Dashboard</span>
          <h2 class="display-xl mt-4 mb-5">Tout piloter depuis un seul écran.</h2>
          <p class="text-lg mb-8" style="color: var(--text-muted)">
            Un dashboard pensé pour la vente, pas pour la technique. Les informations qui comptent, à portée de clic.
          </p>

          <div class="space-y-6">
            <div v-for="h in highlights" :key="h.title" class="flex gap-4">
              <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 mt-1" style="background: var(--color-brand); color: white">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              </div>
              <div>
                <h3 class="font-display font-bold mb-1" style="color: var(--text-primary)">{{ h.title }}</h3>
                <p class="text-sm leading-relaxed" style="color: var(--text-muted)">{{ h.text }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
