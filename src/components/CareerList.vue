<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '../services/api'

interface Position {
  id: number
  title: string
  slug: string
  short_description: string | null
  location: string
  type: string
  category: string | null
}

const props = defineProps<{
  locale?: string
  dict: Record<string, unknown>
}>()

const positions = ref<Position[]>([])
const loading = ref(true)
const error = ref('')

const t = (path: string): string => {
  const keys = path.split('.')
  let val: unknown = props.dict
  for (const k of keys) {
    if (val && typeof val === 'object') val = (val as Record<string, unknown>)[k]
    else return path
  }
  return String(val ?? path)
}

const typeOpts: Record<string, string> = props.locale === 'en'
  ? { remote: 'Remote', hybrid: 'Hybrid', 'on-site': 'On-site' }
  : { remote: 'Remoto', hybrid: 'Híbrido', 'on-site': 'Presencial' }

async function fetchPositions() {
  loading.value = true
  error.value = ''

  try {
    const { data } = await api.get('/careers/list.php', {
      params: { status: 'published', locale: props.locale || 'es' },
    })

    const raw = data.data
    const items = Array.isArray(raw) ? raw : (raw?.positions || [])
    positions.value = items

    const countEl = document.getElementById('stats-count')
    if (countEl) countEl.textContent = String(items.length)
  } catch {
    error.value = props.locale === 'en'
      ? 'Error loading positions.'
      : 'Error al cargar las vacantes.'
  } finally {
    loading.value = false
  }
}

function apply(id: number) {
  if ((window as any).__openApplyModal) (window as any).__openApplyModal(id)
}

onMounted(fetchPositions)
</script>

<template>
  <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
    <!-- Loading -->
    <div v-if="loading" class="col-span-full text-center py-12 text-on-surface-variant font-body-lg">
      <svg class="mx-auto mb-3 h-9 w-9 animate-spin text-electric-blue" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
      </svg>
      {{ props.locale === 'en' ? 'Loading positions...' : 'Cargando vacantes...' }}
    </div>

    <!-- Error -->
    <div v-else-if="error" class="col-span-full text-center py-16 text-on-surface-variant">
      <p class="font-body-lg text-body-lg">{{ error }}</p>
    </div>

    <!-- Empty -->
    <div v-else-if="!positions.length" class="col-span-full text-center py-16 text-on-surface-variant">
      <p class="font-body-lg text-body-lg">{{ props.locale === 'en' ? 'No open positions at this time.' : 'No hay vacantes abiertas en este momento.' }}</p>
    </div>

    <!-- Positions -->
    <div
      v-for="p in positions"
      :key="p.id"
      class="group rounded-xl border border-outline-variant/20 bg-surface p-6 md:p-8 transition-all duration-300 hover:border-electric-blue/30 hover:shadow-[0_0_30px_-8px_rgba(0,168,255,0.15)]"
    >
      <div class="flex flex-col gap-4">
        <div class="flex flex-wrap items-start justify-between gap-3">
          <div>
            <h3 class="font-display-lg text-headline-sm text-on-surface">{{ p.title }}</h3>
            <span v-if="p.category" class="font-label-mono text-label-mono text-on-surface-variant/70 text-xs uppercase tracking-wider mt-1 block">{{ p.category }}</span>
          </div>
          <span class="inline-flex shrink-0 items-center gap-1 rounded-full border border-electric-blue/20 bg-electric-blue/10 px-3 py-1 text-xs font-medium text-electric-blue">{{ typeOpts[p.type] || p.type }}</span>
        </div>
        <p v-if="p.short_description" class="font-body-md text-body-md text-slate-text line-clamp-3">{{ p.short_description }}</p>
        <div class="flex flex-wrap items-center gap-4 text-sm text-on-surface-variant/80">
          <span class="inline-flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" /></svg>
            {{ p.location }}
          </span>
        </div>
        <div class="pt-2">
          <button
            @click="apply(p.id)"
            class="inline-flex items-center gap-2 rounded-lg bg-electric-blue px-5 py-2.5 text-sm font-semibold text-white transition-all duration-300 hover:bg-electric-blue/90 hover:shadow-lg hover:shadow-electric-blue/25"
          >
            {{ props.locale === 'en' ? 'Apply now' : 'Postularme' }}
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
