<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '../../stores/auth'
import { categoryService } from '../../services/categoryService'
import { useToast } from '../../composables/useToast'
import ConfirmDialog from './ui/ConfirmDialog.vue'
import CategoryModal from './CategoryModal.vue'
import VunotekIcon from './ui/VunotekIcon.vue'

const toast = useToast()
const auth = useAuthStore()
const confirmRef = ref<{ show: (msg: string) => Promise<boolean> } | null>(null)
const modalRef = ref<InstanceType<typeof CategoryModal> | null>(null)

const canEdit = computed(() => auth.hasPermission('categories', 'edit'))
const canDelete = computed(() => auth.hasPermission('categories', 'delete'))

interface Category {
  id: number
  name: string
  slug: string
  description: string | null
  color: string
  sort_order: number
  post_count: number
  created_at: string
}

const categories = ref<Category[]>([])
const loading = ref(true)
const deletingId = ref<number | null>(null)

async function fetchCategories() {
  loading.value = true
  try {
    const { data } = await categoryService.listAdmin()
    if (data.success && Array.isArray(data.data)) {
      categories.value = data.data
    }
  } catch {
    // silent
  } finally {
    loading.value = false
  }
}

async function deleteCategory(id: number) {
  const confirmed = await confirmRef.value?.show('¿Eliminar esta categoría?')
  if (!confirmed) return
  deletingId.value = id
  try {
    const { data } = await categoryService.delete(id)
    if (data.success) {
      toast.success('Categoría eliminada')
      await fetchCategories()
    } else {
      toast.error(data.message || 'Error al eliminar')
    }
  } catch {
    toast.error('Error de conexión')
  } finally {
    deletingId.value = null
  }
}

onMounted(async () => {
  auth.verify()
  await fetchCategories()
})
</script>

<template>
  <div>
    <ConfirmDialog ref="confirmRef" />
    <CategoryModal ref="modalRef" @saved="fetchCategories" />

    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
      <p class="text-on-surface-variant">Gestiona las categorías del blog</p>
      <button
        @click="modalRef?.open()"
        class="inline-flex items-center justify-center gap-2 rounded-lg bg-vue-green px-4 py-2.5 font-medium text-on-secondary transition-colors hover:bg-vue-green/90 w-full md:w-auto"
      >
        <VunotekIcon icon="add" :size="20" />
        Nueva categoría
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="rounded-xl border border-outline-variant/20 bg-surface-container p-8 text-center text-on-surface-variant">
      <VunotekIcon icon="hourglass_empty" :size="36" class="mb-2 block animate-pulse text-outline" />
      Cargando...
    </div>

    <!-- Empty -->
    <div v-else-if="categories.length === 0" class="rounded-xl border border-outline-variant/20 bg-surface-container p-8 text-center text-on-surface-variant">
      <VunotekIcon icon="label" :size="36" class="mb-2 block text-outline" />
      No hay categorías. Crea la primera.
    </div>

    <!-- Desktop table -->
    <div v-else class="rounded-xl border border-outline-variant/20 bg-surface-container overflow-hidden hidden md:block">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-outline-variant/20 text-left text-on-surface-variant">
              <th class="px-4 py-3 font-medium">Nombre</th>
              <th class="px-4 py-3 font-medium">Slug</th>
              <th class="px-4 py-3 font-medium">Color</th>
              <th class="px-4 py-3 font-medium">Posts</th>
              <th class="px-4 py-3 font-medium">Orden</th>
              <th class="px-4 py-3 font-medium text-right">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="cat in categories"
              :key="cat.id"
              class="border-b border-outline-variant/10 hover:bg-surface-container-high/50 transition-colors"
            >
              <td class="px-4 py-3">
                <button @click="modalRef?.open(cat.id)" class="text-on-surface hover:text-vue-green transition-colors font-medium text-left">
                  {{ cat.name }}
                </button>
              </td>
              <td class="px-4 py-3 text-on-surface-variant font-mono text-xs">{{ cat.slug }}</td>
              <td class="px-4 py-3">
                <span class="inline-flex items-center gap-2">
                  <span class="h-3 w-3 rounded-full" :style="{ backgroundColor: cat.color }"></span>
                  <span class="text-xs text-on-surface-variant font-mono">{{ cat.color }}</span>
                </span>
              </td>
              <td class="px-4 py-3 text-on-surface-variant">{{ cat.post_count }}</td>
              <td class="px-4 py-3 text-on-surface-variant">{{ cat.sort_order }}</td>
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-1">
                  <button
                    v-if="canEdit"
                    @click="modalRef?.open(cat.id)"
                    class="rounded p-1.5 text-on-surface-variant hover:bg-surface-container-highest hover:text-on-surface transition-colors"
                    aria-label="Editar"
                  >
                    <VunotekIcon icon="edit" :size="18" />
                  </button>
                  <button
                    v-if="canDelete"
                    @click="deleteCategory(cat.id)"
                    :disabled="deletingId === cat.id"
                    class="rounded p-1.5 text-on-surface-variant hover:bg-error-container/20 hover:text-error transition-colors disabled:opacity-50"
                    aria-label="Eliminar"
                  >
                    <VunotekIcon :icon="deletingId === cat.id ? 'hourglass_empty' : 'delete'" :size="18" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Mobile cards -->
    <div v-if="!loading && categories.length > 0" class="md:hidden space-y-3">
      <div
        v-for="cat in categories"
        :key="cat.id"
        class="admin-glass-card overflow-hidden rounded-xl"
      >
        <div class="px-5 pt-4 pb-3 border-b border-outline-variant/10">
          <div class="flex items-center justify-between gap-2">
            <button @click="modalRef?.open(cat.id)" class="text-on-surface hover:text-vue-green font-medium text-sm text-left truncate">
              {{ cat.name }}
            </button>
            <span class="inline-flex items-center gap-1.5 shrink-0">
              <span class="h-2.5 w-2.5 rounded-full" :style="{ backgroundColor: cat.color }"></span>
              <span class="text-[10px] text-on-surface-variant font-mono">{{ cat.color }}</span>
            </span>
          </div>
        </div>
        <div class="px-5 py-3 space-y-2 text-sm">
          <div class="flex justify-between">
            <span class="text-on-surface-variant">Slug</span>
            <span class="font-mono text-xs text-on-surface-variant">{{ cat.slug }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-on-surface-variant">Posts</span>
            <span class="text-on-surface">{{ cat.post_count }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-on-surface-variant">Orden</span>
            <span class="text-on-surface">{{ cat.sort_order }}</span>
          </div>
        </div>
        <div class="px-5 pb-4 pt-3 border-t border-outline-variant/10 flex gap-2">
          <button
            v-if="canEdit"
            @click="modalRef?.open(cat.id)"
            class="admin-touch-target flex-1 rounded-lg border border-outline-variant/40 py-2.5 text-sm font-medium text-on-surface-variant hover:bg-surface-container-high transition-colors"
          >
            Editar
          </button>
          <button
            v-if="canDelete"
            @click="deleteCategory(cat.id)"
            :disabled="deletingId === cat.id"
            class="admin-touch-target flex-1 rounded-lg border border-error-container/40 py-2.5 text-sm font-medium text-error hover:bg-error-container/20 transition-colors disabled:opacity-50"
          >
            Eliminar
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
