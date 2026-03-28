<script setup>
import { ref, computed, watch, nextTick } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useAuth } from '../composables/useAuth'
import { countries } from '../data/countries'

const route = useRoute()
const router = useRouter()
const { register, login } = useAuth()

const isLogin = computed(() => route.meta.authMode === 'login')

const form = ref({
  name: '',
  email: '',
  phone: '',
  password: '',
  shopName: ''
})

const selectedCountry = ref(countries[0]) // Cameroun par défaut
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

// Auto-focus search when opening picker
watch(showCountryPicker, (v) => {
  if (v) nextTick(() => countrySearchInput.value?.focus())
})

const showPassword = ref(false)
const loading = ref(false)
const errorMessage = ref('')
const fieldErrors = ref({})

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
      // Show first error as main message
      const firstField = Object.keys(err.errors)[0]
      errorMessage.value = err.errors[firstField][0]
    } else {
      errorMessage.value = err.message || 'Une erreur est survenue. Réessayez.'
    }
  } finally {
    loading.value = false
  }
}

// Close country picker on outside click
const onClickOutside = (e) => {
  if (showCountryPicker.value && !e.target.closest('.country-picker-wrapper')) {
    showCountryPicker.value = false
    countrySearch.value = ''
  }
}

const getFieldError = (field) => {
  return fieldErrors.value[field]?.[0] || ''
}
</script>

<template>
  <main class="min-h-screen flex items-center justify-center pt-24 pb-16 px-6 relative" @click="onClickOutside">
    <!-- Background -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="absolute -top-40 -right-40 w-[500px] h-[500px] bg-brand/8 rounded-full blur-[120px]" />
      <div class="absolute -bottom-40 -left-40 w-[400px] h-[400px] bg-electric/8 rounded-full blur-[100px]" />
    </div>

    <div class="relative w-full max-w-md">
      <!-- Logo -->
      <div class="text-center mb-8">
        <router-link to="/" class="inline-flex items-center gap-2 no-underline mb-6">
          <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-brand to-brand-amber flex items-center justify-center">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
          </div>
          <span class="font-display text-xl font-bold text-white">Storely</span>
        </router-link>
        <h1 class="font-display font-bold text-2xl md:text-3xl text-white mb-2">
          {{ isLogin ? 'Content de vous revoir' : 'Créez votre vitrine' }}
        </h1>
        <p class="text-sm text-white/40">
          {{ isLogin ? 'Connectez-vous pour gérer votre boutique' : 'Rejoignez +2 500 commerçants sur Storely' }}
        </p>
      </div>

      <!-- Error banner -->
      <div
        v-if="errorMessage"
        class="mb-4 px-4 py-3 rounded-xl bg-brand-coral/10 border border-brand-coral/20 flex items-start gap-3"
      >
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FF4D6A" stroke-width="2" class="shrink-0 mt-0.5">
          <circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
        </svg>
        <p class="text-sm text-brand-coral">{{ errorMessage }}</p>
      </div>

      <!-- Form -->
      <div class="glass-light rounded-2xl p-6 md:p-8">
        <form @submit.prevent="submit" class="space-y-4">
          <!-- Name (register only) -->
          <div v-if="!isLogin">
            <label class="block text-xs font-display font-medium text-white/50 mb-1.5 uppercase tracking-wider">Votre nom</label>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="Jean Kamga"
              class="w-full px-4 py-3 rounded-xl t-input text-sm"
              :class="getFieldError('name') ? '!border-brand-coral/50' : ''"
            />
            <p v-if="getFieldError('name')" class="text-xs text-brand-coral mt-1">{{ getFieldError('name') }}</p>
          </div>

          <!-- Shop name (register only) -->
          <div v-if="!isLogin">
            <label class="block text-xs font-display font-medium text-white/50 mb-1.5 uppercase tracking-wider">Nom de votre boutique</label>
            <input
              v-model="form.shopName"
              type="text"
              required
              placeholder="Élégance Mode"
              class="w-full px-4 py-3 rounded-xl t-input text-sm"
              :class="getFieldError('shop_name') ? '!border-brand-coral/50' : ''"
            />
            <p v-if="getFieldError('shop_name')" class="text-xs text-brand-coral mt-1">{{ getFieldError('shop_name') }}</p>
          </div>

          <!-- Phone (register only) -->
          <div v-if="!isLogin">
            <label class="block text-xs font-display font-medium text-white/50 mb-1.5 uppercase tracking-wider">Téléphone</label>
            <div class="relative country-picker-wrapper">
              <!-- Unified input -->
              <div
                class="flex items-center rounded-xl t-input overflow-hidden transition-all"
                :class="[
                  phoneInputFocused ? 'ring-2 ring-brand/30' : '',
                  getFieldError('phone') ? '!border-brand-coral/50' : ''
                ]"
              >
                <!-- Flag trigger -->
                <button
                  type="button"
                  @click.stop="showCountryPicker = !showCountryPicker"
                  class="flex items-center gap-1.5 pl-3.5 pr-2 py-3 shrink-0 cursor-pointer hover:bg-white/5 transition-colors group"
                >
                  <span class="text-xl leading-none">{{ selectedCountry.flag }}</span>
                  <svg class="w-3.5 h-3.5 opacity-30 group-hover:opacity-60 transition-all" :class="showCountryPicker ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <!-- Separator -->
                <div class="w-px h-5 bg-white/10 shrink-0" />
                <!-- Dial code + input -->
                <span class="pl-2.5 text-sm text-white/40 font-medium shrink-0 select-none">{{ selectedCountry.dial }}</span>
                <input
                  v-model="form.phone"
                  type="tel"
                  required
                  placeholder="6XX XXX XXX"
                  class="flex-1 bg-transparent px-2 py-3 text-sm focus:outline-none"
                  style="color: var(--text-primary, #fff)"
                  @focus="phoneInputFocused = true; showCountryPicker = false"
                  @blur="phoneInputFocused = false"
                />
              </div>

              <!-- Country dropdown -->
              <transition name="dropdown">
                <div
                  v-if="showCountryPicker"
                  class="absolute top-full left-0 right-0 mt-2 rounded-2xl border shadow-2xl z-50 overflow-hidden flex flex-col"
                  style="background: var(--bg-secondary, #1a1a2e); border-color: var(--input-border); max-height: 320px; backdrop-filter: blur(20px)"
                >
                  <!-- Search header -->
                  <div class="p-3 border-b" style="border-color: var(--input-border, rgba(255,255,255,0.08))">
                    <div class="relative">
                      <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                      <input
                        ref="countrySearchInput"
                        v-model="countrySearch"
                        placeholder="Rechercher un pays..."
                        class="w-full pl-9 pr-3 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-brand/30 transition-all"
                        style="background: var(--input-bg, rgba(255,255,255,0.05)); border: 1px solid var(--input-border, rgba(255,255,255,0.08)); color: var(--text-primary, #fff)"
                        @click.stop
                      />
                    </div>
                  </div>
                  <!-- Country list -->
                  <div class="overflow-y-auto flex-1 py-1 scrollbar-thin">
                    <div
                      v-for="c in filteredCountries"
                      :key="c.code"
                      @click="selectCountry(c)"
                      class="flex items-center gap-3 px-3.5 py-2.5 cursor-pointer transition-all text-sm mx-1 rounded-lg"
                      :class="c.code === selectedCountry.code
                        ? 'bg-brand/10 text-white'
                        : 'hover:bg-white/5 text-white/80 hover:text-white'"
                    >
                      <span class="text-xl leading-none w-7 text-center">{{ c.flag }}</span>
                      <span class="flex-1 truncate font-medium">{{ c.name }}</span>
                      <span class="text-xs font-mono" :class="c.code === selectedCountry.code ? 'text-brand' : 'opacity-30'">{{ c.dial }}</span>
                      <!-- Checkmark for selected -->
                      <svg v-if="c.code === selectedCountry.code" class="w-4 h-4 text-brand shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div v-if="!filteredCountries.length" class="px-4 py-6 text-center">
                      <p class="text-xs opacity-40">Aucun pays trouvé</p>
                    </div>
                  </div>
                </div>
              </transition>
            </div>
            <p v-if="getFieldError('phone')" class="text-xs text-brand-coral mt-1">{{ getFieldError('phone') }}</p>
          </div>

          <!-- Email -->
          <div>
            <label class="block text-xs font-display font-medium text-white/50 mb-1.5 uppercase tracking-wider">Email</label>
            <input
              v-model="form.email"
              type="email"
              required
              placeholder="jean@email.com"
              class="w-full px-4 py-3 rounded-xl t-input text-sm"
              :class="getFieldError('email') ? '!border-brand-coral/50' : ''"
            />
            <p v-if="getFieldError('email')" class="text-xs text-brand-coral mt-1">{{ getFieldError('email') }}</p>
          </div>

          <!-- Password -->
          <div>
            <div class="flex items-center justify-between mb-1.5">
              <label class="block text-xs font-display font-medium text-white/50 uppercase tracking-wider">Mot de passe</label>
              <router-link v-if="isLogin" to="/help" class="text-xs text-brand hover:underline no-underline">Oublié ?</router-link>
            </div>
            <div class="relative">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                required
                :minlength="isLogin ? undefined : 6"
                placeholder="••••••••"
                class="w-full px-4 py-3 rounded-xl t-input text-sm pr-12"
                :class="getFieldError('password') ? '!border-brand-coral/50' : ''"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3 top-1/2 -translate-y-1/2 text-white/30 hover:text-white/60 transition-colors"
              >
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path v-if="!showPassword" d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle v-if="!showPassword" cx="12" cy="12" r="3"/>
                  <path v-if="showPassword" d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line v-if="showPassword" x1="1" y1="1" x2="23" y2="23"/>
                </svg>
              </button>
            </div>
            <p v-if="getFieldError('password')" class="text-xs text-brand-coral mt-1">{{ getFieldError('password') }}</p>
            <p v-if="!isLogin && !getFieldError('password')" class="text-xs text-white/20 mt-1">Minimum 6 caractères</p>
          </div>

          <!-- Submit -->
          <button
            type="submit"
            class="btn-primary !w-full !justify-center !mt-6 !py-3.5"
            :disabled="loading"
            :class="loading ? 'opacity-70 pointer-events-none' : ''"
          >
            <svg v-if="loading" width="18" height="18" viewBox="0 0 24 24" class="animate-spin" fill="none">
              <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" opacity="0.25"/>
              <path d="M12 2a10 10 0 019.95 9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
            </svg>
            <span v-else>{{ isLogin ? 'Se connecter' : 'Créer ma vitrine' }}</span>
            <svg v-if="!loading" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
            </svg>
          </button>
        </form>

        <!-- Divider -->
        <div class="flex items-center gap-3 my-6">
          <div class="flex-1 h-px" style="background: var(--border-default)" />
          <span class="text-xs" style="color: var(--text-faint)">ou</span>
          <div class="flex-1 h-px" style="background: var(--border-default)" />
        </div>

        <!-- Social login -->
        <button class="w-full flex items-center justify-center gap-3 px-4 py-3 rounded-xl text-sm transition-all" style="background: var(--btn-secondary-bg); border: 1px solid var(--btn-secondary-border); color: var(--text-secondary)">
          <svg width="18" height="18" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 01-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
          Continuer avec Google
        </button>

        <!-- Toggle -->
        <p class="text-center text-sm mt-6" style="color: var(--text-faint)">
          {{ isLogin ? 'Pas encore de compte ?' : 'Déjà un compte ?' }}
          <router-link
            :to="isLogin ? '/register' : '/login'"
            class="text-brand hover:text-brand-light transition-colors no-underline font-medium"
          >
            {{ isLogin ? 'Créer ma vitrine' : 'Se connecter' }}
          </router-link>
        </p>
      </div>
    </div>
  </main>
</template>

<style scoped>
.dropdown-enter-active { transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1); }
.dropdown-leave-active { transition: all 0.15s ease-in; }
.dropdown-enter-from { opacity: 0; transform: translateY(-8px) scale(0.96); }
.dropdown-leave-to { opacity: 0; transform: translateY(-4px) scale(0.98); }

.scrollbar-thin::-webkit-scrollbar { width: 4px; }
.scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
.scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 4px; }
.scrollbar-thin::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.2); }
</style>
