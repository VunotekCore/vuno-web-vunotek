<script setup lang="ts">
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import { projectService } from '../services/projectService'

const props = withDefaults(defineProps<{
  title: string
  locale?: string
}>(), { locale: 'es' })

interface Project {
  name: string
  tag: string
  slug: string | null
  image: string
  live_url: string | null
  is_saas: boolean
  description?: string
  tech?: string[]
}

const projects = ref<Project[]>([])
const loading = ref(true)

const detailLabel = props.locale === 'en' ? 'Details' : 'Detalles'
const liveLabel = props.locale === 'en' ? 'Live Preview' : 'Demo en vivo'
const closeLabel = props.locale === 'en' ? 'Close' : 'Cerrar'
const techLabel = props.locale === 'en' ? 'Tech Stack' : 'Tecnología utilizada'

function detailHref(p: Project): string | null {
  if (!p.is_saas || !p.slug) return null
  return props.locale === 'en' ? `/en/proyectos/${p.slug}` : `/proyectos/${p.slug}`
}

function openDialog(name: string) {
  const el = document.querySelector<HTMLDialogElement>(`dialog[data-modal="${name}"]`)
  el?.showModal()
}

function closeDialog(name: string) {
  const el = document.querySelector<HTMLDialogElement>(`dialog[data-modal="${name}"]`)
  el?.close()
}

let io: IntersectionObserver | null = null

onMounted(async () => {
  try {
    const { data } = await projectService.listPublic()
    if (data.success && Array.isArray(data.data)) {
      projects.value = data.data
    }
  } catch {
    // silent
  } finally {
    loading.value = false
  }

  await nextTick()

  document.querySelectorAll<HTMLDialogElement>('dialog[data-modal]').forEach((dialog) => {
    dialog.addEventListener('click', (e) => {
      if (e.target === dialog) dialog.close()
    })
  })

  const trackWrapper = document.querySelector('.showcase-track-wrapper') as HTMLElement | null
  if (trackWrapper) {
    trackWrapper.classList.add('is-offscreen')
    io = new IntersectionObserver(([entry]) => {
      trackWrapper.classList.toggle('is-offscreen', !entry.isIntersecting)
    }, { threshold: 0 })
    io.observe(trackWrapper)
  }
})

onUnmounted(() => {
  io?.disconnect()
})
</script>

<template>
  <div>
    <div class="text-center mb-lg">
      <h2 class="font-display-lg text-headline-lg-mobile md:text-headline-lg text-on-surface">{{ title }}</h2>
    </div>

    <div v-if="loading" class="text-center py-12 text-on-surface-variant">
      {{ locale === 'en' ? 'Loading projects...' : 'Cargando proyectos...' }}
    </div>

    <div v-else class="showcase-track-wrapper">
      <div class="showcase-track">
        <template v-for="(project, idx) in [...projects, ...projects]" :key="`${project.slug}-${idx}`">
          <article class="showcase-card group">
            <div class="showcase-card-image">
              <img
                v-if="project.image"
                :src="project.image"
                :alt="project.name"
                loading="lazy"
                decoding="async"
                width="640"
                height="360"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                fetchpriority="low"
              />
              <div v-else class="w-full h-full bg-surface-charcoal flex items-center justify-center">
                <span class="text-4xl text-slate-text opacity-40">📁</span>
              </div>
            </div>
            <div class="showcase-card-body">
              <span class="showcase-tag">{{ project.tag }}</span>
              <h3 class="font-headline-md text-headline-md text-on-surface truncate">{{ project.name }}</h3>
              <div class="showcase-card-actions">
                <a
                  v-if="project.is_saas && detailHref(project)"
                  :href="detailHref(project)"
                  class="showcase-btn showcase-btn-outline"
                >
                  <span class="text-base">👁</span>
                  {{ detailLabel }}
                </a>
                <button
                  v-else-if="!project.is_saas"
                  type="button"
                  class="showcase-btn showcase-btn-outline"
                  @click="openDialog(project.name)"
                >
                  <span class="text-base">👁</span>
                  {{ detailLabel }}
                </button>
                <a
                  v-if="project.live_url"
                  :href="project.live_url"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="showcase-btn showcase-btn-solid"
                >
                  <span class="text-base">↗</span>
                  {{ liveLabel }}
                </a>
                <span v-else class="showcase-btn showcase-btn-disabled">
                  <span class="text-base">🚧</span>
                  {{ liveLabel }}
                </span>
              </div>
            </div>
          </article>
        </template>
      </div>
    </div>

    <template v-for="project in projects.filter(p => !p.is_saas)" :key="`dialog-${project.slug}`">
      <dialog
        class="showcase-dialog"
        :data-modal="project.name"
        :aria-label="project.name"
      >
        <div class="showcase-dialog-content">
          <div class="showcase-dialog-header">
            <span class="showcase-tag">{{ project.tag }}</span>
            <button
              type="button"
              class="showcase-dialog-close"
              :aria-label="closeLabel"
              @click="closeDialog(project.name)"
            >
              ✕
            </button>
          </div>
          <h3 class="font-headline-lg text-headline-lg text-on-surface">{{ project.name }}</h3>
          <p v-if="project.description" class="font-body-md text-body-md text-slate-text">
            {{ project.description }}
          </p>
          <div v-if="project.tech && project.tech.length > 0" class="showcase-dialog-tech">
            <span class="font-label-mono text-label-mono text-outline">{{ techLabel }}</span>
            <div class="flex flex-wrap gap-sm">
              <span
                v-for="t in project.tech"
                :key="t"
                class="font-label-mono text-label-mono text-electric-blue bg-electric-blue/10 border border-electric-blue/20 px-2.5 py-0.5 rounded-md"
              >
                {{ t }}
              </span>
            </div>
          </div>
        </div>
      </dialog>
    </template>
  </div>
</template>

<style scoped>
.showcase-track-wrapper {
  overflow: hidden;
  mask-image: linear-gradient(to right, transparent 0%, black 5%, black 95%, transparent 100%);
  -webkit-mask-image: linear-gradient(to right, transparent 0%, black 5%, black 95%, transparent 100%);
}

.showcase-track {
  display: flex;
  gap: 24px;
  width: max-content;
  animation: scroll 35s linear infinite;
}

.showcase-track-wrapper:hover .showcase-track {
  animation-play-state: paused;
}

@keyframes scroll {
  0% { transform: translateX(0); }
  100% { transform: translateX(-50%); }
}

.showcase-card {
  flex: 0 0 340px;
  background: rgba(30, 41, 59, 0.7);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(64, 73, 68, 0.3);
  border-radius: 12px;
  overflow: hidden;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

@media (max-width: 768px) {
  .showcase-card {
    backdrop-filter: none;
    -webkit-backdrop-filter: none;
  }
}

.showcase-card:hover {
  border-color: rgba(0, 168, 255, 0.5);
  box-shadow: 0 0 24px rgba(0, 168, 255, 0.12);
}

.showcase-card-image {
  aspect-ratio: 16 / 9;
  overflow: hidden;
  border-bottom: 1px solid rgba(64, 73, 68, 0.3);
}

.showcase-card-body {
  padding: 20px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.showcase-tag {
  font-family: var(--font-label-mono);
  font-size: 12px;
  letter-spacing: 0.05em;
  color: var(--color-electric-blue);
  background: rgba(0, 168, 255, 0.1);
  border: 1px solid rgba(0, 168, 255, 0.2);
  padding: 2px 8px;
  border-radius: 4px;
  text-transform: uppercase;
  width: fit-content;
}

.showcase-card-actions {
  display: flex;
  gap: 8px;
  margin-top: 8px;
}

.showcase-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-family: var(--font-label-mono);
  font-size: 12px;
  font-weight: 500;
  padding: 6px 12px;
  border-radius: 6px;
  text-decoration: none;
  transition: all 0.2s ease;
  white-space: nowrap;
  cursor: pointer;
}

.showcase-btn-outline {
  color: var(--color-electric-blue);
  border: 1px solid rgba(0, 168, 255, 0.3);
  background: transparent;
}

.showcase-btn-outline:hover {
  background: rgba(0, 168, 255, 0.1);
  border-color: rgba(0, 168, 255, 0.6);
}

.showcase-btn-solid {
  color: #003549;
  background: var(--color-electric-blue);
  border: 1px solid var(--color-electric-blue);
}

.showcase-btn-solid:hover {
  background: #33b3ff;
  border-color: #33b3ff;
}

.showcase-btn-disabled {
  color: var(--color-outline);
  border: 1px solid rgba(64, 73, 68, 0.3);
  background: transparent;
  cursor: not-allowed;
  opacity: 0.5;
}

.showcase-dialog {
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%) scale(0.95);
  max-width: 480px;
  width: calc(100% - 48px);
  padding: 0;
  border: 1px solid rgba(64, 73, 68, 0.4);
  border-radius: 16px;
  background: var(--color-surface-container);
  color: var(--color-on-surface);
  box-shadow: 0 24px 80px rgba(0, 0, 0, 0.6);
  opacity: 0;
  transition: opacity 0.25s ease, transform 0.25s ease;
  overflow: hidden;
}

.showcase-dialog[open] {
  opacity: 1;
  transform: translate(-50%, -50%) scale(1);
}

.showcase-dialog::backdrop {
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
}

.showcase-dialog-content {
  padding: 28px;
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.showcase-dialog-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
}

.showcase-dialog-close {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
  border: 1px solid rgba(64, 73, 68, 0.3);
  border-radius: 8px;
  background: transparent;
  color: var(--color-slate-text);
  cursor: pointer;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.showcase-dialog-close:hover {
  background: rgba(255, 255, 255, 0.05);
  color: var(--color-on-surface);
  border-color: rgba(64, 73, 68, 0.6);
}

.showcase-dialog-tech {
  display: flex;
  flex-direction: column;
  gap: 8px;
  padding-top: 8px;
  border-top: 1px solid rgba(64, 73, 68, 0.2);
}

@media (max-width: 640px) {
  .showcase-card {
    flex: 0 0 280px;
  }

  .showcase-track {
    gap: 16px;
  }
}

@media (prefers-reduced-motion: reduce) {
  .showcase-track {
    animation: none;
  }

  .showcase-track-wrapper {
    overflow-x: auto;
    overflow-y: hidden;
    -webkit-overflow-scrolling: touch;
    scroll-snap-type: x mandatory;
  }

  .showcase-card {
    scroll-snap-align: center;
  }

  .showcase-dialog {
    transition: none;
  }
}

.showcase-track-wrapper.is-offscreen .showcase-track {
  animation-play-state: paused;
}
</style>
