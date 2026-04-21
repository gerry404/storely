<script setup>
import { ref } from 'vue'
import { useReveal } from '../../composables/useReveal'
import { useSnapCarousel } from '../../composables/useParallax'

const headerRef = ref(null)
const gridRef = ref(null)
const carouselRef = ref(null)
useReveal(headerRef)
useReveal(gridRef)

const { activeIndex, scrollTo } = useSnapCarousel(carouselRef)

const features = [
  {
    icon: 'social',
    title: 'Un lien unique pour vos réseaux sociaux',
    text: 'Mettez votre URL Storely en bio Instagram, TikTok, Facebook ou WhatsApp. Vos followers découvrent tout votre catalogue, ajoutent au panier, et payent, sans quitter leur téléphone.',
    accent: '#FF6B2C',
  },
  {
    icon: 'momo',
    title: 'Mobile Money qui se réconcilie seul',
    text: 'MTN MoMo, Orange Money, Visa, Mastercard. Chaque paiement est rattaché à la bonne commande automatiquement. Fini les captures d\'écran à vérifier une par une.',
    accent: '#FFC000',
    badge: 'Exclusif',
  },
  {
    icon: 'cart',
    title: 'Paniers abandonnés relancés',
    text: 'Un client quitte sans payer. Storely lui envoie un rappel WhatsApp avec un code promo, automatiquement. Nos marchands récupèrent en moyenne 1 panier sur 4.',
    accent: '#EC4899',
    badge: 'Nouveau',
  },
  {
    icon: 'sparkle',
    title: 'L\'IA écrit vos fiches produits',
    text: 'Prenez une photo. L\'IA reconnaît le produit, écrit un titre accrocheur, une description qui vend, et les tags pour le SEO. Vous relisez, vous publiez.',
    accent: '#6C5CE7',
  },
  {
    icon: 'truck',
    title: 'Zones de livraison au tarif juste',
    text: 'Douala centre, Yaoundé, province, international. Chaque client voit le bon prix à son adresse, au moment du checkout. Plus de désaccord sur les frais.',
    accent: '#2DD4A8',
  },
  {
    icon: 'chart',
    title: 'Tableau de bord qui pilote vraiment',
    text: 'Revenus du jour, commandes à traiter, top produits, taux de conversion. L\'IA vous suggère quand lancer une promo, quel produit pousser, quel canal marche le mieux.',
    accent: '#38BDF8',
  },
]

const iconPath = (name) => {
  const map = {
    social: 'M18 8a3 3 0 11-2.83-2.995M18 8a3 3 0 01-6 0M18 8v1.5a3 3 0 003 3V15M6 8a3 3 0 106 0M6 8v1.5a3 3 0 01-3 3V15m0 0a3 3 0 103 3m0 0h12m0-3a3 3 0 11-3 3m0-3v-.5m3-7.5V5',
    momo: 'M2 7h20v10a2 2 0 01-2 2H4a2 2 0 01-2-2V7zM2 7l2-3h16l2 3M7 14h3M14 14h3',
    cart: 'M3 3h2l.9 4M5.9 7H21l-2 8H7.5M5.9 7L7.5 15M10 20a1 1 0 100-2 1 1 0 000 2zM18 20a1 1 0 100-2 1 1 0 000 2z',
    sparkle: 'M12 2l2.4 7.4H22l-6 4.5 2.3 7.3L12 16.6l-6.3 4.6L8 13.9 2 9.4h7.6z',
    truck: 'M2 16V7a1 1 0 011-1h13v10H2zM16 8h4l3 3v5h-7M5.5 20a2.5 2.5 0 100-5 2.5 2.5 0 000 5zM18.5 20a2.5 2.5 0 100-5 2.5 2.5 0 000 5z',
    chart: 'M4 20V10M10 20V4M16 20v-7M22 20H2',
  }
  return map[name] || map.sparkle
}
</script>

<template>
  <section id="features" class="section-lg relative">
    <div class="container-max">
      <div ref="headerRef" class="reveal-mask max-w-2xl mb-12 md:mb-14">
        <span class="eyebrow">Tout ce qui compte, dans un seul outil</span>
        <h2 class="display-xl mt-4 mb-4">Vous vendez, Storely s'occupe du reste.</h2>
        <p class="text-lg" style="color: var(--text-muted)">
          Six fonctionnalités qui remplacent tous les outils qu'on vous vend à côté. Pensées pour un marchand qui gère sa boutique depuis son téléphone, entre deux commandes.
        </p>
      </div>

      <!-- Desktop grid -->
      <div ref="gridRef" class="reveal-stagger hidden md:grid grid-cols-2 lg:grid-cols-3 gap-5">
        <article v-for="f in features" :key="f.title" class="feature-tile lift-card group">
          <div class="flex items-start justify-between mb-5">
            <div class="relative w-12 h-12 rounded-xl flex items-center justify-center transition-transform group-hover:scale-110 group-hover:rotate-3"
                 :style="{ background: `linear-gradient(135deg, ${f.accent}18, ${f.accent}06)`, border: `1px solid ${f.accent}28` }">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" :stroke="f.accent" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path :d="iconPath(f.icon)" />
              </svg>
            </div>
            <span v-if="f.badge" class="badge badge-brand text-[10px]">{{ f.badge }}</span>
          </div>
          <h3 class="font-display font-bold text-lg mb-2 tracking-tight" style="color: var(--text-primary)">{{ f.title }}</h3>
          <p class="text-sm leading-relaxed" style="color: var(--text-muted)">{{ f.text }}</p>
          <!-- accent line qui pousse au hover -->
          <div class="mt-5 h-px w-8 transition-all group-hover:w-16" :style="{ background: f.accent }" />
        </article>
      </div>

      <!-- Mobile carousel -->
      <div class="md:hidden -mx-6">
        <div ref="carouselRef" class="snap-row">
          <article v-for="f in features" :key="f.title" class="feature-tile">
            <div class="flex items-start justify-between mb-5">
              <div class="w-12 h-12 rounded-xl flex items-center justify-center"
                   :style="{ background: `linear-gradient(135deg, ${f.accent}18, ${f.accent}06)`, border: `1px solid ${f.accent}28` }">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" :stroke="f.accent" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path :d="iconPath(f.icon)" />
                </svg>
              </div>
              <span v-if="f.badge" class="badge badge-brand text-[10px]">{{ f.badge }}</span>
            </div>
            <h3 class="font-display font-bold text-lg mb-2 tracking-tight" style="color: var(--text-primary)">{{ f.title }}</h3>
            <p class="text-sm leading-relaxed" style="color: var(--text-muted)">{{ f.text }}</p>
            <div class="mt-5 h-px w-10" :style="{ background: f.accent }" />
          </article>
        </div>
        <div class="snap-dots px-6">
          <button v-for="(f, i) in features" :key="i"
                  :class="{ active: activeIndex === i }"
                  @click="scrollTo(i)"
                  :aria-label="`Aller à la fonctionnalité ${i + 1}`" />
        </div>
      </div>
    </div>
  </section>
</template>
