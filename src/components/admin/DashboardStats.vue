<script setup lang="ts">
import { ref, onMounted } from 'vue'

const API_URL = import.meta.env.PUBLIC_API_URL || 'https://api.vunotek.com'

const published = ref<number | null>(null)
const drafts = ref<number | null>(null)
const categories = ref<number | null>(null)
const loading = ref(true)

onMounted(async () => {
  const token = localStorage.getItem('admin_token')
  if (!token) return

  const headers = { Authorization: `Bearer ${token}` }

  try {
    const [blogRes, catRes] = await Promise.all([
      fetch(`${API_URL}/blog/list.php?status=published`, { headers }),
      fetch(`${API_URL}/categories/list.php`, { headers }),
    ])

    const blogData = await blogRes.json()
    const catData = await catRes.json()

    if (blogData.success) {
      published.value = blogData.data?.total ?? 0
    }

    const draftsRes = await fetch(`${API_URL}/blog/list.php?status=draft`, { headers })
    const draftsData = await draftsRes.json()
    if (draftsData.success) {
      drafts.value = draftsData.data?.total ?? 0
    }

    if (catData.success) {
      categories.value = Array.isArray(catData.data) ? catData.data.length : 0
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
