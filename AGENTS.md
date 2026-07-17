# 🤖 AGENT: ASTRO & VUNOTEK EXPERT

> **Proyecto:** Vunotek — Sitio web corporativo e ingeniería de software
> **Marca:** VUNOTEK — "Software Infrastructure & Advanced Automation"
> **Stack:** Astro · Tailwind CSS 4 · TypeScript strict · pnpm
> **i18n:** Spanish default (`/`), English (`/en/`)

---

## Índice

1. [Resumen del Proyecto](#1-resumen-del-proyecto)
2. [i18n y Internacionalización](#2-i18n-y-internacionalización)
3. [Estructura del Proyecto](#3-estructura-del-proyecto)
4. [Componentes y Diseño](#4-componentes-y-diseño)
5. [Design Tokens y Estilos](#5-design-tokens-y-estilos)
6. [Images y CDN](#6-images-y-cdn)
7. [Section Spacing y Logo](#7-section-spacing-y-logo)
8. [Layouts: Público vs Backoffice](#8-layouts-público-vs-backoffice)
9. [Backoffice (Implementado)](#9-backoffice-implementado)
10. [API Pattern SOA](#10-api-pattern-soa)
11. [Service Layer — Patrón Frontend](#11-service-layer--patrón-frontend)
12. [Cómo agregar un módulo nuevo al Backoffice](#12-cómo-agregar-un-módulo-nuevo-al-backoffice)
13. [Convenciones de Código](#13-convenciones-de-código)
14. [Performance](#14-performance)
15. [Comandos y Verificación](#15-comandos-y-verificación)
16. [Workflow del Agente](#16-workflow-del-agente)

---

## 1. Resumen del Proyecto

Vunotek es el sitio web corporativo que presenta las capacidades de ingeniería de software, stack tecnológico, servicios y portafolio de proyectos. Incluye secciones de-home, servicios, proyectos, blog, about y contacto con internacionalización completa (ES/EN).

### Stack Técnico

| Capa | Tecnología | Detalle |
|------|-----------|---------|
| Framework | Astro | SSG con islands |
| Estilos | Tailwind CSS | v4 con `@theme` |
| Lenguaje | TypeScript | strict mode |
| i18n | JSON + utils | `src/i18n/es.json`, `en.json` |
| Fonts | @fontsource/* | Locales, sin blocking |
| Icons | Material Symbols | Via CSS |
| Images | ImageKit CDN | `src/utils/images.ts` |
| Package Mgr | pnpm | Lockfile |

---

## 2. i18n y Internacionalización

- **Spanish** es el idioma default → ruta raíz `/`
- **English** → ruta `/en/`
- Traducciones en `src/i18n/es.json` y `src/i18n/en.json`
- Helper: `t('key')` desde `src/i18n/utils`
- **IMPORTANTE:** Ambos archivos deben mantener la misma estructura y keys
- Image keys usan **snake_case** (`pos_interface`, `shop_filtros`)
- Al editar traducciones, siempre sincronizar ambos archivos

---

## 3. Estructura del Proyecto

```
/
├── public/
│   ├── favicon.ico
│   └── fonts/
├── src/
│   ├── components/
│   │   ├── atoms/               # Componentes atómicos (Button, Icon, etc.)
│   │   ├── molecules/           # Combinaciones (CardService, etc.)
│   │   └── organisms/           # Secciones completas (Navbar, Hero, Footer, etc.)
│   ├── i18n/
│   │   ├── es.json              # Traducciones español
│   │   ├── en.json              # Traducciones inglés
│   │   └── utils.ts             # Helper t() para i18n
│   ├── layouts/
│   │   └── BaseLayout.astro     # Layout principal (SEO, fonts, scripts)
│   ├── pages/
│   │   ├── index.astro          # Home (ES)
│   │   ├── en/
│   │   │   └── index.astro      # Home (EN)
│   │   ├── servicios.astro
│   │   ├── en/
│   │   │   └── servicios.astro
│   │   ├── proyectos.astro
│   │   ├── en/
│   │   │   └── proyectos.astro
│   │   ├── blog/
│   │   ├── en/
│   │   │   └── blog/
│   │   ├── nosotros.astro
│   │   ├── en/
│   │   │   └── nosotros.astro
│   │   ├── contacto.astro
│   │   ├── en/
│   │   │   └── contacto.astro
│   │   ├── privacidad.astro
│   │   ├── en/
│   │   │   └── privacidad.astro
│   │   └── terminos.astro
│   │       └── en/
│   │           └── terminos.astro
│   ├── styles/
│   │   └── global.css           # Tailwind @theme + design tokens + utilitarias
│   ├── templates/               # Plantillas de página reutilizables
│   ├── utils/
│   │   └── images.ts            # ImageKit CDN helper (IMAGES, img())
│   ├── seo/
│   │   └── metadata.ts          # SEO metadata por página
│   ├── config/
│   │   └── site.ts              # Config de marca
│   └── env.d.ts
├── astro.config.mjs
├── tsconfig.json
├── package.json
└── AGENTS.md                    # Este archivo
```

---

## 4. Componentes y Diseño

Se utiliza **Atomic Design** para organizar componentes:

### Atoms (Componentes más pequeños)
- `Button.astro` — Botones primary/secondary/ghost
- `Icon.astro` — Wrapper Material Symbols
- `Badge.astro` — Etiquetas y tags
- `AnimatedReveal.astro` — Wrapper IntersectionObserver

### Molecules (Combinación de atoms)
- `CardService.astro` — Card de servicio con icono + título
- `CardProject.astro` — Card de proyecto del portafolio
- `CardTestimonial.astro` — Testimonio de cliente
- `StepCard.astro` — Paso del proceso de ingeniería

### Organisms (Secciones completas)
- `Navbar.astro` — Navegación sticky con i18n
- `Footer.astro` — Footer completo con columnas
- `Hero.astro` — 4 variantes: `code`, `gradient`, `centered`, `contact`
- `ServicesSection.astro` — Grid de servicios
- `PortfolioSection.astro` — Portafolio de proyectos
- `ProcessSection.astro` — Proceso de ingeniería
- `ContactForm.astro` — Formulario de contacto
- `TestimonialsSection.astro` — Testimonios

---

## 5. Design Tokens y Estilos

Los tokens de diseño están definidos en `src/styles/global.css` via Tailwind v4 `@theme`:

**Paleta:**
- Vue Green: `var(--color-vue-green)` — Acento primario
- On Surface: `var(--color-on-surface)` — Texto principal
- Surface: `var(--color-surface)` — Fondos
- Glass: `var(--color-glass)` — Paneles glassmorphism

**Tipografía:**
- `font-display-lg` — Hanken Grotesk (títulos)
- `font-body` — Inter (cuerpo)

**Espaciado:**
- Container: `max-w-7xl mx-auto`
- Section spacing: `!pt-32 md:!pt-40` (128px/160px)

**Clases utilitarias:**
- `.glass-panel` — Efecto glassmorphism
- `.text-gradient` — Texto con gradiente
- `.metric-glow` — Glow en métricas

---

## 6. Images y CDN

- **ImageKit CDN** vía `src/utils/images.ts`
- Constante `IMAGES` con todas las imágenes disponibles
- Helper `img(key)` para construir URLs
- Variable de entorno: `PUBLIC_IMAGEKIT_ENDPOINT` en `.env`
- **Recomendado:** Usar `<Image />` de Astro para performance (LCP/CLS)
- Formatos: WebP preferido, fallback a PNG/JPG

---

## 7. Section Spacing y Logo

### Section Spacing
- Primera sección después del nav debe usar `!pt-32 md:!pt-40` (128px/160px)
- Esto coincide con el padding del Hero
- **Estándar para todas las páginas** via `src/templates/`

### Logo
- Tipographic wordmark: **VUNO** (`text-on-surface`) + **TEK** (`text-vue-green`)
- Font: `font-display-lg` (Hanken Grotesk)
- Navbar: `text-headline-lg`
- Footer: `text-headline-md`

---

## 8. Layouts: Público vs Backoffice

### Layout Público (Actual)

`src/layouts/BaseLayout.astro`
- Google Fonts (Hanken Grotesk + Inter)
- SEO meta tags via `src/seo/metadata.ts`
- Diseño: dark theme, vue green accents, glassmorphism
- Tailwind v4 `@theme` tokens

### Layout Backoffice (Implementado)

`src/layouts/AdminLayout.astro`
- Sidebar fija (260px) con fondo `#0b1326`
- Auth guard vía `authService.verify()` en `<script>`
- Tema oscuro: monolith-black, off-white, vue-green accents
- Iconos Material Symbols (via CSS)
- Responsive: sidebar colapsa a top nav en mobile
- Nav items con `data-nav-permission` para RBAC
- `<Toast client:load />` para notificaciones

**Conveniencia:** Ambos layouts coexisten. `BaseLayout` se usa para rutas públicas y `AdminLayout` para rutas `/admin/*`. Comparten `global.css` con los tokens base; cada layout carga sus propios estilos adicionales.

---

## 9. Backoffice Futuro (Vue 3 + Vunotek Theme)

### Stack Implementado

| Capa | Tecnología | Detalle |
|------|-----------|---------|
| Framework Admin | Vue 3 | Astro islands con `client:only="vue"` |
| State Management | Pinia | Setup stores |
| HTTP Client | axios | Service layer con interceptores |
| Iconos | Material Symbols | Via CSS |
| Estilos | Tailwind CSS 4 | Tema vunotek en admin.css |
| Autenticación | JWT + localStorage | Token Bearer en interceptor |
| Datos | APIs PHP (SOA) | MySQL → `api/` directory |

### Estructura Actual

```
src/
├── services/                    # Service layer (axios)
│   ├── api.ts                   # Instancia axios + interceptores + getApiUrl()
│   ├── authService.ts           # login(), verify()
│   ├── blogService.ts           # list(), get(), create(), update(), delete()
│   └── categoryService.ts       # list(), get(), create(), update(), delete()
├── composables/                 # Compartidos (no Pinia)
│   └── useToast.ts              # Estado de notificaciones toast
├── stores/                      # Pinia stores
│   └── auth.ts                  # Token, user, permissions, login/verify/logout
├── components/
│   └── admin/                   # Componentes Vue del panel
│       ├── LoginForm.vue        # Login page (two-column layout)
│       ├── BlogForm.vue         # Editor de posts
│       ├── BlogTable.vue        # Listado de posts
│       ├── CategoryForm.vue     # Editor de categorías
│       ├── CategoryTable.vue    # Listado de categorías
│       ├── DashboardStats.vue   # Stats cards
│       └── ui/
│           └── Toast.vue        # Notificaciones toast
├── pages/
│   └── admin/                   # Páginas Astro shell
│       ├── login.astro          # Login standalone (sin AdminLayout)
│       ├── index.astro          # Dashboard
│       ├── blog/
│       │   ├── index.astro      # Listado posts
│       │   ├── [id].astro       # Editar post
│       │   └── new.astro        # Crear post
│       └── categories/
│           ├── index.astro      # Listado categorías
│           ├── [id].astro       # Editar categoría
│           └── new.astro        # Crear categoría
└── styles/
    └── admin.css                # Estilos admin (glass-card, admin-btn, etc.)
```

### Tema Vunotek (Admin)

```css
/* Tokens del tema oscuro admin */
--color-monolith-black: #1A1A1A;
--color-off-white: #F5F3F0;
--color-clay-accent: #C18C7E;
--sidebar-bg: #0b1326;
--sidebar-width: 260px;
```

---

## 10. API Pattern SOA

Backend PHP implementado en `api/` siguiendo Service-Oriented Architecture:

```
api/
├── api/                    # Entry points HTTP (≤15 líneas c/u)
│   ├── admin/
│   │   ├── login.php       # POST - autenticación
│   │   └── verify.php      # GET - verificar token
│   ├── blog/
│   │   ├── list.php        # GET - lista paginada
│   │   ├── get.php         # GET - detalle por ID
│   │   ├── create.php      # POST - crear (admin)
│   │   ├── update.php      # PUT - actualizar (admin)
│   │   └── delete.php      # DELETE - eliminar (admin)
│   └── categories/
│       ├── list.php        # GET - todas las categorías
│       ├── get.php         # GET - detalle por ID
│       ├── create.php      # POST - crear (admin)
│       ├── update.php      # PUT - actualizar (admin)
│       └── delete.php      # DELETE - eliminar (admin)
├── Controllers/            # Lógica de negocio
│   ├── AuthController.php
│   ├── BlogController.php
│   └── CategoryController.php
├── Models/                 # Solo SQL prepared statements
│   ├── UserModel.php
│   ├── BlogModel.php
│   ├── CategoryModel.php
│   └── RoleModel.php
├── Services/               # Wrappers servicios externos
│   └── AuthService.php     # password_hash/verify
├── Traits/
│   ├── ApiResponse.php     # jsonSuccess(), jsonError()
│   └── JwtAuth.php         # JWT token creation/verification
├── Config/
│   └── Database.php        # PDO singleton
└── schema.sql              # Schema + seed data
```

Cada endpoint sigue la estructura:
```php
<?php
declare(strict_types=1);
require_once __DIR__ . '/../../bootstrap.php';
use App\Config\Database;
use App\Controllers\ExperienciaController;
use App\Models\ExperienciaModel;

setCorsHeaders();
$controller = new ExperienciaController(new ExperienciaModel(Database::getConnection()));
$controller->list();
```

### Formato de Respuesta

Todos los endpoints retornan JSON con esta forma:
```json
{
  "success": true,
  "data": { ... }
}
```

Errores:
```json
{
  "success": false,
  "message": "Descripción del error"
}
```

---

## 11. Service Layer — Patrón Frontend

### api.ts — Instancia Central

`src/services/api.ts`:
- Instancia axios con `baseURL` vía `getApiUrl()`
- **Request interceptor:** inyecta `Authorization: Bearer <token>` desde localStorage
- **Response interceptor:** catch 401 → limpia token → redirect a `/admin/login`
- `getApiUrl()`: detecta `localhost` → `http://localhost:8000`, fallback a `PUBLIC_API_URL`

### Services por Módulo

Cada módulo CRUD tiene su propio service:

```typescript
// src/services/blogService.ts
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
```

### Convención de Respuesta

Con axios, el body JSON queda en `res.data`:
```
res.data       → { success: true, data: { token: "...", user: {...} } }
res.data.data  → { token: "...", user: {...} }    ← payload real
res.data.success → true
```

### Composables — useToast

`src/composables/useToast.ts`:
- Estado compartido a nivel de módulo (no Pinia)
- Métodos: `success(msg)`, `error(msg)`, `info(msg)`, `removeToast(id)`
- Auto-dismiss a los 4000ms
- Componente: `src/components/admin/ui/Toast.vue`

---

## 12. Cómo agregar un módulo nuevo al Backoffice

### Ejemplo: Módulo "Experiencias"

#### Paso 1: Service

Crear `src/services/experienciaService.ts`:
```typescript
import api from './api'

export const experienciaService = {
  list(params?: Record<string, string>) {
    return api.get('/experiencias/list.php', { params })
  },
  get(id: number) {
    return api.get('/experiencias/get.php', { params: { id } })
  },
  create(data: Record<string, unknown>) {
    return api.post('/experiencias/create.php', data)
  },
  update(id: number, data: Record<string, unknown>) {
    return api.put(`/experiencias/update.php?id=${id}`, data)
  },
  delete(id: number) {
    return api.delete('/experiencias/delete.php', { params: { id } })
  },
}
```

#### Paso 2: Componentes Vue

Crear en `src/components/admin/`:
- `ExperienciaTable.vue` — Listado con tabla, filtros, paginación
- `ExperienciaForm.vue` — Formulario crear/editar

Patrón para Table:
```vue
<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { experienciaService } from '../../services/experienciaService'
import { useToast } from '../../composables/useToast'

const toast = useToast()
const items = ref([])
const loading = ref(true)

async function fetchItems() {
  loading.value = true
  try {
    const { data } = await experienciaService.list()
    if (data.success && Array.isArray(data.data)) {
      items.value = data.data
    }
  } catch { /* silent */ }
  finally { loading.value = false }
}

async function deleteItem(id: number) {
  if (!confirm('¿Eliminar?')) return
  try {
    const { data } = await experienciaService.delete(id)
    if (data.success) {
      toast.success('Eliminado')
      await fetchItems()
    } else {
      toast.error(data.message || 'Error')
    }
  } catch { toast.error('Error de conexión') }
}

onMounted(fetchItems)
</script>
```

Patrón para Form:
```vue
<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { experienciaService } from '../../services/experienciaService'
import { useToast } from '../../composables/useToast'

const toast = useToast()
const saving = ref(false)
const error = ref('')
const form = ref({ title: '', description: '' })

async function handleSubmit() {
  saving.value = true
  try {
    const { data } = await experienciaService.create(form.value)
    if (data.success) {
      toast.success('Creado')
      window.location.href = '/admin/experiencias'
    } else {
      error.value = data.message || 'Error'
    }
  } catch { error.value = 'Error de conexión' }
  finally { saving.value = false }
}
</script>
```

#### Paso 3: Páginas Astro

Crear en `src/pages/admin/experiencias/`:
- `index.astro` — Shell con `<ExperienciaTable client:load />`
- `new.astro` — Shell con `<ExperienciaForm client:load />`
- `[id].astro` — Shell con `<ExperienciaForm client:load />`

Patrón de página:
```astro
---
import AdminLayout from '../../../layouts/AdminLayout.astro'
import ExperienciaTable from '../../../components/admin/ExperienciaTable.vue'
---
<AdminLayout title="Experiencias">
  <ExperienciaTable client:load />
</AdminLayout>
```

#### Paso 4: Sidebar

Agregar en `src/layouts/AdminLayout.astro`, array `navItems`:
```typescript
{ href: '/admin/experiencias', label: 'Experiencias', icon: 'work', permission: 'experiencias' },
```

#### Paso 5: Permisos (opcional)

Si el módulo requiere permisos granulares, agregar en la interfaz `Permissions` de `auth.ts`:
```typescript
interface Permissions {
  all?: boolean
  experiencias?: string[]  // ['create', 'edit', 'delete']
  // ...
}
```

#### Paso 6: Verificación

```bash
pnpm astro check    # 0 errores
pnpm build          # Compila OK
```

### Reglas para nuevos módulos

| Regla | Detalle |
|-------|---------|
| Service pattern | Un archivo `src/services/{module}Service.ts` por módulo |
| Toast notifications | Usar `useToast()` en vez de `alert()` o `confirm()` |
| Sin fetch inline | Nunca usar `fetch()` directamente, siempre vía service |
| Sin API_URL hardcodeada | `api.ts` centraliza la URL con detección localhost |
| Error handling | Services lanzan error → componente catch → toast.error() |
| Loading states | Siempre mostrar skeleton/spinner durante carga |
| Client directive | Usar `client:load` para componentes con datos |

---

## 13. Convenciones de Código

### Astro/TypeScript
- TypeScript strict (tsconfig.json hereda de `astro/tsconfigs/strict`)
- Imports absolutos desde `src/`
- Props tipadas con interfaz en cada componente
- `getStaticPaths()` para rutas dinámicas

### Componentes
- **Atomic Design**: atoms → molecules → organisms
- Sin librerías UI externas (Tailwind + vanilla JS)
- Sin comentarios en código (el código se explica solo)
- Props desestructuradas desde `Astro.props`

### Estilos
- Tailwind 4 con `@theme` para tokens de diseño
- Clases utilitarias inline (sin CSS modules)
- Preferir variantes de color semánticas (`bg-primary`, `text-secondary`)

### Naming
- Archivos: `PascalCase.astro` para componentes, `kebab-case.ts` para utilidades
- Directorios: `snake_case` plural
- Rutas: `kebab-case` para slugs y URLs
- Interfaces: `PascalCase` con sufijo descriptivo
- i18n keys: `snake_case` para image keys

---

## 14. Performance

- **LCP/CLS/TBT** son las métricas prioritarias
- Usar `<Image />` de Astro para imágenes (auto WebP, lazy loading)
- Fonts locales via `@fontsource/*` (sin blocking requests)
- No scripts blocking — usar `defer` o `is:inline`
- Glassmorphism: cuidado con `backdrop-filter` en mobile (afecta CLS)
- Reutilizar componentes pesados vía Astro islands

---

## 15. Comandos y Verificación

### Desarrollo

```bash
# Iniciar servidor de desarrollo
pnpm dev

# Iniciar PHP API server (localhost:8000)
pnpm dev:api

# Iniciar ambos en paralelo (Astro + PHP)
pnpm dev:all

# Build de producción
pnpm build

# Preview del build
pnpm preview
```

### Verificación (Obligatoria)

```bash
# 1. Type check — SIEMPRE después de cada cambio
pnpm astro check

# 2. Build — Verificar que compila sin errores
pnpm build
```

### Commands

| Comando | Descripción |
|---------|-----------|
| `pnpm dev` | Inicia servidor desarrollo |
| `pnpm dev:api` | Inicia PHP API server (localhost:8000) |
| `pnpm dev:all` | Inicia ambos en paralelo (Astro + PHP) |
| `pnpm build` | Build producción → `dist/` |
| `pnpm preview` | Preview del build |
| `pnpm astro check` | TypeScript check |
| `pnpm astro add` | Agregar integración |
| `pnpm astro sync` | Generar tipos TypeScript |

### Variables de Entorno

```env
PUBLIC_IMAGEKIT_ENDPOINT=https://ik.imagekit.io/your_id
```

---

## 16. Workflow del Agente

1. **Greeting:** "Engineer reporting. Astro/Vunotek Mode. [Health/Errors Scan]."
2. **Plan:** Technical steps + Performance impact warning.
3. **Execution:** Code + **Immediate error validation** (`pnpm astro check`).
4. **Git:** `[ASTRO] Action`.
5. **End:** ✅/⚠️ | Perf Status | Files | Next step.

### Validación Obligatoria

- Después de **cada** cambio de código: `pnpm astro check`
- Después de cambios significativos: `pnpm build`
- Si hay errores → **corregir antes de continuar**

---

### Referencias

- [Astro Docs](https://docs.astro.build)
- [Tailwind CSS](https://tailwindcss.com)
- [ImageKit](https://imagekit.io)
- [Vue.js](https://vuejs.org)
