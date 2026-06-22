---
title: "TypeScript Avanzado: Conditional Types, Mapped Types y Template Literals"
excerpt: "Domina el sistema de tipos de TypeScript con patrones avanzados que convierten tu código en contratos inmutables autodocumentados."
date: 2025-03-28
category: TypeScript
author: Daniel Flores
locale: es
image: https://placehold.co/1200x520/1e293b/42b883?text=TypeScript+Avanzado
---

El sistema de tipos de TypeScript es Turing completo. Esto significa que puedes codificar lógica compleja directamente en tus tipos.

## Conditional Types

```typescript
type IsString<T> = T extends string ? true : false
type Result = IsString<'hello'> // true
type Result2 = IsString<42>    // false
```

## Mapped Types

Transforman propiedades de un tipo existente:

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

## Conditional Inference

```typescript
type GetReturnType<T> = T extends (...args: any[]) => infer R ? R : never
type FnType = GetReturnType<typeof someFunction>
```

## Utility Types personalizados

```typescript
type DeepPartial<T> = {
  [K in keyof T]?: T[K] extends object ? DeepPartial<T[K]> : T[K]
}

type NonEmptyArray<T> = [T, ...T[]]
```

## Conclusión

Dominar estos patrones reduce errores en compilación y elimina categorías enteras de bugs en producción. Un sistema de tipos expresivo es la mejor documentación que puede tener tu código.
