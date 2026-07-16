<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '../../stores/auth'
import { blogService } from '../../services/blogService'
import { categoryService } from '../../services/categoryService'
import { useToast } from '../../composables/useToast'

const auth = useAuthStore()
const isViewer = computed(() => auth.isViewer)
const toast = useToast()

interface Category {
  id: number
  name: string
  slug: string
  color: string
}

interface PostData {
  id: number
  title: string
  slug: string
  excerpt: string
  content: string
  category_id: number
  author: string
  locale: string
  image: string
  meta_title: string
  og_image: string
  status: string
}

const props = defineProps<{ postId?: string }>()

const isEdit = computed(() => !!props.postId)

const categories = ref<Category[]>([])
const loading = ref(true)
const saving = ref(false)
const error = ref('')
const success = ref('')

const form = ref({
  title: '',
  slug: '',
  excerpt: '',
  content: '',
  category_id: 0,
  author: 'Daniel Flores',
  locale: 'es',
  image: '',
  meta_title: '',
  og_image: '',
  status: 'draft',
})

function autoSlug() {
  form.value.slug = form.value.title
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .trim()
}

async function fetchCategories() {
  try {
    const { data } = await categoryService.list()
    if (data.success && Array.isArray(data.data)) {
      categories.value = data.data
    }
  } catch {
    // silent
  }
}

async function fetchPost() {
  if (!props.postId) {
    loading.value = false
    return
  }

  try {
    const { data } = await blogService.get(Number(props.postId))
    if (data.success && data.data) {
      const p = data.data as PostData
      form.value = {
        title: p.title,
        slug: p.slug,
        excerpt: p.excerpt,
        content: p.content,
        category_id: p.category_id,
        author: p.author,
        locale: p.locale,
        image: p.image ?? '',
        meta_title: p.meta_title ?? '',
        og_image: p.og_image ?? '',
        status: p.status,
      }
    }
  } catch {
    error.value = 'Error al cargar el post'
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  error.value = ''
  success.value = ''

  if (!form.value.title || !form.value.slug || !form.value.content || !form.value.category_id) {
    error.value = 'Título, slug, contenido y categoría son requeridos'
    return
  }

  saving.value = true
  try {
    const { data } = isEdit.value
      ? await blogService.update(Number(props.postId), form.value)
      : await blogService.create(form.value)

    if (data.success) {
      success.value = isEdit.value ? 'Post actualizado exitosamente' : 'Post creado exitosamente'
      if (!isEdit.value) {
        window.location.href = '/admin/blog'
      }
    } else {
      error.value = data.message || 'Error al guardar'
    }
  } catch {
    error.value = 'Error de conexión'
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  await fetchCategories()
  await fetchPost()
})
</script>

<template>
  <div v-if="isViewer" class="rounded-xl border border-vue-green/30 bg-vue-green/10 p-8 text-center">
    <span class="material-symbols-rounded text-4xl mb-2 block text-vue-green">lock</span>
    <p class="text-on-surface font-medium">Modo solo lectura</p>
    <p class="text-sm text-on-surface-variant mt-1">No tienes permisos para crear o editar posts.</p>
  </div>

  <div v-else-if="loading" class="rounded-xl border border-outline-variant/20 bg-surface-container p-8 text-center text-on-surface-variant">
    <span class="material-symbols-rounded text-4xl mb-2 block animate-pulse text-outline">hourglass_empty</span>
    Cargando...
  </div>

  <form v-else @submit.prevent="handleSubmit" class="flex flex-col gap-5">
    <div class="grid gap-5 sm:grid-cols-2">
      <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Título *</label>
        <input
          v-model="form.title"
          @blur="!isEdit && autoSlug()"
          type="text"
          required
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
          placeholder="Mi artículo"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Slug *</label>
        <input
          v-model="form.slug"
          type="text"
          required
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface font-mono text-sm placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
          placeholder="mi-articulo"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Categoría *</label>
        <select
          v-model="form.category_id"
          required
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
        >
          <option :value="0" disabled>Seleccionar categoría</option>
          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Idioma</label>
        <select
          v-model="form.locale"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
        >
          <option value="es">Español</option>
          <option value="en">English</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Estado</label>
        <select
          v-model="form.status"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
        >
          <option value="draft">Borrador</option>
          <option value="published">Publicado</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Autor</label>
        <input
          v-model="form.author"
          type="text"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Meta Title</label>
        <input
          v-model="form.meta_title"
          type="text"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
          placeholder="SEO title"
        />
      </div>

      <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Excerpt *</label>
        <textarea
          v-model="form.excerpt"
          required
          rows="2"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30 resize-y"
          placeholder="Breve descripción del artículo..."
        ></textarea>
      </div>

      <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Contenido (Markdown) *</label>
        <textarea
          v-model="form.content"
          required
          rows="14"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface font-mono text-sm placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30 resize-y"
          placeholder="# Título del artículo&#10;&#10;Contenido aquí..."
        ></textarea>
      </div>

      <div>
        <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Imagen URL</label>
        <input
          v-model="form.image"
          type="text"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
          placeholder="https://..."
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-on-surface-variant mb-1.5">OG Image URL</label>
        <input
          v-model="form.og_image"
          type="text"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
          placeholder="https://..."
        />
      </div>
    </div>

    <div v-if="error" class="rounded-lg bg-error-container/20 px-4 py-3 text-sm text-error">{{ error }}</div>
    <div v-if="success" class="rounded-lg bg-secondary/15 px-4 py-3 text-sm text-secondary">{{ success }}</div>

    <div class="flex items-center gap-3 pt-2">
      <button
        type="submit"
        :disabled="saving"
        class="rounded-lg bg-vue-green px-6 py-2.5 font-semibold text-on-secondary transition-colors hover:bg-vue-green/90 disabled:opacity-50"
      >
        {{ saving ? 'Guardando...' : isEdit ? 'Actualizar post' : 'Crear post' }}
      </button>
      <a href="/admin/blog" class="rounded-lg border border-outline-variant/40 px-6 py-2.5 font-medium text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-on-surface">
        Cancelar
      </a>
    </div>
  </form>
</template>
