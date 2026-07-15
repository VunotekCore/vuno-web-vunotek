// @ts-check
import { defineConfig } from 'astro/config'
import tailwindcss from '@tailwindcss/vite'
import sitemap from '@astrojs/sitemap'
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
    inlineStylesheets: 'always'
  },
  prefetch: {
    prefetchAll: true,
    defaultStrategy: 'hover'
  },
  integrations: [
    sitemap({
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
          for (const file of ['sitemap-index.xml', 'sitemap-0.xml']) {
            const path = join(dir.pathname, file)
            const content = await readFile(path, 'utf-8')
            const formatted = content.replace(/>\s*</g, '>\n<')
            await writeFile(path, formatted, 'utf-8')
          }
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
