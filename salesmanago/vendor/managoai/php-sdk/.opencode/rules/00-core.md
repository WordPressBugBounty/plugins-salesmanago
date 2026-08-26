# Core

- Start with `AGENTS.md`, then use these rules for task-specific behavior.
- Treat this repository as a PHP library with compatibility-sensitive public surfaces.
- Prefer the smallest safe change that fits the current architecture.
- Read the nearest existing implementation and test before editing.
- Trace the path before changing behavior: caller -> mapper/builder -> service -> serializer/output.
- End every task with concrete validation notes or a clear explanation of what could not be verified.
- Do not invent secrets, endpoints, tenant data, or fallback configuration values.
- If a change affects external behavior, explicitly call out the risk and the affected contract.
