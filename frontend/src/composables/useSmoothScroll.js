import Lenis from 'lenis'
import { ref, onMounted, onUnmounted } from 'vue'

let lenis = null
const scrollY = ref(0)
const scrollProgress = ref(0)

export function useSmoothScroll() {
  const init = () => {
    if (lenis) return

    lenis = new Lenis({
      duration: 1.4,
      easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
      touchMultiplier: 1.5,
      smoothWheel: true,
    })

    lenis.on('scroll', ({ scroll, limit }) => {
      scrollY.value = scroll
      scrollProgress.value = scroll / limit
    })

    const raf = (time) => {
      lenis.raf(time)
      requestAnimationFrame(raf)
    }
    requestAnimationFrame(raf)
  }

  onMounted(init)

  return { scrollY, scrollProgress, lenis: () => lenis }
}
