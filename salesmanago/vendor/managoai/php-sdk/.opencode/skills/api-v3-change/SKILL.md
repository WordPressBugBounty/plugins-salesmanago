---
name: api-v3-change
description: Use when changing API V3 auth, payloads, request flow, response handling, or exception behavior.
compatibility: opencode
metadata:
  domain: api-v3
  repo: api-sso-util
---

# API V3 Change

- Follow `AGENTS.md` and `.opencode/rules/**/*.md`.
- Trace config/auth -> builder -> service -> response/error -> consumer before editing.
- Treat request payload keys and omission rules as compatibility-sensitive.
- Reuse existing exception and mapping patterns when possible.
- Do not assume integration env vars exist.
- Prefer path-scoped verification plus shared static analysis.
