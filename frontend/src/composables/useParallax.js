import { onMounted, onUnmounted } from 'vue'

// Parallax 3D léger sur un conteneur : les enfants qui portent data-parallax
// se déplacent en fonction de la position de la souris, avec un facteur différent.
// Désactivé sur tactile et quand prefers-reduced-motion est actif.
export function usePointerParallax(containerRef, { strength = 16 } = {}) {
  let raf = null
  let targetX = 0
  let targetY = 0
  let currentX = 0
  let currentY = 0

  const reduceMotion = typeof window !== 'undefined' &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches
  const isTouch = typeof window !== 'undefined' &&
    window.matchMedia('(hover: none)').matches

  const onMove = (e) => {
    const el = containerRef.value
    if (!el) return
    const rect = el.getBoundingClientRect()
    const x = (e.clientX - rect.left) / rect.width - 0.5
    const y = (e.clientY - rect.top) / rect.height - 0.5
    targetX = x
    targetY = y
    if (!raf) raf = requestAnimationFrame(tick)
  }

  const onLeave = () => {
    targetX = 0
    targetY = 0
    if (!raf) raf = requestAnimationFrame(tick)
  }

  const tick = () => {
    currentX += (targetX - currentX) * 0.08
    currentY += (targetY - currentY) * 0.08
    const el = containerRef.value
    if (el) {
      const layers = el.querySelectorAll('[data-parallax]')
      layers.forEach((layer) => {
        const depth = parseFloat(layer.dataset.parallax) || 1
        const tx = currentX * strength * depth
        const ty = currentY * strength * depth
        layer.style.transform = `translate3d(${tx}px, ${ty}px, 0)`
      })
    }
    if (Math.abs(targetX - currentX) > 0.001 || Math.abs(targetY - currentY) > 0.001) {
      raf = requestAnimationFrame(tick)
    } else {
      raf = null
    }
  }

  onMounted(() => {
    if (reduceMotion || isTouch) return
    const el = containerRef.value
    if (!el) return
    el.addEventListener('mousemove', onMove)
    el.addEventListener('mouseleave', onLeave)
  })

  onUnmounted(() => {
    const el = containerRef.value
    if (el) {
      el.removeEventListener('mousemove', onMove)
      el.removeEventListener('mouseleave', onLeave)
    }
    if (raf) cancelAnimationFrame(raf)
  })
}

// Synchronise la position de scroll du carousel avec un dot indicator.
// Retourne un index réactif (0-based).
import { ref } from 'vue'
export function useSnapCarousel(containerRef) {
  const activeIndex = ref(0)

  const onScroll = () => {
    const el = containerRef.value
    if (!el) return
    const children = Array.from(el.children)
    if (!children.length) return
    const mid = el.scrollLeft + el.clientWidth / 2
    let best = 0
    let bestDist = Infinity
    children.forEach((c, i) => {
      const center = c.offsetLeft + c.clientWidth / 2
      const d = Math.abs(center - mid)
      if (d < bestDist) { bestDist = d; best = i }
    })
    activeIndex.value = best
  }

  const scrollTo = (i) => {
    const el = containerRef.value
    if (!el) return
    const child = el.children[i]
    if (!child) return
    el.scrollTo({ left: child.offsetLeft - el.offsetLeft, behavior: 'smooth' })
  }

  onMounted(() => {
    const el = containerRef.value
    if (el) el.addEventListener('scroll', onScroll, { passive: true })
    onScroll()
  })
  onUnmounted(() => {
    const el = containerRef.value
    if (el) el.removeEventListener('scroll', onScroll)
  })

  return { activeIndex, scrollTo }
}
