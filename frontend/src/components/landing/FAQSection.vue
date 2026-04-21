<script setup>
import { ref } from 'vue'
import { useReveal } from '../../composables/useReveal'

const opened = ref(0)
const headerRef = ref(null)
const listRef = ref(null)
useReveal(headerRef)
useReveal(listRef)

const faqs = [
  {
    q: 'Je vends déjà sur Instagram et WhatsApp, pourquoi Storely en plus ?',
    a: 'Vos réseaux sociaux sont faits pour créer l\'envie, pas pour encaisser. Avec Storely, vous mettez une seule URL en bio, et vos clients passent de "j\'aime cette photo" à "j\'ai payé" en 30 secondes, sans discussion par DM, sans capture d\'écran de paiement, sans relance manuelle.',
  },
  {
    q: 'Combien coûte réellement Storely ?',
    a: 'Un plan gratuit complet pour démarrer (jusqu\'à 10 produits). Pour vendre sans limite avec paiement en ligne, le plan Starter est à 5 000 FCFA par mois, le Pro à 15 000 FCFA par mois. Zéro commission sur vos ventes, zéro frais caché. Seuls les frais standards de Mobile Money et de carte s\'appliquent.',
  },
  {
    q: 'Comment fonctionne la réconciliation Mobile Money ?',
    a: 'Quand un client paie via MTN MoMo ou Orange Money, Storely reçoit la notification de paiement et rattache automatiquement la transaction à la bonne commande. Le statut passe à "Payée" sans que vous leviez le petit doigt. Vous économisez des heures de comptabilité manuelle chaque semaine.',
  },
  {
    q: 'Est-ce qu\'il faut savoir coder ou être à l\'aise avec l\'informatique ?',
    a: 'Absolument pas. Tout se fait depuis une interface visuelle simple, avec des formulaires et des boutons. Si vous savez publier une photo sur Instagram, vous savez gérer Storely. Et si vous bloquez, notre équipe répond en moins de 2 heures par WhatsApp.',
  },
  {
    q: 'Puis-je utiliser mon propre domaine (ma-boutique.com) ?',
    a: 'Oui, avec le plan Pro. Vous connectez votre domaine en quelques clics et votre boutique devient accessible à votre adresse. Certificat SSL offert, configuration automatique.',
  },
  {
    q: 'Et si mes clients n\'ont pas de smartphone récent ?',
    a: 'Votre boutique fonctionne sur tous les appareils, smartphone, ordinateur, tablette, même les téléphones basiques qui accèdent au web. Vous pouvez aussi enregistrer des commandes manuelles depuis votre dashboard pour les clients qui vous appellent directement.',
  },
  {
    q: 'Que se passe-t-il si j\'arrête de payer l\'abonnement ?',
    a: 'Votre boutique reste accessible en lecture seule pendant 30 jours, le temps de réactiver. Vos données (produits, commandes, clients) ne sont jamais supprimées. Vous pouvez réactiver à tout moment sans rien perdre.',
  },
  {
    q: 'L\'IA qui rédige les fiches produit, comment ça marche concrètement ?',
    a: 'Vous uploadez une photo de votre produit. L\'IA reconnaît l\'objet, écrit un titre accrocheur, une description vendeuse, et les tags optimisés pour Google. Vous relisez, vous ajustez si besoin, vous publiez. Ce qui prenait 20 minutes prend désormais 30 secondes.',
  },
]

const toggle = (i) => { opened.value = opened.value === i ? -1 : i }
</script>

<template>
  <section id="faq" class="section-lg">
    <div class="container-max">
      <div class="grid lg:grid-cols-12 gap-10 lg:gap-14 items-start">
        <div ref="headerRef" class="reveal-mask lg:col-span-4 lg:sticky lg:top-24">
          <span class="eyebrow">Questions fréquentes</span>
          <h2 class="display-xl mt-4 mb-5">Vos questions, nos vraies réponses.</h2>
          <p class="text-lg mb-6" style="color: var(--text-muted)">
            Les questions qu'on nous pose le plus souvent, sans langue de bois. Une autre question ? Écrivez-nous, on répond vite.
          </p>
          <router-link to="/contact" class="btn-secondary">
            Poser une question
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
            </svg>
          </router-link>
        </div>

        <div ref="listRef" class="reveal-stagger lg:col-span-8 space-y-3">
          <div v-for="(f, i) in faqs" :key="f.q"
               class="card-flat overflow-hidden transition-all duration-300"
               :style="{
                 background: opened === i ? 'var(--bg-secondary)' : 'var(--card-bg)',
                 borderColor: opened === i ? 'rgba(255, 107, 44, 0.3)' : 'var(--card-border)',
                 boxShadow: opened === i ? 'var(--card-shadow-hover)' : 'var(--card-shadow)'
               }">
            <button
              @click="toggle(i)"
              class="w-full flex items-center justify-between gap-4 p-5 text-left cursor-pointer"
            >
              <span class="font-display font-bold pr-4" style="color: var(--text-primary)">{{ f.q }}</span>
              <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 transition-all duration-300"
                   :style="{ background: opened === i ? 'var(--color-brand)' : 'var(--bg-tertiary)', color: opened === i ? 'white' : 'var(--text-primary)' }">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="transition-transform duration-300" :class="opened === i ? 'rotate-45' : ''">
                  <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
              </div>
            </button>
            <transition
              enter-active-class="transition-all duration-300 ease-out"
              leave-active-class="transition-all duration-200 ease-in"
              enter-from-class="opacity-0 max-h-0"
              enter-to-class="opacity-100 max-h-96"
              leave-from-class="opacity-100 max-h-96"
              leave-to-class="opacity-0 max-h-0"
            >
              <div v-show="opened === i" class="px-5 pb-5 text-sm leading-relaxed overflow-hidden" style="color: var(--text-secondary)">
                {{ f.a }}
              </div>
            </transition>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
