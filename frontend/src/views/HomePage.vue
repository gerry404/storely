<script setup>
import HeroSection from '../components/landing/HeroSection.vue'
import LogoTicker from '../components/landing/LogoTicker.vue'
import FeaturesSection from '../components/landing/FeaturesSection.vue'
import HowItWorks from '../components/landing/HowItWorks.vue'
import ShowcaseSection from '../components/landing/ShowcaseSection.vue'
import TestimonialsSection from '../components/landing/TestimonialsSection.vue'
import PricingPreview from '../components/landing/PricingPreview.vue'
import CTASection from '../components/landing/CTASection.vue'
import WaveDivider from '../components/landing/WaveDivider.vue'
</script>

<template>
  <main>
    <HeroSection />
    <LogoTicker />
    <WaveDivider accent="#FF6B2C" :variant="1" />
    <FeaturesSection />
    <WaveDivider accent="#6C5CE7" :variant="2" flip />
    <ShowcaseSection />
    <WaveDivider accent="#2DD4A8" :variant="3" />
    <HowItWorks />
    <WaveDivider accent="#FF6B2C" :variant="1" flip />
    <TestimonialsSection />
    <WaveDivider accent="#6C5CE7" :variant="2" />
    <PricingPreview />
    <WaveDivider accent="#FFAA33" :variant="3" flip />
    <CTASection />
  </main>
</template>
