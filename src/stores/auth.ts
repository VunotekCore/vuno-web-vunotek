import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

function getApiUrl(): string {
  if (typeof window !== 'undefined' && window.location.hostname === 'localhost') {
    return 'http://localhost:8000'
  }
  return (import.meta.env.PUBLIC_API_URL as string) || 'https://api.vunotek.com'
}

interface Permissions {
  all?: boolean
  blog?: string[]
  categories?: string[]
}

interface User {
  id: number
  email: string
  name: string
  role_id: number
  role_name: string
  role_slug: string
  permissions: Permissions
}

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(null)
  const user = ref<User | null>(null)

  const isAuthenticated = computed(() => !!token.value)

  const hasPermission = computed(() => {
    return (module: string, action: string): boolean => {
      if (!user.value?.permissions) return false
      if (user.value.permissions.all) return true
      const perms = user.value.permissions[module as keyof Permissions]
      if (!Array.isArray(perms)) return false
      return perms.includes(action)
    }
  })

  const isViewer = computed(() => user.value?.role_slug === 'viewer')

  function init() {
    const stored = localStorage.getItem('admin_token')
    if (stored) {
      token.value = stored
    }
  }

  async function login(email: string, password: string): Promise<boolean> {
    const API_URL = getApiUrl()
    const res = await fetch(`${API_URL}/admin/login.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email, password }),
    })

    const data = await res.json()

    if (data.success && data.token) {
      token.value = data.token
      user.value = data.user ?? null
      localStorage.setItem('admin_token', data.token)
      return true
    }

    return false
  }

  function logout() {
    token.value = null
    user.value = null
    localStorage.removeItem('admin_token')
    window.location.href = '/admin/login'
  }

  async function verify(): Promise<boolean> {
    if (!token.value) return false

    try {
      const API_URL = getApiUrl()
      const res = await fetch(`${API_URL}/admin/verify.php`, {
        headers: { Authorization: `Bearer ${token.value}` },
      })

      const data = await res.json()

      if (!data.success) {
        logout()
        return false
      }

      user.value = data.user ?? null
      return true
    } catch {
      logout()
      return false
    }
  }

  function authHeaders(): Record<string, string> {
    return token.value ? { Authorization: `Bearer ${token.value}` } : {}
  }

  return { token, user, isAuthenticated, isViewer, hasPermission, init, login, logout, verify, authHeaders }
})
