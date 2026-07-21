<script setup lang="ts">
import { ref, nextTick } from 'vue'
import CategoryForm from './CategoryForm.vue'
import VunotekIcon from './ui/VunotekIcon.vue'

const visible = ref(false)
const editId = ref<number | null>(null)
const dialogRef = ref<HTMLElement | null>(null)

function open(id?: number) {
  editId.value = id ?? null
  visible.value = true
  nextTick(() => dialogRef.value?.focus())
}

function close() {
  visible.value = false
  editId.value = null
}

function onSaved() {
  close()
  emit('saved')
}

function onKeydown(e: KeyboardEvent) {
  if (e.key === 'Escape') close()
}

const emit = defineEmits<{
  saved: []
}>()

defineExpose({ open })
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-all duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="visible"
        class="fixed inset-0 z-[10000] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4"
        @click.self="close"
        @keydown="onKeydown"
      >
        <Transition
          enter-active-class="transition-all duration-200 ease-out"
          enter-from-class="scale-95 opacity-0"
          enter-to-class="scale-100 opacity-100"
          leave-active-class="transition-all duration-150 ease-in"
          leave-from-class="scale-100 opacity-100"
          leave-to-class="scale-95 opacity-0"
        >
          <div
            v-if="visible"
            ref="dialogRef"
            role="dialog"
            aria-modal="true"
            :aria-label="editId ? 'Editar categoría' : 'Nueva categoría'"
            tabindex="-1"
            class="flex w-full max-w-2xl max-h-[90vh] flex-col rounded-2xl border border-outline-variant/20 bg-[#111d2e] shadow-2xl"
          >
            <div class="flex items-center justify-between border-b border-[#1e293b] px-4 sm:px-6 py-3 sm:py-4 shrink-0">
              <h2 class="text-lg font-semibold text-[#dae2fd]">
                {{ editId ? 'Editar categoría' : 'Nueva categoría' }}
              </h2>
              <button
                @click="close"
                class="rounded-lg p-1.5 text-[#94a3b8] transition-colors hover:bg-white/[0.06] hover:text-[#dae2fd]"
                aria-label="Cerrar"
              >
                <VunotekIcon icon="close" :size="20" />
              </button>
            </div>
            <div class="flex-1 overflow-y-auto p-4 sm:p-6">
              <CategoryForm :category-id="editId" @saved="onSaved" @close="close" />
            </div>
          </div>
        </Transition>
      </div>
    </Transition>
  </Teleport>
</template>
