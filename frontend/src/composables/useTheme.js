import { ref, onMounted } from 'vue'

const theme = ref('dark')

export function useTheme() {
  const init = () => {
    const saved = localStorage.getItem('storely-theme')
    if (saved) {
      theme.value = saved
    } else {
      theme.value = window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark'
    }
    applyTheme()
  }

  const toggle = () => {
    // Add transition class for smooth theme switch
    document.documentElement.classList.add('theme-transition')
    theme.value = theme.value === 'dark' ? 'light' : 'dark'
    localStorage.setItem('storely-theme', theme.value)
    applyTheme()
    // Remove transition class after animation
    setTimeout(() => {
      document.documentElement.classList.remove('theme-transition')
    }, 500)
  }

  const applyTheme = () => {
    document.documentElement.classList.remove('light', 'dark')
    document.documentElement.classList.add(theme.value)

    // Update meta theme-color for PWA/mobile browser
    const metaTheme = document.querySelector('meta[name="theme-color"]')
    if (metaTheme) {
      metaTheme.setAttribute('content', theme.value === 'light' ? '#FAFAF8' : '#0A0A0F')
    }
  }

  const isDark = () => theme.value === 'dark'

  onMounted(init)

  return { theme, toggle, isDark }
}
