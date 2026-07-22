<script setup lang="ts">
import { ref, computed, onMounted, nextTick } from 'vue'
import { ArrowLeft } from '@lucide/vue'
import { blogService } from '../services/blogService'
import { IK_BASE } from '../utils/imagekit'
import DOMPurify from 'dompurify'

const props = defineProps<{
  locale: string
  backUrl: string
  backLabel: string
  relatedTitle: string
  shellRendered?: boolean
  initialPost?: {
    id: number
    title: string
    slug: string
    excerpt: string
    content: string
    category_name: string
    category_slug: string
    author: string
    image: string | null
    meta_title: string | null
    og_image: string | null
    created_at: string
  } | null
}>()

interface Post {
  id: number
  title: string
  slug: string
  excerpt: string
  content: string
  category_name: string
  category_slug: string
  author: string
  image: string | null
  meta_title: string | null
  og_image: string | null
  created_at: string
}

interface RelatedPost {
  id: number
  title: string
  slug: string
  excerpt: string
  category_name: string
  image: string | null
  created_at: string
}

const post = ref<Post | null>(props.initialPost as Post | null)
const related = ref<RelatedPost[]>([])
const loading = ref(!props.initialPost)
const notFound = ref(false)

const prefix = props.locale === 'en' ? '/en' : ''

const safeContent = ref('')

function sanitizeContent(html: string) {
  if (typeof window !== 'undefined') {
    safeContent.value = DOMPurify.sanitize(html)
  } else {
    safeContent.value = html
  }
}

const imageSrcset = computed(() => {
  const url = post.value?.image
  if (!url || !url.startsWith(IK_BASE)) return null
  const path = url.slice(IK_BASE.length + 1)
  return [400, 800, 1200].map(w => `${IK_BASE}/tr:w-${w}/${path} ${w}w`).join(', ')
})

function formatDate(dateStr: string): string {
  return new Date(dateStr).toISOString().split('T')[0]
}

function updateMeta(postData: Post) {
  const title = postData.meta_title || `${postData.title} | Vunotek`
  document.title = title

  const setMeta = (name: string, content: string) => {
    let el = document.querySelector(`meta[name="${name}"], meta[property="${name}"]`)
    if (!el) {
      el = document.createElement('meta')
      if (name.startsWith('og:')) el.setAttribute('property', name)
      else el.setAttribute('name', name)
      document.head.appendChild(el)
    }
    el.setAttribute('content', content)
  }

  setMeta('description', postData.excerpt)
  setMeta('og:title', title)
  setMeta('og:description', postData.excerpt)
  if (postData.og_image) setMeta('og:image', postData.og_image)
  setMeta('og:type', 'article')

  const siteUrl = 'https://vunotek.com'
  const jsonLd = {
    '@context': 'https://schema.org',
    '@type': 'BlogPosting',
    headline: postData.title,
    description: postData.excerpt,
    author: { '@type': 'Person', name: postData.author || 'Daniel Flores', url: `${siteUrl}/about` },
    publisher: {
      '@type': 'Organization',
      name: 'Vunotek',
      logo: { '@type': 'ImageObject', url: `${siteUrl}/logo.webp` },
    },
    datePublished: postData.created_at,
    dateModified: postData.created_at,
    image: postData.og_image || postData.image || undefined,
    url: `${siteUrl}${prefix}/blog/${postData.slug}/`,
  }
  const script = document.createElement('script')
  script.type = 'application/ld+json'
  script.textContent = JSON.stringify(jsonLd)
  document.head.appendChild(script)
}

async function fetchRelated(categorySlug: string, postId: number) {
  try {
    const { data: relData } = await blogService.list({
      locale: props.locale,
      status: 'published',
      category_slug: categorySlug,
    })
    if (relData.success && Array.isArray(relData.data?.posts)) {
      related.value = relData.data.posts
        .filter((p: RelatedPost) => p.id !== postId)
        .slice(0, 3)
    }
  } catch { /* silent */ }
}

onMounted(async () => {
  if (props.initialPost) {
    sanitizeContent(props.initialPost.content)
    updateMeta(props.initialPost)
    if (props.initialPost.category_slug) {
      fetchRelated(props.initialPost.category_slug, props.initialPost.id)
    }
    return
  }

  const slug = window.location.pathname.replace(/\/$/, '').split('/').pop() || ''
  if (!slug) {
    notFound.value = true
    loading.value = false
    return
  }

  try {
    const { data } = await blogService.getBySlug(slug)
    if (data.success && data.data) {
      post.value = data.data
      await nextTick()
      sanitizeContent(data.data.content)
      updateMeta(data.data)
      if (data.data.category_slug) {
        fetchRelated(data.data.category_slug, data.data.id)
      }
    } else {
      notFound.value = true
    }
  } catch {
    notFound.value = true
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div v-if="loading">
    <div class="animate-pulse mb-4">
      <div class="flex items-center gap-3 mb-4">
        <div class="h-5 w-20 bg-surface-container-highest rounded"></div>
        <div class="h-5 w-24 bg-surface-container-highest rounded"></div>
        <div class="h-5 w-28 bg-surface-container-highest rounded"></div>
      </div>
      <div class="h-10 w-3/4 bg-surface-container-highest rounded mb-4"></div>
      <div class="h-10 w-1/2 bg-surface-container-highest rounded mb-6"></div>
      <div class="aspect-[21/9] bg-surface-container-highest rounded-xl mb-8"></div>
    </div>
    <div class="max-w-3xl mx-auto animate-pulse space-y-4">
      <div class="h-5 w-full bg-surface-container-highest rounded"></div>
      <div class="h-5 w-5/6 bg-surface-container-highest rounded"></div>
      <div class="h-5 w-4/6 bg-surface-container-highest rounded"></div>
      <div class="h-32 w-full bg-surface-container-highest rounded mt-6"></div>
      <div class="h-5 w-full bg-surface-container-highest rounded"></div>
      <div class="h-5 w-3/4 bg-surface-container-highest rounded"></div>
    </div>
  </div>

  <div v-else-if="notFound" class="text-center py-20">
    <span class="material-symbols-outlined text-6xl text-outline/30 mb-4 block" aria-hidden="true">article</span>
    <h2 class="font-display-lg text-headline-lg text-on-surface mb-2">{{ locale === 'en' ? 'Article not found' : 'Articulo no encontrado' }}</h2>
    <p class="text-on-surface-variant mb-6">{{ locale === 'en' ? 'The article you are looking for does not exist or has been removed.' : 'El articulo que buscas no existe o ha sido eliminado.' }}</p>
    <a :href="backUrl" class="inline-flex items-center gap-2 text-electric-blue hover:underline">
      <ArrowLeft :size="16" />
      {{ backLabel }}
    </a>
  </div>

  <template v-else-if="post">
    <div v-if="!shellRendered" class="flex flex-wrap items-center gap-3 mb-md">
      <span class="font-label-mono text-[11px] text-electric-blue bg-electric-blue/10 px-3 py-1 rounded">
        {{ post.category_name }}
      </span>
      <time class="font-label-mono text-[11px] text-slate-text" :datetime="formatDate(post.created_at)">
        {{ formatDate(post.created_at) }}
      </time>
      <span class="font-label-mono text-[11px] text-slate-text/60">
        — {{ post.author || 'Daniel Flores' }}
      </span>
    </div>

    <h1 v-if="!shellRendered" class="font-display-lg text-headline-lg-mobile md:text-headline-lg text-on-surface max-w-4xl">
      {{ post.title }}
    </h1>

    <div v-if="post.image" class="aspect-video max-w-4xl mx-auto glass-panel rounded-xl mt-xl overflow-hidden">
      <img
        :src="post.image"
        :srcset="imageSrcset"
        sizes="(max-width: 768px) 100vw, 1200px"
        :alt="post.title"
        width="1200"
        height="675"
        class="w-full h-full object-cover"
        loading="eager"
        fetchpriority="high"
      />
    </div>

    <div class="max-w-3xl mx-auto mt-xl">
      <div class="blog-content" v-html="safeContent"></div>
    </div>

    <div v-if="related.length > 0" class="mt-xl">
      <h2 class="font-display-lg text-headline-lg text-on-surface mb-lg">
        {{ relatedTitle }}
      </h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-md">
        <article v-for="rp in related" :key="rp.id" class="group cursor-pointer">
          <a :href="`${prefix}/blog/${rp.slug}`" class="block">
            <div class="aspect-[16/10] glass-panel rounded-xl mb-md flex items-center justify-center overflow-hidden transition-all duration-300 group-hover:border-electric-blue/50">
              <img
                v-if="rp.image"
                :src="rp.image"
                :alt="rp.title"
                width="640"
                height="400"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                loading="lazy"
              />
              <span v-else class="material-symbols-outlined text-6xl text-outline/30">description</span>
            </div>
            <div class="flex items-center gap-2 mb-sm">
              <span class="font-label-mono text-[11px] text-electric-blue bg-electric-blue/10 px-2 py-0.5 rounded">
                {{ rp.category_name }}
              </span>
              <span class="font-label-mono text-[11px] text-slate-text">
                {{ formatDate(rp.created_at) }}
              </span>
            </div>
            <h3 class="font-headline-md text-headline-md max-sm:text-headline-sm text-on-surface group-hover:text-electric-blue transition-colors mb-xs">
              {{ rp.title }}
            </h3>
            <p class="font-body-md text-body-md text-slate-text line-clamp-2">{{ rp.excerpt }}</p>
          </a>
        </article>
      </div>
    </div>
  </template>
</template>
