<template>
  <footer class="relative border-t" style="border-color: var(--border-default); background: var(--bg-primary)">
    <div class="max-w-6xl mx-auto px-6 py-16">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-12 md:gap-8">
        <!-- Brand -->
        <div class="md:col-span-1">
          <router-link to="/" class="flex items-center gap-2 mb-4 no-underline">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand to-brand-amber flex items-center justify-center">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
              </svg>
            </div>
            <span class="font-display text-lg font-bold" style="color: var(--logo-text)">Storely</span>
          </router-link>
          <p class="text-sm leading-relaxed" style="color: var(--text-muted)">
            La vitrine digitale pour les commerçants africains. Créez votre page en 10 minutes.
          </p>
        </div>

        <!-- Links -->
        <div>
          <h4 class="font-display text-sm font-semibold mb-4 uppercase tracking-wider" style="color: var(--text-primary)">Produit</h4>
          <ul class="space-y-3">
            <li><router-link to="/pricing" class="text-sm hover:text-brand transition-colors no-underline" style="color: var(--text-faint)">Tarifs</router-link></li>
            <li><router-link to="/#features" class="text-sm hover:text-brand transition-colors no-underline" style="color: var(--text-faint)">Fonctions</router-link></li>
            <li><router-link to="/examples" class="text-sm hover:text-brand transition-colors no-underline" style="color: var(--text-faint)">Exemples</router-link></li>
          </ul>
        </div>
        <div>
          <h4 class="font-display text-sm font-semibold mb-4 uppercase tracking-wider" style="color: var(--text-primary)">Support</h4>
          <ul class="space-y-3">
            <li><router-link to="/help" class="text-sm hover:text-brand transition-colors no-underline" style="color: var(--text-faint)">Centre d'aide</router-link></li>
            <li><router-link to="/contact" class="text-sm hover:text-brand transition-colors no-underline" style="color: var(--text-faint)">Nous contacter</router-link></li>
            <li><router-link to="/help" class="text-sm hover:text-brand transition-colors no-underline" style="color: var(--text-faint)">FAQ</router-link></li>
          </ul>
        </div>
        <div>
          <h4 class="font-display text-sm font-semibold mb-4 uppercase tracking-wider" style="color: var(--text-primary)">Légal</h4>
          <ul class="space-y-3">
            <li><router-link to="/terms" class="text-sm hover:text-brand transition-colors no-underline" style="color: var(--text-faint)">Conditions</router-link></li>
            <li><router-link to="/privacy" class="text-sm hover:text-brand transition-colors no-underline" style="color: var(--text-faint)">Confidentialité</router-link></li>
          </ul>
        </div>
      </div>

      <div class="mt-16 pt-8 flex flex-col md:flex-row items-center justify-between gap-4" style="border-top: 1px solid var(--border-default)">
        <p class="text-xs" style="color: var(--text-faint)">&copy; 2026 Storely. Tous droits réservés.</p>
        <div class="flex items-center gap-4">
          <span class="inline-flex items-center gap-1.5 text-xs" style="color: var(--text-faint)">
            <span class="w-1.5 h-1.5 rounded-full bg-mint animate-pulse" />
            Tous les systèmes opérationnels
          </span>
        </div>
      </div>
    </div>
  </footer>
</template>
