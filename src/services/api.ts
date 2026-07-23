import axios from 'axios'

const TOKEN_KEY = 'vunotek_admin_token'

export function getApiUrl(): string {
  if (import.meta.env.DEV) {
    return 'http://localhost:8000'
  }
  const envUrl = import.meta.env.PUBLIC_API_URL as string | undefined
  if (envUrl) {
    return envUrl
  }
  return 'https://api.vunotek.com'
}

const api = axios.create({
  baseURL: getApiUrl(),
  timeout: 15000,
  withCredentials: true,
  headers: { 'Content-Type': 'application/json' },
})

api.interceptors.request.use((config) => {
  if (typeof window !== 'undefined') {
    const token = localStorage.getItem(TOKEN_KEY)
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
  }
  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      if (typeof window !== 'undefined') {
        localStorage.removeItem(TOKEN_KEY)
        const path = window.location.pathname
        if (path.startsWith('/admin')) {
          window.location.href = '/admin/login'
        }
      }
    }
    return Promise.reject(error)
  }
)

export default api
