---
name: export-flow-review
description: Use when changing export-related arrays, transfer outputs, or downstream export contracts.
compatibility: opencode
metadata:
  domain: exports
  repo: api-sso-util
---

# Export Flow Review

- Identify every producer and consumer of the export-related array.
- Treat key names, optional fields, and grouping rules as contract-sensitive.
- Review whether the change affects transfer semantics or only internal preparation.
- Prefer isolated validation around the touched builder or serializer.
