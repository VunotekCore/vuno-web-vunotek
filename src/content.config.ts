import { defineCollection } from 'astro:content'
import { z } from 'astro/zod'
import { glob } from 'astro/loaders'

const blog = defineCollection({
  loader: glob({ pattern: '**/*.md', base: './src/content/blog' }),
  schema: z.object({
    title: z.string(),
    excerpt: z.string(),
    date: z.date(),
    category: z.string(),
    author: z.string().default('Daniel Flores'),
    locale: z.enum(['es', 'en']),
    image: z.string().optional(),
  }),
})

export const collections = { blog }
