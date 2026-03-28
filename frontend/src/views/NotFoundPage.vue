<template>
  <main class="min-h-screen flex items-center justify-center px-6">
    <div class="text-center max-w-md">
      <!-- 404 Number -->
      <div class="relative mb-8">
        <span class="font-display font-bold text-[140px] md:text-[180px] leading-none text-white/[0.03] select-none">404</span>
        <div class="absolute inset-0 flex items-center justify-center">
          <div class="w-20 h-20 rounded-2xl bg-brand/10 border border-brand/20 flex items-center justify-center">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#FF6B2C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <path d="M16 16s-1.5-2-4-2-4 2-4 2"/>
              <line x1="9" y1="9" x2="9.01" y2="9"/>
              <line x1="15" y1="9" x2="15.01" y2="9"/>
            </svg>
          </div>
        </div>
      </div>

      <h1 class="font-display font-bold text-2xl md:text-3xl text-white mb-3">
        Page introuvable
      </h1>
      <p class="text-white/40 mb-8 leading-relaxed">
        La page que vous cherchez n'existe pas ou a été déplacée. Peut-être cherchez-vous une boutique ?
      </p>

      <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
        <router-link to="/" class="btn-primary">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          Retour à l'accueil
        </router-link>
        <router-link to="/help" class="btn-secondary">
          Centre d'aide
        </router-link>
      </div>
    </div>
  </main>
</template>
