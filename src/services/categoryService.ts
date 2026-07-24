import api from './api'

export const categoryService = {
  list() {
    return api.get('/categories/list.php')
  },

  listAdmin() {
    return api.get('/categories/list-admin.php')
  },

  get(id: number) {
    return api.get('/categories/get.php', { params: { id } })
  },

  create(data: Record<string, unknown>) {
    return api.post('/categories/create.php', data)
  },

  update(id: number, data: Record<string, unknown>) {
    return api.put(`/categories/update.php?id=${id}`, data)
  },

  delete(id: number) {
    return api.delete('/categories/delete.php', { params: { id } })
  },
}
