<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const x = ref(-100)
const y = ref(-100)
const visible = ref(false)
const isTouch = ref(false)

const onMove = (e) => {
  x.value = e.clientX
  y.value = e.clientY
  if (!visible.value) visible.value = true
}

const onLeave = () => { visible.value = false }
const onEnter = () => { visible.value = true }

onMounted(() => {
  isTouch.value = 'ontouchstart' in window
  if (isTouch.value) return
  window.addEventListener('mousemove', onMove)
  document.addEventListener('mouseleave', onLeave)
  document.addEventListener('mouseenter', onEnter)
})

onUnmounted(() => {
  window.removeEventListener('mousemove', onMove)
  document.removeEventListener('mouseleave', onLeave)
  document.removeEventListener('mouseenter', onEnter)
})
</script>

<template>
  <div
    v-if="!isTouch"
    class="pointer-events-none fixed z-[9998] transition-opacity duration-500"
    :class="visible ? 'opacity-100' : 'opacity-0'"
    :style="{
      left: x + 'px',
      top: y + 'px',
      transform: 'translate(-50%, -50%)',
    }"
  >
    <div class="w-[500px] h-[500px] rounded-full bg-brand/[0.04] blur-[80px]" />
  </div>
</template>
