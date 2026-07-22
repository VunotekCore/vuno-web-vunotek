<script setup lang="ts">
import { useToast } from '../../../composables/useToast'
import VunotekIcon from './VunotekIcon.vue'

const toast = useToast()

function icon(type: string): string {
  if (type === 'success') return 'check_circle'
  if (type === 'error') return 'error'
  return 'info'
}

function variantClass(type: string): string {
  if (type === 'success') return 'admin-toast-success'
  if (type === 'error') return 'admin-toast-error'
  return 'admin-toast-info'
}
</script>

<template>
  <Teleport to="body">
    <div
      v-for="(t, i) in toast.toasts.value"
      :key="t.id"
      class="admin-toast"
      :class="variantClass(t.type)"
      :style="{ top: (16 + i * 76) + 'px' }"
      role="alert"
    >
      <VunotekIcon :icon="icon(t.type)" :size="22" class="admin-toast-icon" />
      <span class="admin-toast-text">{{ t.message }}</span>
      <button class="admin-toast-close" @click="toast.removeToast(t.id)" aria-label="Cerrar notificación">
        <VunotekIcon icon="close" :size="18" class="admin-toast-close-icon" />
      </button>
    </div>
  </Teleport>
</template>
