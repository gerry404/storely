import { onMounted, onUnmounted } from 'vue'

// Applique .is-visible sur un élément quand il entre dans le viewport.
// Fonctionne avec les classes .reveal-mask, .reveal-blur, .reveal-stagger, .word-reveal.
export function useReveal(target, { threshold = 0.18, once = true } = {}) {
  let observer = null

  const setup = () => {
    const el = target.value
    if (!el) return
    if (!('IntersectionObserver' in window)) {
      el.classList.add('is-visible')
      return
    }
    observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible')
            if (once) observer.unobserve(entry.target)
          } else if (!once) {
            entry.target.classList.remove('is-visible')
          }
        })
      },
      { threshold, rootMargin: '0px 0px -10% 0px' }
    )
    observer.observe(el)
  }

  onMounted(setup)
  onUnmounted(() => observer?.disconnect())
}

// Observe plusieurs refs d'un coup (utile pour une grille de cartes).
export function useRevealAll(targets, opts = {}) {
  let observer = null
  const setup = () => {
    if (!('IntersectionObserver' in window)) {
      targets.value.forEach((el) => el?.classList.add('is-visible'))
      return
    }
    observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            entry.target.classList.add('is-visible')
            observer.unobserve(entry.target)
          }
        })
      },
      { threshold: opts.threshold ?? 0.18, rootMargin: '0px 0px -8% 0px' }
    )
    targets.value.forEach((el) => el && observer.observe(el))
  }
  onMounted(setup)
  onUnmounted(() => observer?.disconnect())
}
