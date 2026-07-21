import api from './api'

export const projectService = {
  list(params?: Record<string, string>) {
    return api.get('/projects/list-admin.php', { params })
  },

  listPublic(params?: Record<string, string>) {
    return api.get('/projects/list.php', { params })
  },

  get(id: number) {
    return api.get('/projects/get.php', { params: { id } })
  },

  create(data: Record<string, unknown>) {
    return api.post('/projects/create.php', data)
  },

  update(id: number, data: Record<string, unknown>) {
    return api.put(`/projects/update.php?id=${id}`, data)
  },

  delete(id: number) {
    return api.delete('/projects/delete.php', { params: { id } })
  },
}
