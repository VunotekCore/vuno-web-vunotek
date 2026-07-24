<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { blogService } from '../../services/blogService'
import { categoryService } from '../../services/categoryService'

const published = ref<number | null>(null)
const drafts = ref<number | null>(null)
const categories = ref<number | null>(null)
const loading = ref(true)

onMounted(async () => {
  try {
    const [blogRes, catRes, draftsRes] = await Promise.all([
      blogService.list({ status: 'published' }),
      categoryService.list(),
      blogService.list({ status: 'draft' }),
    ])

    if (blogRes.data.success) {
      published.value = blogRes.data.data?.total ?? 0
    }

    if (draftsRes.data.success) {
      drafts.value = draftsRes.data.data?.total ?? 0
    }

    if (catRes.data.success) {
      categories.value = Array.isArray(catRes.data.data) ? catRes.data.data.length : 0
    }
  } catch {
    // Silently fail — stats are non-critical
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
    <div class="rounded-xl border border-outline-variant/20 bg-surface-container p-6">
      <p class="text-sm text-on-surface-variant">Posts publicados</p>
      <p class="mt-2 text-3xl font-bold text-on-surface">
        <template v-if="loading">
          <span class="inline-block h-8 w-12 animate-pulse rounded bg-surface-container-high"></span>
        </template>
        <template v-else>{{ published ?? '—' }}</template>
      </p>
    </div>
    <div class="rounded-xl border border-outline-variant/20 bg-surface-container p-6">
      <p class="text-sm text-on-surface-variant">Borradores</p>
      <p class="mt-2 text-3xl font-bold text-on-surface">
        <template v-if="loading">
          <span class="inline-block h-8 w-12 animate-pulse rounded bg-surface-container-high"></span>
        </template>
        <template v-else>{{ drafts ?? '—' }}</template>
      </p>
    </div>
    <div class="rounded-xl border border-outline-variant/20 bg-surface-container p-6">
      <p class="text-sm text-on-surface-variant">Categorías</p>
      <p class="mt-2 text-3xl font-bold text-on-surface">
        <template v-if="loading">
          <span class="inline-block h-8 w-12 animate-pulse rounded bg-surface-container-high"></span>
        </template>
        <template v-else>{{ categories ?? '—' }}</template>
      </p>
    </div>
    <div class="rounded-xl border border-outline-variant/20 bg-surface-container p-6">
      <p class="text-sm text-on-surface-variant">Última sesión</p>
      <p class="mt-2 text-3xl font-bold text-on-surface">—</p>
    </div>
  </div>
</template>
