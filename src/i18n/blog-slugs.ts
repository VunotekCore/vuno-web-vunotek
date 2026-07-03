import { blogSlugToEn, blogSlugToEs } from './blog-slugs-map.js';

export { blogSlugToEn, blogSlugToEs };

export function getAlternateSlug(slug: string, targetLocale: string): string | undefined {
  if (targetLocale === 'en') return blogSlugToEn[slug]
  if (targetLocale === 'es') return blogSlugToEs[slug]
  return undefined
}
