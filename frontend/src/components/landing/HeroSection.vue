<script setup>
import { ref, computed } from 'vue'
import { useReveal } from '../../composables/useReveal'
import { usePointerParallax } from '../../composables/useParallax'

const heroRef = ref(null)
const mockupRef = ref(null)
const headlineRef = ref(null)
const subRef = ref(null)
const ctaRef = ref(null)
const badgeRef = ref(null)

useReveal(badgeRef)
useReveal(headlineRef)
useReveal(subRef)
useReveal(ctaRef)
usePointerParallax(mockupRef, { strength: 22 })

const headline1 = 'Vos réseaux sociaux'
const headline2 = 'créent l\'envie,'
const headline3 = 'Storely encaisse la vente.'

const words = computed(() => [
  ...headline1.split(' ').map((w) => ({ w, brand: false })),
  ...headline2.split(' ').map((w) => ({ w, brand: false })),
  ...headline3.split(' ').map((w) => ({ w, brand: true })),
])

const liveMetrics = [
  { label: 'Vues aujourd\'hui', value: '1 420', accent: '#6C5CE7' },
  { label: 'Commandes', value: '8', accent: '#FF6B2C' },
  { label: 'Encaissé', value: '127,5k', accent: '#2DD4A8' },
]
</script>

<template>
  <section ref="heroRef" class="relative overflow-hidden pt-24 pb-16 md:pt-32 md:pb-24">
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
      <div class="halo-brand" style="top: -20%; right: -12%;" />
      <div class="halo-brand" style="top: 40%; left: -18%; width: 420px; height: 420px; opacity: 0.55;" />
      <div class="absolute inset-0 dots-bg opacity-50" style="mask-image: radial-gradient(ellipse at center top, black 40%, transparent 75%); -webkit-mask-image: radial-gradient(ellipse at center top, black 40%, transparent 75%);" />
    </div>

    <div class="container-max relative">
      <div class="grid lg:grid-cols-12 gap-10 lg:gap-14 items-center">
        <div class="lg:col-span-6">
          <div ref="badgeRef" class="reveal-blur inline-flex items-center gap-2 px-3 py-1.5 rounded-full mb-7"
               style="background: var(--highlight-bg); border: 1px solid var(--highlight-border);">
            <span class="inline-flex w-1.5 h-1.5 rounded-full animate-soft-pulse" style="background: var(--color-brand)" />
            <span class="text-xs font-semibold font-display tracking-wide" style="color: var(--color-brand-dark)">
              Nouveau, réconciliation Mobile Money automatique
            </span>
          </div>

          <h1 ref="headlineRef" class="display-hero mb-6 word-reveal">
            <span v-for="(item, i) in words" :key="i" class="word" :class="item.brand ? 'text-gradient' : ''">
              {{ item.w }}<template v-if="i < words.length - 1">&nbsp;</template>
            </span>
          </h1>

          <p ref="subRef" class="reveal-blur text-lg md:text-xl leading-relaxed mb-8 max-w-xl" style="color: var(--text-muted)">
            Vos clients scrollent, voient votre produit sur Instagram, TikTok, WhatsApp, cliquent sur le lien, payent en Mobile Money. Vous recevez la commande et l'argent, sans capture d'écran ni fichier Excel.
          </p>

          <div ref="ctaRef" class="reveal-stagger flex flex-col sm:flex-row items-start gap-3 mb-10">
            <router-link to="/register" class="btn-primary btn-lg shine-sweep">
              Créer ma boutique, c'est gratuit
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>
              </svg>
            </router-link>
            <router-link to="/marketplace" class="btn-secondary btn-lg">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="5 3 19 12 5 21 5 3"/>
              </svg>
              Voir des boutiques réelles
            </router-link>
          </div>

          <div class="flex flex-wrap items-center gap-x-6 gap-y-3 text-sm" style="color: var(--text-muted)">
            <div class="flex items-center gap-2">
              <div class="flex -space-x-2">
                <div class="w-7 h-7 rounded-full border-2 border-white shadow-sm flex items-center justify-center text-[10px] font-bold font-display text-white" style="background: linear-gradient(135deg, #FF6B2C, #FFAA33)">A</div>
                <div class="w-7 h-7 rounded-full border-2 border-white shadow-sm flex items-center justify-center text-[10px] font-bold font-display text-white" style="background: linear-gradient(135deg, #6C5CE7, #A78BFA)">J</div>
                <div class="w-7 h-7 rounded-full border-2 border-white shadow-sm flex items-center justify-center text-[10px] font-bold font-display text-white" style="background: linear-gradient(135deg, #2DD4A8, #38BDF8)">C</div>
                <div class="w-7 h-7 rounded-full border-2 border-white shadow-sm flex items-center justify-center text-[10px] font-bold font-display text-white" style="background: #0F0F14">+340</div>
              </div>
              <span>marchands vendent déjà</span>
            </div>
            <div class="flex items-center gap-1">
              <svg v-for="s in 5" :key="s" width="14" height="14" viewBox="0 0 24 24" fill="#FFAA33" stroke="none">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01z"/>
              </svg>
              <span class="ml-1"><strong style="color: var(--text-primary)">4.9</strong>, 127 avis vérifiés</span>
            </div>
          </div>
        </div>

        <div ref="mockupRef" class="lg:col-span-6 relative">
          <!-- Photo réelle en arrière-plan, ancre visuelle -->
          <div class="relative" style="perspective: 1400px;">
            <div data-parallax="0.6" class="relative rounded-[28px] overflow-hidden aspect-[4/5] md:aspect-[5/6]"
                 style="box-shadow: var(--card-shadow-xl); transform-style: preserve-3d;">
              <img src="/landing/fashion.jpg" alt="Créatrice de mode camerounaise"
                   class="absolute inset-0 w-full h-full object-cover"
                   loading="eager" />
              <!-- Overlay de teinte chaude, pour intégration au système -->
              <div class="absolute inset-0" style="background: linear-gradient(145deg, rgba(255,107,44,0.12), transparent 55%, rgba(15,15,20,0.45));" />
              <!-- Coin supérieur, nom de boutique en direct -->
              <div class="absolute top-4 left-4 flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-display font-bold"
                   style="background: rgba(255,255,255,0.92); color: #0F0F14; backdrop-filter: blur(8px);">
                <span class="inline-flex w-1.5 h-1.5 rounded-full animate-soft-pulse" style="background: #FF4D6A;" />
                En direct, Douala
              </div>
            </div>

            <!-- Carte métrique flottante haute -->
            <div data-parallax="1.4"
                 class="float-drift absolute -top-6 -right-4 md:-right-8 z-10 hidden sm:block rounded-2xl px-4 py-3.5"
                 style="background: var(--card-bg); border: 1px solid var(--border-default); box-shadow: var(--card-shadow-xl); min-width: 220px;">
              <div class="flex items-center gap-2.5 mb-2.5">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: rgba(37, 211, 102, 0.14)">
                  <svg width="16" height="16" viewBox="0 0 24 24" fill="#25D366" stroke="none">
                    <path d="M20.52 3.48A11.94 11.94 0 0012.07 0C5.4 0 .03 5.38.03 12.04c0 2.12.55 4.19 1.6 6.02L0 24l6.12-1.6a12 12 0 005.95 1.52h.01c6.66 0 12.04-5.38 12.04-12.04 0-3.22-1.25-6.24-3.6-8.4zM12.08 21.9h-.01a9.89 9.89 0 01-5.04-1.38l-.36-.21-3.63.95.97-3.54-.24-.37A9.87 9.87 0 012.03 12c0-5.45 4.44-9.89 9.9-9.89 2.64 0 5.12 1.03 6.99 2.9a9.82 9.82 0 012.89 6.99c0 5.45-4.44 9.89-9.73 9.9z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-[10px] uppercase tracking-wider font-display font-bold" style="color: var(--text-muted)">Commande #4821</p>
                  <p class="text-sm font-bold font-display" style="color: var(--text-primary)">Amina K.</p>
                </div>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-xs" style="color: var(--text-muted)">Robe Ankara, taille M</span>
                <span class="text-sm font-black font-display" style="color: #2DD4A8">+30 000 F</span>
              </div>
            </div>

            <!-- Carte Mobile Money flottante basse gauche -->
            <div data-parallax="1.2"
                 class="float-drift-alt absolute -bottom-6 -left-4 md:-left-8 z-10 hidden sm:flex items-center gap-3 rounded-2xl px-4 py-3"
                 style="background: var(--card-bg); border: 1px solid var(--border-default); box-shadow: var(--card-shadow-xl); min-width: 240px;">
              <div class="flex -space-x-1.5">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-[11px] font-black font-display text-white border-2 border-white" style="background: #FFC000">MTN</div>
                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-[11px] font-black font-display text-white border-2 border-white" style="background: #FF7900">OM</div>
              </div>
              <div class="min-w-0">
                <p class="text-[10px] uppercase tracking-wider font-display font-bold" style="color: var(--text-muted)">Paiement reçu</p>
                <p class="text-sm font-bold font-display" style="color: var(--text-primary)">15 000 FCFA, MoMo</p>
              </div>
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2DD4A8" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="shrink-0">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </div>

            <!-- Strip KPIs en bas, intégré à la photo -->
            <div data-parallax="0.9"
                 class="absolute bottom-4 left-4 right-4 z-10 hidden md:grid grid-cols-3 gap-2 rounded-2xl p-2"
                 style="background: rgba(255,255,255,0.9); border: 1px solid rgba(15,15,20,0.06); backdrop-filter: blur(12px); box-shadow: 0 20px 50px -20px rgba(15,15,20,0.3);">
              <div v-for="m in liveMetrics" :key="m.label" class="px-2 py-1.5 text-center">
                <p class="text-[10px] font-display font-bold uppercase tracking-wider" style="color: #6B6B76">{{ m.label }}</p>
                <p class="font-display font-black text-base" :style="{ color: m.accent }">{{ m.value }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Bandeau trust, ultra fin, ancré au hero -->
    <div class="container-max mt-16 md:mt-20">
      <div class="flex flex-wrap items-center justify-center gap-x-8 gap-y-4 py-6 border-t border-b" style="border-color: var(--border-default);">
        <span class="text-[11px] font-display font-bold uppercase tracking-[0.18em]" style="color: var(--text-muted)">Paiements acceptés</span>
        <div class="flex items-center gap-3 flex-wrap justify-center">
          <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold" style="background: var(--bg-secondary); border: 1px solid var(--border-default);">
            <span class="w-4 h-4 rounded flex items-center justify-center text-[8px] font-black text-white" style="background: #FFC000">M</span>
            MTN MoMo
          </span>
          <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold" style="background: var(--bg-secondary); border: 1px solid var(--border-default);">
            <span class="w-4 h-4 rounded flex items-center justify-center text-[8px] font-black text-white" style="background: #FF7900">O</span>
            Orange Money
          </span>
          <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold" style="background: var(--bg-secondary); border: 1px solid var(--border-default);">
            <span class="w-4 h-4 rounded flex items-center justify-center text-[8px] font-black text-white" style="background: #1A1F71">V</span>
            Visa
          </span>
          <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold" style="background: var(--bg-secondary); border: 1px solid var(--border-default);">
            <span class="w-4 h-4 rounded flex items-center justify-center text-[8px] font-black text-white" style="background: #EB001B">M</span>
            Mastercard
          </span>
          <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold" style="background: var(--bg-secondary); border: 1px solid var(--border-default);">
            <span class="w-4 h-4 rounded flex items-center justify-center text-[8px] font-black text-white" style="background: #F5A623">F</span>
            Flutterwave
          </span>
        </div>
      </div>
    </div>
  </section>
</template>
