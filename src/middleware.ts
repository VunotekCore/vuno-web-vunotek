import { defineMiddleware } from 'astro:middleware'

export const onRequest = defineMiddleware((context, next) => {
  const match = context.url.pathname.match(/^\/(en)(\/|$)/)
  context.locals.locale = match ? 'en' : 'es'
  return next()
})
