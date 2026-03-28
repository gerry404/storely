<script setup>
import { ref } from 'vue'

const search = ref('')
const activeCategory = ref('all')

const categories = [
  { id: 'all', label: 'Tout' },
  { id: 'getting-started', label: 'Démarrage' },
  { id: 'products', label: 'Produits' },
  { id: 'orders', label: 'Commandes' },
  { id: 'billing', label: 'Facturation' },
  { id: 'account', label: 'Compte' },
]

const faqs = [
  { category: 'getting-started', q: 'Comment créer ma boutique en ligne ?', a: 'Inscrivez-vous avec votre numéro de téléphone ou email, remplissez les informations de votre boutique (nom, catégorie, adresse), ajoutez vos produits avec photos et prix, et c\'est prêt ! Vous obtenez immédiatement votre lien unique storely.app/votre-boutique.' },
  { category: 'getting-started', q: 'Combien de temps faut-il pour mettre ma boutique en ligne ?', a: 'En moyenne, nos commerçants créent leur vitrine en 10 minutes. L\'ajout de produits prend quelques secondes chacun — prenez une photo, ajoutez un nom et un prix, c\'est tout.' },
  { category: 'getting-started', q: 'Ai-je besoin d\'un ordinateur ?', a: 'Non ! Storely est conçu pour fonctionner parfaitement sur téléphone. Vous pouvez tout gérer depuis votre smartphone : création de boutique, ajout de produits, suivi des commandes.' },
  { category: 'products', q: 'Combien de produits puis-je ajouter ?', a: 'Le plan Starter permet jusqu\'à 20 produits. Le plan Pro offre un nombre illimité de produits. Vous pouvez changer de plan à tout moment.' },
  { category: 'products', q: 'Quels formats de photos sont acceptés ?', a: 'Nous acceptons JPG, PNG et WebP. Les photos sont automatiquement optimisées pour un chargement rapide. Nous recommandons des images d\'au moins 800x800 pixels pour un meilleur rendu.' },
  { category: 'products', q: 'Comment modifier ou supprimer un produit ?', a: 'Allez dans votre tableau de bord > Produits. Cliquez sur le produit à modifier. Vous pouvez changer le nom, le prix, la description, les photos ou le supprimer définitivement.' },
  { category: 'orders', q: 'Comment fonctionne la réception de commandes ?', a: 'Quand un client commande depuis votre vitrine, vous recevez une notification. Vous pouvez voir toutes vos commandes dans le tableau de bord et contacter le client par WhatsApp pour organiser la livraison et le paiement.' },
  { category: 'orders', q: 'Les paiements passent-ils par Storely ?', a: 'Non. Storely est une vitrine, pas une plateforme de paiement. Vos clients vous contactent via WhatsApp et le paiement se fait directement entre vous (Mobile Money, espèces, virement). Vous gardez 100% de vos ventes.' },
  { category: 'billing', q: 'Comment payer mon abonnement ?', a: 'Nous acceptons Orange Money et MTN Mobile Money. Le paiement se fait chaque mois automatiquement, ou manuellement si vous préférez. Vous pouvez aussi payer par virement bancaire.' },
  { category: 'billing', q: 'Puis-je annuler à tout moment ?', a: 'Oui. Il n\'y a aucun engagement. Vous pouvez annuler votre abonnement à tout moment depuis les paramètres de votre compte. Votre boutique restera accessible jusqu\'à la fin de la période payée.' },
  { category: 'billing', q: 'Y a-t-il une période d\'essai ?', a: 'Nous offrons 7 jours d\'essai gratuit sur le plan Pro. Aucune carte ou Mobile Money n\'est demandé à l\'inscription. Vous pouvez tester toutes les fonctionnalités sans engagement.' },
  { category: 'account', q: 'Comment obtenir le badge Vérifié ?', a: 'Le badge Vérifié est inclus dans le plan Pro. Il indique à vos clients que votre boutique est authentique et vérifiée par notre équipe. Un agent Storely peut aussi venir vérifier votre boutique physique.' },
  { category: 'account', q: 'Comment changer mon lien de boutique ?', a: 'Allez dans Tableau de bord > Paramètres > Lien de la boutique. Vous pouvez changer votre slug une fois. Choisissez un lien court et mémorable comme storely.app/votre-boutique.' },
  { category: 'account', q: 'J\'ai oublié mon mot de passe', a: 'Sur la page de connexion, cliquez sur "Mot de passe oublié". Entrez votre numéro de téléphone ou email et suivez les instructions pour réinitialiser votre mot de passe.' },
]

const filteredFaqs = () => {
  let result = faqs
  if (activeCategory.value !== 'all') {
    result = result.filter(f => f.category === activeCategory.value)
  }
  if (search.value.trim()) {
    const s = search.value.toLowerCase()
    result = result.filter(f => f.q.toLowerCase().includes(s) || f.a.toLowerCase().includes(s))
  }
  return result
}
</script>

<template>
  <main class="min-h-screen pt-28 pb-20">
    <div class="max-w-4xl mx-auto px-6">
      <!-- Header -->
      <div class="text-center mb-12">
        <span class="inline-block px-4 py-1.5 rounded-full glass text-xs font-display font-medium text-mint uppercase tracking-wider mb-6">
          Centre d'aide
        </span>
        <h1 class="font-display font-bold text-4xl md:text-6xl text-white tracking-tight mb-4">
          Comment pouvons-nous<br />
          <span class="text-gradient-cool">vous aider ?</span>
        </h1>
      </div>

      <!-- Search -->
      <div class="max-w-xl mx-auto mb-12">
        <div class="relative">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="absolute left-4 top-1/2 -translate-y-1/2 text-white/25">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input
            v-model="search"
            type="text"
            placeholder="Rechercher une question..."
            class="w-full pl-12 pr-4 py-4 rounded-2xl t-input text-sm"
          />
        </div>
      </div>

      <!-- Category tabs -->
      <div class="flex flex-wrap justify-center gap-2 mb-10">
        <button
          v-for="cat in categories"
          :key="cat.id"
          @click="activeCategory = cat.id"
          class="px-4 py-2 rounded-xl text-sm font-display font-medium transition-all duration-300"
          :class="activeCategory === cat.id
            ? 'bg-white/10 text-white border border-white/10'
            : 'text-white/40 hover:text-white/60 border border-transparent'"
        >
          {{ cat.label }}
        </button>
      </div>

      <!-- FAQ list -->
      <div class="space-y-3">
        <details
          v-for="(faq, i) in filteredFaqs()"
          :key="i"
          class="glass-card rounded-2xl group"
        >
          <summary class="flex items-center justify-between cursor-pointer p-6 list-none">
            <span class="font-display font-medium text-white pr-4">{{ faq.q }}</span>
            <svg
              width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              class="shrink-0 text-white/30 transition-transform duration-300 group-open:rotate-45"
            >
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
          </summary>
          <div class="px-6 pb-6 -mt-1">
            <p class="text-sm text-white/50 leading-relaxed">{{ faq.a }}</p>
          </div>
        </details>

        <div v-if="filteredFaqs().length === 0" class="text-center py-16">
          <p class="text-white/30 text-lg mb-4">Aucun résultat trouvé</p>
          <p class="text-white/20 text-sm">Essayez un autre terme ou <router-link to="/contact" class="text-brand hover:underline">contactez-nous</router-link></p>
        </div>
      </div>

      <!-- CTA -->
      <div class="mt-16 text-center glass-card rounded-2xl p-10">
        <h3 class="font-display font-bold text-xl text-white mb-2">Vous n'avez pas trouvé votre réponse ?</h3>
        <p class="text-white/40 mb-6">Notre équipe est disponible pour vous aider directement.</p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
          <a href="https://wa.me/237600000000" target="_blank" class="btn-whatsapp !text-sm">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
            Écrire sur WhatsApp
          </a>
          <router-link to="/contact" class="btn-secondary !text-sm">Formulaire de contact</router-link>
        </div>
      </div>
    </div>
  </main>
</template>
