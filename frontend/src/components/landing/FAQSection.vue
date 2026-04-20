<script setup>
import { ref } from 'vue'

const opened = ref(0)

const faqs = [
  {
    q: 'Combien coûte réellement Storely ?',
    a: 'Storely propose un plan gratuit complet pour démarrer (jusqu\'à 10 produits). Pour vendre sans limites avec paiement en ligne, le plan Starter est à 5 000 FCFA/mois et le plan Pro à 15 000 FCFA/mois. Pas de frais cachés, pas de commission sur vos ventes (sauf les frais standards de Mobile Money / carte).',
  },
  {
    q: 'Comment fonctionne la réconciliation Mobile Money ?',
    a: 'Quand un client paie via MTN MoMo ou Orange Money, Storely reçoit la notification et associe automatiquement la transaction à la commande correspondante. Le statut passe à "Payée" sans action de votre part. Vous évitez les heures de comptabilité manuelle.',
  },
  {
    q: 'Dois-je savoir coder pour utiliser Storely ?',
    a: 'Absolument pas. Storely est conçu pour les marchands qui ne connaissent rien à la technique. Tout se fait depuis une interface visuelle simple : vous remplissez des formulaires, vous cliquez. Aucune ligne de code à écrire.',
  },
  {
    q: 'Puis-je utiliser mon propre domaine (ma-boutique.com) ?',
    a: 'Oui, avec le plan Pro. Vous connectez votre domaine en quelques clics et votre boutique devient accessible à votre adresse. Certificat SSL offert et configuration automatique.',
  },
  {
    q: 'Et si mes clients n\'ont pas de smartphone ?',
    a: 'Votre boutique fonctionne sur tous les appareils — smartphone, ordinateur, tablette, même les téléphones basiques qui accèdent au web. Vous pouvez aussi enregistrer des commandes manuelles depuis votre dashboard pour les clients qui vous appellent directement.',
  },
  {
    q: 'Que se passe-t-il si j\'arrête de payer l\'abonnement ?',
    a: 'Votre boutique reste accessible en lecture seule pendant 30 jours, le temps de réactiver. Vos données (produits, commandes, clients) ne sont jamais supprimées. Vous pouvez réactiver à tout moment sans rien perdre.',
  },
  {
    q: 'Storely prélève-t-il une commission sur mes ventes ?',
    a: 'Non, zéro commission Storely. Vous payez uniquement votre abonnement mensuel. Seuls les frais des opérateurs de paiement (Flutterwave, MTN, Orange) s\'appliquent — ce sont les frais standards du marché, indépendants de Storely.',
  },
  {
    q: 'L\'IA qui rédige les fiches produit, comment ça marche ?',
    a: 'Vous uploadez une photo de votre produit. Notre IA analyse l\'image, identifie le produit, et génère automatiquement un titre accrocheur, une description vendeur et des tags optimisés pour le SEO. Vous pouvez modifier le résultat avant publication. Gain de temps énorme.',
  },
]

const toggle = (i) => { opened.value = opened.value === i ? -1 : i }
</script>

<template>
  <section id="faq" class="section-lg">
    <div class="container-max">
      <div class="grid lg:grid-cols-12 gap-12 items-start">
        <div class="lg:col-span-4">
          <span class="eyebrow">Questions fréquentes</span>
          <h2 class="display-xl mt-4 mb-5">Vous avez des questions ? On a les réponses.</h2>
          <p class="text-lg mb-6" style="color: var(--text-muted)">
            Les réponses aux questions qu'on nous pose le plus. Une autre question ? Contactez-nous.
          </p>
          <router-link to="/contact" class="btn-secondary">
            Poser une question
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
          </router-link>
        </div>

        <div class="lg:col-span-8 space-y-3">
          <div v-for="(f, i) in faqs" :key="f.q"
               class="card-flat overflow-hidden transition-all"
               :style="{ background: opened === i ? 'var(--bg-secondary)' : 'var(--card-bg)', borderColor: opened === i ? 'var(--border-strong)' : 'var(--card-border)' }">
            <button
              @click="toggle(i)"
              class="w-full flex items-center justify-between gap-4 p-5 text-left cursor-pointer"
            >
              <span class="font-display font-bold" style="color: var(--text-primary)">{{ f.q }}</span>
              <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 transition-all"
                   :style="{ background: opened === i ? 'var(--color-brand)' : 'var(--bg-tertiary)', color: opened === i ? 'white' : 'var(--text-primary)' }">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" class="transition-transform" :class="opened === i ? 'rotate-45' : ''">
                  <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
              </div>
            </button>
            <div v-show="opened === i" class="px-5 pb-5 text-sm leading-relaxed" style="color: var(--text-secondary)">
              {{ f.a }}
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
