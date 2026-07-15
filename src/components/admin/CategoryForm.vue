<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'

const API_URL = import.meta.env.PUBLIC_API_URL || 'https://api.vunotek.com'

interface CategoryData {
  id: number
  name: string
  slug: string
  description: string | null
  color: string
  sort_order: number
}

const props = defineProps<{ categoryId?: string }>()

const isEdit = computed(() => !!props.categoryId)

const loading = ref(!!props.categoryId)
const saving = ref(false)
const error = ref('')
const success = ref('')

const form = ref({
  name: '',
  slug: '',
  description: '',
  color: '#69dca4',
  sort_order: 0,
})

function authHeaders(): Record<string, string> {
  const token = localStorage.getItem('admin_token')
  return token ? { Authorization: `Bearer ${token}` } : {}
}

function autoSlug() {
  form.value.slug = form.value.name
    .toLowerCase()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .trim()
}

async function fetchCategory() {
  if (!props.categoryId) {
    loading.value = false
    return
  }

  try {
    const res = await fetch(`${API_URL}/categories/get.php?id=${props.categoryId}`, { headers: authHeaders() })
    const data = await res.json()
    if (data.success && data.data) {
      const c = data.data as CategoryData
      form.value = {
        name: c.name,
        slug: c.slug,
        description: c.description ?? '',
        color: c.color,
        sort_order: c.sort_order,
      }
    }
  } catch {
    error.value = 'Error al cargar la categoría'
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  error.value = ''
  success.value = ''

  if (!form.value.name || !form.value.slug) {
    error.value = 'Nombre y slug son requeridos'
    return
  }

  saving.value = true
  try {
    const url = isEdit.value
      ? `${API_URL}/categories/update.php?id=${props.categoryId}`
      : `${API_URL}/categories/create.php`

    const method = isEdit.value ? 'PUT' : 'POST'

    const res = await fetch(url, {
      method,
      headers: { 'Content-Type': 'application/json', ...authHeaders() },
      body: JSON.stringify(form.value),
    })

    const data = await res.json()

    if (data.success) {
      success.value = isEdit.value ? 'Categoría actualizada exitosamente' : 'Categoría creada exitosamente'
      if (!isEdit.value) {
        window.location.href = '/admin/categories'
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

onMounted(fetchCategory)
</script>

<template>
  <div v-if="loading" class="rounded-xl border border-outline-variant/20 bg-surface-container p-8 text-center text-on-surface-variant">
    <span class="material-symbols-rounded text-4xl mb-2 block animate-pulse text-outline">hourglass_empty</span>
    Cargando...
  </div>

  <form v-else @submit.prevent="handleSubmit" class="flex flex-col gap-5 max-w-xl">
    <div>
      <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Nombre *</label>
      <input
        v-model="form.name"
        @blur="!isEdit && autoSlug()"
        type="text"
        required
        class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
        placeholder="Frontend"
      />
    </div>

    <div>
      <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Slug *</label>
      <input
        v-model="form.slug"
        type="text"
        required
        class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface font-mono text-sm placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
        placeholder="frontend"
      />
    </div>

    <div>
      <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Descripción</label>
      <textarea
        v-model="form.description"
        rows="3"
        class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30 resize-y"
        placeholder="Descripción breve de la categoría..."
      ></textarea>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
      <div>
        <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Color</label>
        <div class="flex items-center gap-3">
          <input
            v-model="form.color"
            type="color"
            class="h-10 w-14 cursor-pointer rounded-lg border border-outline-variant/40 bg-transparent"
          />
          <input
            v-model="form.color"
            type="text"
            class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface font-mono text-sm transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
          />
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Orden</label>
        <input
          v-model.number="form.sort_order"
          type="number"
          min="0"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
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
        {{ saving ? 'Guardando...' : isEdit ? 'Actualizar categoría' : 'Crear categoría' }}
      </button>
      <a href="/admin/categories" class="rounded-lg border border-outline-variant/40 px-6 py-2.5 font-medium text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-on-surface">
        Cancelar
      </a>
    </div>
  </form>
</template>
