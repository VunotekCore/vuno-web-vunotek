<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useAuthStore } from '../../stores/auth'
import { careerService } from '../../services/careerService'
import { useToast } from '../../composables/useToast'
import VunotekIcon from './ui/VunotekIcon.vue'

const auth = useAuthStore()
const toast = useToast()
const isViewer = computed(() => auth.isViewer)

interface Question {
  question: string
  options: string[]
  correct: number
}

interface PositionData {
  id: number
  title: string
  slug: string
  short_description: string
  full_description: string
  requirements: string
  responsibilities: string
  location: string
  type: string
  category: string
  questions: Question[] | null
  passing_score: number
  locale: string
  status: string
  sort_order: number
}

const params = new URLSearchParams(window.location.search)
const positionId = params.get('id')

const isEdit = computed(() => !!positionId)

const loading = ref(true)
const saving = ref(false)
const error = ref('')
const success = ref('')

const form = ref({
  title: '',
  slug: '',
  short_description: '',
  full_description: '',
  requirements: '',
  responsibilities: '',
  location: 'Remote',
  type: 'remote',
  category: '',
  questions: [] as Question[],
  passing_score: 70,
  locale: 'es',
  status: 'draft',
  sort_order: 0,
})

const newQuestion = ref('')
const newOptions = ref(['', '', '', ''])
const editingQuestionIndex = ref<number | null>(null)
const showQuestionForm = ref(false)

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

function resetQuestionForm() {
  newQuestion.value = ''
  newOptions.value = ['', '', '', '']
  editingQuestionIndex.value = null
  showQuestionForm.value = false
}

function addQuestion() {
  if (!newQuestion.value.trim()) return
  const opts = newOptions.value.filter(o => o.trim())
  if (opts.length < 2) return

  const q: Question = {
    question: newQuestion.value.trim(),
    options: opts,
    correct: 0,
  }

  if (editingQuestionIndex.value !== null) {
    form.value.questions[editingQuestionIndex.value] = q
  } else {
    form.value.questions.push(q)
  }
  resetQuestionForm()
}

function editQuestion(index: number) {
  const q = form.value.questions[index]
  newQuestion.value = q.question
  newOptions.value = [...q.options, ...Array(4 - q.options.length).fill('')]
  editingQuestionIndex.value = index
  showQuestionForm.value = true
}

function removeQuestion(index: number) {
  form.value.questions.splice(index, 1)
}

function setCorrect(index: number, optIndex: number) {
  form.value.questions[index].correct = optIndex
}

async function fetchPosition() {
  if (!positionId) {
    loading.value = false
    return
  }

  try {
    const { data } = await careerService.get(Number(positionId))
    if (data.success && data.data) {
      const p = data.data as PositionData
      form.value = {
        title: p.title,
        slug: p.slug,
        short_description: p.short_description ?? '',
        full_description: p.full_description ?? '',
        requirements: p.requirements ?? '',
        responsibilities: p.responsibilities ?? '',
        location: p.location ?? 'Remote',
        type: p.type ?? 'remote',
        category: p.category ?? '',
        questions: Array.isArray(p.questions) ? p.questions : [],
        passing_score: p.passing_score ?? 70,
        locale: p.locale,
        status: p.status,
        sort_order: p.sort_order,
      }
    }
  } catch {
    error.value = 'Error al cargar la vacante'
  } finally {
    loading.value = false
  }
}

async function handleSubmit() {
  error.value = ''
  success.value = ''

  if (!form.value.title || !form.value.slug) {
    error.value = 'Título y slug son requeridos'
    return
  }

  saving.value = true
  try {
    const { data } = isEdit.value
      ? await careerService.update(Number(positionId), form.value)
      : await careerService.create(form.value)

    if (data.success) {
      toast.success(isEdit.value ? 'Vacante actualizada' : 'Vacante creada')
      if (!isEdit.value) {
        window.location.href = '/admin/careers'
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
  await fetchPosition()
})
</script>

<template>
  <div v-if="isViewer" class="rounded-xl border border-vue-green/30 bg-vue-green/10 p-8 text-center">
    <VunotekIcon icon="lock" :size="36" class="mb-2 block text-vue-green" />
    <p class="text-on-surface font-medium">Modo solo lectura</p>
    <p class="text-sm text-on-surface-variant mt-1">No tienes permisos para crear o editar vacantes.</p>
  </div>

  <div v-else-if="loading" class="rounded-xl border border-outline-variant/20 bg-surface-container p-8 text-center text-on-surface-variant">
    <VunotekIcon icon="hourglass_empty" :size="36" class="mb-2 block animate-pulse text-outline" />
    Cargando...
  </div>

  <form v-else @submit.prevent="handleSubmit" class="flex flex-col gap-5">
    <div class="grid gap-5 sm:grid-cols-2">
      <div class="sm:col-span-2">
        <label for="pos-title" class="block text-sm font-medium text-on-surface-variant mb-1.5">Título *</label>
        <input
          id="pos-title"
          v-model="form.title"
          @blur="!isEdit && autoSlug()"
          type="text"
          required
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
          placeholder="Senior Vue.js Developer"
        />
      </div>

      <div>
        <label for="pos-slug" class="block text-sm font-medium text-on-surface-variant mb-1.5">Slug *</label>
        <input
          id="pos-slug"
          v-model="form.slug"
          type="text"
          required
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface font-mono text-sm placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
          placeholder="senior-vue-developer"
        />
      </div>

      <div>
        <label for="pos-type" class="block text-sm font-medium text-on-surface-variant mb-1.5">Tipo</label>
        <select
          id="pos-type"
          v-model="form.type"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
        >
          <option value="remote">Remoto</option>
          <option value="hybrid">Híbrido</option>
          <option value="on-site">Presencial</option>
        </select>
      </div>

      <div>
        <label for="pos-location" class="block text-sm font-medium text-on-surface-variant mb-1.5">Ubicación</label>
        <input
          id="pos-location"
          v-model="form.location"
          type="text"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
          placeholder="Remote - Anywhere"
        />
      </div>

      <div>
        <label for="pos-category" class="block text-sm font-medium text-on-surface-variant mb-1.5">Categoría</label>
        <input
          id="pos-category"
          v-model="form.category"
          type="text"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
          placeholder="Frontend, Backend, Full Stack..."
        />
      </div>

      <div>
        <label for="pos-locale" class="block text-sm font-medium text-on-surface-variant mb-1.5">Idioma</label>
        <select
          id="pos-locale"
          v-model="form.locale"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
        >
          <option value="es">Español</option>
          <option value="en">English</option>
        </select>
      </div>

      <div>
        <label for="pos-status" class="block text-sm font-medium text-on-surface-variant mb-1.5">Estado</label>
        <select
          id="pos-status"
          v-model="form.status"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
        >
          <option value="draft">Borrador</option>
          <option value="published">Publicado</option>
        </select>
      </div>

      <div>
        <label for="pos-sort" class="block text-sm font-medium text-on-surface-variant mb-1.5">Orden</label>
        <input
          id="pos-sort"
          v-model.number="form.sort_order"
          type="number"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
        />
      </div>

      <div>
        <label for="pos-passing" class="block text-sm font-medium text-on-surface-variant mb-1.5">Score mínimo %</label>
        <input
          id="pos-passing"
          v-model.number="form.passing_score"
          type="number"
          min="0"
          max="100"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
        />
      </div>

      <div class="sm:col-span-2">
        <label for="pos-short-desc" class="block text-sm font-medium text-on-surface-variant mb-1.5">Descripción corta</label>
        <textarea
          id="pos-short-desc"
          v-model="form.short_description"
          rows="2"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30 resize-y"
          placeholder="Breve descripción de la vacante..."
        ></textarea>
      </div>

      <div class="sm:col-span-2">
        <label for="pos-full-desc" class="block text-sm font-medium text-on-surface-variant mb-1.5">Descripción completa</label>
        <textarea
          id="pos-full-desc"
          v-model="form.full_description"
          rows="4"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30 resize-y"
          placeholder="Descripción detallada del puesto..."
        ></textarea>
      </div>

      <div class="sm:col-span-2">
        <label for="pos-requirements" class="block text-sm font-medium text-on-surface-variant mb-1.5">Requisitos</label>
        <textarea
          id="pos-requirements"
          v-model="form.requirements"
          rows="4"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30 resize-y"
          placeholder="- Experiencia en Vue.js 3&#10;- TypeScript avanzado&#10;- ..."
        ></textarea>
      </div>

      <div class="sm:col-span-2">
        <label for="pos-responsibilities" class="block text-sm font-medium text-on-surface-variant mb-1.5">Responsabilidades</label>
        <textarea
          id="pos-responsibilities"
          v-model="form.responsibilities"
          rows="4"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-on-surface placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30 resize-y"
          placeholder="- Desarrollar componentes Vue.js&#10;- ..."
        ></textarea>
      </div>
    </div>

    <div class="rounded-xl border border-outline-variant/20 bg-surface-container p-5">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-sm font-semibold text-on-surface">Preguntas de evaluación ({{ form.questions.length }})</h3>
        <button
          v-if="!showQuestionForm"
          type="button"
          @click="showQuestionForm = true"
          class="rounded-lg bg-electric-blue px-3 py-1.5 text-xs font-semibold text-white transition-colors hover:bg-electric-blue/90"
        >
          + Agregar pregunta
        </button>
      </div>

      <div v-if="showQuestionForm" class="mb-4 rounded-lg border border-electric-blue/30 bg-electric-blue/5 p-4">
        <div class="mb-3">
          <label class="block text-xs font-medium text-on-surface-variant mb-1">Pregunta</label>
          <input
            v-model="newQuestion"
            type="text"
            class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-3 py-2 text-sm text-on-surface focus:border-electric-blue focus:outline-none"
            placeholder="¿Qué es...?"
          />
        </div>
        <div class="mb-3">
          <label class="block text-xs font-medium text-on-surface-variant mb-1">Opciones (mínimo 2)</label>
          <div class="flex flex-col gap-2">
            <input
              v-for="(opt, i) in newOptions"
              :key="i"
              v-model="newOptions[i]"
              type="text"
              class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-3 py-2 text-sm text-on-surface placeholder:text-on-surface-variant/50 focus:border-electric-blue focus:outline-none"
              :placeholder="`Opción ${i + 1}`"
            />
          </div>
        </div>
        <div class="flex gap-2">
          <button
            type="button"
            @click="addQuestion"
            class="rounded-lg bg-vue-green px-4 py-2 text-xs font-semibold text-on-secondary transition-colors hover:bg-vue-green/90"
          >
            {{ editingQuestionIndex !== null ? 'Actualizar' : 'Agregar' }}
          </button>
          <button
            type="button"
            @click="resetQuestionForm"
            class="rounded-lg border border-outline-variant/40 px-4 py-2 text-xs font-medium text-on-surface-variant transition-colors hover:bg-surface-container-high"
          >
            Cancelar
          </button>
        </div>
      </div>

      <div v-if="form.questions.length === 0" class="text-sm text-on-surface-variant text-center py-4">
        No hay preguntas de evaluación. Los postulantes no tendrán evaluación.
      </div>

      <div v-else class="flex flex-col gap-3">
        <div
          v-for="(q, i) in form.questions"
          :key="i"
          class="rounded-lg border border-outline-variant/20 bg-surface-container-high p-4"
        >
          <div class="flex items-start justify-between mb-2">
            <span class="text-sm font-medium text-on-surface">{{ i + 1 }}. {{ q.question }}</span>
            <div class="flex gap-1">
              <button type="button" @click="editQuestion(i)" class="rounded p-1 text-on-surface-variant hover:text-on-surface hover:bg-surface-container-highest transition-colors">
                <VunotekIcon icon="edit" :size="16" />
              </button>
              <button type="button" @click="removeQuestion(i)" class="rounded p-1 text-on-surface-variant hover:text-error hover:bg-error-container/20 transition-colors">
                <VunotekIcon icon="delete" :size="16" />
              </button>
            </div>
          </div>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="(opt, oi) in q.options"
              :key="oi"
              type="button"
              @click="setCorrect(i, oi)"
              class="rounded-lg px-2.5 py-1 text-xs transition-colors"
              :class="q.correct === oi
                ? 'bg-vue-green/20 text-vue-green border border-vue-green/40'
                : 'bg-surface-container text-on-surface-variant border border-outline-variant/30'"
            >
              {{ opt }}
              <span v-if="q.correct === oi" class="ml-1">✓</span>
            </button>
          </div>
        </div>
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
        {{ saving ? 'Guardando...' : isEdit ? 'Actualizar vacante' : 'Crear vacante' }}
      </button>
      <a href="/admin/careers" class="rounded-lg border border-outline-variant/40 px-6 py-2.5 font-medium text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-on-surface">
        Cancelar
      </a>
    </div>
  </form>
</template>
