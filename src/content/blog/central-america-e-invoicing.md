---
title: "E-Invoicing in Central America: Challenges and Solutions"
excerpt: "Integrating e-invoicing systems for Costa Rica, Guatemala, and El Salvador through APIs adapted to each regulation."
date: 2025-01-20
category: Integration
author: Daniel Flores
locale: en
image: blog/e-invoicing-central-america
---

Electronic invoicing in Central America presents unique challenges that require tailored solutions.

## Regional Context

Each Central American country has its own regulatory body and e-invoicing format:

- **Costa Rica**: Ministry of Finance - Electronic Invoice (FEC)
- **Guatemala**: SAT - Electronic Invoice (FEL)
- **El Salvador**: MH - Electronic Invoice (CFE)

## Our Implementation

We developed an abstraction layer that unifies the different formats and exposes a uniform API for the invoicing system:

```typescript
interface InvoicePayload {
  country: 'CR' | 'GT' | 'SV'
  document: FacturaElectronica
  customer: DatosCliente
  items: LineItem[]
}
```

## Key Takeaways

- Real-time validation with government agencies reduces later rejections
- Tax code mapping must be configurable per country
- Electronic signatures require secure certificate handling
