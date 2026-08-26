# API V3 Flows

- Inspect existing API V3 auth, request, mapping, and exception patterns before editing.
- Trace the full flow when changing API V3 behavior:
  - configuration source
  - auth setup
  - request builder or mapper
  - service call
  - response handling
  - consumer behavior
- Do not assume `ApiV3Key` or `ApiV3Endpoint` exists in the environment.
- Reuse current error handling conventions and exception types where possible.
- Avoid duplicating cross-cutting API client behavior in multiple services.
- Be explicit about whether a change affects request shape, auth setup, or response interpretation.
