---
title: "Building REST APIs with Node.js: Architecture and Best Practices"
excerpt: "A practical guide to designing robust REST APIs with Node.js, Express, middleware patterns, validation, and JWT authentication."
date: 2026-05-20
category: Backend
author: Daniel Flores
locale: en
image: https://ik.imagekit.io/vijys5g3r/blog/nodejs-backend-apis.webp
metaTitle: "Building REST APIs with Node.js: Architecture and Best Practices | Vunotek"
ogImage: https://ik.imagekit.io/vijys5g3r/blog/nodejs-backend-apis.webp
---

Node.js has become the standard for building fast, scalable REST APIs. A well-defined architecture is the difference between a maintainable project and an impossible-to-extend legacy.

## Project structure

```
src/
├── middlewares/    # authentication, validation, errors
├── routes/        # endpoint definitions
├── controllers/   # request handling logic
├── services/      # business logic
├── repositories/  # data access layer
└── validators/    # validation schemas
```

## Middleware pattern

Express relies on a chain of middlewares. Each receives `req`, `res`, `next` and can modify the request, respond, or delegate:

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

## Clean controllers

Controllers only translate requests to data and delegate logic to services:

```typescript
// controllers/product.controller.ts
export async function getProducts(req: Request, res: Response) {
  const { page = 1, limit = 10 } = req.query
  const result = await productService.list({ page: +page, limit: +limit })
  res.json(result)
}
```

## Centralized error handling

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

## Conclusion

A well-architected Node.js API is modular, testable, and scalable. Clear layer separation and middleware usage lets you add functionality without breaking existing code.
