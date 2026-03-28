<script setup>
import { ref } from 'vue'

const form = ref({ name: '', email: '', phone: '', subject: '', message: '' })
const sending = ref(false)
const sent = ref(false)

const subjects = [
  'Question générale',
  'Problème technique',
  'Demande de partenariat',
  'Devenir agent terrain',
  'Facturation',
  'Autre'
]

const submit = () => {
  sending.value = true
  // Simulate API call
  setTimeout(() => {
    sending.value = false
    sent.value = true
    form.value = { name: '', email: '', phone: '', subject: '', message: '' }
  }, 1500)
}
</script>

<template>
  <main class="min-h-screen pt-28 pb-20">
    <div class="max-w-5xl mx-auto px-6">
      <!-- Header -->
      <div class="text-center mb-16">
        <span class="inline-block px-4 py-1.5 rounded-full glass text-xs font-display font-medium text-brand uppercase tracking-wider mb-6">
          Contact
        </span>
        <h1 class="font-display font-bold text-4xl md:text-6xl text-white tracking-tight mb-4">
          Parlons de votre<br />
          <span class="text-gradient">projet</span>
        </h1>
        <p class="text-white/40 max-w-lg mx-auto">
          Une question ? Un problème ? Nous sommes là pour vous aider. Réponse sous 24h.
        </p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-5 gap-10">
        <!-- Contact info cards -->
        <div class="lg:col-span-2 space-y-4">
          <div class="glass-card rounded-2xl p-6">
            <div class="w-10 h-10 rounded-xl bg-[#25D366]/10 flex items-center justify-center mb-4">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="#25D366"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
            </div>
            <h3 class="font-display font-bold text-white mb-1">WhatsApp</h3>
            <p class="text-sm text-white/40 mb-3">Réponse rapide, du lundi au samedi</p>
            <a href="https://wa.me/237600000000" target="_blank" class="text-sm font-medium text-[#25D366] hover:underline">+237 6XX XXX XXX</a>
          </div>

          <div class="glass-card rounded-2xl p-6">
            <div class="w-10 h-10 rounded-xl bg-brand/10 flex items-center justify-center mb-4">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FF6B2C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
            </div>
            <h3 class="font-display font-bold text-white mb-1">Email</h3>
            <p class="text-sm text-white/40 mb-3">Pour les demandes détaillées</p>
            <a href="mailto:support@storely.app" class="text-sm font-medium text-brand hover:underline">support@storely.app</a>
          </div>

          <div class="glass-card rounded-2xl p-6">
            <div class="w-10 h-10 rounded-xl bg-electric/10 flex items-center justify-center mb-4">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#6C5CE7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <h3 class="font-display font-bold text-white mb-1">Bureaux</h3>
            <p class="text-sm text-white/40 mb-3">Venez nous rencontrer</p>
            <p class="text-sm text-white/60">Douala, Akwa<br />Yaoundé, Bastos</p>
          </div>
        </div>

        <!-- Contact form -->
        <div class="lg:col-span-3">
          <div v-if="sent" class="glass-card rounded-2xl p-10 text-center">
            <div class="w-16 h-16 rounded-full bg-mint/10 flex items-center justify-center mx-auto mb-6">
              <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#2DD4A8" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <h3 class="font-display font-bold text-2xl text-white mb-2">Message envoyé !</h3>
            <p class="text-white/40 mb-6">Nous vous répondrons dans les 24 heures.</p>
            <button @click="sent = false" class="btn-secondary">Envoyer un autre message</button>
          </div>

          <form v-else @submit.prevent="submit" class="glass-card rounded-2xl p-8 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-display font-medium text-white/50 mb-2">Nom complet</label>
                <input v-model="form.name" type="text" required placeholder="Votre nom" class="w-full px-4 py-3 rounded-xl t-input text-sm" />
              </div>
              <div>
                <label class="block text-xs font-display font-medium text-white/50 mb-2">Téléphone</label>
                <input v-model="form.phone" type="tel" placeholder="+237 6XX XXX XXX" class="w-full px-4 py-3 rounded-xl t-input text-sm" />
              </div>
            </div>
            <div>
              <label class="block text-xs font-display font-medium text-white/50 mb-2">Email</label>
              <input v-model="form.email" type="email" required placeholder="votre@email.com" class="w-full px-4 py-3 rounded-xl t-input text-sm" />
            </div>
            <div>
              <label class="block text-xs font-display font-medium text-white/50 mb-2">Sujet</label>
              <select v-model="form.subject" required class="w-full px-4 py-3 rounded-xl t-input text-sm appearance-none cursor-pointer">
                <option value="" disabled>Choisir un sujet</option>
                <option v-for="s in subjects" :key="s" :value="s">{{ s }}</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-display font-medium text-white/50 mb-2">Message</label>
              <textarea v-model="form.message" required rows="5" placeholder="Décrivez votre demande..." class="w-full px-4 py-3 rounded-xl t-input text-sm resize-none" />
            </div>
            <button type="submit" class="btn-primary !w-full !justify-center" :disabled="sending">
              <svg v-if="sending" width="18" height="18" viewBox="0 0 24 24" class="animate-spin"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="31.4" stroke-linecap="round"/></svg>
              <span>{{ sending ? 'Envoi...' : 'Envoyer le message' }}</span>
            </button>
          </form>
        </div>
      </div>
    </div>
  </main>
</template>
