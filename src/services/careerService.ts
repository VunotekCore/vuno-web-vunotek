import api from './api'

export const careerService = {
  list(params?: Record<string, string>) {
    return api.get('/careers/list-admin.php', { params })
  },

  listPublic(params?: Record<string, string>) {
    return api.get('/careers/list.php', { params })
  },

  get(id: number) {
    return api.get('/careers/get.php', { params: { id } })
  },

  getBySlug(slug: string) {
    return api.get('/careers/get.php', { params: { slug } })
  },

  create(data: Record<string, unknown>) {
    return api.post('/careers/create.php', data)
  },

  update(id: number, data: Record<string, unknown>) {
    return api.put(`/careers/update.php?id=${id}`, data)
  },

  delete(id: number) {
    return api.delete('/careers/delete.php', { params: { id } })
  },
}
