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
    <WaveDivider accent="#FF6B2C" />
    <FeaturesSection />
    <WaveDivider accent="#6C5CE7" flip />
    <HowItWorks />
    <WaveDivider accent="#2DD4A8" />
    <ShowcaseSection />
    <WaveDivider accent="#FF6B2C" flip />
    <TestimonialsSection />
    <WaveDivider accent="#6C5CE7" />
    <PricingPreview />
    <WaveDivider accent="#FFAA33" flip />
    <CTASection />
  </main>
</template>
