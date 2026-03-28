import { ref, computed, onMounted } from 'vue'

const isStandalone = ref(false)
const isOnline = ref(navigator.onLine)
const isIOS = ref(false)
const deferredPrompt = ref(null)
const canInstall = ref(false)
const updateAvailable = ref(false)

export function usePWA() {
  onMounted(() => {
    // Detect standalone mode (installed PWA)
    isStandalone.value =
      window.matchMedia('(display-mode: standalone)').matches ||
      window.navigator.standalone === true

    // Detect iOS
    isIOS.value = /iPad|iPhone|iPod/.test(navigator.userAgent) ||
      (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1)

    // Online/offline
    window.addEventListener('online', () => { isOnline.value = true })
    window.addEventListener('offline', () => { isOnline.value = false })

    // Install prompt
    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault()
      deferredPrompt.value = e
      canInstall.value = true
    })

    // App installed
    window.addEventListener('appinstalled', () => {
      canInstall.value = false
      deferredPrompt.value = null
    })

    // Service worker update
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.getRegistration().then(reg => {
        if (reg) {
          reg.addEventListener('updatefound', () => {
            const newWorker = reg.installing
            if (newWorker) {
              newWorker.addEventListener('statechange', () => {
                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                  updateAvailable.value = true
                }
              })
            }
          })
        }
      })
    }
  })

  const install = async () => {
    if (!deferredPrompt.value) return false
    deferredPrompt.value.prompt()
    const { outcome } = await deferredPrompt.value.userChoice
    deferredPrompt.value = null
    canInstall.value = false
    return outcome === 'accepted'
  }

  const applyUpdate = () => {
    if ('serviceWorker' in navigator) {
      navigator.serviceWorker.getRegistration().then(reg => {
        if (reg?.waiting) {
          reg.waiting.postMessage({ type: 'SKIP_WAITING' })
          window.location.reload()
        }
      })
    }
  }

  // Haptic feedback (vibration API)
  const haptic = (pattern = 10) => {
    if ('vibrate' in navigator) {
      navigator.vibrate(pattern)
    }
  }

  const hapticLight = () => haptic(5)
  const hapticMedium = () => haptic(15)
  const hapticHeavy = () => haptic([10, 30, 10])
  const hapticSuccess = () => haptic([10, 50, 20])
  const hapticError = () => haptic([30, 50, 30, 50, 30])

  const isMobile = computed(() => window.innerWidth < 768)

  return {
    isStandalone,
    isOnline,
    isIOS,
    canInstall,
    updateAvailable,
    isMobile,
    install,
    applyUpdate,
    haptic,
    hapticLight,
    hapticMedium,
    hapticHeavy,
    hapticSuccess,
    hapticError,
  }
}
