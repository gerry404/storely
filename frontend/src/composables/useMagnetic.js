export function useMagnetic(el, strength = 0.3) {
  if (!el) return

  const onMove = (e) => {
    const rect = el.getBoundingClientRect()
    const cx = rect.left + rect.width / 2
    const cy = rect.top + rect.height / 2
    const dx = (e.clientX - cx) * strength
    const dy = (e.clientY - cy) * strength
    el.style.transform = `translate(${dx}px, ${dy}px)`
  }

  const onLeave = () => {
    el.style.transform = 'translate(0, 0)'
    el.style.transition = 'transform 0.5s cubic-bezier(0.16, 1, 0.3, 1)'
    setTimeout(() => { el.style.transition = '' }, 500)
  }

  el.addEventListener('mousemove', onMove)
  el.addEventListener('mouseleave', onLeave)

  return () => {
    el.removeEventListener('mousemove', onMove)
    el.removeEventListener('mouseleave', onLeave)
  }
}
