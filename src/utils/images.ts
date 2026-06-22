import { imageKitSrc, type ImageKitOptions } from './imagekit';

function img(path: string, opts?: ImageKitOptions): string {
  return imageKitSrc(path, { quality: 85, format: 'auto', ...opts });
}

export { img, imageKitSrc, type ImageKitOptions };

export const IMAGES = {
  webDev: img('hero/web-dev.webp', { width: 1200 }),
  vueCode: img('hero/vue-code.webp', { width: 800 }),
  workstation: img('services/workstation.webp', { width: 800 }),
  serverRoom: img('services/server-room.webp', { width: 800 }),
  dashboard: img('portfolio/dashboard.webp', { width: 800 }),
  crm: img('portfolio/crm.webp', { width: 800 }),
  pos: img('portfolio/pos.webp', { width: 800 }),
  bridge: img('contact/bridge.webp', { width: 1200 }),
} as const;
