<script setup>
import { ref, onMounted } from 'vue'
import { usePlan } from '../../composables/usePlan'

const { isFree, isStarter, isPro, showUpgradeModal, dismissUpgradeModal, planLabel } = usePlan()
const visible = ref(false)
const animStep = ref(0)

const advantages = [
  { icon: 'unlimited', title: 'Produits illimités', desc: 'Ajoutez autant de produits que vous voulez' },
  { icon: 'orders', title: 'Commandes en ligne', desc: 'Recevez et gérez les commandes directement' },
  { icon: 'stats', title: 'Statistiques complètes', desc: 'Suivez vos ventes, revenus et clients' },
  { icon: 'badge', title: 'Badge vérifié', desc: 'Inspirez confiance avec le badge officiel' },
  { icon: 'promo', title: 'Codes promo', desc: 'Créez des promotions attractives' },
  { icon: 'stock', title: 'Gestion des stocks', desc: 'Alertes de stock bas automatiques' },
]

onMounted(() => {
  if (showUpgradeModal.value && !isPro.value) {
    setTimeout(() => { visible.value = true }, 500)
    let step = 0
    const interval = setInterval(() => {
      step++
      animStep.value = step
      if (step >= advantages.length + 2) clearInterval(interval)
    }, 150)
  }
})

const close = () => {
  visible.value = false
  dismissUpgradeModal()
}
</script>

<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="visible && !isPro" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="close" />

        <!-- Modal -->
        <div class="relative w-full max-w-md rounded-3xl overflow-hidden" style="background: var(--bg-secondary)">
          <!-- Gradient border top -->
          <div class="h-1 bg-gradient-to-r from-brand via-amber-400 to-brand" />

          <!-- Content -->
          <div class="p-6 md:p-8">
            <!-- Header -->
            <div class="text-center mb-6">
              <div
                class="w-14 h-14 mx-auto mb-4 rounded-2xl bg-gradient-to-br from-brand to-amber-400 flex items-center justify-center"
                :style="{ opacity: animStep >= 1 ? 1 : 0, transform: `scale(${animStep >= 1 ? 1 : 0.5})`, transition: 'all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)' }"
              >
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
              </div>
              <h2
                class="font-display font-bold text-xl text-white mb-2"
                :style="{ opacity: animStep >= 2 ? 1 : 0, transform: `translateY(${animStep >= 2 ? 0 : 10}px)`, transition: 'all 0.4s ease' }"
              >
                Débloquez tout le potentiel
              </h2>
              <p class="text-sm text-white/50" :style="{ opacity: animStep >= 2 ? 1 : 0, transition: 'opacity 0.4s ease' }">
                Vous êtes en plan <strong class="text-white/70">{{ planLabel }}</strong>. Passez au niveau supérieur.
              </p>
            </div>

            <!-- Advantages grid -->
            <div class="grid grid-cols-2 gap-3 mb-6">
              <div
                v-for="(adv, i) in advantages"
                :key="i"
                class="p-3 rounded-xl border transition-all"
                :style="{
                  borderColor: 'var(--border-default)',
                  background: 'var(--bg-primary)',
                  opacity: animStep >= i + 3 ? 1 : 0,
                  transform: `translateY(${animStep >= i + 3 ? 0 : 15}px)`,
                  transition: 'all 0.3s ease',
                }"
              >
                <svg v-if="adv.icon === 'unlimited'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FF6B2C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mb-2"><path d="M18.178 8c5.096 0 5.096 8 0 8-5.095 0-7.133-8-12.739-8-4.585 0-4.585 8 0 8 5.606 0 7.644-8 12.74-8z"/></svg>
                <svg v-if="adv.icon === 'orders'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FF6B2C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mb-2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <svg v-if="adv.icon === 'stats'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FF6B2C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mb-2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                <svg v-if="adv.icon === 'badge'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FF6B2C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mb-2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                <svg v-if="adv.icon === 'promo'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FF6B2C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mb-2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                <svg v-if="adv.icon === 'stock'" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#FF6B2C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mb-2"><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                <p class="text-xs font-semibold text-white mb-0.5">{{ adv.title }}</p>
                <p class="text-[10px] text-white/40 leading-tight">{{ adv.desc }}</p>
              </div>
            </div>

            <!-- Promo highlight -->
            <div class="p-3 rounded-xl bg-brand/10 border border-brand/20 text-center mb-5">
              <p class="text-sm text-brand font-semibold">-63% les 3 premiers mois</p>
              <p class="text-xs text-white/40 mt-0.5">Pro à partir de 3 663 F CFA/mois</p>
            </div>

            <!-- CTA -->
            <div class="flex gap-3">
              <button
                @click="close"
                class="flex-1 py-3 rounded-xl text-sm font-medium border transition-colors"
                :style="{ color: 'var(--text-muted)', borderColor: 'var(--border-default)' }"
              >
                Plus tard
              </button>
              <router-link
                to="/dashboard/upgrade"
                @click="close"
                class="flex-1 py-3 rounded-xl text-sm font-medium text-center text-white bg-gradient-to-r from-brand to-amber-500 hover:shadow-lg hover:shadow-brand/20 transition-all no-underline"
              >
                Voir les plans
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<style scoped>
.modal-enter-active { transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1); }
.modal-leave-active { transition: all 0.25s ease; }
.modal-enter-from { opacity: 0; }
.modal-enter-from > div:last-child { transform: scale(0.9) translateY(20px); }
.modal-leave-to { opacity: 0; }
.modal-leave-to > div:last-child { transform: scale(0.95); }
</style>
