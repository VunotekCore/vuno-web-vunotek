<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '../services/api'

interface Question {
  question: string
  options: string[]
  correct: number
}

interface Position {
  id: number
  title: string
  slug: string
  short_description: string
  full_description: string
  requirements: string
  responsibilities: string
  location: string
  type: string
  category: string | null
  questions: Question[] | null
  passing_score: number
  locale: string
}

const props = defineProps<{
  dict: Record<string, unknown>
  locale?: string
}>()

const t = (path: string, vars?: Record<string, unknown>): string => {
  const keys = path.split('.')
  let val: unknown = props.dict
  for (const k of keys) {
    if (val && typeof val === 'object') val = (val as Record<string, unknown>)[k]
    else return path
  }
  const str = String(val ?? path)
  if (vars) {
    return str.replace(/\{\{(\w+)\}\}/g, (_, k) => String(vars[k] ?? ''))
  }
  return str
}

const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
const phoneRegex = /^\+?[\d\s\-()]{7,20}$/

const visible = ref(false)
const loading = ref(false)
const step = ref<'form' | 'quiz' | 'result'>('form')
const saving = ref(false)
const error = ref('')
const score = ref(0)
const passed = ref(false)

const position = ref<Position | null>(null)

const form = ref({ name: '', email: '', phone: '' })
const cvFile = ref<File | null>(null)
const cvError = ref('')
const answers = ref<number[]>([])

const fieldErrors = ref({ name: '', email: '', phone: '' })

function reset() {
  step.value = 'form'
  error.value = ''
  fieldErrors.value = { name: '', email: '', phone: '' }
  form.value = { name: '', email: '', phone: '' }
  cvFile.value = null
  cvError.value = ''
  answers.value = []
  score.value = 0
  passed.value = false
  saving.value = false
  loading.value = false
}

async function open(positionId: number) {
  reset()
  loading.value = true
  visible.value = true
  document.body.style.overflow = 'hidden'

  try {
    const { data } = await api.get('/careers/get.php', { params: { id: positionId, public: '1' } })

    if (data.success && data.data) {
      position.value = data.data
      const qs = data.data.questions
      if (Array.isArray(qs)) {
        answers.value = new Array(qs.length).fill(-1)
      }
    } else {
      error.value = t('careers.apply_modal.error')
    }
  } catch {
    error.value = t('careers.apply_modal.error')
  } finally {
    loading.value = false
  }
}

function close() {
  visible.value = false
  document.body.style.overflow = ''
}

function validateForm(): boolean {
  fieldErrors.value = { name: '', email: '', phone: '' }
  let valid = true

  if (!form.value.name.trim()) {
    fieldErrors.value.name = t('careers.apply_modal.required')
    valid = false
  }

  if (!form.value.email.trim()) {
    fieldErrors.value.email = t('careers.apply_modal.required')
    valid = false
  } else if (!emailRegex.test(form.value.email.trim())) {
    fieldErrors.value.email = t('careers.apply_modal.email_invalid')
    valid = false
  }

  if (form.value.phone.trim() && !phoneRegex.test(form.value.phone.trim())) {
    fieldErrors.value.phone = t('careers.apply_modal.phone_invalid')
    valid = false
  }

  if (!cvFile.value) {
    cvError.value = t('careers.apply_modal.required')
    valid = false
  } else {
    cvError.value = ''
  }

  if (!valid) error.value = ''
  return valid
}

function validateQuiz(): boolean {
  for (let i = 0; i < answers.value.length; i++) {
    if (answers.value[i] === -1) {
      error.value = t('careers.apply_modal.select_answer')
      return false
    }
  }
  return true
}

function handleFileChange(event: Event) {
  const target = event.target as HTMLInputElement
  if (target.files && target.files.length > 0) {
    const file = target.files[0]
    const validTypes = [
      'application/pdf',
      'application/msword',
      'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ]
    if (!validTypes.includes(file.type)) {
      cvError.value = 'Formato no válido. Usá PDF, DOC o DOCX.'
      cvFile.value = null
      return
    }
    if (file.size > 5 * 1024 * 1024) {
      cvError.value = t('careers.apply_modal.cv_size')
      cvFile.value = null
      return
    }
    cvError.value = ''
    cvFile.value = file
  }
}

function handleNextStep() {
  if (!validateForm()) return
  const qs = position.value?.questions
  if (qs && qs.length > 0) {
    step.value = 'quiz'
    error.value = ''
  } else {
    submitApplication()
  }
}

function calculateScore(): number {
  const qs = position.value?.questions
  if (!qs || qs.length === 0) return 100
  let correct = 0
  for (let i = 0; i < qs.length; i++) {
    if (answers.value[i] === qs[i].correct) correct++
  }
  return Math.round((correct / qs.length) * 100)
}

async function submitApplication() {
  if (!position.value) return
  saving.value = true
  error.value = ''

  const formData = new FormData()
  formData.append('position_id', String(position.value.id))
  formData.append('name', form.value.name.trim())
  formData.append('email', form.value.email.trim())
  formData.append('phone', form.value.phone.trim())

  if (cvFile.value) {
    formData.append('cv', cvFile.value)
  }

  const qs = position.value.questions
  if (qs && qs.length > 0) {
    formData.append('answers', JSON.stringify(answers.value))
  }

  try {
    const { data } = await api.post('/applications/apply.php', formData)

    if (data.success) {
      step.value = 'result'
      score.value = calculateScore()
      const passScore = position.value.passing_score ?? 70
      passed.value = !qs || qs.length === 0 || score.value >= passScore
    } else {
      error.value = data.message || t('careers.apply_modal.error')
    }
  } catch {
    error.value = t('careers.apply_modal.error')
  } finally {
    saving.value = false
  }
}

function handleSubmitQuiz() {
  if (!validateQuiz()) return
  submitApplication()
}

onMounted(() => {
  (window as any).__openApplyModal = open
})

defineExpose({ open })
</script>

<template>
  <div
    v-if="visible"
    class="fixed inset-0 z-[100] flex items-start justify-center overflow-y-auto py-10 px-4"
    @click.self="close"
  >
    <div class="fixed inset-0 bg-[#0a1022]/80 backdrop-blur-md"></div>
    <div class="relative z-10 w-full max-w-2xl rounded-2xl border border-outline-variant/20 bg-surface shadow-2xl">
      <!-- Header -->
      <div class="flex items-center justify-between border-b border-outline-variant/20 px-6 py-4">
        <div>
          <h2 class="font-display-lg text-headline-sm text-on-surface">
            {{ step === 'result' ? (passed ? t('careers.apply_modal.score_passed') : t('careers.apply_modal.score_failed')) : t('careers.apply_modal.title') }}
          </h2>
          <p v-if="position" class="text-sm text-on-surface-variant mt-0.5">{{ position.title }}</p>
        </div>
        <button
          @click="close"
          class="rounded-lg p-2 text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface transition-colors"
          aria-label="Cerrar"
        >
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Body -->
      <div class="p-6">
        <!-- Loading -->
        <div v-if="loading" class="flex items-center justify-center py-12">
          <svg class="h-8 w-8 animate-spin text-electric-blue" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
          </svg>
        </div>

        <template v-else>
          <!-- Step indicator -->
          <div v-if="step !== 'result'" class="flex items-center gap-2 mb-6">
            <span
              :class="[
                'flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold transition-colors',
                step === 'form' ? 'bg-electric-blue text-white' : 'bg-electric-blue/20 text-electric-blue',
              ]"
            >1</span>
            <span class="h-px flex-1" :class="step === 'quiz' ? 'bg-electric-blue' : 'bg-outline-variant/40'"></span>
            <span
              v-if="position?.questions?.length"
              :class="[
                'flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold transition-colors',
                step === 'quiz' ? 'bg-electric-blue text-white' : 'bg-outline-variant/20 text-on-surface-variant',
              ]"
            >2</span>
          </div>

          <!-- Error -->
          <div v-if="error" class="mb-4 rounded-lg bg-error/10 border border-error/30 px-4 py-3 text-sm text-error">
            {{ error }}
          </div>

          <!-- Step 1: Form -->
          <div v-if="step === 'form'" class="flex flex-col gap-5">
            <p class="text-sm text-on-surface-variant">{{ t('careers.apply_modal.subtitle') }}</p>

            <div>
              <label class="block text-sm font-medium text-on-surface-variant mb-1.5">{{ t('careers.apply_modal.personal_info') }}</label>
              <div class="grid gap-4 sm:grid-cols-2">
                <div>
                  <input
                    v-model="form.name"
                    type="text"
                    maxlength="100"
                    :placeholder="t('careers.apply_modal.name')"
                    class="w-full rounded-lg border px-4 py-2.5 text-sm text-on-surface placeholder:text-on-surface-variant/50 bg-surface-container transition-colors focus:border-electric-blue focus:outline-none focus:ring-2 focus:ring-electric-blue/30"
                    :class="fieldErrors.name ? 'border-error/60' : 'border-outline-variant/40'"
                  />
                  <p v-if="fieldErrors.name" class="mt-1 text-xs text-error">{{ fieldErrors.name }}</p>
                </div>
                <div>
                  <input
                    v-model="form.email"
                    type="email"
                    maxlength="255"
                    :placeholder="t('careers.apply_modal.email')"
                    class="w-full rounded-lg border px-4 py-2.5 text-sm text-on-surface placeholder:text-on-surface-variant/50 bg-surface-container transition-colors focus:border-electric-blue focus:outline-none focus:ring-2 focus:ring-electric-blue/30"
                    :class="fieldErrors.email ? 'border-error/60' : 'border-outline-variant/40'"
                  />
                  <p v-if="fieldErrors.email" class="mt-1 text-xs text-error">{{ fieldErrors.email }}</p>
                </div>
              </div>
              <div class="mt-3">
                <input
                  v-model="form.phone"
                  type="tel"
                  maxlength="20"
                  :placeholder="t('careers.apply_modal.phone')"
                  class="w-full rounded-lg border px-4 py-2.5 text-sm text-on-surface placeholder:text-on-surface-variant/50 bg-surface-container transition-colors focus:border-electric-blue focus:outline-none focus:ring-2 focus:ring-electric-blue/30"
                  :class="fieldErrors.phone ? 'border-error/60' : 'border-outline-variant/40'"
                />
                <p v-if="fieldErrors.phone" class="mt-1 text-xs text-error">{{ fieldErrors.phone }}</p>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-on-surface-variant mb-1.5">{{ t('careers.apply_modal.cv') }}</label>
              <input
                type="file"
                accept=".pdf,.doc,.docx"
                @change="handleFileChange"
                class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-2.5 text-sm text-on-surface file:mr-3 file:rounded-lg file:border-0 file:bg-electric-blue/15 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-electric-blue hover:file:bg-electric-blue/25 transition-colors focus:border-electric-blue focus:outline-none"
              />
              <p v-if="cvError" class="mt-1 text-xs text-error">{{ cvError }}</p>
              <p class="mt-1 text-xs text-on-surface-variant/60">{{ t('careers.apply_modal.cv_hint') }}</p>
            </div>

            <div class="flex justify-end pt-2">
              <button
                @click="handleNextStep"
                class="rounded-lg bg-electric-blue px-6 py-2.5 text-sm font-semibold text-white transition-all hover:bg-electric-blue/90 hover:shadow-lg hover:shadow-electric-blue/25"
              >
                {{ position?.questions?.length ? t('careers.apply_modal.next') : t('careers.apply_modal.submit') }}
              </button>
            </div>
          </div>

          <!-- Step 2: Quiz -->
          <div v-if="step === 'quiz'" class="flex flex-col gap-6">
            <p class="text-sm text-on-surface-variant">
              {{ t('careers.apply_modal.assessment_hint', { score: position?.passing_score ?? 70 }) }}
            </p>

            <div v-for="(q, i) in position?.questions" :key="i" class="rounded-xl border border-outline-variant/15 bg-surface-container-lowest p-5">
              <p class="text-sm font-medium text-on-surface mb-3">{{ i + 1 }}. {{ q.question }}</p>
              <div class="flex flex-col gap-2">
                <label
                  v-for="(opt, oi) in q.options"
                  :key="oi"
                  :class="[
                    'flex cursor-pointer items-center gap-3 rounded-lg border px-4 py-2.5 text-sm transition-colors',
                    answers[i] === oi
                      ? 'border-electric-blue bg-electric-blue/10 text-on-surface'
                      : 'border-outline-variant/30 bg-surface-container hover:border-outline-variant/60 text-on-surface-variant',
                  ]"
                >
                  <input type="radio" :name="'q-' + i" :value="oi" v-model.number="answers[i]" class="sr-only" />
                  <span
                    :class="[
                      'flex h-4 w-4 shrink-0 items-center justify-center rounded-full border transition-colors',
                      answers[i] === oi ? 'border-electric-blue bg-electric-blue' : 'border-outline-variant/50',
                    ]"
                  >
                    <span v-if="answers[i] === oi" class="h-2 w-2 rounded-full bg-white"></span>
                  </span>
                  {{ opt }}
                </label>
              </div>
            </div>

            <div class="flex justify-between pt-2">
              <button
                @click="step = 'form'"
                class="rounded-lg border border-outline-variant/40 px-5 py-2.5 text-sm font-medium text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-on-surface"
              >
                {{ t('careers.apply_modal.back') }}
              </button>
              <button
                @click="handleSubmitQuiz"
                :disabled="saving"
                class="rounded-lg bg-electric-blue px-6 py-2.5 text-sm font-semibold text-white transition-all hover:bg-electric-blue/90 hover:shadow-lg hover:shadow-electric-blue/25 disabled:opacity-50"
              >
                {{ saving ? t('careers.apply_modal.sending') : t('careers.apply_modal.submit') }}
              </button>
            </div>
          </div>

          <!-- Result -->
          <div v-if="step === 'result'" class="flex flex-col items-center gap-4 py-6 text-center">
            <div
              :class="[
                'flex h-16 w-16 items-center justify-center rounded-full text-3xl font-bold',
                passed ? 'bg-vue-green/20 text-vue-green' : 'bg-error/20 text-error',
              ]"
            >
              {{ passed ? '✓' : '✗' }}
            </div>
            <div>
              <p class="text-lg font-semibold text-on-surface">
                {{ passed ? t('careers.apply_modal.score_passed') : t('careers.apply_modal.score_failed') }}
              </p>
              <p v-if="position?.questions?.length" class="text-sm text-on-surface-variant mt-1">
                {{ score }}% — {{ t('careers.apply_modal.assessment') }}
              </p>
            </div>
            <p class="text-sm text-on-surface-variant mt-2">
              {{ passed ? t('careers.apply_modal.success') : t('careers.apply_modal.score_failed') }}
            </p>
            <button
              @click="close"
              class="mt-4 rounded-lg border border-outline-variant/40 px-6 py-2.5 text-sm font-medium text-on-surface-variant transition-colors hover:bg-surface-container-high hover:text-on-surface"
            >
              {{ t('common.close') }}
            </button>
          </div>
        </template>
      </div>
    </div>
  </div>
</template>
