/** @type {Record<string, string>} */
export const blogSlugToEn = {
  'typescript-avanzado': 'typescript-advanced-en',
  'vuejs-composition-api': 'vuejs-composition-api-en',
  'astro-static-site': 'astro-static-site-en',
  'nodejs-backend-apis': 'nodejs-backend-apis-en',
};

/** @type {Record<string, string>} */
export const blogSlugToEs = {};
for (const [es, en] of Object.entries(blogSlugToEn)) {
  blogSlugToEs[en] = es;
}
