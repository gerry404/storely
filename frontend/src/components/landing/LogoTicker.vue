<script setup>
const mentions = [
  'Made in Cameroon',
  'E-commerce facile',
  'Paiements Mobile Money',
  'Sans code',
  'Pour les marchands africains',
  'Commandes illimitées',
  'Livraison intégrée',
  'SSL gratuit',
]
</script>

<template>
  <section class="py-8 border-y overflow-hidden" style="background: var(--bg-primary); border-color: var(--border-default)">
    <div class="marquee-mask">
      <div class="flex gap-12 animate-marquee whitespace-nowrap" style="width: max-content">
        <template v-for="n in 3" :key="n">
          <span v-for="m in mentions" :key="m + n" class="inline-flex items-center gap-2 font-display font-bold text-sm uppercase tracking-wider" style="color: var(--text-muted)">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15 8.5 22 9.3 17 14 18.2 21 12 17.8 5.8 21 7 14 2 9.3 9 8.5 12 2"/></svg>
            {{ m }}
          </span>
        </template>
      </div>
    </div>
  </section>
</template>
