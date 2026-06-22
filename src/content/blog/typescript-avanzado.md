---
title: "TypeScript Avanzado: Tipos Condicionales, Mapped Types y Template Literals"
excerpt: "Domina el sistema de tipos de TypeScript con patrones avanzados que transforman tu código en contratos inmutables y auto-documentados."
date: 2025-03-28
category: TypeScript
author: Daniel Flores
locale: es
image: blog/typescript-avanzado
---

El sistema de tipos de TypeScript es Turing completo. Esto significa que puedes codificar lógica compleja directamente en los tipos.

## Tipos Condicionales

```typescript
type IsString<T> = T extends string ? true : false
type Result = IsString<'hola'> // true
type Result2 = IsString<42>    // false
```

## Mapped Types

Transforman las propiedades de un tipo existente:

```typescript
type Readonly<T> = {
  readonly [K in keyof T]: T[K]
}

type Optional<T> = {
  [K in keyof T]?: T[K]
}
```

## Template Literal Types

```typescript
type EventName = `on${Capitalize<string>}`
type ClickEvent = EventName & 'onClick' // 'onClick'
```

## Inferencia Condicional

```typescript
type GetReturnType<T> = T extends (...args: any[]) => infer R ? R : never
type FnType = GetReturnType<typeof someFunction>
```

Dominar estos patrones reduce errores en tiempo de compilación y elimina categorías enteras de bugs en producción.
