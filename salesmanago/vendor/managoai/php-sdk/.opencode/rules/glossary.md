# Glossary

- `API V3 request payloads`: arrays ultimately sent to the API V3 endpoint.
- `auth/config arrays`: arrays carrying credentials, endpoints, auth settings, or related configuration that influence request setup.
- `export-related arrays`: arrays produced for export, transfer, or downstream consumption outside a narrow internal implementation detail.
- `contract-sensitive`: stable enough that key removals, renames, default changes, or omission-rule changes require explicit review.
- `internal array`: helper-level array not relied on across stable library boundaries.
- `shared configuration`: mutable configuration state reused across multiple services or requests.
- `legacy-safe change`: a minimal change that preserves historical behavior and avoids opportunistic modernization.
