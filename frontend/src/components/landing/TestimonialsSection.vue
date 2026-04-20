<script setup>
const testimonials = [
  {
    name: 'Amina Ngonga',
    role: 'Fondatrice · Élégance Douala',
    shop: 'storely.app/elegance-douala',
    photo: '👩🏾',
    color: '#FF6B2C',
    quote: 'En un mois sur Storely, j\'ai vendu plus qu\'en 6 mois sur Instagram. La réconciliation Mobile Money m\'évite des heures de comptabilité.',
    metric: { value: '+340%', label: 'de ventes en 30 jours' },
  },
  {
    name: 'Joseph Mbarga',
    role: 'Gérant · Tech Bamenda',
    shop: 'storely.app/tech-bamenda',
    photo: '👨🏾‍💼',
    color: '#6C5CE7',
    quote: 'Ce que j\'aime : tout est dans la même app. Commandes, paiements, livraisons, chat clients. Plus besoin de jongler entre 5 outils différents.',
    metric: { value: '92 commandes', label: 'en novembre' },
  },
  {
    name: 'Claire Tchoumi',
    role: 'Créatrice · Maison Claire',
    shop: 'storely.app/maison-claire',
    photo: '👩🏾‍🎨',
    color: '#2DD4A8',
    quote: 'Le panier abandonné récupéré via WhatsApp m\'a rapporté 280 000 FCFA le premier mois. Sans rien faire. C\'est magique.',
    metric: { value: '280k F', label: 'récupérés en panier abandonné' },
  },
]
</script>

<template>
  <section class="section-lg" style="background: var(--section-alt-bg)">
    <div class="container-max">
      <div class="max-w-2xl mx-auto text-center mb-14">
        <span class="eyebrow">Ils nous font confiance</span>
        <h2 class="display-xl mt-4 mb-4">L'avis de marchands réels.</h2>
        <p class="text-lg" style="color: var(--text-muted)">
          Pas des témoignages fabriqués. Des entrepreneurs qui vendent chaque jour avec Storely.
        </p>
      </div>

      <div class="grid md:grid-cols-3 gap-5">
        <div v-for="t in testimonials" :key="t.name" class="card p-7 flex flex-col">
          <!-- Quote icon -->
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" :stroke="t.color" stroke-width="2" class="mb-5 opacity-80">
            <path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"/>
            <path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"/>
          </svg>

          <p class="text-sm leading-relaxed flex-1 mb-6" style="color: var(--text-primary)">
            « {{ t.quote }} »
          </p>

          <div class="p-4 rounded-xl mb-5" :style="{ background: `${t.color}10`, border: `1px solid ${t.color}26` }">
            <p class="font-display font-black text-2xl" :style="{ color: t.color }">{{ t.metric.value }}</p>
            <p class="text-xs" style="color: var(--text-muted)">{{ t.metric.label }}</p>
          </div>

          <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-full flex items-center justify-center text-2xl" :style="{ background: `${t.color}18`, border: `1px solid ${t.color}30` }">
              {{ t.photo }}
            </div>
            <div>
              <p class="text-sm font-bold font-display" style="color: var(--text-primary)">{{ t.name }}</p>
              <p class="text-xs" style="color: var(--text-muted)">{{ t.role }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
