import { onMounted, onUnmounted } from 'vue'

export function useScrollCinematic() {
  let ticking = false
  let elements = []

  const lerp = (start, end, factor) => start + (end - start) * factor

  const getProgress = (el) => {
    const rect = el.getBoundingClientRect()
    const vh = window.innerHeight
    // 0 = element just entering bottom, 1 = element just leaving top
    const raw = 1 - (rect.top / vh)
    return Math.min(Math.max(raw, 0), 2)
  }

  const update = () => {
    elements.forEach(({ el, effects }) => {
      const progress = getProgress(el)

      effects.forEach((effect) => {
        const { type, target } = effect
        const node = target ? el.querySelector(target) : el

        if (!node) return

        switch (type) {
          case 'fade-slide': {
            // Fade in + slide up as section enters
            const p = Math.min(Math.max((progress - 0.1) / 0.5, 0), 1)
            const ease = p < 0.5 ? 4 * p * p * p : 1 - Math.pow(-2 * p + 2, 3) / 2
            node.style.opacity = ease
            node.style.transform = `translateY(${(1 - ease) * 60}px)`
            break
          }
          case 'parallax-slow': {
            // Subtle parallax - moves slower than scroll
            const offset = (progress - 0.5) * -40
            node.style.transform = `translateY(${offset}px)`
            break
          }
          case 'parallax-fast': {
            const offset = (progress - 0.5) * -80
            node.style.transform = `translateY(${offset}px)`
            break
          }
          case 'scale-in': {
            // Scale from 0.92 to 1 as it enters
            const p = Math.min(Math.max((progress - 0.05) / 0.6, 0), 1)
            const ease = 1 - Math.pow(1 - p, 3)
            const scale = lerp(0.92, 1, ease)
            node.style.transform = `scale(${scale})`
            node.style.opacity = ease
            break
          }
          case 'blur-in': {
            // Deblur as enters
            const p = Math.min(Math.max((progress - 0.05) / 0.5, 0), 1)
            const ease = 1 - Math.pow(1 - p, 3)
            const blur = lerp(8, 0, ease)
            node.style.filter = `blur(${blur}px)`
            node.style.opacity = ease
            break
          }
          case 'clip-reveal': {
            // Clip from bottom
            const p = Math.min(Math.max((progress - 0.1) / 0.5, 0), 1)
            const ease = 1 - Math.pow(1 - p, 3)
            node.style.clipPath = `inset(0 0 ${(1 - ease) * 100}% 0)`
            break
          }
          case 'rotate-in': {
            const p = Math.min(Math.max((progress - 0.05) / 0.6, 0), 1)
            const ease = 1 - Math.pow(1 - p, 3)
            const rotate = lerp(-3, 0, ease)
            const scale = lerp(0.95, 1, ease)
            node.style.transform = `perspective(1200px) rotateX(${rotate}deg) scale(${scale})`
            node.style.opacity = ease
            break
          }
          case 'stagger': {
            // Stagger children animations
            const children = node.children
            for (let i = 0; i < children.length; i++) {
              const delay = i * 0.08
              const p = Math.min(Math.max((progress - 0.1 - delay) / 0.4, 0), 1)
              const ease = 1 - Math.pow(1 - p, 3)
              children[i].style.opacity = ease
              children[i].style.transform = `translateY(${(1 - ease) * 30}px)`
            }
            break
          }
          case 'divider-glow': {
            // Animated glow line between sections
            const p = Math.min(Math.max((progress - 0.2) / 0.4, 0), 1)
            const ease = 1 - Math.pow(1 - p, 3)
            node.style.opacity = ease
            node.style.transform = `scaleX(${ease})`
            break
          }
          case 'counter': {
            // Number count-up effect
            const p = Math.min(Math.max((progress - 0.15) / 0.4, 0), 1)
            const ease = 1 - Math.pow(1 - p, 3)
            const max = parseFloat(node.dataset.count) || 0
            const suffix = node.dataset.suffix || ''
            const prefix = node.dataset.prefix || ''
            node.textContent = prefix + Math.round(max * ease).toLocaleString('fr-FR') + suffix
            break
          }
        }
      })
    })

    ticking = false
  }

  const onScroll = () => {
    if (!ticking) {
      requestAnimationFrame(update)
      ticking = true
    }
  }

  const register = (el, effects) => {
    if (!el) return
    elements.push({ el, effects })
  }

  onMounted(() => {
    window.addEventListener('scroll', onScroll, { passive: true })
    // Initial pass
    setTimeout(update, 50)
  })

  onUnmounted(() => {
    window.removeEventListener('scroll', onScroll)
    elements = []
  })

  return { register }
}
