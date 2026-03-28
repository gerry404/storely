<script setup>
const siteUrl = 'https://storely-one.vercel.app'
const shareText = 'Storely - Créez votre vitrine digitale en 10 minutes !'

const socials = [
  { name: 'Facebook', href: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(siteUrl)}`, color: '#1877F2', icon: 'M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z' },
  { name: 'X', href: `https://twitter.com/intent/tweet?text=${encodeURIComponent(shareText)}&url=${encodeURIComponent(siteUrl)}`, color: '#fff', icon: 'M4 4l7.07 9.34L4 20h1.6l5.87-5.52L16 20h4.4l-7.45-9.83L19.8 4h-1.6l-5.47 5.14L8.4 4H4zm2.2 1.2h2.12l9.48 13.6h-2.12L6.2 5.2z' },
  { name: 'WhatsApp', href: `https://wa.me/?text=${encodeURIComponent(shareText + ' ' + siteUrl)}`, color: '#25D366', icon: 'M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z' },
  { name: 'LinkedIn', href: `https://www.linkedin.com/shareArticle?mini=true&url=${encodeURIComponent(siteUrl)}&title=${encodeURIComponent(shareText)}`, color: '#0A66C2', icon: 'M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2zM4 2a2 2 0 110 4 2 2 0 010-4z' },
]
</script>

<template>
  <footer class="relative border-t" style="border-color: var(--border-default); background: var(--bg-primary)">
    <div class="max-w-6xl mx-auto px-6 py-16">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-12 md:gap-8">
        <!-- Brand -->
        <div class="md:col-span-1">
          <router-link to="/" class="flex items-center gap-2 mb-4 no-underline">
            <img src="/logo.png" alt="Storely" class="w-8 h-8 object-contain" />
            <span class="font-display text-lg font-bold" style="color: var(--logo-text)">Storely</span>
          </router-link>
          <p class="text-sm leading-relaxed" style="color: var(--text-muted)">
            La vitrine digitale pour les commerçants ambitieux. Créez votre page en 10 minutes.
          </p>

          <!-- Social share -->
          <div class="flex items-center gap-2 mt-5">
            <a
              v-for="s in socials"
              :key="s.name"
              :href="s.href"
              target="_blank"
              rel="noopener noreferrer"
              class="w-9 h-9 rounded-xl flex items-center justify-center transition-all hover:scale-110 hover:-translate-y-0.5"
              :title="`Partager sur ${s.name}`"
              style="background: var(--glass-bg-light); border: 1px solid var(--border-subtle)"
            >
              <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" :style="{ color: s.color }">
                <path :d="s.icon" />
              </svg>
            </a>
          </div>
        </div>

        <!-- Links -->
        <div>
          <h4 class="font-display text-sm font-semibold mb-4 uppercase tracking-wider" style="color: var(--text-primary)">Produit</h4>
          <ul class="space-y-3">
            <li><router-link to="/pricing" class="text-sm hover:text-brand transition-colors no-underline" style="color: var(--text-faint)">Tarifs</router-link></li>
            <li><router-link to="/#features" class="text-sm hover:text-brand transition-colors no-underline" style="color: var(--text-faint)">Fonctions</router-link></li>
            <li><router-link to="/marketplace" class="text-sm hover:text-brand transition-colors no-underline" style="color: var(--text-faint)">Marketplace</router-link></li>
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
