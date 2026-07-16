<script setup lang="ts">
import { useToast } from '../../composables/useToast'

const toast = useToast()

function icon(type: string): string {
  if (type === 'success') return 'check_circle'
  if (type === 'error') return 'error'
  return 'info'
}

function colorClass(type: string): string {
  if (type === 'success') return 'bg-secondary/15 text-secondary'
  if (type === 'error') return 'bg-error-container/20 text-error'
  return 'bg-primary/15 text-primary'
}
</script>

<template>
  <div class="fixed top-4 right-4 z-[9999] flex flex-col gap-2 pointer-events-none">
    <TransitionGroup
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="translate-x-8 opacity-0"
      enter-to-class="translate-x-0 opacity-100"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="translate-x-0 opacity-100"
      leave-to-class="translate-x-8 opacity-0"
    >
      <div
        v-for="t in toast.toasts.value"
        :key="t.id"
        :class="['pointer-events-auto flex items-center gap-3 rounded-xl border border-outline-variant/20 px-4 py-3 text-sm font-medium shadow-lg backdrop-blur-sm', colorClass(t.type)]"
      >
        <span class="material-symbols-rounded text-lg">{{ icon(t.type) }}</span>
        <span>{{ t.message }}</span>
        <button
          @click="toast.removeToast(t.id)"
          class="ml-2 rounded p-0.5 opacity-60 hover:opacity-100 transition-opacity"
        >
          <span class="material-symbols-rounded text-base">close</span>
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>
