---
name: configuration-state-safety
description: Use when changing shared configuration, auth setup, defaults, or stateful flows that can leak between services or tests.
compatibility: opencode
metadata:
  domain: configuration
  repo: api-sso-util
---

# Configuration State Safety

- Trace auth/config arrays end to end.
- Identify precedence and fallback rules before editing.
- Review test leakage risk from shared mutable configuration.
- Prefer local guards over shared semantic changes when possible.
- Never expose or log credentials.
