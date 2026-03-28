<script setup>
defineProps({
  flip: { type: Boolean, default: false },
  accent: { type: String, default: '' },
})
</script>

<template>
  <div
    class="relative w-full h-24 md:h-32 overflow-hidden pointer-events-none select-none"
    :class="flip ? 'rotate-180' : ''"
  >
    <!-- Top fade out -->
    <div class="absolute inset-x-0 top-0 h-1/2" style="background: linear-gradient(to bottom, var(--bg-primary), transparent)" />
    <!-- Bottom fade in -->
    <div class="absolute inset-x-0 bottom-0 h-1/2" style="background: linear-gradient(to top, var(--bg-primary), transparent)" />
    <!-- Subtle center glow -->
    <div
      v-if="accent"
      class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[2px] rounded-full opacity-[0.07]"
      :style="{ background: `linear-gradient(90deg, transparent, ${accent}, transparent)`, filter: 'blur(8px)' }"
    />
    <!-- Ultra-thin line -->
    <div class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-[40%] max-w-[500px] h-px rounded-full opacity-[0.04]" style="background: linear-gradient(90deg, transparent, rgba(255,255,255,0.5), transparent)" />
  </div>
</template>
