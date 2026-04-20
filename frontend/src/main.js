import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import router from './router'

// Light-first: default light unless user picked dark or OS explicitly dark
const savedTheme = localStorage.getItem('storely-theme')
  || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
document.documentElement.classList.add(savedTheme)
document.querySelector('meta[name="theme-color"]')?.setAttribute(
  'content', savedTheme === 'light' ? '#FDFBF7' : '#0B0B10'
)

const app = createApp(App)
app.use(router)
app.mount('#app')

// PWA registration
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js').catch(() => {})
  })
}

// Scroll reveal observer
const observeReveal = () => {
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible')
        }
      })
    },
    { threshold: 0.1, rootMargin: '0px 0px -50px 0px' }
  )

  document.querySelectorAll('.reveal').forEach((el) => observer.observe(el))
}

router.afterEach(() => {
  setTimeout(observeReveal, 100)
})

document.addEventListener('DOMContentLoaded', observeReveal)
