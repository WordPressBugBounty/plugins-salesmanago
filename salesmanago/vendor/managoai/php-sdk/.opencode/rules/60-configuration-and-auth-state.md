# Configuration And Auth State

- Treat singleton-style configuration and shared mutable state as regression hotspots.
- Reset shared configuration in tests when a test mutates API keys, endpoints, auth flags, or related state.
- Trace auth/config arrays end to end before changing defaults or propagation logic.
- Do not log or expose API keys, credentials, or raw auth material.
- Preserve existing precedence rules between explicit config values, defaults, and absent values unless the task explicitly changes them.
- When in doubt, prefer local guards over changing shared configuration semantics.
