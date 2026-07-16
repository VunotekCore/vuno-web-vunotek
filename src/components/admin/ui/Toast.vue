<script setup lang="ts">
import { useToast } from '../../../composables/useToast'

const { toasts, removeToast } = useToast()

function icon(type: string) {
  if (type === 'success') return 'check_circle'
  if (type === 'error') return 'error'
  return 'info'
}

function colorClass(type: string) {
  if (type === 'success') return 'border-emerald-500/30 bg-emerald-500/10 text-emerald-400'
  if (type === 'error') return 'border-red-500/30 bg-red-500/10 text-red-400'
  return 'border-blue-500/30 bg-blue-500/10 text-blue-400'
}
</script>

<template>
  <div class="fixed top-4 right-4 z-[110] flex flex-col gap-2 pointer-events-none">
    <TransitionGroup
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="translate-x-full opacity-0"
      enter-to-class="translate-x-0 opacity-100"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="translate-x-0 opacity-100"
      leave-to-class="translate-x-full opacity-0"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :class="[
          'pointer-events-auto flex items-center gap-3 px-4 py-3 rounded-lg border shadow-lg backdrop-blur-sm min-w-[280px] max-w-sm',
          colorClass(toast.type),
        ]"
      >
        <span class="material-symbols-rounded text-xl shrink-0">{{ icon(toast.type) }}</span>
        <span class="text-sm flex-1">{{ toast.message }}</span>
        <button
          class="shrink-0 opacity-60 hover:opacity-100 transition-opacity"
          @click="removeToast(toast.id)"
        >
          <span class="material-symbols-rounded text-lg">close</span>
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>
