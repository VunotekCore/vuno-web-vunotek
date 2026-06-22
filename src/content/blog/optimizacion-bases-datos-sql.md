---
title: "Optimización de Bases de Datos SQL Server: Índices, Planes de Ejecución y Buenas Prácticas"
excerpt: "Guía práctica para optimizar consultas SQL Server usando índices estratégicos, análisis de planes de ejecución y patrones de modelado eficientes."
date: 2025-06-01
category: Backend
author: Daniel Flores
locale: es
image: blog/sql-server-optimization
---

La optimización de bases de datos es una de las habilidades más rentables en ingeniería de software. Una consulta mal optimizada puede convertir un sistema rápido en una experiencia frustrante.

## Identificando Cuellos de Botella

### Planes de Ejecución

El plan de ejecución es tu mejor aliado. Muestra exactamente cómo SQL Server procesa una consulta:

```sql
SET STATISTICS IO ON;
SET STATISTICS TIME ON;

SELECT * FROM Ventas WHERE Fecha >= '2025-01-01';
```

Lo que buscamos:
- **Table Scan** → falta índice
- **Key Lookup** → índice no cubre todas las columnas
- **Spools** → operaciones costosas en tempdb

## Estrategia de Indexación

No se trata de agregar índices por todos lados, sino de poner los correctos:

```sql
-- Índice compuesto para consultas de rango + filtro
CREATE INDEX IX_Ventas_Fecha_Cliente
ON Ventas (Fecha, ClienteId)
INCLUDE (Total, Estado);
```

## Buenas Prácticas

1. **Fragmentación**: Mantener índices con fragmentación < 30%
2. **Estadísticas actualizadas**: `UPDATE STATISTICS` después de cambios masivos
3. **Evitar SELECT ***: Siempre especificar columnas
4. **Particionamiento**: Para tablas > 50 millones de registros

La clave está en medir antes y después de cada cambio. Sin métricas, no hay optimización.
