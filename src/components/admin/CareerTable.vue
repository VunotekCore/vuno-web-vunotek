<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '../../stores/auth'
import { careerService } from '../../services/careerService'
import { useToast } from '../../composables/useToast'
import ConfirmDialog from './ui/ConfirmDialog.vue'
import VunotekIcon from './ui/VunotekIcon.vue'

const auth = useAuthStore()
const toast = useToast()
const confirmRef = ref<{ show: (msg: string) => Promise<boolean> } | null>(null)

const canEdit = computed(() => auth.hasPermission('careers', 'edit'))
const canCreate = computed(() => auth.hasPermission('careers', 'create'))
const canDelete = computed(() => auth.hasPermission('careers', 'delete'))

interface Position {
  id: number
  title: string
  slug: string
  location: string
  type: string
  category: string | null
  locale: string
  status: 'draft' | 'published'
  sort_order: number
  created_at: string
}

const positions = ref<Position[]>([])
const loading = ref(true)
const currentPage = ref(1)
const totalPages = ref(1)
const total = ref(0)
const activeLocale = ref<string>('')
const activeStatus = ref<string>('')
const deletingId = ref<number | null>(null)

async function fetchPositions() {
  loading.value = true
  try {
    const params: Record<string, string> = { page: String(currentPage.value) }
    if (activeLocale.value) params.locale = activeLocale.value
    if (activeStatus.value) params.status = activeStatus.value

    const { data } = await careerService.list(params)

    if (data.success) {
      positions.value = data.data.positions
      totalPages.value = data.data.pages
      total.value = data.data.total
    }
  } catch {
    // silent
  } finally {
    loading.value = false
  }
}

async function deletePosition(id: number) {
  const confirmed = await confirmRef.value?.show('¿Eliminar esta vacante?')
  if (!confirmed) return
  deletingId.value = id
  try {
    const { data } = await careerService.delete(id)
    if (data.success) {
      toast.success('Vacante eliminada')
      await fetchPositions()
    } else {
      toast.error(data.message || 'Error al eliminar')
    }
  } catch {
    toast.error('Error de conexión')
  } finally {
    deletingId.value = null
  }
}

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString('es', { day: '2-digit', month: 'short', year: 'numeric' })
}

function setFilter(type: 'locale' | 'status', value: string) {
  if (type === 'locale') activeLocale.value = value
  else activeStatus.value = value
  currentPage.value = 1
  fetchPositions()
}

const typeLabels: Record<string, string> = {
  remote: 'Remoto',
  hybrid: 'Híbrido',
  'on-site': 'Presencial',
}

onMounted(async () => {
  auth.verify()
  await fetchPositions()
})
</script>

<template>
  <div>
    <ConfirmDialog ref="confirmRef" />
    <div class="flex flex-wrap items-center gap-3 mb-4">
      <select
        :value="activeLocale"
        @change="setFilter('locale', ($event.target as HTMLSelectElement).value)"
        aria-label="Filtrar por idioma"
        class="rounded-lg border border-outline-variant/40 bg-surface-container px-3 py-2 text-sm text-on-surface focus:border-vue-green focus:outline-none"
      >
        <option value="">Todos los idiomas</option>
        <option value="es">Español</option>
        <option value="en">English</option>
      </select>

      <select
        :value="activeStatus"
        @change="setFilter('status', ($event.target as HTMLSelectElement).value)"
        aria-label="Filtrar por estado"
        class="rounded-lg border border-outline-variant/40 bg-surface-container px-3 py-2 text-sm text-on-surface focus:border-vue-green focus:outline-none"
      >
        <option value="">Todos los estados</option>
        <option value="published">Publicado</option>
        <option value="draft">Borrador</option>
      </select>

      <span class="ml-auto text-sm text-on-surface-variant">{{ total }} vacantes</span>

      <a
        v-if="canCreate"
        href="/admin/careers/new"
        class="rounded-lg bg-vue-green px-4 py-2 text-sm font-semibold text-on-secondary transition-colors hover:bg-vue-green/90"
      >
        + Nueva vacante
      </a>
    </div>

    <div v-if="loading" class="rounded-xl border border-outline-variant/20 bg-surface-container p-8 text-center text-on-surface-variant">
      <VunotekIcon icon="hourglass_empty" :size="36" class="mb-2 block animate-pulse text-outline" />
      Cargando...
    </div>

    <div v-else-if="positions.length === 0" class="rounded-xl border border-outline-variant/20 bg-surface-container p-8 text-center text-on-surface-variant">
      <VunotekIcon icon="briefcase" :size="36" class="mb-2 block text-outline" />
      No hay vacantes para mostrar
    </div>

    <div v-else class="rounded-xl border border-outline-variant/20 bg-surface-container overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-outline-variant/20 text-left text-on-surface-variant">
              <th class="px-4 py-3 font-medium">Título</th>
              <th class="px-4 py-3 font-medium">Tipo</th>
              <th class="px-4 py-3 font-medium">Ubicación</th>
              <th class="px-4 py-3 font-medium">Idioma</th>
              <th class="px-4 py-3 font-medium">Estado</th>
              <th class="px-4 py-3 font-medium">Orden</th>
              <th class="px-4 py-3 font-medium text-right">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="position in positions"
              :key="position.id"
              class="border-b border-outline-variant/10 hover:bg-surface-container-high/50 transition-colors"
            >
              <td class="px-4 py-3">
                <a :href="canEdit ? `/admin/careers/editar?id=${position.id}` : '#'" :class="canEdit ? 'text-on-surface hover:text-vue-green transition-colors font-medium' : 'text-on-surface font-medium'">
                  {{ position.title }}
                </a>
              </td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                  :style="{ backgroundColor: 'color-mix(in srgb, var(--color-primary) 15%, transparent)', color: 'var(--color-primary)' }"
                >
                  {{ typeLabels[position.type] || position.type }}
                </span>
              </td>
              <td class="px-4 py-3 text-on-surface-variant text-xs">{{ position.location }}</td>
              <td class="px-4 py-3 text-on-surface-variant uppercase text-xs font-mono">{{ position.locale }}</td>
              <td class="px-4 py-3">
                <span
                  :class="[
                    'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                    position.status === 'published'
                      ? 'bg-secondary/15 text-secondary'
                      : 'bg-on-surface-variant/15 text-on-surface-variant',
                  ]"
                >
                  {{ position.status === 'published' ? 'Publicado' : 'Borrador' }}
                </span>
              </td>
              <td class="px-4 py-3 text-on-surface-variant text-xs">{{ position.sort_order }}</td>
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-1">
                  <a
                    v-if="canEdit"
                    :href="`/admin/careers/editar?id=${position.id}`"
                    class="rounded p-1.5 text-on-surface-variant hover:bg-surface-container-highest hover:text-on-surface transition-colors"
                    aria-label="Editar"
                  >
                    <VunotekIcon icon="edit" :size="18" />
                  </a>
                  <button
                    v-if="canDelete"
                    @click="deletePosition(position.id)"
                    :disabled="deletingId === position.id"
                    class="rounded p-1.5 text-on-surface-variant hover:bg-error-container/20 hover:text-error transition-colors disabled:opacity-50"
                    aria-label="Eliminar"
                  >
                    <VunotekIcon :icon="deletingId === position.id ? 'hourglass_empty' : 'delete'" :size="18" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="totalPages > 1" class="flex items-center justify-center gap-2 border-t border-outline-variant/20 px-4 py-3">
        <button
          @click="currentPage--; fetchPositions()"
          :disabled="currentPage <= 1"
          class="rounded-lg px-3 py-1.5 text-sm text-on-surface-variant hover:bg-surface-container-high disabled:opacity-30 transition-colors"
        >
          Anterior
        </button>
        <span class="text-sm text-on-surface-variant">
          {{ currentPage }} / {{ totalPages }}
        </span>
        <button
          @click="currentPage++; fetchPositions()"
          :disabled="currentPage >= totalPages"
          class="rounded-lg px-3 py-1.5 text-sm text-on-surface-variant hover:bg-surface-container-high disabled:opacity-30 transition-colors"
        >
          Siguiente
        </button>
      </div>
    </div>
  </div>
</template>
