import { defineMiddleware } from 'astro:middleware'

export const onRequest = defineMiddleware((context, next) => {
  const match = context.url.pathname.match(/^\/(en)(\/|$)/)
  context.locals.locale = match ? 'en' : 'es'

  // NOTE: For server-side locale redirect (requires adapter + output: 'hybrid'):
  // if (!match) {
  //   const acceptLang = context.request.headers.get('accept-language') || ''
  //   if (acceptLang.startsWith('en')) {
  //     const target = context.url.pathname === '/' ? '/en' : '/en' + context.url.pathname
  //     return context.redirect(target, 302)
  //   }
  // }

  return next()
})
