---
title: "De Cero a Producción: Desplegando Aplicaciones Node.js en AWS con Docker"
excerpt: "Guía completa para contenerizar aplicaciones Node.js y desplegarlas en AWS ECS con balanceador de carga, base de datos y CI/CD integrado."
date: 2025-04-20
category: DevOps
author: Daniel Flores
locale: es
image: blog/nodejs-aws-docker
---

Desplegar una aplicación Node.js en producción requiere más que solo subir archivos al servidor. AWS con Docker te da escalabilidad y reproducibilidad.

## El Dockerfile Óptimo para Node.js

```dockerfile
FROM node:22-alpine AS builder
WORKDIR /app
COPY package*.json ./
RUN npm ci --only=production

FROM node:22-alpine
WORKDIR /app
COPY --from=builder /app/node_modules ./node_modules
COPY . .
EXPOSE 3000
USER node
CMD ["node", "server.js"]
```

## Arquitectura en AWS

```
CloudFront → ALB → ECS (Fargate) → RDS (PostgreSQL)
                    ↕
              ElastiCache Redis
```

## CI/CD con GitHub Actions

```yaml
jobs:
  deploy:
    steps:
      - run: docker build -t app .
      - run: aws ecs update-service --force-new-deployment
```

## Buenas Prácticas

1. **Health checks** en el balanceador
2. **Auto-scaling** basado en CPU/memoria
3. **Logs centralizados** con CloudWatch
4. **Secretos** en AWS Secrets Manager, nunca en el código

El resultado: despliegues en menos de 2 minutos con zero downtime.
