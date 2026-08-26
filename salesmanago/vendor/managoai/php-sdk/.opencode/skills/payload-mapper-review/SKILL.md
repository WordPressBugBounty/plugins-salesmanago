---
name: payload-mapper-review
description: Use when reviewing or changing mappers, builders, serializers, normalizers, or contract-sensitive array shapes.
compatibility: opencode
metadata:
  domain: payloads
  repo: api-sso-util
---

# Payload Mapper Review

- Identify source fields, transformation steps, and final shape.
- Classify the output as internal or contract-sensitive.
- Review renamed, removed, added, or retyped fields.
- Review default values, omission rules, and null handling.
- Prefer focused mapper or serializer tests when practical.

## Output format

1. Source to output path
2. Contract classification
3. Shape and default risks
4. Minimal safe change
5. Validation plan
