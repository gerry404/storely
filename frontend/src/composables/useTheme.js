import { ref, onMounted } from 'vue'

// Light-first: default is 'light' unless user picked dark or has OS dark preference
const theme = ref('light')

export function useTheme() {
  const init = () => {
    const saved = localStorage.getItem('storely-theme')
    if (saved === 'light' || saved === 'dark') {
      theme.value = saved
    } else {
      theme.value = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
    }
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
