<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useAuthStore } from '../../stores/auth'
import { categoryService } from '../../services/categoryService'
import { useToast } from '../../composables/useToast'
import VunotekIcon from './ui/VunotekIcon.vue'

const props = defineProps<{
  categoryId: number | null
}>()

const emit = defineEmits<{
  saved: []
  close: []
}>()

const auth = useAuthStore()
const toast = useToast()
const isViewer = computed(() => auth.isViewer)

interface CategoryData {
  id: number
  name: string
  slug: string
  description: string | null
  color: string
  sort_order: number
}

const isEdit = computed(() => !!props.categoryId)
const loading = ref(false)
const saving = ref(false)
const error = ref('')

const defaultForm = {
  name: '',
  slug: '',
  description: '',
  color: '#69dca4',
  sort_order: 0,
}

const form = ref({ ...defaultForm })

function resetForm() {
  form.value = { ...defaultForm }
  error.value = ''
}

async function fetchCategory() {
  if (!props.categoryId) {
    loading.value = false
    return
  }

  loading.value = true
  try {
    const { data } = await categoryService.get(props.categoryId)
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

async function handleSubmit() {
  error.value = ''

  if (!form.value.name || !form.value.slug) {
    error.value = 'Nombre y slug son requeridos'
    return
  }

  saving.value = true
  try {
    const { data } = isEdit.value
      ? await categoryService.update(props.categoryId!, form.value)
      : await categoryService.create(form.value)

    if (data.success) {
      toast.success(isEdit.value ? 'Categoría actualizada' : 'Categoría creada')
      emit('saved')
    } else {
      error.value = data.message || 'Error al guardar'
    }
  } catch {
    error.value = 'Error de conexión'
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  auth.initFromGlobal()
})

watch(() => props.categoryId, () => {
  resetForm()
  if (props.categoryId) fetchCategory()
}, { immediate: true })
</script>

<template>
  <div v-if="isViewer" class="rounded-xl border border-vue-green/30 bg-vue-green/10 p-8 text-center">
    <VunotekIcon icon="lock" :size="36" class="mb-2 block text-vue-green" />
    <p class="text-on-surface font-medium">Modo solo lectura</p>
    <p class="text-sm text-on-surface-variant mt-1">No tienes permisos para crear o editar categorías.</p>
  </div>

  <div v-else-if="loading" class="rounded-xl border border-outline-variant/20 bg-surface-container p-8 text-center text-on-surface-variant">
    <VunotekIcon icon="hourglass_empty" :size="36" class="mb-2 block animate-pulse text-outline" />
    Cargando...
  </div>

  <form v-else @submit.prevent="handleSubmit" class="flex flex-col gap-5">
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

    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center gap-3 pt-2">
      <button
        type="submit"
        :disabled="saving"
        class="rounded-lg bg-vue-green px-6 py-2.5 font-semibold text-on-secondary transition-colors hover:bg-vue-green/90 disabled:opacity-50 w-full sm:w-auto"
      >
        {{ saving ? 'Guardando...' : isEdit ? 'Actualizar categoría' : 'Crear categoría' }}
      </button>
      <button
        type="button"
        @click="emit('close')"
        class="rounded-lg border border-outline-variant/40 px-6 py-2.5 font-medium text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-on-surface w-full sm:w-auto"
      >
        Cancelar
      </button>
    </div>
  </form>
</template>
