<script setup>
const methods = [
  { name: 'MTN Mobile Money', color: '#FFC000', initials: 'MTN' },
  { name: 'Orange Money', color: '#FF7900', initials: 'OM' },
  { name: 'Flutterwave', color: '#F5A623', initials: 'FLW' },
  { name: 'Visa', color: '#1A1F71', initials: 'VISA' },
  { name: 'Mastercard', color: '#EB001B', initials: 'MC' },
  { name: 'PayPal', color: '#003087', initials: 'PP' },
]
</script>

<template>
  <section class="py-10 md:py-12 border-y" style="background: var(--bg-secondary); border-color: var(--border-default)">
    <div class="container-max">
      <div class="flex flex-col lg:flex-row items-center gap-6 lg:gap-10">
        <p class="text-xs font-display font-semibold uppercase tracking-[0.14em] whitespace-nowrap" style="color: var(--text-muted)">
          Paiements acceptés
        </p>
        <div class="flex-1 flex flex-wrap items-center justify-center lg:justify-start gap-3 md:gap-5">
          <div v-for="m in methods" :key="m.name"
               class="flex items-center gap-2 px-4 py-2 rounded-xl transition-all"
               style="background: var(--bg-primary); border: 1px solid var(--border-default)">
            <div class="w-8 h-6 rounded-md flex items-center justify-center text-[10px] font-black font-display text-white" :style="{ background: m.color }">
              {{ m.initials }}
            </div>
            <span class="text-sm font-semibold hidden sm:inline" style="color: var(--text-primary)">{{ m.name }}</span>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
