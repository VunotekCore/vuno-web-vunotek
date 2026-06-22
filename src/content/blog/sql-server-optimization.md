---
title: "SQL Server Optimization: Indexes, Execution Plans, and Best Practices"
excerpt: "A practical guide to optimizing SQL Server queries using strategic indexing, execution plan analysis, and efficient modeling patterns."
date: 2025-06-01
category: Backend
author: Daniel Flores
locale: en
image: blog/sql-server-optimization
---

Database optimization is one of the most valuable skills in software engineering. A poorly optimized query can turn a fast system into a frustrating experience.

## Identifying Bottlenecks

### Execution Plans

The execution plan is your best ally. It shows exactly how SQL Server processes a query:

```sql
SET STATISTICS IO ON;
SET STATISTICS TIME ON;

SELECT * FROM Sales WHERE Date >= '2025-01-01';
```

What to look for:
- **Table Scan** → missing index
- **Key Lookup** → index doesn't cover all columns
- **Spools** → expensive tempdb operations

## Indexing Strategy

It's not about adding indexes everywhere, but putting the right ones in place:

```sql
-- Composite index for range + filter queries
CREATE INDEX IX_Sales_Date_Customer
ON Sales (Date, CustomerId)
INCLUDE (Total, Status);
```

## Best Practices

1. **Fragmentation**: Keep index fragmentation below 30%
2. **Updated Statistics**: Run `UPDATE STATISTICS` after bulk changes
3. **Avoid SELECT ***: Always specify columns
4. **Partitioning**: For tables over 50 million rows

The key is measuring before and after every change. Without metrics, there is no optimization.
