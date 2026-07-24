// @ts-check
import { defineConfig } from 'astro/config'
import tailwindcss from '@tailwindcss/vite'
import sitemap from '@astrojs/sitemap'
import vue from '@astrojs/vue'
import { blogSlugToEn, blogSlugToEs } from './src/i18n/blog-slugs-map.js'
import { readFile, writeFile } from 'node:fs/promises'
import { join } from 'node:path'

const siteUrl = 'https://vunotek.com'

export default defineConfig({
  site: siteUrl,
  compressHTML: true,
  scopedStyleStrategy: 'class',
  build: {
    format: 'directory',
    assets: '_astro',
    inlineStylesheets: 'auto'
  },
  prefetch: {
    prefetchAll: false,
    defaultStrategy: 'hover'
  },
  integrations: [
    vue({
      appEntrypoint: '/src/plugins/vue-entrypoint.ts',
    }),
    sitemap({
      filter: (page) => !page.includes('/admin'),
      i18n: {
        defaultLocale: 'es',
        locales: {
          es: 'es-ES',
          en: 'en-US'
        }
      },
      serialize(item) {
        const url = item.url.replace(/\/$/, '')
        const isEnUrl = url.includes('/en/')
        const blogMatch = url.match(/\/blog\/(.+)$/)
        if (blogMatch) {
          const slug = blogMatch[1]
          const esSlug = blogSlugToEs[slug] ?? slug
          const enSlug = blogSlugToEn[slug] ?? slug
          const esUrl = `${siteUrl}/blog/${esSlug}/`
          const enUrl = `${siteUrl}/en/blog/${enSlug}/`
          item.links = [
            { url: esUrl, lang: 'es-ES' },
            { url: enUrl, lang: 'en-US' },
            { url: esUrl, lang: 'x-default' }
          ]
        } else if (!isEnUrl && item.links) {
          const esLink = item.links.find(l => l.lang === 'es-ES')
          if (esLink && !item.links.some(l => l.lang === 'x-default')) {
            item.links.push({ url: esLink.url, lang: 'x-default' })
          }
        }
        return item
      }
    }),
    {
      name: 'format-sitemap',
      hooks: {
        'astro:build:done': async ({ dir }) => {
          const sitemapPath = join(dir.pathname, 'sitemap-0.xml')
          let content = await readFile(sitemapPath, 'utf-8')

          // Fetch blog posts from API and add to sitemap
          try {
            const apiBase = process.env.PUBLIC_API_URL || 'http://localhost:8000'
            const res = await fetch(`${apiBase}/blog/list.php?status=published`)
            const data = await res.json()
            if (data.success && Array.isArray(data.data?.posts)) {
              const blogUrls = data.data.posts.map((/** @type {{ slug: string, updated_at?: string, created_at: string }} */ post) => {
                const lastmod = post.updated_at || post.created_at
                return `  <url>
    <loc>${siteUrl}/blog/${post.slug}/</loc>
    <lastmod>${new Date(lastmod).toISOString().split('T')[0]}</lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.7</priority>
    <xhtml:link rel="alternate" hreflang="es-ES" href="${siteUrl}/blog/${post.slug}/" />
    <xhtml:link rel="alternate" hreflang="en-US" href="${siteUrl}/en/blog/${post.slug}/" />
    <xhtml:link rel="alternate" hreflang="x-default" href="${siteUrl}/blog/${post.slug}/" />
  </url>`
              })
              // Insert before closing </urlset>
              content = content.replace('</urlset>', blogUrls.join('\n') + '\n</urlset>')
            }
          } catch {
            // API not available, continue without blog URLs
          }

          // Format XML
          content = content.replace(/>\s*</g, '>\n<')
          await writeFile(sitemapPath, content, 'utf-8')

          // Format sitemap-index
          const indexPath = join(dir.pathname, 'sitemap-index.xml')
          const indexContent = await readFile(indexPath, 'utf-8')
          await writeFile(indexPath, indexContent.replace(/>\s*</g, '>\n<'), 'utf-8')
        }
      }
    }
  ],
  vite: {
    plugins: [tailwindcss()],
    build: {
      cssMinify: 'lightningcss',
      assetsInlineLimit: 4096
    }
  },
  i18n: {
    defaultLocale: 'es',
    locales: ['es', 'en'],
    routing: {
      prefixDefaultLocale: false
    }
  }
})
