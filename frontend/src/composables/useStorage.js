import { API_BASE } from './useAuth'

/**
 * Build a full API URL from a relative path.
 * In dev: /api/... (proxied by Vite)
 * In prod: https://storely-api.onrender.com/api/...
 */
export function apiUrl(path) {
  if (!path) return path
  if (path.startsWith('http')) return path
  return `${API_BASE}${path}`
}

/**
 * Converts a relative storage path from the API to a full URL.
 * API returns paths like "products/file.jpg" or "shops/logos/file.jpg"
 * We need to prepend "/storage/" to make them accessible.
 */
export function storageUrl(path) {
  if (!path) return null
  // Already a full URL
  if (path.startsWith('http://') || path.startsWith('https://') || path.startsWith('data:')) return path
  // Already prefixed with /storage/
  if (path.startsWith('/storage/')) return `${API_BASE}${path}`
  // Relative path from API
  return `${API_BASE}/storage/${path}`
}

/**
 * Get the first image URL from a product.
 * Products have an `images` field (JSON array of paths) or `image` (single path).
 */
export function productImageUrl(product) {
  if (!product) return null
  // images is an array
  if (Array.isArray(product.images) && product.images.length > 0) {
    return storageUrl(product.images[0])
  }
  // single image field
  if (product.image) return storageUrl(product.image)
  if (product.image_url) return storageUrl(product.image_url)
  return null
}

/**
 * Get shop logo URL.
 */
export function shopLogoUrl(shop) {
  if (!shop) return null
  return storageUrl(shop.logo_url || shop.logo)
}

/**
 * Get shop banner URL.
 */
export function shopBannerUrl(shop) {
  if (!shop) return null
  return storageUrl(shop.banner_url || shop.banner)
}
