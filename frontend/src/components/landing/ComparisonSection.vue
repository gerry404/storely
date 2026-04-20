<script setup>
const rows = [
  { feature: 'Page boutique professionnelle', storely: true, whatsapp: false, instagram: 'partial', autres: true },
  { feature: 'Paiement en ligne direct (MoMo)', storely: true, whatsapp: false, instagram: false, autres: 'partial' },
  { feature: 'Réconciliation auto des paiements', storely: true, whatsapp: false, instagram: false, autres: false, highlight: true },
  { feature: 'Zones de livraison & tarifs', storely: true, whatsapp: false, instagram: false, autres: 'partial' },
  { feature: 'Panier abandonné auto WhatsApp', storely: true, whatsapp: false, instagram: false, autres: false, highlight: true },
  { feature: 'Gestion des stocks', storely: true, whatsapp: false, instagram: false, autres: true },
  { feature: 'Catalogue de +20 produits', storely: true, whatsapp: 'partial', instagram: 'partial', autres: true },
  { feature: 'Statistiques de vente', storely: true, whatsapp: false, instagram: 'partial', autres: true },
  { feature: 'IA rédaction fiche produit', storely: true, whatsapp: false, instagram: false, autres: false, highlight: true },
  { feature: 'Prix mensuel', storely: 'Gratuit · 5 000 F · 15 000 F', whatsapp: 'Gratuit', instagram: 'Gratuit', autres: '29-299 $' },
]

const cellClass = (v) => {
  if (v === true) return 'check'
  if (v === false) return 'cross'
  if (v === 'partial') return 'partial'
  return 'text'
}
</script>

<template>
  <section class="section-lg">
    <div class="container-max">
      <div class="max-w-2xl mb-10">
        <span class="eyebrow">Comparatif</span>
        <h2 class="display-xl mt-4 mb-4">Pourquoi Storely plutôt qu'un autre outil ?</h2>
        <p class="text-lg" style="color: var(--text-muted)">
          Comparaison honnête entre vendre sur Storely, WhatsApp direct, Instagram shop, ou une plateforme étrangère.
        </p>
      </div>

      <div class="card-flat overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead>
              <tr style="background: var(--bg-tertiary); border-bottom: 1px solid var(--border-default)">
                <th class="text-left p-4 font-display font-bold text-xs uppercase tracking-wider" style="color: var(--text-muted); min-width: 260px">Fonctionnalité</th>
                <th class="p-4 font-display font-bold" style="color: var(--color-brand); min-width: 130px">
                  <div class="flex flex-col items-center gap-1">
                    <img src="/logo.png" class="w-6 h-6" alt="Storely" />
                    Storely
                  </div>
                </th>
                <th class="p-4 font-display font-bold" style="color: var(--text-muted); min-width: 130px">
                  <div class="flex flex-col items-center gap-1">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold text-white" style="background: #25D366">W</div>
                    WhatsApp seul
                  </div>
                </th>
                <th class="p-4 font-display font-bold" style="color: var(--text-muted); min-width: 130px">
                  <div class="flex flex-col items-center gap-1">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold text-white" style="background: linear-gradient(135deg, #F58529, #DD2A7B, #8134AF)">I</div>
                    Instagram shop
                  </div>
                </th>
                <th class="p-4 font-display font-bold" style="color: var(--text-muted); min-width: 130px">
                  <div class="flex flex-col items-center gap-1">
                    <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold text-white" style="background: #64748B">S</div>
                    Shopify &co
                  </div>
                </th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="row in rows" :key="row.feature" :style="{ background: row.highlight ? 'var(--highlight-bg)' : 'transparent' }" style="border-bottom: 1px solid var(--border-hairline)">
                <td class="p-4 font-medium" style="color: var(--text-primary)">
                  <span class="flex items-center gap-2">
                    {{ row.feature }}
                    <span v-if="row.highlight" class="badge badge-brand text-[9px]">Exclusif</span>
                  </span>
                </td>
                <td class="p-4 text-center" style="color: var(--text-primary)">
                  <template v-if="row.storely === true">
                    <span class="inline-flex w-7 h-7 rounded-full items-center justify-center" style="background: rgba(45,212,168,0.15); color: #0F9E7A">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </span>
                  </template>
                  <span v-else class="text-xs font-bold font-display" style="color: var(--color-brand-dark)">{{ row.storely }}</span>
                </td>
                <td v-for="(col, k) in ['whatsapp', 'instagram', 'autres']" :key="k" class="p-4 text-center">
                  <template v-if="row[col] === true">
                    <span class="inline-flex w-7 h-7 rounded-full items-center justify-center" style="background: rgba(45,212,168,0.15); color: #0F9E7A">
                      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </span>
                  </template>
                  <template v-else-if="row[col] === false">
                    <span class="inline-flex w-7 h-7 rounded-full items-center justify-center" style="background: rgba(239,68,68,0.1); color: #EF4444">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </span>
                  </template>
                  <template v-else-if="row[col] === 'partial'">
                    <span class="inline-flex w-7 h-7 rounded-full items-center justify-center" style="background: rgba(255,170,51,0.18); color: #E08A00">
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="4"/></svg>
                    </span>
                  </template>
                  <span v-else class="text-xs" style="color: var(--text-muted)">{{ row[col] }}</span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <div class="flex items-center justify-center gap-6 mt-6 text-xs" style="color: var(--text-muted)">
        <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background: #2DD4A8" /> Inclus</span>
        <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background: #FFAA33" /> Partiel</span>
        <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full" style="background: #EF4444" /> Absent</span>
      </div>
    </div>
  </section>
</template>
