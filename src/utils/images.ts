import { IK_BASE, ikSrcset } from './imagekit'

function ik(path: string): string {
  return `${IK_BASE}/${path.replace(/^\//, '')}`
}

export const IMAGES = {
  webDev: ik('hero/web-dev.webp'),
  vueCode: ik('hero/vue-code.webp'),
  workstation: ik('services/workstation.webp'),
  serverRoom: ik('services/server-room.webp'),
  dashboard: ik('portfolio/dashboard.webp'),
  shop: ik('vuno-web/products/ecomerce.webp'),
  hotel: ik('vuno-web/products/reservations.webp'),
  logistics: ik('vuno-web/products/logistics.webp'),
  crm: ik('vuno-web/products/crm.webp'),
  pos: ik('vuno-web/products/Vuno-POS.webp'),
  bridge: ik('contact/bridge.webp'),
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

const IMAGE_PATHS: Record<string, string> = {
  webDev: 'hero/web-dev.webp',
  vueCode: 'hero/vue-code.webp',
  workstation: 'services/workstation.webp',
  serverRoom: 'services/server-room.webp',
  dashboard: 'portfolio/dashboard.webp',
  shop: 'vuno-web/products/ecomerce.webp',
  hotel: 'vuno-web/products/reservations.webp',
  logistics: 'vuno-web/products/logistics.webp',
  crm: 'vuno-web/products/crm.webp',
  pos: 'vuno-web/products/Vuno-POS.webp',
  bridge: 'contact/bridge.webp',
}

export function getResponsiveAttrs(key: string): { srcset: string; sizes: string } | null {
  const path = IMAGE_PATHS[key]
  if (!path) return null
  return {
    srcset: ikSrcset(path, [400, 800, 1200]),
    sizes: '(max-width: 768px) 100vw, (max-width: 1200px) 80vw, 800px',
  }
}
