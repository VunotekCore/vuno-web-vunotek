import { SEO } from './metadata'
export { SEO }

export function getPageMeta(locale: string, pageKey: string) {
  const l = locale === 'en' ? 'en' : 'es'
  const page = (SEO.pages as any)[pageKey]
  if (!page) return null
  return page(l)
}

export function getBreadcrumbSchema(locale: string, currentPath: string, pageName?: string) {
  const l = locale === 'en' ? 'en' : 'es'
  const items = SEO.breadcrumbs[l]
  if (!items) return null

  const active = items.find((i) => i.path === currentPath)
  if (!active && !pageName) return null

  const isBlogPost = /\/blog\/[^/]+\/?$/.test(currentPath)

  let list
  if (isBlogPost && pageName) {
    const blogItem = items.find((i) => i.path.endsWith('/blog/'))
    list = [
      items[0],
      ...(blogItem ? [{ name: blogItem.name, path: blogItem.path }] : []),
      { name: pageName, path: currentPath },
    ]
  } else if (pageName) {
    list = [
      items[0],
      { name: pageName, path: currentPath },
    ]
  } else {
    list = items.filter((i) => currentPath.startsWith(i.path) || i.path === currentPath)
  }

  return {
    '@context': 'https://schema.org',
    '@type': 'BreadcrumbList',
    itemListElement: list.map((item: any, idx: number) => {
      const isLast = idx === list.length - 1
      const entry: Record<string, any> = {
        '@type': 'ListItem',
        position: idx + 1,
        name: item.name,
      }
      if (!isLast) {
        entry.item = `https://vunotek.com${item.path}`
      }
      return entry
    }),
  }
}

export function getImageAlt(
  locale: string,
  projectKey: string,
  featureKey?: string
): string {
  const l = locale === 'en' ? 'en' : 'es'
  try {
    const project = (SEO.images.portfolio as any)[projectKey]
    if (!project) return ''
    if (featureKey) {
      return project.features?.[featureKey]?.alt?.[l] || project.alt?.[l] || ''
    }
    return project.alt?.[l] || ''
  } catch {
    return ''
  }
}

export function getOGImage(post: { metaTitle?: string; ogImage?: string; title: string }) {
  if (post.ogImage) {
    return { src: post.ogImage, width: 1200, height: 630, format: 'webp' as const }
  }
  return SEO.global.ogDefaultImage
}
