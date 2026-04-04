import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'

export const API_BASE = import.meta.env.VITE_API_URL || ''

const user = ref(JSON.parse(localStorage.getItem('storely-user') || 'null'))
const shop = ref(JSON.parse(localStorage.getItem('storely-shop') || 'null'))
const token = ref(localStorage.getItem('storely-token') || null)
const planInfo = ref(JSON.parse(localStorage.getItem('storely-plan-info') || 'null'))

export function useAuth() {
  const router = useRouter()

  const isAuthenticated = computed(() => !!token.value)

  const isAdmin = computed(() => {
    const role = user.value?.role
    return role === 'admin' || role === 'superadmin'
  })

  const setSession = (data) => {
    user.value = data.user
    shop.value = data.shop
    token.value = data.token
    if (data.plan_info) planInfo.value = data.plan_info
    localStorage.setItem('storely-user', JSON.stringify(data.user))
    localStorage.setItem('storely-shop', JSON.stringify(data.shop))
    localStorage.setItem('storely-token', data.token)
    if (data.plan_info) localStorage.setItem('storely-plan-info', JSON.stringify(data.plan_info))
  }

  const clearSession = () => {
    user.value = null
    shop.value = null
    token.value = null
    planInfo.value = null
    localStorage.removeItem('storely-user')
    localStorage.removeItem('storely-shop')
    localStorage.removeItem('storely-token')
    localStorage.removeItem('storely-plan-info')
  }

  const api = async (url, options = {}) => {
    const isFormData = options.body instanceof FormData
    const headers = {
      ...(isFormData ? {} : { 'Content-Type': 'application/json' }),
      'Accept': 'application/json',
      ...(token.value ? { 'Authorization': `Bearer ${token.value}` } : {}),
      ...options.headers,
    }
    const fullUrl = url.startsWith('http') ? url : `${API_BASE}${url}`
    const res = await fetch(fullUrl, { ...options, headers })
    const text = await res.text()
    const data = text ? JSON.parse(text) : {}
    if (!res.ok) {
      const error = new Error(data.message || 'Une erreur est survenue')
      error.status = res.status
      error.errors = data.errors || {}
      throw error
    }
    return data
  }

  const register = async ({ name, email, phone, password, shop_name, country, phone_code, referral_code }) => {
    const data = await api('/api/register', {
      method: 'POST',
      body: JSON.stringify({ name, email, phone, password, shop_name, country, phone_code, referral_code }),
    })
    setSession(data)
    return data
  }

  const login = async ({ email, password }) => {
    const data = await api('/api/login', {
      method: 'POST',
      body: JSON.stringify({ email, password }),
    })
    setSession(data)
    return data
  }

  const logout = async () => {
    try {
      await api('/api/logout', { method: 'POST' })
    } catch {
      // ignore logout errors
    }
    clearSession()
    router.push('/login')
  }

  const fetchUser = async () => {
    try {
      const data = await api('/api/me')
      user.value = data.user
      shop.value = data.shop
      if (data.plan_info) {
        planInfo.value = data.plan_info
        localStorage.setItem('storely-plan-info', JSON.stringify(data.plan_info))
      }
      localStorage.setItem('storely-user', JSON.stringify(data.user))
      localStorage.setItem('storely-shop', JSON.stringify(data.shop))
      return data
    } catch {
      clearSession()
      return null
    }
  }

  return {
    user,
    shop,
    token,
    planInfo,
    isAuthenticated,
    isAdmin,
    register,
    login,
    logout,
    fetchUser,
    api,
  }
}
