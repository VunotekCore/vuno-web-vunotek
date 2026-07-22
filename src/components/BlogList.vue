<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { blogService } from '../services/blogService'

const props = defineProps<{
  locale: string
}>()

interface Post {
  id: number
  title: string
  slug: string
  excerpt: string
  category_name: string
  image: string | null
  created_at: string
}

const posts = ref<Post[]>([])
const loading = ref(true)
const error = ref('')

const prefix = props.locale === 'en' ? '/en' : ''

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString(props.locale === 'en' ? 'en-US' : 'es', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

onMounted(async () => {
  try {
    const { data } = await blogService.list({
      locale: props.locale,
      status: 'published',
    })
    if (data.success && Array.isArray(data.data?.posts)) {
      posts.value = data.data.posts
    }
  } catch {
    error.value = 'Error al cargar artículos'
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <!-- Skeleton -->
  <div v-if="loading" role="status" aria-live="polite" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-md">
    <div v-for="n in 6" :key="n" class="animate-pulse">
      <div class="aspect-[16/10] bg-surface-container-highest rounded-xl mb-4"></div>
      <div class="flex items-center gap-2 mb-2">
        <div class="h-4 w-16 bg-surface-container-highest rounded"></div>
        <div class="h-4 w-20 bg-surface-container-highest rounded"></div>
      </div>
      <div class="h-5 w-3/4 bg-surface-container-highest rounded mb-2"></div>
      <div class="h-4 w-full bg-surface-container-highest rounded mb-1"></div>
      <div class="h-4 w-2/3 bg-surface-container-highest rounded"></div>
    </div>
  </div>

  <!-- Error -->
  <div v-else-if="error" role="status" class="text-center py-12">
    <p class="text-on-surface-variant mb-4">{{ error }}</p>
    <button @click="loading = true; error = ''; $mounted()" class="text-electric-blue hover:underline text-sm">
      Reintentar
    </button>
  </div>

  <!-- Empty -->
  <div v-else-if="posts.length === 0" role="status" class="text-center py-12">
    <p class="text-on-surface-variant">No hay artículos publicados</p>
  </div>

  <!-- Posts grid -->
  <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-md">
    <article v-for="post in posts" :key="post.id" class="group cursor-pointer">
      <a :href="`${prefix}/blog/${post.slug}`" class="block">
        <div class="aspect-[16/10] glass-panel rounded-xl mb-md flex items-center justify-center overflow-hidden transition-all duration-300 group-hover:border-electric-blue/50">
          <img
            v-if="post.image"
            :src="post.image"
            :alt="post.title"
            width="640"
            height="400"
            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
            loading="lazy"
          />
          <span v-else class="material-symbols-outlined text-6xl text-outline/30">description</span>
        </div>
        <div class="flex items-center gap-2 mb-sm">
          <span class="font-label-mono text-[11px] text-electric-blue bg-electric-blue/10 px-2 py-0.5 rounded">
            {{ post.category_name }}
          </span>
          <span class="font-label-mono text-[11px] text-slate-text">
            {{ formatDate(post.created_at) }}
          </span>
        </div>
        <h3 class="font-headline-md text-headline-md max-sm:text-headline-sm text-on-surface group-hover:text-electric-blue transition-colors mb-xs">
          {{ post.title }}
        </h3>
        <p class="font-body-md text-body-md text-slate-text line-clamp-2">
          {{ post.excerpt }}
        </p>
      </a>
    </article>
  </div>
</template>
