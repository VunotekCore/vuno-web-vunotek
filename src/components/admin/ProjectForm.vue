<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '../../stores/auth'
import { projectService } from '../../services/projectService'
import { useToast } from '../../composables/useToast'
import VunotekIcon from './ui/VunotekIcon.vue'
import ImageUpload from './ui/ImageUpload.vue'

const auth = useAuthStore()
const toast = useToast()
const isViewer = computed(() => auth.isViewer)

interface ProjectData {
  id: number
  name: string
  tag: string
  slug: string
  image: string
  live_url: string
  is_saas: boolean
  description: string
  tech: string[]
  locale: string
  status: string
  sort_order: number
}

const params = new URLSearchParams(window.location.search)
const projectId = params.get('id')

const isEdit = computed(() => !!projectId)

const loading = ref(true)
const saving = ref(false)
const error = ref('')
const success = ref('')

const form = ref({
  name: '',
  tag: '',
  slug: '',
  image: '',
  live_url: '',
  is_saas: true,
  description: '',
  tech: [] as string[],
  locale: 'es',
  status: 'draft',
  sort_order: 0,
})

const newTech = ref('')

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

function addTech() {
  const val = newTech.value.trim()
  if (val && !form.value.tech.includes(val)) {
    form.value.tech.push(val)
  }
  newTech.value = ''
}

function removeTech(index: number) {
  form.value.tech.splice(index, 1)
}

async function fetchProject() {
  if (!projectId) {
    loading.value = false
    return
  }

  try {
    const { data } = await projectService.get(Number(projectId))
    if (data.success && data.data) {
      const p = data.data as ProjectData
      form.value = {
        name: p.name,
        tag: p.tag,
        slug: p.slug,
        image: p.image ?? '',
        live_url: p.live_url ?? '',
        is_saas: p.is_saas,
        description: p.description ?? '',
        tech: Array.isArray(p.tech) ? p.tech : [],
        locale: p.locale,
        status: p.status,
        sort_order: p.sort_order,
      }
    }
  } catch {
    error.value = 'Error al cargar el proyecto'
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  error.value = ''
  success.value = ''

  if (!form.value.name || !form.value.tag || !form.value.slug) {
    error.value = 'Nombre, tag y slug son requeridos'
    return
  }

  saving.value = true
  try {
    const { data } = isEdit.value
      ? await projectService.update(Number(projectId), form.value)
      : await projectService.create(form.value)

    if (data.success) {
      toast.success(isEdit.value ? 'Proyecto actualizado' : 'Proyecto creado')
      if (!isEdit.value) {
        window.location.href = '/admin/projects'
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
  auth.verify()
  await fetchProject()
})
</script>

<template>
  <div v-if="isViewer" class="rounded-xl border border-vue-green/30 bg-vue-green/10 p-8 text-center">
    <VunotekIcon icon="lock" :size="36" class="mb-2 block text-vue-green" />
    <p class="text-on-surface font-medium">Modo solo lectura</p>
    <p class="text-sm text-on-surface-variant mt-1">No tienes permisos para crear o editar proyectos.</p>
  </div>

  <div v-else-if="loading" class="rounded-xl border border-outline-variant/20 bg-surface-container p-8 text-center text-on-surface-variant">
    <VunotekIcon icon="hourglass_empty" :size="36" class="mb-2 block animate-pulse text-outline" />
    Cargando...
  </div>

  <form v-else @submit.prevent="handleSubmit" class="flex flex-col gap-5">
    <div class="grid gap-5 sm:grid-cols-2">
      <div class="sm:col-span-2">
        <label for="proj-name" class="block text-sm font-medium text-on-surface-variant mb-1.5">Nombre *</label>
        <input
          id="proj-name"
          v-model="form.name"
          @blur="!isEdit && autoSlug()"
          type="text"
          required
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
          placeholder="Mi proyecto"
        />
      </div>

      <div>
        <label for="proj-tag" class="block text-sm font-medium text-on-surface-variant mb-1.5">Tag *</label>
        <input
          id="proj-tag"
          v-model="form.tag"
          type="text"
          required
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
          placeholder="E-COMMERCE"
        />
      </div>

      <div>
        <label for="proj-slug" class="block text-sm font-medium text-on-surface-variant mb-1.5">Slug *</label>
        <input
          id="proj-slug"
          v-model="form.slug"
          type="text"
          required
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface font-mono text-sm placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
          placeholder="mi-proyecto"
        />
      </div>

      <div>
        <label for="proj-url" class="block text-sm font-medium text-on-surface-variant mb-1.5">URL en vivo</label>
        <input
          id="proj-url"
          v-model="form.live_url"
          type="url"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
          placeholder="https://..."
        />
      </div>

      <div>
        <label for="proj-locale" class="block text-sm font-medium text-on-surface-variant mb-1.5">Idioma</label>
        <select
          id="proj-locale"
          v-model="form.locale"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
        >
          <option value="es">Español</option>
          <option value="en">English</option>
        </select>
      </div>

      <div>
        <label for="proj-status" class="block text-sm font-medium text-on-surface-variant mb-1.5">Estado</label>
        <select
          id="proj-status"
          v-model="form.status"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
        >
          <option value="draft">Borrador</option>
          <option value="published">Publicado</option>
        </select>
      </div>

      <div>
        <label for="proj-type" class="block text-sm font-medium text-on-surface-variant mb-1.5">Tipo</label>
        <select
          id="proj-type"
          v-model="form.is_saas"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
        >
          <option :value="true">SaaS (detalle en página)</option>
          <option :value="false">Web (modal con descripción)</option>
        </select>
      </div>

      <div>
        <label for="proj-order" class="block text-sm font-medium text-on-surface-variant mb-1.5">Orden</label>
        <input
          id="proj-order"
          v-model.number="form.sort_order"
          type="number"
          min="0"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
        />
      </div>

      <div class="sm:col-span-2">
        <label for="proj-desc" class="block text-sm font-medium text-on-surface-variant mb-1.5">Descripción</label>
        <textarea
          id="proj-desc"
          v-model="form.description"
          rows="3"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30 resize-y"
          placeholder="Descripción del proyecto (para modales no-SaaS)..."
        ></textarea>
      </div>

      <div class="sm:col-span-2">
        <label class="block text-sm font-medium text-on-surface-variant mb-1.5">Tecnologías</label>
        <div class="flex flex-wrap gap-2 mb-2">
          <span
            v-for="(tech, index) in form.tech"
            :key="index"
            class="inline-flex items-center gap-1 rounded-md border border-electric-blue/20 bg-electric-blue/10 px-2.5 py-0.5 text-xs font-medium text-electric-blue"
          >
            {{ tech }}
            <button type="button" @click="removeTech(index)" class="ml-0.5 hover:text-on-surface">&times;</button>
          </span>
        </div>
        <div class="flex gap-2">
          <input
            v-model="newTech"
            @keydown.enter.prevent="addTech"
            type="text"
            class="flex-1 rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2 text-sm text-on-surface placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
            placeholder="Agregar tecnología y presionar Enter"
          />
          <button type="button" @click="addTech" class="rounded-lg border border-outline-variant/40 px-3 py-2 text-sm text-on-surface-variant hover:bg-surface-container-high transition-colors">
            + Agregar
          </button>
        </div>
      </div>

      <div class="sm:col-span-2">
        <ImageUpload
          v-model="form.image"
          folder="projects"
          label="Imagen del proyecto"
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
        {{ saving ? 'Guardando...' : isEdit ? 'Actualizar proyecto' : 'Crear proyecto' }}
      </button>
      <a href="/admin/projects" class="rounded-lg border border-outline-variant/40 px-6 py-2.5 font-medium text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-on-surface">
        Cancelar
      </a>
    </div>
  </form>
</template>
