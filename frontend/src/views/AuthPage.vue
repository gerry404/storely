<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'
import { countries } from '../data/countries'

const route = useRoute()
const router = useRouter()
const { register, login, api } = useAuth()

const isLogin = computed(() => route.meta.authMode === 'login')

const form = ref({
  name: '',
  email: '',
  phone: '',
  password: '',
  shopName: ''
})

const selectedCountry = ref(countries[0])
const showCountryPicker = ref(false)
const countrySearch = ref('')

const filteredCountries = computed(() => {
  if (!countrySearch.value) return countries
  const q = countrySearch.value.toLowerCase()
  return countries.filter(c =>
    c.name.toLowerCase().includes(q) ||
    c.dial.includes(q) ||
    c.code.toLowerCase().includes(q)
  )
})

const selectCountry = (c) => {
  selectedCountry.value = c
  showCountryPicker.value = false
  countrySearch.value = ''
}

const phoneInputFocused = ref(false)
const countrySearchInput = ref(null)

watch(showCountryPicker, (v) => {
  if (v) nextTick(() => countrySearchInput.value?.focus())
})

const showPassword = ref(false)
const loading = ref(false)
const googleLoading = ref(false)
const errorMessage = ref('')
const fieldErrors = ref({})

// Password strength
const passwordStrength = computed(() => {
  const p = form.value.password
  if (!p) return { score: 0, label: '', color: '' }
  let score = 0
  if (p.length >= 6) score++
  if (p.length >= 10) score++
  if (/[A-Z]/.test(p)) score++
  if (/[0-9]/.test(p)) score++
  if (/[^A-Za-z0-9]/.test(p)) score++

  if (score <= 1) return { score: 1, label: 'Faible', color: '#FF4D6A' }
  if (score <= 2) return { score: 2, label: 'Moyen', color: '#FFAA33' }
  if (score <= 3) return { score: 3, label: 'Bon', color: '#38BDF8' }
  return { score: 4, label: 'Fort', color: '#2DD4A8' }
})

const submit = async () => {
  loading.value = true
  errorMessage.value = ''
  fieldErrors.value = {}

  try {
    if (isLogin.value) {
      await login({
        email: form.value.email,
        password: form.value.password,
      })
    } else {
      await register({
        name: form.value.name,
        email: form.value.email,
        phone: selectedCountry.value.dial + form.value.phone,
        password: form.value.password,
        shop_name: form.value.shopName,
        country: selectedCountry.value.code,
        phone_code: selectedCountry.value.dial,
      })
    }
    router.push('/dashboard')
  } catch (err) {
    if (err.errors && Object.keys(err.errors).length) {
      fieldErrors.value = err.errors
      const firstField = Object.keys(err.errors)[0]
      errorMessage.value = err.errors[firstField][0]
    } else {
      errorMessage.value = err.message || 'Une erreur est survenue. Réessayez.'
    }
  } finally {
    loading.value = false
  }
}

const googleAuth = async () => {
  googleLoading.value = true
  errorMessage.value = ''
  try {
    const data = await api('/api/auth/google/redirect')
    window.location.href = data.url
  } catch (err) {
    errorMessage.value = 'Impossible de se connecter avec Google.'
    googleLoading.value = false
  }
}

const onClickOutside = (e) => {
  if (showCountryPicker.value && !e.target.closest('.country-picker-wrapper')) {
    showCountryPicker.value = false
    countrySearch.value = ''
  }
}

const getFieldError = (field) => {
  return fieldErrors.value[field]?.[0] || ''
}

// Focus states for floating labels
const focusedField = ref('')
</script>

<template>
  <main class="min-h-screen flex relative" @click="onClickOutside">
    <!-- Left: Branding panel (desktop only) -->
    <div class="hidden lg:flex lg:w-[45%] relative overflow-hidden items-center justify-center" style="background: linear-gradient(135deg, #0f0f17 0%, #1a1025 50%, #0f0f17 100%);">
      <!-- Decorative elements -->
      <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-[10%] left-[15%] w-[300px] h-[300px] bg-brand/10 rounded-full blur-[120px]" />
        <div class="absolute bottom-[20%] right-[10%] w-[250px] h-[250px] bg-electric/8 rounded-full blur-[100px]" />
        <div class="absolute inset-0 opacity-[0.02]" style="background-image: radial-gradient(rgba(255,255,255,0.3) 1px, transparent 1px); background-size: 30px 30px;" />
      </div>

      <div class="relative z-10 max-w-md px-12">
        <!-- Logo -->
        <router-link to="/" class="inline-flex items-center gap-3 no-underline mb-12">
          <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-brand to-brand-amber flex items-center justify-center shadow-lg" style="box-shadow: 0 8px 24px rgba(255,107,44,0.3);">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
          </div>
          <span class="font-display text-2xl font-bold text-white">Storely</span>
        </router-link>

        <h2 class="font-display font-bold text-3xl text-white leading-tight mb-4">
          {{ isLogin ? 'Bon retour parmi nous.' : 'Rejoignez des milliers de commerçants.' }}
        </h2>
        <p class="text-white/40 text-lg leading-relaxed mb-10">
          {{ isLogin ? 'Votre boutique vous attend. Continuez à vendre et développez votre activité.' : 'Créez votre vitrine en ligne gratuitement et commencez à vendre dès aujourd\'hui.' }}
        </p>

        <!-- Feature list -->
        <div class="space-y-4">
          <div v-for="(item, i) in [
            { icon: 'zap', text: 'Boutique en ligne en 10 minutes' },
            { icon: 'share', text: 'Lien unique à partager partout' },
            { icon: 'shield', text: 'Paiements sécurisés intégrés' },
          ]" :key="i" class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-lg bg-white/5 flex items-center justify-center shrink-0">
              <svg v-if="item.icon === 'zap'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#FFAA33" stroke-width="2"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
              <svg v-if="item.icon === 'share'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#6C5CE7" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
              <svg v-if="item.icon === 'shield'" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2DD4A8" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <span class="text-sm text-white/50">{{ item.text }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Right: Form panel -->
    <div class="flex-1 flex items-center justify-center px-6 py-12 lg:py-0" style="background: var(--bg-primary);">
      <div class="w-full max-w-md">
        <!-- Mobile logo -->
        <div class="lg:hidden text-center mb-8">
          <router-link to="/" class="inline-flex items-center gap-2 no-underline mb-4">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand to-brand-amber flex items-center justify-center">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
              </svg>
            </div>
            <span class="font-display text-xl font-bold t-heading">Storely</span>
          </router-link>
        </div>

        <!-- Header -->
        <div class="mb-8">
          <h1 class="font-display font-bold text-2xl md:text-3xl t-heading mb-2">
            {{ isLogin ? 'Connexion' : 'Créer un compte' }}
          </h1>
          <p class="text-sm t-text-faint">
            {{ isLogin ? 'Entrez vos identifiants pour accéder à votre boutique' : 'Remplissez les informations pour démarrer' }}
          </p>
        </div>

        <!-- Google OAuth -->
        <button
          @click="googleAuth"
          :disabled="googleLoading"
          class="w-full flex items-center justify-center gap-3 px-4 py-3.5 rounded-xl text-sm font-medium transition-all duration-200 mb-6 group"
          style="background: var(--btn-secondary-bg); border: 1px solid var(--btn-secondary-border); color: var(--text-secondary);"
          :class="googleLoading ? 'opacity-60 pointer-events-none' : 'hover:border-brand/30 hover:shadow-lg'"
        >
          <svg v-if="!googleLoading" width="18" height="18" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
          <svg v-else width="18" height="18" viewBox="0 0 24 24" class="animate-spin" fill="none">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.25"/>
            <path d="M12 2a10 10 0 019.95 9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
          </svg>
          {{ isLogin ? 'Continuer avec Google' : 'S\'inscrire avec Google' }}
        </button>

        <!-- Divider -->
        <div class="flex items-center gap-3 mb-6">
          <div class="flex-1 h-px" style="background: var(--border-default)" />
          <span class="text-xs font-display uppercase tracking-wider" style="color: var(--text-faint)">ou par email</span>
          <div class="flex-1 h-px" style="background: var(--border-default)" />
        </div>

        <!-- Error banner -->
        <transition name="error-slide">
          <div
            v-if="errorMessage"
            class="mb-4 px-4 py-3 rounded-xl flex items-start gap-3"
            style="background: rgba(255,77,106,0.06); border: 1px solid rgba(255,77,106,0.15);"
          >
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FF4D6A" stroke-width="2" class="shrink-0 mt-0.5">
              <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
            </svg>
            <p class="text-sm" style="color: #FF4D6A;">{{ errorMessage }}</p>
          </div>
        </transition>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-4">
          <!-- Name (register only) -->
          <div v-if="!isLogin" class="auth-field">
            <label class="auth-label">Votre nom</label>
            <div class="relative">
              <svg class="auth-field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <input
                v-model="form.name"
                type="text"
                required
                placeholder="Jean Kamga"
                class="auth-input pl-11"
                :class="getFieldError('name') ? '!border-brand-coral/50' : ''"
              />
            </div>
            <p v-if="getFieldError('name')" class="text-xs mt-1" style="color: #FF4D6A;">{{ getFieldError('name') }}</p>
          </div>

          <!-- Shop name (register only) -->
          <div v-if="!isLogin" class="auth-field">
            <label class="auth-label">Nom de votre boutique</label>
            <div class="relative">
              <svg class="auth-field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
              <input
                v-model="form.shopName"
                type="text"
                required
                placeholder="Élégance Mode"
                class="auth-input pl-11"
                :class="getFieldError('shop_name') ? '!border-brand-coral/50' : ''"
              />
            </div>
            <p v-if="getFieldError('shop_name')" class="text-xs mt-1" style="color: #FF4D6A;">{{ getFieldError('shop_name') }}</p>
          </div>

          <!-- Phone (register only) -->
          <div v-if="!isLogin" class="auth-field">
            <label class="auth-label">Téléphone</label>
            <div class="relative country-picker-wrapper">
              <div
                class="flex items-center rounded-xl t-input overflow-hidden transition-all"
                :class="[
                  phoneInputFocused ? 'ring-2 ring-brand/30' : '',
                  getFieldError('phone') ? '!border-brand-coral/50' : ''
                ]"
              >
                <button
                  type="button"
                  @click.stop="showCountryPicker = !showCountryPicker"
                  class="flex items-center gap-1.5 pl-3.5 pr-2 py-3 shrink-0 cursor-pointer hover:bg-white/5 transition-colors group"
                >
                  <span class="text-xl leading-none">{{ selectedCountry.flag }}</span>
                  <svg class="w-3.5 h-3.5 opacity-30 group-hover:opacity-60 transition-all" :class="showCountryPicker ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="w-px h-5 bg-white/10 shrink-0" />
                <span class="pl-2.5 text-sm t-text-faint font-medium shrink-0 select-none">{{ selectedCountry.dial }}</span>
                <input
                  v-model="form.phone"
                  type="tel"
                  required
                  placeholder="6XX XXX XXX"
                  class="flex-1 bg-transparent px-2 py-3 text-sm focus:outline-none"
                  style="color: var(--text-primary)"
                  @focus="phoneInputFocused = true; showCountryPicker = false"
                  @blur="phoneInputFocused = false"
                />
              </div>

              <transition name="dropdown">
                <div
                  v-if="showCountryPicker"
                  class="absolute top-full left-0 right-0 mt-2 rounded-2xl border shadow-2xl z-50 overflow-hidden flex flex-col"
                  style="background: var(--bg-secondary); border-color: var(--input-border); max-height: 280px; backdrop-filter: blur(20px)"
                >
                  <div class="p-3 border-b" style="border-color: var(--input-border)">
                    <div class="relative">
                      <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                      <input
                        ref="countrySearchInput"
                        v-model="countrySearch"
                        placeholder="Rechercher un pays..."
                        class="w-full pl-9 pr-3 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-brand/30 transition-all"
                        style="background: var(--input-bg); border: 1px solid var(--input-border); color: var(--text-primary)"
                        @click.stop
                      />
                    </div>
                  </div>
                  <div class="overflow-y-auto flex-1 py-1 scrollbar-thin">
                    <div
                      v-for="c in filteredCountries"
                      :key="c.code"
                      @click="selectCountry(c)"
                      class="flex items-center gap-3 px-3.5 py-2.5 cursor-pointer transition-all text-sm mx-1 rounded-lg"
                      :class="c.code === selectedCountry.code
                        ? 'bg-brand/10 t-heading'
                        : 'hover:bg-white/5 t-text-secondary'"
                    >
                      <span class="text-xl leading-none w-7 text-center">{{ c.flag }}</span>
                      <span class="flex-1 truncate font-medium">{{ c.name }}</span>
                      <span class="text-xs font-mono" :class="c.code === selectedCountry.code ? 'text-brand' : 'opacity-30'">{{ c.dial }}</span>
                      <svg v-if="c.code === selectedCountry.code" class="w-4 h-4 text-brand shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div v-if="!filteredCountries.length" class="px-4 py-6 text-center">
                      <p class="text-xs t-text-faint">Aucun pays trouvé</p>
                    </div>
                  </div>
                </div>
              </transition>
            </div>
            <p v-if="getFieldError('phone')" class="text-xs mt-1" style="color: #FF4D6A;">{{ getFieldError('phone') }}</p>
          </div>

          <!-- Email -->
          <div class="auth-field">
            <label class="auth-label">Email</label>
            <div class="relative">
              <svg class="auth-field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
              <input
                v-model="form.email"
                type="email"
                required
                placeholder="jean@email.com"
                class="auth-input pl-11"
                :class="getFieldError('email') ? '!border-brand-coral/50' : ''"
              />
            </div>
            <p v-if="getFieldError('email')" class="text-xs mt-1" style="color: #FF4D6A;">{{ getFieldError('email') }}</p>
          </div>

          <!-- Password -->
          <div class="auth-field">
            <div class="flex items-center justify-between mb-1.5">
              <label class="auth-label !mb-0">Mot de passe</label>
              <router-link v-if="isLogin" to="/help" class="text-xs text-brand hover:text-brand-light transition-colors no-underline font-medium">Mot de passe oublié ?</router-link>
            </div>
            <div class="relative">
              <svg class="auth-field-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                :minlength="isLogin ? undefined : 6"
                placeholder="••••••••"
                class="auth-input pl-11 pr-12"
                :class="getFieldError('password') ? '!border-brand-coral/50' : ''"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 p-1 rounded-lg transition-all duration-200 hover:bg-white/5"
                style="color: var(--text-faint);"
              >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path v-if="!showPassword" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle v-if="!showPassword" cx="12" cy="12" r="3"/>
                  <path v-if="showPassword" d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line v-if="showPassword" x1="1" y1="1" x2="23" y2="23"/>
                </svg>
              </button>
            </div>
            <p v-if="getFieldError('password')" class="text-xs mt-1" style="color: #FF4D6A;">{{ getFieldError('password') }}</p>

            <!-- Password strength indicator (register only) -->
            <div v-if="!isLogin && form.password" class="mt-2">
              <div class="flex items-center gap-2">
                <div class="flex-1 flex gap-1">
                  <div
                    v-for="n in 4"
                    :key="n"
                    class="h-1 flex-1 rounded-full transition-all duration-300"
                    :style="{
                      background: passwordStrength.score >= n ? passwordStrength.color : 'var(--border-default)',
                    }"
                  />
                </div>
                <span class="text-[11px] font-medium" :style="{ color: passwordStrength.color }">{{ passwordStrength.label }}</span>
              </div>
            </div>
            <p v-if="!isLogin && !getFieldError('password') && !form.password" class="text-xs t-text-faint mt-1.5">Minimum 6 caractères</p>
          </div>

          <!-- Submit -->
          <button
            type="submit"
            class="btn-primary !w-full !justify-center !mt-6 !py-3.5 text-base"
            :disabled="loading"
            :class="loading ? 'opacity-70 pointer-events-none' : ''"
          >
            <svg v-if="loading" width="18" height="18" viewBox="0 0 24 24" class="animate-spin" fill="none">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.25"/>
              <path d="M12 2a10 10 0 019.95 9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
            </svg>
            <span v-else>{{ isLogin ? 'Se connecter' : 'Créer mon compte' }}</span>
            <svg v-if="!loading" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
            </svg>
          </button>
        </form>

        <!-- Toggle -->
        <p class="text-center text-sm mt-8" style="color: var(--text-faint)">
          {{ isLogin ? 'Pas encore de compte ?' : 'Déjà un compte ?' }}
          <router-link
            :to="isLogin ? '/register' : '/login'"
            class="text-brand hover:text-brand-light transition-colors no-underline font-semibold"
          >
            {{ isLogin ? 'Créer un compte' : 'Se connecter' }}
          </router-link>
        </p>
      </div>
    </div>
  </main>
</template>

<style scoped>
.auth-label {
  display: block;
  font-size: 0.8rem;
  font-family: var(--font-display);
  font-weight: 500;
  color: var(--text-muted);
  margin-bottom: 0.375rem;
}

.auth-field-icon {
  position: absolute;
  left: 0.875rem;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-faint);
  pointer-events: none;
}

.auth-input {
  width: 100%;
  padding: 0.875rem 1rem;
  border-radius: 0.75rem;
  font-size: 0.875rem;
  transition: all 0.2s ease;
  background: var(--input-bg);
  border: 1px solid var(--input-border);
  color: var(--text-primary);
}
.auth-input::placeholder { color: var(--input-placeholder); }
.auth-input:focus {
  outline: none;
  border-color: rgba(255, 107, 44, 0.5);
  box-shadow: 0 0 0 3px rgba(255, 107, 44, 0.08);
}

.dropdown-enter-active { transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1); }
.dropdown-leave-active { transition: all 0.15s ease-in; }
.dropdown-enter-from { opacity: 0; transform: translateY(-8px) scale(0.96); }
.dropdown-leave-to { opacity: 0; transform: translateY(-4px) scale(0.98); }

.error-slide-enter-active { transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.error-slide-leave-active { transition: all 0.2s ease-in; }
.error-slide-enter-from { opacity: 0; transform: translateY(-8px); }
.error-slide-leave-to { opacity: 0; transform: translateY(-4px); }

.scrollbar-thin::-webkit-scrollbar { width: 4px; }
.scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }
.scrollbar-thin::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
</style>
