<script setup>
defineProps({
  flip: { type: Boolean, default: false },
  accent: { type: String, default: '#FF6B2C' },
  variant: { type: Number, default: 1 },
})
</script>

<template>
  <div
    class="relative w-full overflow-hidden pointer-events-none select-none"
    :class="flip ? 'rotate-180' : ''"
    style="margin: -1px 0; line-height: 0;"
  >
    <!-- Variant 1: Bold layered wave -->
    <svg
      v-if="variant === 1"
      viewBox="0 0 1440 180"
      preserveAspectRatio="none"
      class="block w-full h-[100px] md:h-[140px] lg:h-[180px]"
      xmlns="http://www.w3.org/2000/svg"
    >
      <!-- Back wave (accent color, very subtle) -->
      <path
        d="M0,120 C240,180,480,60,720,100 C960,140,1200,40,1440,80 L1440,180 L0,180Z"
        :fill="accent"
        fill-opacity="0.06"
      >
        <animate attributeName="d"
          dur="8s" repeatCount="indefinite"
          values="
            M0,120 C240,180,480,60,720,100 C960,140,1200,40,1440,80 L1440,180 L0,180Z;
            M0,100 C240,60,480,140,720,120 C960,80,1200,160,1440,100 L1440,180 L0,180Z;
            M0,120 C240,180,480,60,720,100 C960,140,1200,40,1440,80 L1440,180 L0,180Z"
        />
      </path>
      <!-- Middle wave -->
      <path
        d="M0,140 C360,80,720,160,1080,100 C1260,70,1380,120,1440,110 L1440,180 L0,180Z"
        :fill="accent"
        fill-opacity="0.04"
      >
        <animate attributeName="d"
          dur="6s" repeatCount="indefinite"
          values="
            M0,140 C360,80,720,160,1080,100 C1260,70,1380,120,1440,110 L1440,180 L0,180Z;
            M0,110 C360,160,720,80,1080,140 C1260,120,1380,80,1440,130 L1440,180 L0,180Z;
            M0,140 C360,80,720,160,1080,100 C1260,70,1380,120,1440,110 L1440,180 L0,180Z"
        />
      </path>
      <!-- Front wave (bg color) -->
      <path
        d="M0,150 C480,100,960,170,1440,130 L1440,180 L0,180Z"
        fill="var(--bg-primary)"
      >
        <animate attributeName="d"
          dur="10s" repeatCount="indefinite"
          values="
            M0,150 C480,100,960,170,1440,130 L1440,180 L0,180Z;
            M0,130 C480,170,960,100,1440,150 L1440,180 L0,180Z;
            M0,150 C480,100,960,170,1440,130 L1440,180 L0,180Z"
        />
      </path>
    </svg>

    <!-- Variant 2: Sharp dramatic wave -->
    <svg
      v-else-if="variant === 2"
      viewBox="0 0 1440 160"
      preserveAspectRatio="none"
      class="block w-full h-[90px] md:h-[120px] lg:h-[160px]"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path
        d="M0,96 C320,160,640,32,960,96 C1120,128,1280,64,1440,80 L1440,160 L0,160Z"
        :fill="accent"
        fill-opacity="0.05"
      >
        <animate attributeName="d"
          dur="7s" repeatCount="indefinite"
          values="
            M0,96 C320,160,640,32,960,96 C1120,128,1280,64,1440,80 L1440,160 L0,160Z;
            M0,80 C320,32,640,128,960,80 C1120,64,1280,128,1440,96 L1440,160 L0,160Z;
            M0,96 C320,160,640,32,960,96 C1120,128,1280,64,1440,80 L1440,160 L0,160Z"
        />
      </path>
      <path
        d="M0,128 C480,80,960,160,1440,112 L1440,160 L0,160Z"
        fill="var(--bg-primary)"
      >
        <animate attributeName="d"
          dur="9s" repeatCount="indefinite"
          values="
            M0,128 C480,80,960,160,1440,112 L1440,160 L0,160Z;
            M0,112 C480,160,960,80,1440,128 L1440,160 L0,160Z;
            M0,128 C480,80,960,160,1440,112 L1440,160 L0,160Z"
        />
      </path>
    </svg>

    <!-- Variant 3: Organic blob wave -->
    <svg
      v-else
      viewBox="0 0 1440 200"
      preserveAspectRatio="none"
      class="block w-full h-[110px] md:h-[150px] lg:h-[200px]"
      xmlns="http://www.w3.org/2000/svg"
    >
      <path
        d="M0,100 C160,160,320,60,480,120 C640,180,800,40,960,100 C1120,160,1280,60,1440,120 L1440,200 L0,200Z"
        :fill="accent"
        fill-opacity="0.04"
      >
        <animate attributeName="d"
          dur="12s" repeatCount="indefinite"
          values="
            M0,100 C160,160,320,60,480,120 C640,180,800,40,960,100 C1120,160,1280,60,1440,120 L1440,200 L0,200Z;
            M0,120 C160,60,320,160,480,100 C640,40,800,180,960,120 C1120,60,1280,160,1440,100 L1440,200 L0,200Z;
            M0,100 C160,160,320,60,480,120 C640,180,800,40,960,100 C1120,160,1280,60,1440,120 L1440,200 L0,200Z"
        />
      </path>
      <path
        d="M0,150 C360,100,720,180,1080,120 C1260,100,1380,140,1440,130 L1440,200 L0,200Z"
        fill="var(--bg-primary)"
      >
        <animate attributeName="d"
          dur="8s" repeatCount="indefinite"
          values="
            M0,150 C360,100,720,180,1080,120 C1260,100,1380,140,1440,130 L1440,200 L0,200Z;
            M0,130 C360,170,720,90,1080,150 C1260,140,1380,110,1440,140 L1440,200 L0,200Z;
            M0,150 C360,100,720,180,1080,120 C1260,100,1380,140,1440,130 L1440,200 L0,200Z"
        />
      </path>
    </svg>
  </div>
</template>
