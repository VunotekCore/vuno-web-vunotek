import api from './api'

export const blogService = {
  list(params?: Record<string, string>) {
    return api.get('/blog/list.php', { params })
  },

  get(id: number) {
    return api.get('/blog/get.php', { params: { id } })
  },

  create(data: Record<string, unknown>) {
    return api.post('/blog/create.php', data)
  },

  update(id: number, data: Record<string, unknown>) {
    return api.put(`/blog/update.php?id=${id}`, data)
  },

  delete(id: number) {
    return api.delete('/blog/delete.php', { params: { id } })
  },
}
