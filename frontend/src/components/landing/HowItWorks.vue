<script setup>
const steps = [
  {
    num: '01',
    title: 'Créez votre compte',
    text: 'Inscription gratuite en 30 secondes. Pas de carte bancaire demandée, pas d\'engagement.',
    time: '30 sec',
  },
  {
    num: '02',
    title: 'Personnalisez votre boutique',
    text: 'Ajoutez votre logo, bannière, couleurs, nom. Storely génère votre URL : storely.app/votre-boutique.',
    time: '3 min',
  },
  {
    num: '03',
    title: 'Ajoutez vos produits',
    text: 'Photos, prix, description — ou laissez l\'IA écrire vos fiches à votre place. Organisez par catégorie.',
    time: '5 min',
  },
  {
    num: '04',
    title: 'Partagez et vendez',
    text: 'WhatsApp, Instagram, TikTok, bio Facebook — partagez votre lien. Les paiements arrivent sur votre Mobile Money.',
    time: 'Immédiat',
  },
]
</script>

<template>
  <section class="section-lg" style="background: var(--section-alt-bg)">
    <div class="container-max">
      <div class="text-center max-w-2xl mx-auto mb-16">
        <span class="eyebrow">Prise en main</span>
        <h2 class="display-xl mt-4 mb-4">Votre boutique en ligne, en 4 étapes.</h2>
        <p class="text-lg" style="color: var(--text-muted)">
          Moins de 10 minutes entre l'inscription et votre première vente possible. Aucun bagage technique nécessaire.
        </p>
      </div>

      <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-5 relative">
        <!-- Connector line (desktop) -->
        <div class="hidden lg:block absolute top-8 left-[12.5%] right-[12.5%] h-px" style="background: linear-gradient(to right, var(--border-strong), var(--border-default));" />

        <div v-for="(s, i) in steps" :key="s.num" class="card-flat p-6 relative">
          <div class="flex items-start justify-between mb-5">
            <div class="w-14 h-14 rounded-2xl flex items-center justify-center font-display font-black text-lg" :style="{
              background: i === 0 ? 'linear-gradient(135deg, #FF6B2C, #FFAA33)'
                       : i === 1 ? 'linear-gradient(135deg, #6C5CE7, #A78BFA)'
                       : i === 2 ? 'linear-gradient(135deg, #2DD4A8, #38BDF8)'
                       :            'linear-gradient(135deg, #FFAA33, #FF6B2C)',
              color: 'white'
            }">
              {{ s.num }}
            </div>
            <span class="badge">{{ s.time }}</span>
          </div>
          <h3 class="font-display font-bold text-lg mb-2" style="color: var(--text-primary)">{{ s.title }}</h3>
          <p class="text-sm leading-relaxed" style="color: var(--text-muted)">{{ s.text }}</p>
        </div>
      </div>

      <div class="text-center mt-12">
        <router-link to="/register" class="btn-primary btn-lg">
          Commencer maintenant — c'est gratuit
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </router-link>
      </div>
    </div>
  </section>
</template>
