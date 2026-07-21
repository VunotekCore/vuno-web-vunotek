import type { App } from 'vue'
import { createPinia } from 'pinia'

export default function (app: App) {
  if (typeof window === 'undefined') return
  app.use(createPinia())
}
