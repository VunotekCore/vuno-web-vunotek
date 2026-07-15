import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

const API_URL = import.meta.env.PUBLIC_API_URL || 'https://api.vunotek.com'

export const useAuthStore = defineStore('auth', () => {
  const token = ref<string | null>(null)
  const user = ref<{ id: number; email: string; name: string } | null>(null)

  const isAuthenticated = computed(() => !!token.value)

  function init() {
    const stored = localStorage.getItem('admin_token')
    if (stored) {
      token.value = stored
    }
  }

  async function login(email: string, password: string): Promise<boolean> {
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

  return { token, user, isAuthenticated, init, login, logout, verify, authHeaders }
})
