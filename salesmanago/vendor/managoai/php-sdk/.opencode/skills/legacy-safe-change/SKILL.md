---
name: legacy-safe-change
description: Use for routine changes in legacy or mixed-style library code where the main goal is a minimal safe diff with controlled compatibility risk.
compatibility: opencode
metadata:
  domain: php-library
  repo: api-sso-util
  workflow: legacy-safe-change
---

# Legacy Safe Change

Use this skill when the touched area is legacy, shared, or hard to isolate.

## Required workflow

1. Identify the real caller or trigger.
2. Trace the touched path and nearby shared consumers.
3. Choose the smallest viable change.
4. Separate required edits from optional cleanup.
5. End with focused validation and compatibility notes.

## Preferred change order

1. Guard clause or narrow condition fix
2. Small local helper for decision logic or normalization
3. Small internal method split
4. Larger refactor only if explicitly justified

## Output format

1. Traced path
2. Minimal change
3. Compatibility risk
4. Optional follow-ups
5. Validation plan
