---
title: "Offline-First Architecture: Building Apps That Work Without Internet"
excerpt: "Principles and patterns for designing applications that deliver a seamless experience even when the connection drops, using IndexedDB and sync queues."
date: 2025-05-15
category: Architecture
author: Daniel Flores
locale: en
image: blog/offline-first
---

Offline-first applications are not just for when there is no internet: they are for when the connection is slow, intermittent, or expensive.

## The Core Principle

An offline-first app never assumes connectivity. It operates locally by default and syncs when possible.

## Sync Queue Pattern

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
        break // Retry later
      }
    }
  }
}
```

## Recommended Stack

- **IndexedDB** via Dexie.js for local storage
- **Service Workers** for static asset caching
- **Background Sync API** for pending operations
- **Conflict Resolution** with Last-Write-Wins or CRDTs

## Benefits

- Flawless UX even in no-coverage areas
- Reduced server pressure through batch operations
- Resilience against backend outages
