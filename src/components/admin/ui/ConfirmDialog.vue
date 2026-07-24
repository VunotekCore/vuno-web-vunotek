<script setup lang="ts">
import { ref, nextTick, onBeforeUnmount } from 'vue'
import VunotekIcon from './VunotekIcon.vue'

const visible = ref(false)
const message = ref('')
let resolvePromise: ((value: boolean) => void) | null = null
const dialogRef = ref<HTMLElement | null>(null)
let previousFocus: HTMLElement | null = null

function show(msg: string): Promise<boolean> {
  message.value = msg
  previousFocus = document.activeElement as HTMLElement
  visible.value = true
  nextTick(() => dialogRef.value?.focus())
  return new Promise((resolve) => {
    resolvePromise = resolve
  })
}

function confirm() {
  visible.value = false
  resolvePromise?.(true)
  resolvePromise = null
  restoreFocus()
}

function cancel() {
  visible.value = false
  resolvePromise?.(false)
  resolvePromise = null
  restoreFocus()
}

function restoreFocus() {
  nextTick(() => previousFocus?.focus())
}

function trapFocus(e: KeyboardEvent) {
  if (e.key !== 'Tab' || !dialogRef.value) return
  const focusable = dialogRef.value.querySelectorAll<HTMLElement>(
    'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
  )
  if (focusable.length === 0) return
  const first = focusable[0]
  const last = focusable[focusable.length - 1]
  if (e.shiftKey) {
    if (document.activeElement === first) {
      e.preventDefault()
      last.focus()
    }
  } else {
    if (document.activeElement === last) {
      e.preventDefault()
      first.focus()
    }
  }
}

function onKeydown(e: KeyboardEvent) {
  if (e.key === 'Escape') cancel()
  trapFocus(e)
}

defineExpose({ show })
</script>

<template>
  <Teleport to="body">
    <Transition
      enter-active-class="modal-fade-enter-active"
      enter-from-class="modal-fade-enter-from"
      leave-active-class="modal-fade-leave-active"
      leave-to-class="modal-fade-leave-to"
    >
      <div
        v-if="visible"
        class="admin-confirm-overlay"
        @click.self="cancel"
        @keydown="onKeydown"
      >
        <div
          ref="dialogRef"
          class="admin-confirm-card"
          role="dialog"
          aria-modal="true"
          aria-label="Confirmar acción"
          tabindex="-1"
        >
          <div class="admin-confirm-header">
            <VunotekIcon icon="warning" :size="28" class="admin-confirm-icon" />
            <h3 class="admin-confirm-title">Confirmar acción</h3>
          </div>
          <p class="admin-confirm-message">{{ message }}</p>
          <div class="admin-confirm-actions">
            <button @click="cancel" class="admin-btn admin-btn-secondary">Cancelar</button>
            <button @click="confirm" class="admin-btn admin-btn-danger">Eliminar</button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
