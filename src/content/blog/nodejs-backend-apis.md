---
title: "Construyendo APIs REST con Node.js: Arquitectura y Buenas Prácticas"
excerpt: "Guía práctica para diseñar APIs REST robustas con Node.js, Express, middleware patterns, validación y autenticación JWT."
date: 2025-05-20
category: Backend
author: Daniel Flores
locale: es
image: https://placehold.co/1200x520/1e293b/42b883?text=Node.js+REST+APIs
metaTitle: "Construyendo APIs REST con Node.js: Arquitectura y Buenas Prácticas | Vunotek"
ogImage: https://placehold.co/1200x630/1e293b/00a8ff?text=Node.js+REST+APIs
---

Node.js se ha convertido en el estándar para construir APIs REST rápidas y escalables. Una arquitectura bien definida marca la diferencia entre un proyecto mantenible y un legacy imposible de extender.

## Estructura de proyecto

```
src/
├── middlewares/    # autenticación, validación, errores
├── routes/        # definición de endpoints
├── controllers/   # lógica de manejo de requests
├── services/      # lógica de negocio
├── repositories/  # acceso a datos
└── validators/    # esquemas de validación
```

## Middleware pattern

Express se basa en una cadena de middlewares. Cada uno recibe `req`, `res`, `next` y puede modificar el request, responder, o delegar:

```typescript
import { Request, Response, NextFunction } from 'express'

function authMiddleware(req: Request, res: Response, next: NextFunction) {
  const token = req.headers.authorization?.replace('Bearer ', '')
  if (!token) return res.status(401).json({ error: 'No token provided' })

  try {
    const decoded = jwt.verify(token, process.env.JWT_SECRET!)
    req.user = decoded
    next()
  } catch {
    res.status(401).json({ error: 'Invalid token' })
  }
}
```

## Controladores limpios

Los controladores solo traducen el request a datos y delegan la lógica a servicios:

```typescript
// controllers/product.controller.ts
export async function getProducts(req: Request, res: Response) {
  const { page = 1, limit = 10 } = req.query
  const result = await productService.list({ page: +page, limit: +limit })
  res.json(result)
}
```

## Manejo centralizado de errores

```typescript
// middlewares/error-handler.ts
export function errorHandler(err: Error, req: Request, res: Response, next: NextFunction) {
  const status = err instanceof AppError ? err.status : 500
  res.status(status).json({
    error: err.message,
    ...(process.env.NODE_ENV === 'development' && { stack: err.stack })
  })
}
```

## Conclusión

Una API bien arquitecturada con Node.js es modular, testeable y escalable. La separación clara de capas y el uso de middlewares permite agregar funcionalidad sin romper lo existente.
