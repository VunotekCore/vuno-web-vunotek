<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '../../stores/auth'

const API_URL = import.meta.env.PUBLIC_API_URL || 'https://api.vunotek.com'
const auth = useAuthStore()

const canEdit = computed(() => auth.hasPermission('blog', 'edit'))
const canCreate = computed(() => auth.hasPermission('blog', 'create'))
const canDelete = computed(() => auth.hasPermission('blog', 'delete'))

interface Post {
  id: number
  title: string
  slug: string
  excerpt: string
  category_name: string
  category_slug: string
  author: string
  locale: string
  status: 'draft' | 'published'
  created_at: string
  updated_at: string
}

const posts = ref<Post[]>([])
const loading = ref(true)
const currentPage = ref(1)
const totalPages = ref(1)
const total = ref(0)
const activeLocale = ref<string>('')
const activeStatus = ref<string>('')
const deletingId = ref<number | null>(null)

async function fetchPosts() {
  loading.value = true
  try {
    const params = new URLSearchParams({ page: String(currentPage.value) })
    if (activeLocale.value) params.set('locale', activeLocale.value)
    if (activeStatus.value) params.set('status', activeStatus.value)

    const res = await fetch(`${API_URL}/blog/list.php?${params}`, { headers: auth.authHeaders() })
    const data = await res.json()

    if (data.success) {
      posts.value = data.data.posts
      totalPages.value = data.data.pages
      total.value = data.data.total
    }
  } catch {
    // silent
  } finally {
    loading.value = false
  }
}

async function deletePost(id: number) {
  if (!confirm('¿Eliminar este post?')) return
  deletingId.value = id
  try {
    const res = await fetch(`${API_URL}/blog/delete.php?id=${id}`, {
      method: 'DELETE',
      headers: auth.authHeaders(),
    })
    const data = await res.json()
    if (data.success) {
      await fetchPosts()
    } else {
      alert(data.message || 'Error al eliminar')
    }
  } catch {
    alert('Error de conexión')
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
  fetchPosts()
}

onMounted(fetchPosts)
</script>

<template>
  <div>
    <div class="flex flex-wrap items-center gap-3 mb-4">
      <select
        :value="activeLocale"
        @change="setFilter('locale', ($event.target as HTMLSelectElement).value)"
        class="rounded-lg border border-outline-variant/40 bg-surface-container px-3 py-2 text-sm text-on-surface focus:border-vue-green focus:outline-none"
      >
        <option value="">Todos los idiomas</option>
        <option value="es">Español</option>
        <option value="en">English</option>
      </select>

      <select
        :value="activeStatus"
        @change="setFilter('status', ($event.target as HTMLSelectElement).value)"
        class="rounded-lg border border-outline-variant/40 bg-surface-container px-3 py-2 text-sm text-on-surface focus:border-vue-green focus:outline-none"
      >
        <option value="">Todos los estados</option>
        <option value="published">Publicado</option>
        <option value="draft">Borrador</option>
      </select>

      <span class="ml-auto text-sm text-on-surface-variant">{{ total }} posts</span>

      <a
        v-if="canCreate"
        href="/admin/blog/new"
        class="rounded-lg bg-vue-green px-4 py-2 text-sm font-semibold text-on-secondary transition-colors hover:bg-vue-green/90"
      >
        + Nuevo post
      </a>
    </div>

    <div v-if="loading" class="rounded-xl border border-outline-variant/20 bg-surface-container p-8 text-center text-on-surface-variant">
      <span class="material-symbols-rounded text-4xl mb-2 block animate-pulse text-outline">hourglass_empty</span>
      Cargando...
    </div>

    <div v-else-if="posts.length === 0" class="rounded-xl border border-outline-variant/20 bg-surface-container p-8 text-center text-on-surface-variant">
      <span class="material-symbols-rounded text-4xl mb-2 block text-outline">article</span>
      No hay posts para mostrar
    </div>

    <div v-else class="rounded-xl border border-outline-variant/20 bg-surface-container overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="border-b border-outline-variant/20 text-left text-on-surface-variant">
              <th class="px-4 py-3 font-medium">Título</th>
              <th class="px-4 py-3 font-medium">Categoría</th>
              <th class="px-4 py-3 font-medium">Idioma</th>
              <th class="px-4 py-3 font-medium">Estado</th>
              <th class="px-4 py-3 font-medium">Fecha</th>
              <th class="px-4 py-3 font-medium text-right">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="post in posts"
              :key="post.id"
              class="border-b border-outline-variant/10 hover:bg-surface-container-high/50 transition-colors"
            >
              <td class="px-4 py-3">
                <a :href="canEdit ? `/admin/blog/${post.id}` : '#'" :class="canEdit ? 'text-on-surface hover:text-vue-green transition-colors font-medium' : 'text-on-surface font-medium'">
                  {{ post.title }}
                </a>
              </td>
              <td class="px-4 py-3 text-on-surface-variant">
                <span
                  class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                  :style="{ backgroundColor: 'color-mix(in srgb, var(--color-primary) 15%, transparent)', color: 'var(--color-primary)' }"
                >
                  {{ post.category_name }}
                </span>
              </td>
              <td class="px-4 py-3 text-on-surface-variant uppercase text-xs font-mono">{{ post.locale }}</td>
              <td class="px-4 py-3">
                <span
                  :class="[
                    'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                    post.status === 'published'
                      ? 'bg-secondary/15 text-secondary'
                      : 'bg-on-surface-variant/15 text-on-surface-variant',
                  ]"
                >
                  {{ post.status === 'published' ? 'Publicado' : 'Borrador' }}
                </span>
              </td>
              <td class="px-4 py-3 text-on-surface-variant text-xs">{{ formatDate(post.created_at) }}</td>
              <td class="px-4 py-3 text-right">
                <div class="flex items-center justify-end gap-1">
                  <a
                    v-if="canEdit"
                    :href="`/admin/blog/${post.id}`"
                    class="rounded p-1.5 text-on-surface-variant hover:bg-surface-container-highest hover:text-on-surface transition-colors"
                    title="Editar"
                  >
                    <span class="material-symbols-rounded text-lg">edit</span>
                  </a>
                  <button
                    v-if="canDelete"
                    @click="deletePost(post.id)"
                    :disabled="deletingId === post.id"
                    class="rounded p-1.5 text-on-surface-variant hover:bg-error-container/20 hover:text-error transition-colors disabled:opacity-50"
                    title="Eliminar"
                  >
                    <span class="material-symbols-rounded text-lg">{{ deletingId === post.id ? 'hourglass_empty' : 'delete' }}</span>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="totalPages > 1" class="flex items-center justify-center gap-2 border-t border-outline-variant/20 px-4 py-3">
        <button
          @click="currentPage--; fetchPosts()"
          :disabled="currentPage <= 1"
          class="rounded-lg px-3 py-1.5 text-sm text-on-surface-variant hover:bg-surface-container-high disabled:opacity-30 transition-colors"
        >
          Anterior
        </button>
        <span class="text-sm text-on-surface-variant">
          {{ currentPage }} / {{ totalPages }}
        </span>
        <button
          @click="currentPage++; fetchPosts()"
          :disabled="currentPage >= totalPages"
          class="rounded-lg px-3 py-1.5 text-sm text-on-surface-variant hover:bg-surface-container-high disabled:opacity-30 transition-colors"
        >
          Siguiente
        </button>
      </div>
    </div>
  </div>
</template>
