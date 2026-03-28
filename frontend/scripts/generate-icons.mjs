import sharp from 'sharp'
import { mkdirSync } from 'fs'
import { join, dirname } from 'path'
import { fileURLToPath } from 'url'

const __dirname = dirname(fileURLToPath(import.meta.url))
const outDir = join(__dirname, '..', 'public', 'icons')

// Storely icon as SVG with proper padding
const iconSVG = (size, padding = 0) => {
  const p = padding
  const s = size - p * 2
  return Buffer.from(`<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 ${size} ${size}">
    <defs>
      <linearGradient id="g" x1="0%" y1="0%" x2="100%" y2="100%">
        <stop offset="0%" stop-color="#FF6B2C"/>
        <stop offset="100%" stop-color="#FFAA33"/>
      </linearGradient>
    </defs>
    <rect x="${p}" y="${p}" width="${s}" height="${s}" rx="${s * 0.22}" fill="url(#g)"/>
    <g transform="translate(${size / 2 - s * 0.3}, ${size / 2 - s * 0.3}) scale(${s * 0.6 / 24})">
      <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
      <path d="M9 22V12h6v10" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
    </g>
  </svg>`)
}

// Maskable icon: more padding for safe zone (Android adaptive icons need 20% padding)
const maskableSVG = (size) => {
  const padding = Math.round(size * 0.15)
  return iconSVG(size, padding)
}

const sizes = [72, 96, 128, 144, 152, 192, 384, 512]
const maskableSizes = [192, 512]

async function generate() {
  mkdirSync(outDir, { recursive: true })

  // Regular icons
  for (const s of sizes) {
    await sharp(iconSVG(s))
      .resize(s, s)
      .png({ quality: 90 })
      .toFile(join(outDir, `icon-${s}.png`))
    console.log(`  ✓ icon-${s}.png`)
  }

  // Maskable icons (extra padding)
  for (const s of maskableSizes) {
    await sharp(maskableSVG(s))
      .resize(s, s)
      .png({ quality: 90 })
      .toFile(join(outDir, `icon-maskable-${s}.png`))
    console.log(`  ✓ icon-maskable-${s}.png`)
  }

  // Apple touch icons
  for (const s of [120, 152, 167, 180]) {
    await sharp(iconSVG(s))
      .resize(s, s)
      .png({ quality: 90 })
      .toFile(join(outDir, `apple-touch-icon-${s}.png`))
    console.log(`  ✓ apple-touch-icon-${s}.png`)
  }

  console.log('\n  Done! All icons generated.')
}

generate().catch(console.error)
