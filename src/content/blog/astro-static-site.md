---
title: "Astro: El Framework Definitivo para Sitios Estáticos y Dinámicos"
excerpt: "Descubre cómo Astro combina lo mejor del SSG y SSR con su arquitectura de islas, cero JavaScript por defecto y renderizado híbrido."
date: 2025-07-10
category: Frontend
author: Daniel Flores
locale: es
image: https://placehold.co/1200x520/1e293b/42b883?text=Astro+Framework
---

Astro ha redefinido la construcción de sitios web modernos al entregar cero JavaScript por defecto y permitir componentes interactivos solo cuando se necesitan.

## Arquitectura de Islas

El concepto central de Astro son las **islands**: componentes interactivos que se hidratan de forma independiente sin afectar el resto de la página:

```astro
<!-- ProductCard.astro -->
---
import AddToCart from '../components/AddToCart.vue'
---

<div class="card">
  <img src={product.image} alt={product.name} />
  <h3>{product.name}</h3>
  <p>{product.price}</p>
  <AddToCart client:load productId={product.id} />
</div>
```

Solo `AddToCart` envía JavaScript al navegador. El resto es HTML estático.

## Cero JS por defecto

Astro elimina todo el JavaScript que no sea explícitamente marcado con directivas `client:*`:

```astro
<Slider client:visible />    <!-- carga cuando es visible -->
<Modal client:idle />        <!-- carga cuando el navegador está idle -->
<Form client:only="react" /> <!-- solo del lado del cliente -->
```

## Content Collections

El manejo de contenido con type-safety incluido:

```typescript
import { getCollection } from 'astro:content'
const posts = await getCollection('blog')
// posts[0].data.title es string, no any
```

## View Transitions

Transiciones nativas entre páginas sin frameworks pesados:

```astro
---
import { ViewTransitions } from 'astro:transitions'
---

<ViewTransitions />
<a href="/about">Sobre nosotros</a>
```

## Conclusión

Astro es ideal para sitios centrados en contenido que necesitan rendimiento extremo sin sacrificar interactividad. Su modelo de islas y cero JS por defecto lo convierten en la mejor opción para proyectos web modernos.
