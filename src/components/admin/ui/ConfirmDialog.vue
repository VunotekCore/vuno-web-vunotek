<script setup lang="ts">
import { ref, nextTick, watch } from 'vue'
import VunotekIcon from './VunotekIcon.vue'

const visible = ref(false)
const message = ref('')
let resolvePromise: ((value: boolean) => void) | null = null
const dialogRef = ref<HTMLElement | null>(null)

function show(msg: string): Promise<boolean> {
  message.value = msg
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
}

function cancel() {
  visible.value = false
  resolvePromise?.(false)
  resolvePromise = null
}

function onKeydown(e: KeyboardEvent) {
  if (e.key === 'Escape') cancel()
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
          aria-label="Confirm action"
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
