<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { categoryService } from '../../services/categoryService'
import { useToast } from '../../composables/useToast'

const toast = useToast()

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
    const { data } = await categoryService.list()
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
  if (!confirm('¿Eliminar esta categoría?')) return
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

onMounted(fetchCategories)
</script>

<template>
  <div>
    <div v-if="loading" class="rounded-xl border border-outline-variant/20 bg-surface-container p-8 text-center text-on-surface-variant">
      <span class="material-symbols-rounded text-4xl mb-2 block animate-pulse text-outline">hourglass_empty</span>
      Cargando...
    </div>

    <div v-else-if="categories.length === 0" class="rounded-xl border border-outline-variant/20 bg-surface-container p-8 text-center text-on-surface-variant">
      <span class="material-symbols-rounded text-4xl mb-2 block text-outline">label</span>
      No hay categorías. Crea la primera.
    </div>

    <div v-else class="rounded-xl border border-outline-variant/20 bg-surface-container overflow-hidden">
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
                <a :href="`/admin/categories/${cat.id}`" class="text-on-surface hover:text-vue-green transition-colors font-medium">
                  {{ cat.name }}
                </a>
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
                  <a
                    :href="`/admin/categories/${cat.id}`"
                    class="rounded p-1.5 text-on-surface-variant hover:bg-surface-container-highest hover:text-on-surface transition-colors"
                    title="Editar"
                  >
                    <span class="material-symbols-rounded text-lg">edit</span>
                  </a>
                  <button
                    @click="deleteCategory(cat.id)"
                    :disabled="deletingId === cat.id"
                    class="rounded p-1.5 text-on-surface-variant hover:bg-error-container/20 hover:text-error transition-colors disabled:opacity-50"
                    title="Eliminar"
                  >
                    <span class="material-symbols-rounded text-lg">{{ deletingId === cat.id ? 'hourglass_empty' : 'delete' }}</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
