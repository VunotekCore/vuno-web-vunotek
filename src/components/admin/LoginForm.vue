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
const showPassword = ref(false)

async function handleLogin() {
  error.value = ''
  loading.value = true

  try {
    const API_URL = import.meta.env.PUBLIC_API_URL || 'https://api.vunotek.com'
    const res = await fetch(`${API_URL}/admin/login.php`, {
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
  <div class="min-h-screen flex w-full bg-[#0a1022]">
    <!-- Panel izquierdo decorativo (desktop lg+) -->
    <div class="hidden lg:flex w-[480px] xl:w-1/2 bg-[#0b1326] relative overflow-hidden flex-col p-12 shrink-0">
      <!-- SVG ornamental background -->
      <div class="absolute inset-0 opacity-[0.03]">
        <svg class="h-full w-full" viewBox="0 0 800 600" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
          <defs>
            <linearGradient id="ornGrad" x1="0%" y1="0%" x2="100%" y2="100%">
              <stop offset="0%" stop-color="#69dca4"/>
              <stop offset="50%" stop-color="#0b1326"/>
              <stop offset="100%" stop-color="#0a1022"/>
            </linearGradient>
          </defs>
          <circle cx="400" cy="300" r="250" fill="none" stroke="url(#ornGrad)" stroke-width="0.5"/>
          <circle cx="400" cy="300" r="180" fill="none" stroke="url(#ornGrad)" stroke-width="0.5"/>
          <circle cx="400" cy="300" r="110" fill="none" stroke="url(#ornGrad)" stroke-width="0.5"/>
          <line x1="150" y1="300" x2="650" y2="300" stroke="url(#ornGrad)" stroke-width="0.3"/>
          <line x1="400" y1="50" x2="400" y2="550" stroke="url(#ornGrad)" stroke-width="0.3"/>
        </svg>
      </div>

      <!-- Grid pattern -->
      <div class="absolute inset-0 opacity-[0.015]">
        <svg class="h-full w-full" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
          <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
            <path d="M 40 0 L 0 0 0 40" fill="none" stroke="#dae2fd" stroke-width="0.3" stroke-opacity="0.3"/>
          </pattern>
          <rect width="100%" height="100%" fill="url(#grid)"/>
        </svg>
      </div>

      <!-- Contenido centrado -->
      <div class="flex-1 flex flex-col items-center justify-center min-h-0 px-4">
        <!-- Isotipo grande -->
        <div class="flex justify-center w-full max-w-[380px]">
          <svg width="100" height="64" viewBox="100 105 280 180" fill="none" aria-hidden="true" class="xl:w-[120px] xl:h-[77px]">
            <path fill="#449FD9" d="M239.5,234.1c0,10.2,0,20,0,30.5c-8.7,0-17.1,0.2-25.5-0.2c-1.3-0.1-2.8-2.4-3.6-3.9c-15.6-28.5-31.2-57.1-46.8-85.6c-8.5-15.7-17.1-31.3-26.2-47.9c6.7,0,12.8-0.1,18.9,0.1c1,0,2.1,1.7,2.7,2.9c11.6,21.3,23.1,42.6,34.6,63.9c9.5,17.5,19.1,35,28.6,52.5c0.3,0.6,0.8,1.2,2,3.1c0-7.2,0-13,0-19C227.9,233.2,233.5,234.4,239.5,234.1z"/>
            <path fill="#FFFFFF" d="M251.2,143.4c0,5.2,0,9.2,0,13.6c-4-3.4-10.6-4.7-14.7-4.4c0-8.3,0-16.6,0-25.9c6.2,0,12.3,0.3,18.4-0.1c4.6-0.3,7.3,1,9.9,5.2c22.2,34.3,44.7,68.3,67.2,102.4c0.8,1.3,1.8,2.4,3.5,4.6c0-38,0-74.7,0-111.8c6.5,0,12.5,0,18.8,0c0,45.8,0,91.4,0,137.6c-7.7,0-15.2,0.2-22.6-0.2c-1.3-0.1-2.8-1.9-3.7-3.2c-25.1-38.6-50.2-77.3-75.2-116C252.6,144.9,252.4,144.7,251.2,143.4z"/>
            <path fill="#61C3DB" d="M265,194c-0.1,16.1-13.4,29.3-29.4,29.1c-16-0.2-28.7-13.2-28.6-29.3c0.1-16.3,13.1-29.4,29.1-29.4C252.2,164.4,265.1,177.6,265,194z"/>
          </svg>
        </div>

        <!-- Contenido: titulo + features -->
        <div class="flex flex-col items-center text-center w-full max-w-[380px] mt-6">
          <div class="space-y-3 w-full">
            <h2 class="font-headline text-2xl tracking-tight text-[#dae2fd]">
              VUNO<span style="color:#00A8FF">TEK</span>
            </h2>
            <p class="text-sm text-[#94a3b8] leading-relaxed">Software Infrastructure & Advanced Automation</p>
          </div>

          <div class="w-12 h-px bg-[#69dca4]/20 my-6"></div>

          <ul class="space-y-3 w-full">
            <li class="flex items-start gap-3">
              <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-sm bg-[#69dca4]/10 mt-0.5">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#69dca4" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              </span>
              <span class="text-xs text-[#94a3b8] leading-relaxed">Gestión de contenido completa</span>
            </li>
            <li class="flex items-start gap-3">
              <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-sm bg-[#69dca4]/10 mt-0.5">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#69dca4" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              </span>
              <span class="text-xs text-[#94a3b8] leading-relaxed">Control de usuarios y permisos</span>
            </li>
            <li class="flex items-start gap-3">
              <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-sm bg-[#69dca4]/10 mt-0.5">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#69dca4" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              </span>
              <span class="text-xs text-[#94a3b8] leading-relaxed">Dashboard con métricas en tiempo real</span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Bottom -->
      <div class="relative z-10 w-full flex items-center justify-between">
        <div class="flex items-center gap-2">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#69dca4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
          <span class="text-[11px] text-[#4a5568]">Conexión segura · SSL</span>
        </div>
        <p class="text-[11px] text-[#4a5568]">&copy; {{ new Date().getFullYear() }} Vunotek</p>
      </div>
    </div>

    <!-- Panel derecho: formulario -->
    <div class="flex-1 flex items-center justify-center p-6 md:p-12">
      <div class="w-full max-w-[24rem]">
        <!-- Brand mobile/tablet (oculto en desktop lg+) -->
        <div class="lg:hidden text-center mb-10">
          <div class="inline-flex items-center justify-center gap-2 mb-2">
            <svg width="28" height="18" viewBox="100 105 280 180" fill="none" aria-hidden="true">
              <path fill="#449FD9" d="M239.5,234.1c0,10.2,0,20,0,30.5c-8.7,0-17.1,0.2-25.5-0.2c-1.3-0.1-2.8-2.4-3.6-3.9c-15.6-28.5-31.2-57.1-46.8-85.6c-8.5-15.7-17.1-31.3-26.2-47.9c6.7,0,12.8-0.1,18.9,0.1c1,0,2.1,1.7,2.7,2.9c11.6,21.3,23.1,42.6,34.6,63.9c9.5,17.5,19.1,35,28.6,52.5c0.3,0.6,0.8,1.2,2,3.1c0-7.2,0-13,0-19C227.9,233.2,233.5,234.4,239.5,234.1z"/>
              <path fill="#FFFFFF" d="M251.2,143.4c0,5.2,0,9.2,0,13.6c-4-3.4-10.6-4.7-14.7-4.4c0-8.3,0-16.6,0-25.9c6.2,0,12.3,0.3,18.4-0.1c4.6-0.3,7.3,1,9.9,5.2c22.2,34.3,44.7,68.3,67.2,102.4c0.8,1.3,1.8,2.4,3.5,4.6c0-38,0-74.7,0-111.8c6.5,0,12.5,0,18.8,0c0,45.8,0,91.4,0,137.6c-7.7,0-15.2,0.2-22.6-0.2c-1.3-0.1-2.8-1.9-3.7-3.2c-25.1-38.6-50.2-77.3-75.2-116C252.6,144.9,252.4,144.7,251.2,143.4z"/>
              <path fill="#61C3DB" d="M265,194c-0.1,16.1-13.4,29.3-29.4,29.1c-16-0.2-28.7-13.2-28.6-29.3c0.1-16.3,13.1-29.4,29.1-29.4C252.2,164.4,265.1,177.6,265,194z"/>
            </svg>
            <span class="font-headline text-lg text-[#dae2fd] tracking-tight">VUNO<span class="text-[#00A8FF]">TEK</span></span>
          </div>
          <p class="font-label-mono text-[#69dca4]">admin panel</p>
        </div>

        <!-- Glass card -->
        <div class="glass-card overflow-hidden rounded-xl">
          <!-- Header -->
          <div class="px-7 pt-7 pb-2 text-center">
            <svg width="32" height="20" viewBox="100 105 280 180" fill="none" aria-hidden="true" class="mx-auto mb-5">
              <path fill="#449FD9" d="M239.5,234.1c0,10.2,0,20,0,30.5c-8.7,0-17.1,0.2-25.5-0.2c-1.3-0.1-2.8-2.4-3.6-3.9c-15.6-28.5-31.2-57.1-46.8-85.6c-8.5-15.7-17.1-31.3-26.2-47.9c6.7,0,12.8-0.1,18.9,0.1c1,0,2.1,1.7,2.7,2.9c11.6,21.3,23.1,42.6,34.6,63.9c9.5,17.5,19.1,35,28.6,52.5c0.3,0.6,0.8,1.2,2,3.1c0-7.2,0-13,0-19C227.9,233.2,233.5,234.4,239.5,234.1z"/>
              <path fill="#FFFFFF" d="M251.2,143.4c0,5.2,0,9.2,0,13.6c-4-3.4-10.6-4.7-14.7-4.4c0-8.3,0-16.6,0-25.9c6.2,0,12.3,0.3,18.4-0.1c4.6-0.3,7.3,1,9.9,5.2c22.2,34.3,44.7,68.3,67.2,102.4c0.8,1.3,1.8,2.4,3.5,4.6c0-38,0-74.7,0-111.8c6.5,0,12.5,0,18.8,0c0,45.8,0,91.4,0,137.6c-7.7,0-15.2,0.2-22.6-0.2c-1.3-0.1-2.8-1.9-3.7-3.2c-25.1-38.6-50.2-77.3-75.2-116C252.6,144.9,252.4,144.7,251.2,143.4z"/>
              <path fill="#61C3DB" d="M265,194c-0.1,16.1-13.4,29.3-29.4,29.1c-16-0.2-28.7-13.2-28.6-29.3c0.1-16.3,13.1-29.4,29.1-29.4C252.2,164.4,265.1,177.6,265,194z"/>
            </svg>
            <h1 class="text-xl font-headline text-[#dae2fd] tracking-tight">Bienvenido a VUNOTEK</h1>
            <p class="mt-1.5 text-sm text-[#94a3b8] max-w-[260px] mx-auto leading-relaxed">
              Ingresa tus credenciales para acceder al panel de administración.
            </p>
          </div>

          <!-- Error -->
          <div class="px-7">
            <div
              v-if="error"
              class="p-3 rounded-sm bg-[#DC2626]/10 border border-[#DC2626]/20 flex items-center gap-3 text-[#DC2626] text-sm"
              role="alert"
            >
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
              <span>{{ error }}</span>
            </div>
          </div>

          <!-- Form body -->
          <form class="px-7 pt-5 pb-7 space-y-5" @submit.prevent="handleLogin">
            <div class="space-y-1.5">
              <label for="email" class="block font-label-mono text-[#94a3b8]">Email</label>
              <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#4a5568] group-focus-within:text-[#69dca4] transition-colors"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                </div>
                <input
                  id="email"
                  v-model="email"
                  type="email"
                  required
                  autocomplete="email"
                  placeholder="admin@vunotek.com"
                  class="w-full bg-[#1e293b]/60 border border-[#dae2fd]/10 pl-10 pr-4 py-3 text-sm text-[#dae2fd] placeholder-[#4a5568] focus:border-[#69dca4] focus:outline-none focus:ring-1 focus:ring-[#69dca4]/30 transition-all rounded-sm"
                />
              </div>
            </div>

            <div class="space-y-1.5">
              <label for="password" class="block font-label-mono text-[#94a3b8]">Contraseña</label>
              <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-[#4a5568] group-focus-within:text-[#69dca4] transition-colors"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <input
                  id="password"
                  v-model="password"
                  :type="showPassword ? 'text' : 'password'"
                  required
                  autocomplete="current-password"
                  placeholder="••••••••"
                  class="w-full bg-[#1e293b]/60 border border-[#dae2fd]/10 pl-10 pr-10 py-3 text-sm text-[#dae2fd] placeholder-[#4a5568] focus:border-[#69dca4] focus:outline-none focus:ring-1 focus:ring-[#69dca4]/30 transition-all rounded-sm"
                />
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute inset-y-0 right-0 pr-3 flex items-center text-[#4a5568] hover:text-[#94a3b8] transition-colors"
                  tabindex="-1"
                >
                  <svg v-if="!showPassword" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                  <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                </button>
              </div>
            </div>

            <button
              type="submit"
              :disabled="loading"
              class="w-full admin-btn admin-btn-primary text-sm font-semibold tracking-wider uppercase px-6 h-12 disabled:opacity-50 disabled:cursor-not-allowed justify-center gap-2"
            >
              <svg v-if="loading" class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
              <template v-else>
                Iniciar sesión
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
              </template>
            </button>
          </form>
        </div>

        <!-- Mobile/tablet footer (visible when left panel is hidden) -->
        <div class="lg:hidden flex items-center justify-between mt-8">
          <div class="flex items-center gap-2">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#69dca4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <span class="text-[11px] text-[#4a5568]">Conexión segura · SSL</span>
          </div>
          <p class="text-[11px] text-[#4a5568]">&copy; {{ new Date().getFullYear() }} Vunotek</p>
        </div>
      </div>
    </div>
  </div>
</template>
