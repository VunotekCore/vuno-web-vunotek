import { imageKitSrc, imgResponsive, type ImageKitOptions } from './imagekit'

function img(path: string, opts?: ImageKitOptions): string {
  return imageKitSrc(path, { quality: 85, format: 'auto', ...opts })
}

export { img, imageKitSrc, imgResponsive, type ImageKitOptions }

export const IMAGES = {
  webDev: img('hero/web-dev.webp', { width: 1200 }),
  vueCode: img('hero/vue-code.webp', { width: 800 }),
  workstation: img('services/workstation.webp', { width: 800 }),
  serverRoom: img('services/server-room.webp', { width: 800 }),
  dashboard: img('portfolio/dashboard.webp', { width: 800 }),
  shop: img('vuno-web/products/ecomerce.webp', { width: 800 }),
  hotel: img('vuno-web/products/reservations.webp', { width: 800 }),
  logistics: img('vuno-web/products/logistics.webp', { width: 800 }),
  crm: img('vuno-web/products/crm.webp', { width: 800 }),
  pos: img('vuno-web/products/Vuno-POS.webp', { width: 800 }),
  bridge: img('contact/bridge.webp', { width: 1200 })
} as const

export const IMAGE_DIMS: Record<string, { width: number; height: number }> = {
  webDev: { width: 1200, height: 800 },
  vueCode: { width: 800, height: 600 },
  workstation: { width: 800, height: 600 },
  serverRoom: { width: 800, height: 600 },
  dashboard: { width: 800, height: 600 },
  shop: { width: 800, height: 600 },
  hotel: { width: 800, height: 600 },
  logistics: { width: 800, height: 600 },
  crm: { width: 800, height: 600 },
  pos: { width: 800, height: 600 },
  bridge: { width: 1200, height: 800 },
} as const
