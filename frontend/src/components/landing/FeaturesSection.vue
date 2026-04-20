<script setup>
const features = [
  {
    icon: 'store',
    title: 'Vitrine professionnelle',
    text: 'Votre boutique en ligne prête en 10 minutes, avec logo, bannière, couleurs personnalisables et URL courte partageable partout.',
    accent: '#FF6B2C',
  },
  {
    icon: 'momo',
    title: 'Paiements Mobile Money',
    text: 'Encaissez MTN MoMo, Orange Money, Visa et Mastercard en direct sur votre boutique. Réconciliation automatique des paiements reçus.',
    accent: '#FFC000',
    badge: 'Nouveau',
  },
  {
    icon: 'package',
    title: 'Gestion des commandes',
    text: 'Toutes vos commandes au même endroit. Notifications WhatsApp instantanées, statuts personnalisés, historique complet.',
    accent: '#6C5CE7',
  },
  {
    icon: 'truck',
    title: 'Zones de livraison intelligentes',
    text: 'Définissez vos zones de livraison avec des tarifs différents. Douala, Yaoundé, reste du pays — chaque client voit le bon prix au checkout.',
    accent: '#2DD4A8',
    badge: 'Nouveau',
  },
  {
    icon: 'sparkle',
    title: 'IA pour fiches produits',
    text: 'Photographiez votre produit, l\'IA écrit automatiquement le titre, la description et les tags. Gagnez des heures de rédaction.',
    accent: '#FF4D6A',
    badge: 'Nouveau',
  },
  {
    icon: 'chart',
    title: 'Statistiques en temps réel',
    text: 'Vues de la boutique, taux de conversion, top produits, revenus du mois — toutes les métriques importantes dans un dashboard clair.',
    accent: '#38BDF8',
  },
  {
    icon: 'tag',
    title: 'Promotions & codes promo',
    text: 'Créez des remises, flash sales, codes promo, bannières promotionnelles. Chronomètre automatique, déclenchement programmé.',
    accent: '#FFAA33',
  },
  {
    icon: 'cart',
    title: 'Récupération panier abandonné',
    text: 'Quand un client abandonne son panier, Storely le relance automatiquement sur WhatsApp avec un code promo. Récupérez jusqu\'à 30% du chiffre perdu.',
    accent: '#EC4899',
    badge: 'Nouveau',
  },
  {
    icon: 'chat',
    title: 'Chat client intégré',
    text: 'Discutez avec vos clients directement sur votre boutique. Messages non-lus, historique par client, réponses rapides sauvegardées.',
    accent: '#25D366',
  },
  {
    icon: 'palette',
    title: 'Page builder storefront',
    text: 'Composez votre storefront avec des sections drag-and-drop : bannières, carrousels, témoignages, produits mis en avant. Chaque boutique unique.',
    accent: '#A855F7',
    badge: 'Nouveau',
  },
  {
    icon: 'digital',
    title: 'Produits digitaux',
    text: 'Vendez des ebooks, formations, templates. Livraison automatique par email après paiement. Téléchargements sécurisés et limités.',
    accent: '#0EA5E9',
  },
  {
    icon: 'shield',
    title: 'Sécurisé & hébergé',
    text: 'SSL gratuit, sauvegardes quotidiennes, protection anti-fraude. Vous vous concentrez sur la vente, nous nous occupons de la technique.',
    accent: '#64748B',
  },
]

const iconPath = (name) => {
  const icons = {
    store: 'M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z|M9 22V12h6v10',
    momo: 'M2 8h20v12H2z|M2 12h20|M7 16h.01|M11 16h2',
    package: 'M16.5 9.4l-9-5.19|M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z|M3.27 6.96L12 12.01l8.73-5.05|M12 22.08V12',
    truck: 'M1 3h15v13H1z|M16 8h4l3 3v5h-7V8z|M5.5 21a2.5 2.5 0 100-5 2.5 2.5 0 000 5zM18.5 21a2.5 2.5 0 100-5 2.5 2.5 0 000 5z',
    sparkle: 'M12 2l2.4 7.4H22l-6 4.5 2.3 7.3L12 16.6l-6.3 4.6L8 13.9 2 9.4h7.6z',
    chart: 'M18 20V10|M12 20V4|M6 20v-6',
    tag: 'M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z|M7 7h.01',
    cart: 'M9 22a1 1 0 100-2 1 1 0 000 2zM20 22a1 1 0 100-2 1 1 0 000 2zM1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6',
    chat: 'M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z',
    palette: 'M12 2a10 10 0 00-7.071 17.071c.67.67 1.563 1.049 2.486 1.073A2.5 2.5 0 0010 17.5c0-.673-.27-1.324-.746-1.8a.75.75 0 01.53-1.28H12a10 10 0 000-20z|M8.5 9.5a1 1 0 100-2 1 1 0 000 2zM15.5 9.5a1 1 0 100-2 1 1 0 000 2zM17.5 13.5a1 1 0 100-2 1 1 0 000 2z',
    digital: 'M4 22h16|M12 2v14|M7 11l5 5 5-5',
    shield: 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z',
  }
  return icons[name] || icons.store
}
</script>

<template>
  <section id="features" class="section-lg relative">
    <div class="container-max">
      <div class="max-w-2xl mb-14">
        <span class="eyebrow">Tout inclus</span>
        <h2 class="display-xl mt-4 mb-4">Tout ce qu'il vous faut pour vendre en ligne.</h2>
        <p class="text-lg" style="color: var(--text-muted)">
          Une plateforme unique qui remplace 10 outils différents. Pensée pour les marchands africains, prête pour demain.
        </p>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div v-for="f in features" :key="f.title" class="feature-tile">
          <div class="flex items-start justify-between mb-5">
            <div
              class="w-12 h-12 rounded-xl flex items-center justify-center"
              :style="{ background: `${f.accent}12`, border: `1px solid ${f.accent}28` }"
            >
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" :stroke="f.accent" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path v-for="d in iconPath(f.icon).split('|')" :key="d" :d="d" />
              </svg>
            </div>
            <span v-if="f.badge" class="badge badge-brand text-[10px]">{{ f.badge }}</span>
          </div>
          <h3 class="font-display font-bold text-lg mb-2 tracking-tight" style="color: var(--text-primary)">{{ f.title }}</h3>
          <p class="text-sm leading-relaxed" style="color: var(--text-muted)">{{ f.text }}</p>
        </div>
      </div>
    </div>
  </section>
</template>
