export interface ShowcaseProject {
  name: string
  tag: string
  slug: string | null
  imageKey: string
  liveUrl: string
  saas: boolean
  description?: string
  tech?: string[]
}

export const showcaseProjects: ShowcaseProject[] = [
  { name: 'VUNO POS', tag: 'POS', slug: 'vuno-pos', imageKey: 'pos', liveUrl: '', saas: true },
  {
    name: 'VUNO SHOP',
    tag: 'E-COMMERCE',
    slug: 'vuno-shop',
    imageKey: 'shop',
    liveUrl: 'https://shop.vunotek.com',
    saas: true
  },
  {
    name: 'VUNO DRIVE',
    tag: 'E-COMMERCE',
    slug: 'vuno-drive',
    imageKey: 'drive',
    liveUrl: '',
    saas: true
  },
  {
    name: 'VUNO RESERVATIONS',
    tag: 'HOTEL',
    slug: 'vuno-reservations',
    imageKey: 'hotel',
    liveUrl: '',
    saas: true
  },
  {
    name: 'VUNO ENVÍOS',
    tag: 'LOGISTICS',
    slug: 'vuno-envios',
    imageKey: 'logistics',
    liveUrl: 'https://vuno-sa.netlify.app/',
    saas: false,
    description:
      'Plataforma logística digital conectando Miami con Nicaragua con casillero virtual, rastreo de paquetes, envíos aéreos y marítimos, y entrega puerta a puerta.'
  },
  { name: 'VUNO CRM', tag: 'CRM', slug: 'vuno-crm', imageKey: 'crm', liveUrl: '', saas: true },
  {
    name: 'Sabores & nutrición',
    tag: 'Web Site',
    slug: 'sabores-nutricion',
    imageKey: 'sabores',
    liveUrl: 'https://vuno-nutricion.netlify.app',
    saas: false
  },
  {
    name: 'Experiencias',
    tag: 'Web Site',
    slug: 'experiencias',
    imageKey: 'experiencias',
    liveUrl: 'https://vuno-experiencias.netlify.app/',
    saas: false
  }
]
