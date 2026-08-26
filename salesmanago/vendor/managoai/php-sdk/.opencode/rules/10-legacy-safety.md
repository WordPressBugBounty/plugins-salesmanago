# Legacy Safety

- This repo contains mixed legacy and newer PHP styles; preserve the local style of touched files.
- Prefer narrow fixes over broad refactors.
- Preserve historical names, mutable entities, fluent setters, and existing array-building patterns unless the task requires otherwise.
- Do not replace PHPDoc-heavy code with broad native-typing rewrites during routine changes.
- Do not normalize historical naming inconsistencies unless the task depends on it.
- When touching shared code, identify nearby consumers before changing control flow, defaults, or exception behavior.
- Split methods only when it reduces risk locally; avoid architecture-wide cleanup during feature work.
