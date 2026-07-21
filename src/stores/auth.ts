import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authService } from '../services/authService'

interface Permissions {
  all?: boolean
  blog?: string[]
  categories?: string[]
  projects?: string[]
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

const TOKEN_KEY = 'vunotek_admin_token'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)

  const isAuthenticated = computed(() => !!user.value)

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

  function setToken(token: string | undefined) {
    if (token) {
      localStorage.setItem(TOKEN_KEY, token)
    } else {
      localStorage.removeItem(TOKEN_KEY)
    }
  }

  function getToken(): string | null {
    return localStorage.getItem(TOKEN_KEY)
  }

  function clearToken() {
    localStorage.removeItem(TOKEN_KEY)
  }

  async function login(email: string, password: string): Promise<boolean> {
    const { data } = await authService.login(email, password)

    if (data.success && data.data?.user) {
      user.value = data.data.user
      setToken(data.data.token)
      return true
    }

    return false
  }

  function logout() {
    user.value = null
    clearToken()
    authService.logout().catch(() => {})
    window.location.href = '/admin/login'
  }

  async function verify(): Promise<boolean> {
    try {
      const { data } = await authService.verify()

      if (!data.success) {
        user.value = null
        clearToken()
        return false
      }

      user.value = data.data?.user ?? null
      if (data.data?.token) setToken(data.data.token)
      return true
    } catch {
      user.value = null
      clearToken()
      return false
    }
  }

  return { user, isAuthenticated, isViewer, hasPermission, login, logout, verify, getToken }
})
