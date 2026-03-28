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
          Conditions Générales d'Utilisation
        </h1>
        <p class="text-white/30 text-sm">Dernière mise à jour : {{ lastUpdate }}</p>
      </div>

      <div class="prose-storely space-y-10">
        <section>
          <h2>1. Objet</h2>
          <p>Les présentes Conditions Générales d'Utilisation (CGU) régissent l'accès et l'utilisation de la plateforme Storely, accessible à l'adresse storely.app, éditée par Storely SAS (ci-après « Storely », « nous »).</p>
          <p>En créant un compte ou en utilisant nos services, vous acceptez l'intégralité des présentes conditions.</p>
        </section>

        <section>
          <h2>2. Description des services</h2>
          <p>Storely est une plateforme de création de vitrines digitales permettant aux commerçants de :</p>
          <ul>
            <li>Créer une page boutique en ligne personnalisée</li>
            <li>Publier et gérer un catalogue de produits</li>
            <li>Recevoir des demandes de commandes via WhatsApp</li>
            <li>Accéder à des statistiques de visites</li>
            <li>Obtenir un lien unique partageable</li>
          </ul>
          <p>Storely n'est pas une plateforme de paiement. Les transactions financières entre commerçants et clients se font en dehors de la plateforme.</p>
        </section>

        <section>
          <h2>3. Inscription et compte</h2>
          <p>Pour utiliser Storely, vous devez créer un compte en fournissant des informations exactes et à jour. Vous êtes responsable de la confidentialité de vos identifiants de connexion.</p>
          <p>Vous devez être âgé d'au moins 18 ans ou disposer de l'autorisation d'un représentant légal pour utiliser nos services.</p>
        </section>

        <section>
          <h2>4. Abonnements et paiements</h2>
          <p>Storely propose différents plans tarifaires (Starter, Pro) dont les détails sont disponibles sur notre page Tarifs. Les prix sont indiqués en Francs CFA (XAF) et sont susceptibles d'être modifiés avec un préavis de 30 jours.</p>
          <p>Les paiements s'effectuent via Mobile Money (Orange Money, MTN Mobile Money) ou par virement bancaire. L'abonnement est mensuel et sans engagement.</p>
        </section>

        <section>
          <h2>5. Obligations du commerçant</h2>
          <p>En tant que commerçant, vous vous engagez à :</p>
          <ul>
            <li>Ne publier que des produits dont la vente est légale au Cameroun</li>
            <li>Fournir des descriptions et prix exacts</li>
            <li>Ne pas utiliser la plateforme à des fins frauduleuses</li>
            <li>Respecter les droits de propriété intellectuelle</li>
            <li>Répondre aux commandes de vos clients dans un délai raisonnable</li>
          </ul>
        </section>

        <section>
          <h2>6. Propriété intellectuelle</h2>
          <p>Le contenu de la plateforme (design, code, textes, logos) est la propriété exclusive de Storely. Le contenu publié par les commerçants (photos, descriptions) reste leur propriété.</p>
          <p>En publiant du contenu sur Storely, vous nous accordez une licence non exclusive d'utilisation pour l'affichage et la promotion de votre boutique.</p>
        </section>

        <section>
          <h2>7. Résiliation</h2>
          <p>Vous pouvez résilier votre compte à tout moment depuis les paramètres de votre tableau de bord. Storely se réserve le droit de suspendre ou supprimer un compte en cas de violation des présentes conditions.</p>
        </section>

        <section>
          <h2>8. Limitation de responsabilité</h2>
          <p>Storely fournit ses services « en l'état ». Nous ne garantissons pas un fonctionnement ininterrompu de la plateforme. Nous ne sommes pas responsables des transactions entre commerçants et clients, ni des litiges qui pourraient en découler.</p>
        </section>

        <section>
          <h2>9. Droit applicable</h2>
          <p>Les présentes CGU sont soumises au droit camerounais. En cas de litige, les tribunaux de Douala sont seuls compétents.</p>
        </section>

        <section>
          <h2>10. Contact</h2>
          <p>Pour toute question relative aux présentes conditions, contactez-nous :</p>
          <ul>
            <li>Email : <a href="mailto:legal@storely.app" class="text-brand hover:underline">legal@storely.app</a></li>
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
</style>
