<script setup>
const lastUpdate = '15 mars 2026'
</script>

<template>
  <main class="min-h-screen pt-28 pb-20">
    <div class="max-w-3xl mx-auto px-6">
      <div class="mb-12">
        <span class="inline-block px-4 py-1.5 rounded-full glass text-xs font-display font-medium text-white/50 uppercase tracking-wider mb-6">
          Légal
        </span>
        <h1 class="font-display font-bold text-4xl md:text-5xl text-white tracking-tight mb-4">
          Politique de Confidentialité
        </h1>
        <p class="text-white/30 text-sm">Dernière mise à jour : {{ lastUpdate }}</p>
      </div>

      <div class="prose-storely space-y-10">
        <section>
          <h2>1. Introduction</h2>
          <p>Chez Storely, la protection de vos données personnelles est une priorité. Cette politique de confidentialité explique quelles données nous collectons, comment nous les utilisons et quels sont vos droits.</p>
        </section>

        <section>
          <h2>2. Données collectées</h2>
          <p>Nous collectons les données suivantes :</p>
          <ul>
            <li><strong>Données d'inscription :</strong> nom, adresse email, numéro de téléphone</li>
            <li><strong>Données de boutique :</strong> nom de la boutique, adresse, catégorie, logo, description</li>
            <li><strong>Données de produits :</strong> noms, descriptions, prix, photos des produits</li>
            <li><strong>Données de navigation :</strong> adresse IP, type de navigateur, pages visitées, durée des sessions</li>
            <li><strong>Données de commande :</strong> nom du client, téléphone, produit commandé</li>
          </ul>
        </section>

        <section>
          <h2>3. Utilisation des données</h2>
          <p>Vos données sont utilisées pour :</p>
          <ul>
            <li>Fournir et améliorer nos services</li>
            <li>Afficher votre vitrine en ligne</li>
            <li>Générer des statistiques de visites pour votre tableau de bord</li>
            <li>Vous envoyer des notifications importantes (commandes, mises à jour)</li>
            <li>Assurer la sécurité de la plateforme</li>
          </ul>
          <p>Nous ne vendons jamais vos données personnelles à des tiers.</p>
        </section>

        <section>
          <h2>4. Stockage et sécurité</h2>
          <p>Vos données sont stockées sur des serveurs sécurisés. Nous utilisons le chiffrement SSL/TLS pour toutes les communications. Les mots de passe sont hashés et ne sont jamais stockés en clair.</p>
          <p>L'accès aux données est restreint aux employés autorisés de Storely dans le cadre de l'exécution de leurs fonctions.</p>
        </section>

        <section>
          <h2>5. Cookies</h2>
          <p>Storely utilise des cookies essentiels pour :</p>
          <ul>
            <li>Maintenir votre session de connexion</li>
            <li>Mémoriser vos préférences (thème clair/sombre)</li>
            <li>Assurer le fonctionnement technique de la plateforme</li>
          </ul>
          <p>Nous n'utilisons pas de cookies publicitaires ou de traçage tiers.</p>
        </section>

        <section>
          <h2>6. Partage des données</h2>
          <p>Vos données peuvent être partagées avec :</p>
          <ul>
            <li><strong>Vos clients :</strong> les informations publiques de votre boutique (nom, produits, contact)</li>
            <li><strong>Prestataires techniques :</strong> hébergement, envoi d'emails (sous contrat de confidentialité)</li>
            <li><strong>Autorités :</strong> en cas d'obligation légale uniquement</li>
          </ul>
        </section>

        <section>
          <h2>7. Vos droits</h2>
          <p>Conformément à la réglementation applicable, vous disposez des droits suivants :</p>
          <ul>
            <li><strong>Accès :</strong> demander une copie de vos données personnelles</li>
            <li><strong>Rectification :</strong> corriger des données inexactes</li>
            <li><strong>Suppression :</strong> demander la suppression de votre compte et données</li>
            <li><strong>Portabilité :</strong> recevoir vos données dans un format exploitable</li>
          </ul>
          <p>Pour exercer ces droits, contactez-nous à <a href="mailto:privacy@storely.app" class="text-brand hover:underline">privacy@storely.app</a>.</p>
        </section>

        <section>
          <h2>8. Durée de conservation</h2>
          <p>Vos données sont conservées tant que votre compte est actif. Après suppression de votre compte, vos données sont effacées dans un délai de 30 jours, sauf obligation légale de conservation plus longue.</p>
        </section>

        <section>
          <h2>9. Modifications</h2>
          <p>Nous pouvons modifier cette politique à tout moment. En cas de modification substantielle, nous vous en informerons par email ou notification dans votre tableau de bord.</p>
        </section>

        <section>
          <h2>10. Contact</h2>
          <p>Pour toute question relative à la confidentialité de vos données :</p>
          <ul>
            <li>Email : <a href="mailto:privacy@storely.app" class="text-brand hover:underline">privacy@storely.app</a></li>
            <li>WhatsApp : +237 6XX XXX XXX</li>
          </ul>
        </section>
      </div>
    </div>
  </main>
</template>

<style scoped>
.prose-storely h2 {
  font-family: var(--font-display);
  font-weight: 700;
  font-size: 1.25rem;
  color: var(--text-primary);
  margin-bottom: 0.75rem;
}
.prose-storely p {
  font-size: 0.9375rem;
  line-height: 1.7;
  color: var(--text-muted);
  margin-bottom: 0.75rem;
}
.prose-storely ul {
  list-style: none;
  padding-left: 0;
  margin-bottom: 0.75rem;
}
.prose-storely li {
  position: relative;
  padding-left: 1.25rem;
  font-size: 0.9375rem;
  line-height: 1.7;
  color: var(--text-muted);
  margin-bottom: 0.25rem;
}
.prose-storely li::before {
  content: '';
  position: absolute;
  left: 0;
  top: 0.7em;
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: var(--color-brand);
  opacity: 0.5;
}
.prose-storely strong {
  color: var(--text-primary);
  font-weight: 600;
}
</style>
