// @ts-check
import { defineConfig } from 'astro/config';
import tailwindcss from '@tailwindcss/vite';
import sitemap from '@astrojs/sitemap';
import { blogSlugToEn, blogSlugToEs } from './src/i18n/blog-slugs-map.js';

const siteUrl = 'https://vunotek.com';

export default defineConfig({
  site: siteUrl,
  compressHTML: true,
  scopedStyleStrategy: 'class',
  build: {
    format: 'directory',
    assets: '_astro',
    inlineStylesheets: 'always',
  },
  prefetch: {
    prefetchAll: true,
    defaultStrategy: 'hover',
  },
  integrations: [
    sitemap({
      i18n: {
        defaultLocale: 'es',
        locales: {
          es: 'es-ES',
          en: 'en-US',
        },
      },
      serialize(item) {
        const url = item.url.replace(/\/$/, '');
        const blogMatch = url.match(/\/blog\/(.+)$/);
        if (blogMatch) {
          const slug = blogMatch[1];
          const esSlug = blogSlugToEs[slug] ?? slug;
          const enSlug = blogSlugToEn[slug] ?? slug;
          const esUrl = `${siteUrl}/blog/${esSlug}/`;
          const enUrl = `${siteUrl}/en/blog/${enSlug}/`;
          item.links = [
            { url: esUrl, lang: 'es' },
            { url: enUrl, lang: 'en' },
            { url: enUrl, lang: 'x-default' },
          ];
        }
        return item;
      },
    }),
  ],
  vite: {
    plugins: [tailwindcss()],
    build: {
      cssMinify: 'lightningcss',
      assetsInlineLimit: 4096,
    },
  },
  i18n: {
    defaultLocale: 'es',
    locales: ['es', 'en'],
    routing: {
      prefixDefaultLocale: false
    }
  }
});
