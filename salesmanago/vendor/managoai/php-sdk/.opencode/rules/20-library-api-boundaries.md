# Library API Boundaries

- Treat public classes, public methods, constructor parameters, constants, and thrown exception types as compatibility-sensitive.
- Treat the following as contract-sensitive arrays:
  - API V3 request payloads
  - export-related arrays
  - auth/config arrays
- Renaming or removing keys in contract-sensitive arrays requires explicit review.
- Changing defaults, conditional population rules, or nullability in contract-sensitive arrays requires explicit review.
- Internal arrays may change more freely only after confirming they do not feed stable builders, serializers, requests, or exports.
- Prefer additive changes over breaking changes.
- If a breaking change is intentional or unavoidable, report it clearly.
