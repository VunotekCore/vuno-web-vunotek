---
title: "Zero to Production: Deploying Node.js Applications on AWS with Docker"
excerpt: "Complete guide to containerizing Node.js applications and deploying them on AWS ECS with load balancers, databases, and integrated CI/CD."
date: 2025-04-20
category: DevOps
author: Daniel Flores
locale: en
image: blog/nodejs-aws-docker
---

Deploying a Node.js application to production requires more than just uploading files to a server. AWS with Docker gives you scalability and reproducibility.

## The Optimal Dockerfile for Node.js

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

## AWS Architecture

```
CloudFront → ALB → ECS (Fargate) → RDS (PostgreSQL)
                    ↕
              ElastiCache Redis
```

## CI/CD with GitHub Actions

```yaml
jobs:
  deploy:
    steps:
      - run: docker build -t app .
      - run: aws ecs update-service --force-new-deployment
```

## Best Practices

1. **Health checks** on the load balancer
2. **Auto-scaling** based on CPU/memory
3. **Centralized logs** with CloudWatch
4. **Secrets** in AWS Secrets Manager, never in code

The result: deployments in under 2 minutes with zero downtime.
