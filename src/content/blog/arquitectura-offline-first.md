---
title: "Arquitectura Offline-First: Cómo Construir Apps que Funcionan Sin Internet"
excerpt: "Principios y patrones para diseñar aplicaciones que brinden una experiencia fluida incluso cuando la conexión falla, usando IndexedDB y colas de sincronización."
date: 2025-05-15
category: Arquitectura
author: Daniel Flores
locale: es
image: blog/offline-first
---

Las aplicaciones offline-first no son solo para cuando no hay internet: son para cuando la conexión es lenta, intermitente o costosa.

## El Principio Fundamental

Una app offline-first nunca asume que hay conexión. Opera localmente por defecto y sincroniza cuando puede.

## Patrón de Sincronización con Cola

```typescript
class SyncQueue {
  private queue: PendingOperation[] = []

  async enqueue(operation: PendingOperation) {
    this.queue.push(operation)
    await this.persistQueue()
    if (navigator.onLine) {
      await this.flush()
    }
  }

  async flush() {
    while (this.queue.length > 0) {
      const op = this.queue[0]
      try {
        await this.sendToServer(op)
        this.queue.shift()
      } catch {
        break // Reintentar después
      }
    }
  }
}
```

## Stack Recomendado

- **IndexedDB** via Dexie.js para almacenamiento local
- **Service Workers** para cache de recursos estáticos
- **Background Sync API** para operaciones pendientes
- **Conflict Resolution** con Last-Write-Wins o CRDTs

## Beneficios

- UX impecable incluso en zonas sin cobertura
- Menos presión en el servidor al batch-operaciones
- Resiliencia ante caídas del backend
