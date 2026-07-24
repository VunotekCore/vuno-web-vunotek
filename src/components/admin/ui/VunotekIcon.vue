<script setup lang="ts">
import { computed } from 'vue'
import {
  Plus,
  Pencil,
  Trash2,
  X,
  AlertTriangle,
  Info,
  Lock,
  ArrowLeft,
  ExternalLink,
  LogOut,
  Menu,
  LayoutGrid,
  CheckCircle,
  CircleX,
  Tag,
  FileText,
  Loader2,
  Languages,
} from '@lucide/vue'

const props = defineProps<{
  icon: string
  size?: number
  class?: string
}>()

const iconMap: Record<string, any> = {
  add: Plus,
  edit: Pencil,
  delete: Trash2,
  close: X,
  warning: AlertTriangle,
  info: Info,
  lock: Lock,
  arrow_back: ArrowLeft,
  open_in_new: ExternalLink,
  logout: LogOut,
  menu: Menu,
  grid_view: LayoutGrid,
  check_circle: CheckCircle,
  error: CircleX,
  label: Tag,
  article: FileText,
  hourglass_empty: Loader2,
  translate: Languages,
}

const isLoading = computed(() => props.icon === 'hourglass_empty')
const iconComponent = computed(() => iconMap[props.icon] || null)
</script>

<template>
  <component
    v-if="iconComponent"
    :is="iconComponent"
    :size="size ?? 20"
    :class="[isLoading ? 'animate-spin' : '', props.class]"
  />
</template>
