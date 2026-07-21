<script setup lang="ts">
import { ref, watch } from 'vue'
import { useToast } from '../../../composables/useToast'
import { getApiUrl } from '../../../services/api'

const props = defineProps<{
  modelValue: string
  folder?: string
  label?: string
}>()

const emit = defineEmits<{
  'update:modelValue': [value: string]
  uploaded: [data: { url: string; fileId: string }]
  deleted: []
}>()

const toast = useToast()

const preview = ref(props.modelValue)
const uploading = ref(false)
const dragover = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)

watch(() => props.modelValue, (val) => {
  preview.value = val
})

function openFilePicker() {
  fileInput.value?.click()
}

function onFileChange(e: Event) {
  const input = e.target as HTMLInputElement
  if (input.files?.length) {
    uploadFile(input.files[0])
    input.value = ''
  }
}

function onDrop(e: DragEvent) {
  dragover.value = false
  const file = e.dataTransfer?.files[0]
  if (file) uploadFile(file)
}

function onDragOver(e: DragEvent) {
  e.preventDefault()
  dragover.value = true
}

function onDragLeave() {
  dragover.value = false
}

async function uploadFile(file: File) {
  const allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml']
  if (!allowed.includes(file.type)) {
    toast.error('Formato no permitido. Usa JPG, PNG, WebP, GIF o SVG.')
    return
  }
  if (file.size > 10 * 1024 * 1024) {
    toast.error('El archivo excede 10MB.')
    return
  }

  uploading.value = true
  try {
    const fd = new FormData()
    fd.append('file', file)
    fd.append('folder', props.folder || 'blog')

    const res = await fetch(`${getApiUrl()}/imagekit/upload.php`, {
      method: 'POST',
      body: fd,
      credentials: 'include',
    })

    const data = await res.json()

    if (data.success && data.data?.url) {
      preview.value = data.data.url
      emit('update:modelValue', data.data.url)
      emit('uploaded', { url: data.data.url, fileId: data.data.fileId })
      toast.success('Imagen subida')
    } else {
      toast.error(data.message || 'Error al subir imagen')
    }
  } catch {
    toast.error('Error de conexión al subir imagen')
  } finally {
    uploading.value = false
  }
}

function removeImage() {
  preview.value = ''
  emit('update:modelValue', '')
  emit('deleted')
}
</script>

<template>
  <div class="image-upload">
    <label v-if="label" class="block text-sm font-medium text-on-surface-variant mb-1.5">
      {{ label }}
    </label>

    <div
      v-if="!preview"
      class="image-upload-dropzone"
      :class="{ 'is-dragover': dragover }"
      @click="openFilePicker"
      @drop.prevent="onDrop"
      @dragover="onDragOver"
      @dragleave="onDragLeave"
    >
      <input
        ref="fileInput"
        type="file"
        accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml"
        class="hidden"
        @change="onFileChange"
      />

      <div v-if="uploading" class="flex flex-col items-center gap-2">
        <svg class="animate-spin h-8 w-8 text-vue-green" viewBox="0 0 24 24" fill="none">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        <span class="text-sm text-on-surface-variant">Subiendo...</span>
      </div>

      <div v-else class="flex flex-col items-center gap-2">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-outline">
          <rect width="18" height="18" x="3" y="3" rx="2" ry="2" /><circle cx="9" cy="9" r="2" /><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" />
        </svg>
        <span class="text-sm text-on-surface-variant">
          <span class="text-vue-green font-medium">Click para subir</span> o arrastra una imagen
        </span>
        <span class="text-xs text-on-surface-variant/60">JPG, PNG, WebP, GIF, SVG — Max 10MB</span>
      </div>
    </div>

    <div v-else class="image-upload-preview">
      <img :src="preview" alt="Preview" class="image-upload-preview-img" />
      <div class="image-upload-preview-overlay">
        <button
          type="button"
          @click.stop="openFilePicker"
          class="image-upload-preview-btn"
          title="Cambiar imagen"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
        </button>
        <button
          type="button"
          @click.stop="removeImage"
          class="image-upload-preview-btn is-danger"
          title="Eliminar imagen"
        >
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
        </button>
      </div>
      <input
        ref="fileInput"
        type="file"
        accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml"
        class="hidden"
        @change="onFileChange"
      />
    </div>
  </div>
</template>

<style scoped>
.image-upload-dropzone {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 140px;
  border: 2px dashed #404944;
  border-radius: 0.5rem;
  background: #1e293b;
  cursor: pointer;
  transition: border-color 0.2s, background 0.2s;
  padding: 1.5rem;
}
.image-upload-dropzone:hover {
  border-color: #69dca4;
  background: rgba(105, 220, 164, 0.04);
}
.image-upload-dropzone.is-dragover {
  border-color: #69dca4;
  background: rgba(105, 220, 164, 0.08);
}

.image-upload-preview {
  position: relative;
  border-radius: 0.5rem;
  overflow: hidden;
  border: 1px solid #404944;
  background: #1e293b;
}
.image-upload-preview-img {
  width: 100%;
  max-height: 240px;
  object-fit: cover;
  display: block;
}
.image-upload-preview-overlay {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  background: rgba(0, 0, 0, 0.5);
  opacity: 0;
  transition: opacity 0.2s;
}
.image-upload-preview:hover .image-upload-preview-overlay {
  opacity: 1;
}
.image-upload-preview-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 0.375rem;
  border: none;
  background: rgba(255, 255, 255, 0.15);
  color: #fff;
  cursor: pointer;
  transition: background 0.15s;
  backdrop-filter: blur(4px);
}
.image-upload-preview-btn:hover {
  background: rgba(255, 255, 255, 0.25);
}
.image-upload-preview-btn.is-danger:hover {
  background: rgba(220, 38, 38, 0.8);
}
</style>
