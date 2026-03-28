<script setup>
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue'
import { useMagnetic } from '../../composables/useMagnetic'

const loaded = ref(false)
const scrollY = ref(0)
const heroRef = ref(null)
const ctaPrimaryRef = ref(null)
const ctaSecondaryRef = ref(null)
const headlineChars = ref([])
const subtitleRevealed = ref(false)
const statsRevealed = ref(false)
const deviceMode = ref('phone')
let deviceTimer = null

const line1 = 'Votre boutique,'
const line2 = 'visible partout.'

let cleanupMagnetic1 = null
let cleanupMagnetic2 = null

let ticking = false
const onScroll = () => {
  if (ticking) return
  ticking = true
  requestAnimationFrame(() => {
    scrollY.value = window.scrollY
    ticking = false
  })
}

// Auto-alternate device every 4s
function startDeviceTimer() {
  stopDeviceTimer()
  deviceTimer = setInterval(() => {
    deviceMode.value = deviceMode.value === 'phone' ? 'desktop' : 'phone'
  }, 4000)
}
function stopDeviceTimer() {
  if (deviceTimer) { clearInterval(deviceTimer); deviceTimer = null }
}

onMounted(async () => {
  window.addEventListener('scroll', onScroll, { passive: true })
  await nextTick()
  // Stagger animations with more generous delays
  setTimeout(() => { loaded.value = true }, 300)
  setTimeout(() => {
    headlineChars.value.forEach((el, i) => {
      if (el) setTimeout(() => { el.classList.add('revealed') }, i * 40)
    })
  }, 700)
  setTimeout(() => { subtitleRevealed.value = true }, 1800)
  setTimeout(() => { statsRevealed.value = true }, 2600)
  // Start device auto-switch after hero loads
  setTimeout(() => { startDeviceTimer() }, 3000)
  if (ctaPrimaryRef.value?.$el) cleanupMagnetic1 = useMagnetic(ctaPrimaryRef.value.$el, 0.25)
  if (ctaSecondaryRef.value?.$el) cleanupMagnetic2 = useMagnetic(ctaSecondaryRef.value.$el, 0.2)
})

onUnmounted(() => {
  window.removeEventListener('scroll', onScroll)
  stopDeviceTimer()
  if (cleanupMagnetic1) cleanupMagnetic1()
  if (cleanupMagnetic2) cleanupMagnetic2()
})

const eased = (factor) => {
  const t = Math.min(scrollY.value / 1000, 1)
  return scrollY.value * factor * (1 - t * 0.3)
}

const products = [
  { name: 'Robe Ankara', price: '15 000', color: '#FFAA33', icon: 'dress' },
  { name: 'Sac cuir tressé', price: '8 500', color: '#6C5CE7', icon: 'bag' },
  { name: "Boucles d'or", price: '5 200', color: '#2DD4A8', icon: 'jewelry' },
  { name: 'Sandales perlées', price: '12 000', color: '#FF4D6A', icon: 'shoe' },
]
</script>

<template>
  <section ref="heroRef" class="relative min-h-screen flex items-center justify-center overflow-hidden pt-24 pb-16">
    <!-- Background layers -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
      <div class="parallax-layer absolute -top-40 -right-40 w-[700px] h-[700px] bg-brand/8 rounded-full blur-[150px] animate-blob" :style="{ transform: `translateY(${eased(0.04)}px)` }" />
      <div class="parallax-layer absolute -bottom-40 -left-40 w-[600px] h-[600px] bg-electric/8 rounded-full blur-[120px] animate-blob" style="animation-delay: -4s" :style="{ transform: `translateY(${eased(-0.03)}px)` }" />
      <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-brand-amber/6 rounded-full blur-[100px] animate-glow-pulse" />
      <div class="parallax-layer absolute inset-0 opacity-[0.03]" style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 60px 60px;" :style="{ transform: `translateY(${eased(0.02)}px)` }" />
      <div class="parallax-layer absolute top-[15%] left-[8%] w-3 h-3 rounded-full bg-brand/30 animate-float" :style="{ transform: `translateY(${eased(-0.15)}px)` }" />
      <div class="parallax-layer absolute top-[25%] right-[12%] w-2 h-2 rounded-full bg-mint/40 animate-float-slow" :style="{ transform: `translateY(${eased(-0.1)}px)` }" />
      <div class="parallax-layer absolute bottom-[25%] left-[18%] w-4 h-4 rounded-sm bg-electric/20 animate-float" :style="{ transform: `rotate(45deg) translateY(${eased(-0.2)}px)` }" />
      <div class="parallax-layer absolute top-[55%] right-[8%] w-2.5 h-2.5 rounded-full bg-brand-amber/30 animate-float-slow" :style="{ transform: `translateY(${eased(-0.12)}px)` }" />
    </div>

    <!-- Content -->
    <div class="relative z-10 max-w-6xl mx-auto px-6 text-center">

      <!-- Text block — fades on scroll -->
      <div
        :style="{
          opacity: Math.max(1 - scrollY / 600, 0),
          transform: `translateY(${eased(0.15)}px)`,
        }"
      >
      <!-- Badge -->
      <div
        class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass-light mb-8 transition-all duration-1000"
        :class="loaded ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-6'"
        :style="loaded ? 'clip-path: inset(0 0 0 0)' : 'clip-path: inset(0 100% 0 0)'"
      >
        <span class="w-2 h-2 rounded-full bg-mint animate-pulse" />
        <span class="text-xs font-display font-medium text-white/70 uppercase tracking-wider">+2 500 boutiques actives</span>
      </div>

      <!-- Headline -->
      <h1 class="font-display font-bold text-5xl md:text-7xl lg:text-8xl text-white leading-[1.05] tracking-tight mb-6" style="perspective: 600px">
        <span class="split-line">
          <span class="split-line-inner" :class="loaded ? 'revealed' : ''">
            <span v-for="(char, i) in line1.split('')" :key="'a'+i" :ref="el => { if (el) headlineChars[i] = el }" class="char-reveal inline-block" :style="{ transitionDelay: `${i * 40}ms` }">{{ char === ' ' ? '\u00A0' : char }}</span>
          </span>
        </span>
        <span class="split-line mt-2">
          <span class="split-line-inner" :class="loaded ? 'revealed' : ''" style="transition-delay: 300ms">
            <span v-for="(char, i) in line2.split('')" :key="'b'+i" :ref="el => { if (el) headlineChars[line1.length + i] = el }" class="char-reveal inline-block text-gradient" :style="{ transitionDelay: `${(line1.length + i) * 40}ms` }">{{ char === ' ' ? '\u00A0' : char }}</span>
          </span>
        </span>
      </h1>

      <!-- Subtitle -->
      <p
        class="max-w-2xl mx-auto text-lg md:text-xl text-white/50 leading-relaxed mb-10 font-light transition-all duration-1000 ease-out"
        :style="{ opacity: subtitleRevealed ? 1 : 0, transform: subtitleRevealed ? 'translateY(0)' : 'translateY(20px)', filter: subtitleRevealed ? 'blur(0)' : 'blur(6px)' }"
      >
        Créez votre vitrine digitale en <span class="text-white font-medium">10 minutes</span>.
        Vos produits, vos prix, votre lien — partagez-le sur WhatsApp, Instagram, partout.
      </p>

      <!-- CTA -->
      <div
        class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-14 transition-all duration-1000"
        :style="{ opacity: subtitleRevealed ? 1 : 0, transform: subtitleRevealed ? 'translateY(0)' : 'translateY(15px)' }"
      >
        <router-link ref="ctaPrimaryRef" to="/register" class="btn-primary text-lg !px-8 !py-4 magnetic">
          <span>Créer ma vitrine</span>
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </router-link>
        <a ref="ctaSecondaryRef" href="#pricing" class="btn-secondary text-lg magnetic">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
          <span>Voir les tarifs</span>
        </a>
      </div>
      </div><!-- /text fade wrapper -->

      <!-- Device mode indicator (auto dots) -->
      <div
        class="flex items-center justify-center gap-3 mb-8 transition-all duration-700"
        :style="{ opacity: loaded ? 1 : 0, transform: loaded ? 'translateY(0)' : 'translateY(10px)' }"
      >
        <button
          @click="deviceMode = 'phone'; startDeviceTimer()"
          class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-display font-medium transition-all duration-500"
          :class="deviceMode === 'phone' ? 'bg-white/10 text-white shadow-lg shadow-white/5' : 'text-white/30 hover:text-white/50'"
        >
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/></svg>
          Mobile
        </button>
        <div class="w-px h-4 bg-white/10"></div>
        <button
          @click="deviceMode = 'desktop'; startDeviceTimer()"
          class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-display font-medium transition-all duration-500"
          :class="deviceMode === 'desktop' ? 'bg-white/10 text-white shadow-lg shadow-white/5' : 'text-white/30 hover:text-white/50'"
        >
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
          Desktop
        </button>
      </div>

      <!-- ═══════ DEVICE MOCKUPS ═══════ -->
      <div
        class="relative mx-auto device-container"
        :class="deviceMode === 'phone' ? 'max-w-[320px]' : 'max-w-[780px]'"
        :style="{
          opacity: loaded ? 1 : 0,
          transform: `perspective(1200px) rotateX(${Math.min(scrollY * 0.01, 5)}deg) translateY(${loaded ? 0 : 60}px)`,
          transition: 'opacity 1.2s ease, transform 1.2s cubic-bezier(0.16, 1, 0.3, 1), max-width 0.8s cubic-bezier(0.16, 1, 0.3, 1)',
        }"
      >
        <!-- Glow under device -->
        <div class="absolute -inset-16 bg-gradient-to-t from-brand/20 via-brand/8 to-transparent blur-3xl rounded-full pointer-events-none" />
        <!-- Reflection glow -->
        <div class="absolute -bottom-20 inset-x-10 h-20 bg-brand/10 blur-2xl rounded-full pointer-events-none" />

        <!-- ══ PHONE MOCKUP ══ -->
        <Transition name="device-fade">
        <div
          v-if="deviceMode === 'phone'"
          key="phone"
          class="relative mx-auto"
          style="width: 280px"
        >
          <!-- Phone outer shell — titanium effect -->
          <div class="phone-shell relative rounded-[48px] p-[12px] shadow-2xl">
            <!-- Titanium edge highlights -->
            <div class="absolute inset-0 rounded-[48px] pointer-events-none" style="background: linear-gradient(135deg, rgba(255,255,255,0.08) 0%, transparent 40%, transparent 60%, rgba(255,255,255,0.04) 100%); z-index: 1;" />

            <!-- Side buttons -->
            <div class="absolute -left-[3px] top-[85px] w-[3.5px] h-[30px] rounded-l-sm" style="background: linear-gradient(to bottom, #3a3a42, #28282f, #3a3a42)" />
            <div class="absolute -left-[3px] top-[125px] w-[3.5px] h-[50px] rounded-l-sm" style="background: linear-gradient(to bottom, #3a3a42, #28282f, #3a3a42)" />
            <div class="absolute -left-[3px] top-[182px] w-[3.5px] h-[50px] rounded-l-sm" style="background: linear-gradient(to bottom, #3a3a42, #28282f, #3a3a42)" />
            <div class="absolute -right-[3px] top-[135px] w-[3.5px] h-[65px] rounded-r-sm" style="background: linear-gradient(to bottom, #3a3a42, #28282f, #3a3a42)" />

            <!-- Screen area -->
            <div class="relative bg-black rounded-[36px] overflow-hidden" style="box-shadow: inset 0 0 0 1px rgba(255,255,255,0.05);">

              <!-- Dynamic Island -->
              <div class="absolute top-0 left-0 right-0 z-30 flex justify-center pt-[10px]">
                <div class="w-[100px] h-[30px] bg-black rounded-full flex items-center justify-center gap-2 relative" style="box-shadow: inset 0 0 4px rgba(0,0,0,0.8);">
                  <div class="w-[10px] h-[10px] rounded-full relative" style="background: radial-gradient(circle at 35% 35%, #1a1a3a, #0a0a15); box-shadow: inset 0 1px 2px rgba(0,0,0,0.5), 0 0 2px rgba(50,50,80,0.3);">
                    <div class="absolute top-[1.5px] left-[2px] w-[3px] h-[2px] rounded-full bg-[#2a2a5a]/40" />
                  </div>
                </div>
              </div>

              <!-- Status bar -->
              <div class="relative z-20 flex items-center justify-between px-7 pt-[14px] pb-1" style="background: linear-gradient(to bottom, #0f0f17, transparent)">
                <span class="text-[11px] text-white/90 font-semibold tracking-tight" style="font-family: -apple-system, system-ui, sans-serif">9:41</span>
                <div class="flex items-center gap-1">
                  <svg width="15" height="11" viewBox="0 0 15 11"><rect x="0" y="7" width="3" height="4" rx="0.5" fill="white"/><rect x="4" y="5" width="3" height="6" rx="0.5" fill="white"/><rect x="8" y="2.5" width="3" height="8.5" rx="0.5" fill="white"/><rect x="12" y="0" width="3" height="11" rx="0.5" fill="white"/></svg>
                  <svg width="14" height="11" viewBox="0 0 14 11" fill="white"><path d="M7 9.5a1.5 1.5 0 110 3 1.5 1.5 0 010-3zm-3.5-2.8a5.2 5.2 0 017 0 .6.6 0 01-.85.85 3.9 3.9 0 00-5.3 0 .6.6 0 01-.85-.85zm-2.5-2.5a8.5 8.5 0 0112 0 .6.6 0 01-.85.85 7.2 7.2 0 00-10.3 0A.6.6 0 011 4.2z" opacity=".9"/></svg>
                  <div class="flex items-center gap-[1px]">
                    <div class="w-[22px] h-[11px] border border-white/40 rounded-[3px] p-[1.5px] relative">
                      <div class="h-full w-[80%] bg-[#30D158] rounded-[1px]" />
                    </div>
                    <div class="w-[1.5px] h-[5px] bg-white/40 rounded-r-sm" />
                  </div>
                </div>
              </div>

              <!-- App content -->
              <div class="relative" style="background: linear-gradient(180deg, #0f0f17 0%, #12121c 100%)">
                <!-- Banner image area -->
                <div class="relative h-[80px] overflow-hidden">
                  <div class="absolute inset-0" style="background: linear-gradient(135deg, #FFAA33 0%, #FF6B2C 50%, #FF4D6A 100%); opacity: 0.3;" />
                  <div class="absolute inset-0" style="background: linear-gradient(to top, #0f0f17, transparent 80%)" />
                  <!-- Floating pattern -->
                  <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 50%, white 1px, transparent 1px), radial-gradient(circle at 80% 30%, white 1px, transparent 1px); background-size: 30px 30px;" />
                </div>

                <!-- Store header (overlaps banner) -->
                <div class="px-4 -mt-6 relative z-10 pb-3">
                  <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-[16px] flex items-center justify-center text-white font-bold text-base shrink-0 shadow-lg" style="background: linear-gradient(135deg, #FFAA33, #FF6B2C); font-family: var(--font-display); box-shadow: 0 4px 12px rgba(255,107,44,0.3);">E</div>
                    <div class="flex-1 min-w-0">
                      <div class="flex items-center gap-1.5">
                        <span class="text-white font-semibold text-[13px] truncate" style="font-family: var(--font-display)">Élégance Douala</span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" fill="#2DD4A8"/><polyline points="8 12 11 15 16 9" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                      </div>
                      <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="w-[5px] h-[5px] rounded-full bg-[#2DD4A8] shadow-[0_0_4px_rgba(45,212,168,0.5)]" />
                        <span class="text-[10px] text-white/40">Ouvert · Akwa, Douala</span>
                        <span class="text-[10px] text-white/20">·</span>
                        <div class="flex items-center">
                          <svg v-for="s in 5" :key="s" width="8" height="8" viewBox="0 0 24 24" fill="#FFAA33"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                          <span class="text-[9px] text-white/30 ml-0.5">4.8</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Search bar -->
                <div class="px-4 pb-3">
                  <div class="flex items-center gap-2 px-3 py-2.5 rounded-xl" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06); backdrop-filter: blur(8px);">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.25)" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <span class="text-[11px] text-white/20">Rechercher un produit...</span>
                  </div>
                </div>

                <!-- Product grid -->
                <div class="px-4 pb-2">
                  <div class="grid grid-cols-2 gap-2.5">
                    <div v-for="(p, idx) in products" :key="idx" class="rounded-2xl overflow-hidden group/card" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                      <div class="aspect-square relative flex items-center justify-center" :style="{ background: `linear-gradient(145deg, ${p.color}18, ${p.color}08)` }">
                        <!-- Product icon -->
                        <svg v-if="p.icon === 'dress'" width="28" height="28" viewBox="0 0 24 24" fill="none" :stroke="p.color" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6.5 2H12l-1.5 5h3L8 22l2-8H6l.5-12z" /><path d="M12 2h5.5L18 14h-4l2 8-5.5-15h3L12 2z" /></svg>
                        <svg v-else-if="p.icon === 'bag'" width="28" height="28" viewBox="0 0 24 24" fill="none" :stroke="p.color" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                        <svg v-else-if="p.icon === 'jewelry'" width="28" height="28" viewBox="0 0 24 24" fill="none" :stroke="p.color" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <svg v-else-if="p.icon === 'shoe'" width="28" height="28" viewBox="0 0 24 24" fill="none" :stroke="p.color" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 18h20v2H2z"/><path d="M4 18c0-3 1-5 3-6l2-4h6l2 4c2 1 3 3 3 6"/><path d="M9 8V5a3 3 0 016 0v3"/></svg>
                        <!-- Price tag -->
                        <div class="absolute top-2 right-2 px-1.5 py-0.5 rounded-md text-[8px] font-bold backdrop-blur-sm" :style="{ background: `${p.color}20`, color: p.color, border: `1px solid ${p.color}15` }">
                          {{ p.price }} F
                        </div>
                        <!-- Promo badge on first -->
                        <div v-if="idx === 0" class="absolute top-2 left-2 px-1.5 py-0.5 rounded-md bg-red-500/90 text-white text-[7px] font-bold">-20%</div>
                      </div>
                      <div class="px-2.5 py-2">
                        <p class="text-[10px] text-white/60 truncate leading-tight">{{ p.name }}</p>
                        <div class="flex items-center justify-between mt-1">
                          <p class="text-[11px] font-bold" :style="{ color: p.color }">{{ p.price }} F</p>
                          <div class="w-5 h-5 rounded-full flex items-center justify-center" :style="{ background: `${p.color}15` }">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" :stroke="p.color" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Bottom action button -->
                <div class="px-4 pt-1 pb-4">
                  <div class="flex items-center justify-center gap-2 py-2.5 rounded-2xl" style="background: linear-gradient(135deg, rgba(255,107,44,0.15), rgba(255,170,51,0.1)); border: 1px solid rgba(255,107,44,0.15)">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#FFAA33" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                    <span class="text-[11px] font-bold text-[#FFAA33]">Commander maintenant</span>
                  </div>
                </div>

                <!-- Home indicator -->
                <div class="flex justify-center pb-2 pt-1">
                  <div class="w-[100px] h-[4px] bg-white/15 rounded-full" />
                </div>
              </div>
            </div>
          </div>

          <!-- Phone screen reflection -->
          <div class="absolute inset-[12px] rounded-[36px] pointer-events-none z-10" style="background: linear-gradient(165deg, rgba(255,255,255,0.06) 0%, transparent 35%, transparent 100%);" />
        </div>
        </Transition>

        <!-- ══ DESKTOP MOCKUP ══ -->
        <Transition name="device-fade">
        <div
          v-if="deviceMode === 'desktop'"
          key="desktop"
          class="relative mx-auto w-full"
        >
          <!-- Laptop body -->
          <div class="relative">
            <!-- Screen bezel — aluminum dark finish -->
            <div class="laptop-bezel rounded-t-2xl p-[8px] shadow-2xl">
              <!-- Top bezel with webcam -->
              <div class="flex justify-center py-[6px] relative">
                <div class="w-[7px] h-[7px] rounded-full relative" style="background: radial-gradient(circle at 35% 35%, #3a3a45, #1a1a22); box-shadow: inset 0 1px 2px rgba(0,0,0,0.5), 0 0 3px rgba(60,60,80,0.2);">
                  <div class="absolute top-[1px] left-[1.5px] w-[2px] h-[1.5px] rounded-full bg-[#4a4a6a]/30" />
                </div>
                <!-- Recording indicator -->
                <div class="absolute right-4 top-1/2 -translate-y-1/2 w-[5px] h-[5px] rounded-full bg-[#30D158]/60" />
              </div>

              <!-- Screen -->
              <div class="rounded-lg overflow-hidden" style="box-shadow: inset 0 0 0 1px rgba(255,255,255,0.04), 0 0 30px rgba(0,0,0,0.3);">
                <!-- Browser chrome -->
                <div class="flex items-center gap-2 px-4 py-2.5" style="background: linear-gradient(to bottom, #1a1a24, #161620); border-bottom: 1px solid rgba(255,255,255,0.04);">
                  <!-- Traffic lights -->
                  <div class="flex items-center gap-[6px]">
                    <div class="w-[11px] h-[11px] rounded-full" style="background: radial-gradient(circle at 35% 35%, #FF6B6B, #FF5F57); box-shadow: inset 0 -1px 1px rgba(0,0,0,0.15);" />
                    <div class="w-[11px] h-[11px] rounded-full" style="background: radial-gradient(circle at 35% 35%, #FFC84D, #FFBD2E); box-shadow: inset 0 -1px 1px rgba(0,0,0,0.15);" />
                    <div class="w-[11px] h-[11px] rounded-full" style="background: radial-gradient(circle at 35% 35%, #38D94E, #28C840); box-shadow: inset 0 -1px 1px rgba(0,0,0,0.15);" />
                  </div>
                  <!-- Tab -->
                  <div class="flex items-center gap-2 ml-4 px-3 py-1 rounded-lg" style="background: rgba(255,255,255,0.05);">
                    <div class="w-3 h-3 rounded bg-gradient-to-br from-brand to-brand-amber flex items-center justify-center">
                      <svg width="7" height="7" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                    </div>
                    <span class="text-[10px] text-white/50">Élégance Douala — Storely</span>
                    <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" class="opacity-30"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                  </div>
                  <div class="flex-1" />
                  <!-- URL bar -->
                  <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg w-[280px]" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06);">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#30D158" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    <span class="text-[10px] text-white/35 truncate">storely.app/elegance-douala</span>
                  </div>
                </div>

                <!-- Website content -->
                <div class="flex" style="height: 400px; background: linear-gradient(180deg, #0f0f17 0%, #12121c 100%);">
                  <!-- Main content area -->
                  <div class="flex-1 overflow-hidden">
                    <!-- Hero banner -->
                    <div class="relative h-[110px] overflow-hidden">
                      <div class="absolute inset-0" style="background: linear-gradient(135deg, #FFAA33 0%, #FF6B2C 40%, #FF4D6A 100%); opacity: 0.25;" />
                      <div class="absolute inset-0" style="background: linear-gradient(to top, #0f0f17, transparent 90%)" />
                      <div class="absolute inset-0 opacity-[0.05]" style="background-image: radial-gradient(circle at 30% 40%, white 1px, transparent 1px); background-size: 20px 20px;" />
                    </div>

                    <div class="px-5 -mt-8 relative z-10">
                      <!-- Store header -->
                      <div class="flex items-center gap-3 mb-4">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white font-bold text-xl shrink-0 shadow-xl" style="background: linear-gradient(135deg, #FFAA33, #FF6B2C); font-family: var(--font-display); box-shadow: 0 6px 20px rgba(255,107,44,0.3);">E</div>
                        <div>
                          <div class="flex items-center gap-2">
                            <span class="text-white font-semibold text-sm" style="font-family: var(--font-display)">Élégance Douala</span>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" fill="#2DD4A8"/><polyline points="8 12 11 15 16 9" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            <span class="px-2 py-0.5 rounded-md text-[8px] font-bold" style="background: rgba(45,212,168,0.1); color: #2DD4A8; border: 1px solid rgba(45,212,168,0.15);">Vérifié</span>
                          </div>
                          <p class="text-[10px] text-white/35 mt-0.5">Mode & Accessoires · Akwa, Douala · <span class="text-[#2DD4A8]">Ouvert maintenant</span></p>
                        </div>
                      </div>

                      <!-- Category tabs -->
                      <div class="flex gap-1.5 mb-4">
                        <span class="px-2.5 py-1 rounded-lg text-[9px] font-semibold" style="background: rgba(255,107,44,0.12); color: #FFAA33;">Tous</span>
                        <span class="px-2.5 py-1 rounded-lg bg-white/[0.04] text-white/30 text-[9px]">Robes</span>
                        <span class="px-2.5 py-1 rounded-lg bg-white/[0.04] text-white/30 text-[9px]">Sacs</span>
                        <span class="px-2.5 py-1 rounded-lg bg-white/[0.04] text-white/30 text-[9px]">Bijoux</span>
                        <span class="px-2.5 py-1 rounded-lg bg-white/[0.04] text-white/30 text-[9px]">Chaussures</span>
                      </div>

                      <!-- Product grid -->
                      <div class="grid grid-cols-4 gap-2.5">
                        <div v-for="(p, idx) in [...products, ...products.slice(0,2)]" :key="idx" class="rounded-xl overflow-hidden" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                          <div class="aspect-square flex items-center justify-center relative" :style="{ background: `linear-gradient(145deg, ${p.color}14, ${p.color}06)` }">
                            <!-- Product icon -->
                            <svg v-if="p.icon === 'dress'" width="20" height="20" viewBox="0 0 24 24" fill="none" :stroke="p.color" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6.5 2H12l-1.5 5h3L8 22l2-8H6l.5-12z" /><path d="M12 2h5.5L18 14h-4l2 8-5.5-15h3L12 2z" /></svg>
                            <svg v-else-if="p.icon === 'bag'" width="20" height="20" viewBox="0 0 24 24" fill="none" :stroke="p.color" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>
                            <svg v-else-if="p.icon === 'jewelry'" width="20" height="20" viewBox="0 0 24 24" fill="none" :stroke="p.color" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            <svg v-else-if="p.icon === 'shoe'" width="20" height="20" viewBox="0 0 24 24" fill="none" :stroke="p.color" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 18h20v2H2z"/><path d="M4 18c0-3 1-5 3-6l2-4h6l2 4c2 1 3 3 3 6"/><path d="M9 8V5a3 3 0 016 0v3"/></svg>
                            <div v-if="idx === 0" class="absolute top-1.5 left-1.5 px-1 py-0.5 rounded bg-red-500/90 text-white text-[6px] font-bold">-20%</div>
                          </div>
                          <div class="px-2 py-1.5">
                            <p class="text-[8px] text-white/50 truncate">{{ p.name }}</p>
                            <p class="text-[9px] font-bold" :style="{ color: p.color }">{{ p.price }} F</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Sidebar -->
                  <div class="w-[170px] border-l border-white/[0.04] p-4 shrink-0">
                    <!-- Search -->
                    <div class="flex items-center gap-1.5 px-2.5 py-2 rounded-lg mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                      <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                      <span class="text-[8px] text-white/15">Rechercher...</span>
                    </div>

                    <!-- Store info -->
                    <div class="space-y-3">
                      <div class="text-[9px] text-white/30">
                        <p class="text-white/50 font-medium mb-1">Horaires</p>
                        <p>Lun-Sam · 8h-19h</p>
                      </div>
                      <div class="text-[9px] text-white/30">
                        <p class="text-white/50 font-medium mb-1">Contact</p>
                        <p>+237 6XX XXX XXX</p>
                      </div>
                      <div class="text-[9px] text-white/30">
                        <p class="text-white/50 font-medium mb-1">Évaluations</p>
                        <div class="flex items-center gap-1">
                          <div class="flex">
                            <svg v-for="s in 5" :key="s" width="9" height="9" viewBox="0 0 24 24" fill="#FFAA33"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                          </div>
                          <span class="text-white/40 text-[8px]">4.8</span>
                        </div>
                      </div>

                      <!-- Order CTA -->
                      <div class="flex items-center justify-center gap-1.5 py-2 rounded-xl" style="background: linear-gradient(135deg, rgba(255,107,44,0.12), rgba(255,170,51,0.08)); border: 1px solid rgba(255,107,44,0.12);">
                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="#FFAA33" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
                        <span class="text-[9px] font-bold text-[#FFAA33]">Commander</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Laptop base / hinge — realistic -->
            <div class="relative">
              <div class="h-[16px] rounded-b-[5px] mx-[3px]" style="background: linear-gradient(to bottom, #1c1c24, #252530, #1e1e28);" />
              <div class="h-[7px] rounded-b-xl mx-[-10px]" style="background: linear-gradient(to bottom, #2a2a35, #222230); box-shadow: 0 4px 15px rgba(0,0,0,0.4);">
                <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-[70px] h-[3px] rounded-t-sm" style="background: linear-gradient(to right, #2a2a35, #38384a, #2a2a35)" />
              </div>
            </div>

            <!-- Desktop screen reflection -->
            <div class="absolute top-[8px] bottom-[23px] left-[8px] right-[8px] rounded-lg pointer-events-none z-10" style="background: linear-gradient(165deg, rgba(255,255,255,0.04) 0%, transparent 30%, transparent 100%);" />
          </div>
        </div>
        </Transition>

      </div>

      <!-- Stats -->
      <div class="grid grid-cols-3 gap-6 max-w-lg mx-auto mt-14">
        <div
          v-for="(stat, i) in [
            { value: '2.5k+', label: 'Boutiques' },
            { value: '50k+', label: 'Produits' },
            { value: '12M+', label: 'Vues / mois' }
          ]"
          :key="i"
          class="text-center transition-all duration-700"
          :style="{
            opacity: statsRevealed ? 1 : 0,
            transform: statsRevealed ? 'translateY(0) scale(1)' : 'translateY(20px) scale(0.9)',
            filter: statsRevealed ? 'blur(0)' : 'blur(4px)',
            transitionDelay: `${i * 150}ms`,
          }"
        >
          <p class="font-display font-bold text-2xl md:text-3xl text-white">{{ stat.value }}</p>
          <p class="text-xs text-white/40 mt-1">{{ stat.label }}</p>
        </div>
      </div>
    </div>

    <!-- Scroll indicator -->
    <div
      class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2"
      :style="{ opacity: Math.max(1 - scrollY / 150, 0) }"
    >
      <span class="text-[10px] text-white/40 font-display uppercase tracking-widest">Défiler</span>
      <div class="w-5 h-8 border border-white/20 rounded-full flex justify-center pt-1.5">
        <div class="w-1 h-2 bg-white/40 rounded-full animate-bounce" />
      </div>
    </div>
  </section>
</template>

<style scoped>
.phone-shell {
  background: linear-gradient(145deg, #1e1e26, #16161e, #1a1a22);
  box-shadow:
    0 0 0 1px rgba(255,255,255,0.06),
    0 0 0 3px #0c0c12,
    0 0 0 4px rgba(255,255,255,0.03),
    0 30px 70px rgba(0,0,0,0.6),
    0 15px 30px rgba(0,0,0,0.3);
}

.laptop-bezel {
  background: linear-gradient(145deg, #1e1e26, #16161e, #1a1a22);
  box-shadow:
    0 0 0 1px rgba(255,255,255,0.05),
    0 0 0 3px #0c0c12,
    0 30px 70px rgba(0,0,0,0.5),
    0 15px 35px rgba(0,0,0,0.3);
}

.device-fade-enter-active,
.device-fade-leave-active {
  transition: opacity 0.5s ease, transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
  position: absolute;
  inset: 0;
}
.device-fade-enter-from {
  opacity: 0;
  transform: scale(0.95) translateY(10px);
}
.device-fade-leave-to {
  opacity: 0;
  transform: scale(0.95) translateY(-10px);
}

.device-container {
  position: relative;
  min-height: 520px;
}
</style>
