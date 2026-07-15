<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '../../stores/auth'

const auth = useAuthStore()
auth.init()

if (auth.isAuthenticated) {
  window.location.href = '/admin'
}

const email = ref('')
const password = ref('')
const error = ref('')
const loading = ref(false)

async function handleLogin() {
  error.value = ''
  loading.value = true

  try {
    const res = await fetch(`${import.meta.env.PUBLIC_API_URL || 'https://api.vunotek.com'}/admin/login.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: email.value, password: password.value }),
    })

    const data = await res.json()

    if (data.success && data.token) {
      localStorage.setItem('admin_token', data.token)
      window.location.href = '/admin'
    } else {
      error.value = data.message || 'Credenciales incorrectas'
    }
  } catch {
    error.value = 'Error de conexión. Intenta nuevamente.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="w-full max-w-sm px-6">
    <div class="mb-8 text-center">
      <a href="/" class="inline-flex items-center gap-2 mb-6">
        <span class="font-display-lg text-headline-lg text-on-surface">VUNO</span>
        <span class="font-display-lg text-headline-lg text-vue-green">TEK</span>
      </a>
      <h1 class="text-headline-md font-display-lg text-on-surface">Admin Panel</h1>
      <p class="mt-2 text-body-md text-on-surface-variant">Inicia sesión para continuar</p>
    </div>

    <form @submit.prevent="handleLogin" class="flex flex-col gap-4">
      <div>
        <label for="email" class="block text-sm font-medium text-on-surface-variant mb-1.5">Email</label>
        <input
          id="email"
          v-model="email"
          type="email"
          required
          autocomplete="email"
          placeholder="admin@vunotek.com"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
        />
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-on-surface-variant mb-1.5">Contraseña</label>
        <input
          id="password"
          v-model="password"
          type="password"
          required
          autocomplete="current-password"
          placeholder="••••••••"
          class="w-full rounded-lg border border-outline-variant/40 bg-surface-container px-4 py-3 text-on-surface placeholder:text-on-surface-variant/50 transition-colors focus:border-vue-green focus:outline-none focus:ring-2 focus:ring-vue-green/30"
        />
      </div>

      <div
        v-if="error"
        class="rounded-lg bg-error-container/20 px-4 py-3 text-sm text-error"
      >
        {{ error }}
      </div>

      <button
        type="submit"
        :disabled="loading"
        class="mt-2 w-full rounded-lg bg-vue-green px-6 py-3 font-semibold text-on-secondary transition-colors hover:bg-vue-green/90 disabled:opacity-50"
      >
        {{ loading ? 'Ingresando...' : 'Iniciar sesión' }}
      </button>
    </form>
  </div>
</template>
