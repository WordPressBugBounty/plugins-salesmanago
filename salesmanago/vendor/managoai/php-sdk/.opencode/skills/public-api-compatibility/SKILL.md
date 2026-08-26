---
name: public-api-compatibility
description: Use when a change may affect public methods, exception contracts, constants, or contract-sensitive arrays.
compatibility: opencode
metadata:
  domain: compatibility
  repo: api-sso-util
---

# Public API Compatibility

- Treat public methods and selected contract arrays as review surfaces.
- Check signature changes, constructor changes, return shape changes, exception changes, and default changes.
- Distinguish additive changes from breaking changes.
- Call out risk even when the code change looks small.

## Output format

1. Surface reviewed
2. Potential contract changes
3. Compatibility risk level
4. Safer alternatives if needed
