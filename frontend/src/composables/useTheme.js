import { ref, onMounted } from 'vue'

// Light-first, always. We ignore OS preference on first visit so the landing
// stays on its intended palette, and only honor the user's explicit toggle.
const theme = ref('light')

export function useTheme() {
  const init = () => {
    const saved = localStorage.getItem('storely-theme')
    theme.value = saved === 'dark' ? 'dark' : 'light'
    applyTheme()
  }

  const toggle = () => {
    document.documentElement.classList.add('theme-transition')
    theme.value = theme.value === 'dark' ? 'light' : 'dark'
    localStorage.setItem('storely-theme', theme.value)
    applyTheme()
    setTimeout(() => {
      document.documentElement.classList.remove('theme-transition')
    }, 400)
  }

  const applyTheme = () => {
    document.documentElement.classList.remove('light', 'dark')
    document.documentElement.classList.add(theme.value)

    const metaTheme = document.querySelector('meta[name="theme-color"]')
    if (metaTheme) {
      metaTheme.setAttribute('content', theme.value === 'light' ? '#FDFBF7' : '#0B0B10')
    }
  }

  const isDark = () => theme.value === 'dark'

  onMounted(init)

  return { theme, toggle, isDark }
}
