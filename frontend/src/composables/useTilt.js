export function useTilt(el, maxTilt = 8) {
  if (!el) return

  const onMove = (e) => {
    const rect = el.getBoundingClientRect()
    const x = (e.clientX - rect.left) / rect.width - 0.5
    const y = (e.clientY - rect.top) / rect.height - 0.5
    const rotateX = -y * maxTilt
    const rotateY = x * maxTilt
    el.style.transform = `perspective(800px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`
    el.style.transition = 'transform 0.1s ease'

    // Shine effect
    const shine = el.querySelector('.tilt-shine')
    if (shine) {
      shine.style.background = `radial-gradient(circle at ${(x + 0.5) * 100}% ${(y + 0.5) * 100}%, rgba(255,255,255,0.12) 0%, transparent 60%)`
    }
  }

  const onLeave = () => {
    el.style.transform = 'perspective(800px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)'
    el.style.transition = 'transform 0.6s cubic-bezier(0.16, 1, 0.3, 1)'
    const shine = el.querySelector('.tilt-shine')
    if (shine) shine.style.background = 'transparent'
  }

  el.addEventListener('mousemove', onMove)
  el.addEventListener('mouseleave', onLeave)

  return () => {
    el.removeEventListener('mousemove', onMove)
    el.removeEventListener('mouseleave', onLeave)
  }
}
