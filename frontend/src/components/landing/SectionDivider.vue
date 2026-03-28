<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

defineProps({
  color: { type: String, default: 'brand' },
  flip: { type: Boolean, default: false }
})

const progress = ref(0)
const dividerRef = ref(null)

let ticking = false
const onScroll = () => {
  if (ticking) return
  ticking = true
  requestAnimationFrame(() => {
    if (!dividerRef.value) { ticking = false; return }
    const rect = dividerRef.value.getBoundingClientRect()
    const vh = window.innerHeight
    progress.value = Math.min(Math.max(1 - rect.top / (vh * 0.8), 0), 1)
    ticking = false
  })
}

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }))
onUnmounted(() => window.removeEventListener('scroll', onScroll))
</script>

<template>
  <div
    ref="dividerRef"
    class="relative h-32 md:h-48 overflow-hidden pointer-events-none"
    :class="flip ? 'rotate-180' : ''"
  >
    <!-- Gradient fade -->
    <div class="absolute inset-0" style="background: linear-gradient(to bottom, var(--bg-primary), transparent 30%, transparent 70%, var(--bg-primary))" />

    <!-- Animated line that expands from center -->
    <div class="absolute top-1/2 left-0 right-0 h-px -translate-y-1/2">
      <div
        class="h-full mx-auto relative glow-line"
        :class="`bg-gradient-to-r from-transparent via-${color}/30 to-transparent`"
        :style="{
          maxWidth: '600px',
          transform: `scaleX(${Math.min(progress * 1.5, 1)})`,
          opacity: Math.min(progress * 2, 1),
          transition: 'none'
        }"
      />
    </div>

    <!-- Floating orb with pulse -->
    <div
      class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2"
      :style="{
        opacity: Math.min(progress * 3, 1),
        transform: `translate(-50%, -50%) scale(${0.5 + progress * 0.5})`,
        transition: 'none'
      }"
    >
      <div :class="`w-2 h-2 rounded-full bg-${color}/60 animate-glow-pulse`" />
      <div :class="`absolute -inset-3 rounded-full bg-${color}/10 blur-md animate-glow-pulse`" />
    </div>
  </div>
</template>
