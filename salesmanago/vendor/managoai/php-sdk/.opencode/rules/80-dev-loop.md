# Dev Loop

## Bug Fix

1. Identify the failing contract, method, or flow.
2. Trace the narrowest relevant path.
3. Implement the smallest fix.
4. Add or update focused coverage.
5. Run static analysis first, then the narrowest test.

## New Feature

1. Inspect the closest existing implementation.
2. Choose the nearest namespace and pattern.
3. Keep the change additive when possible.
4. Validate the touched flow with focused checks.

## Refactor

1. Keep behavior unchanged unless the task says otherwise.
2. Protect public API and contract-sensitive arrays.
3. Limit refactor scope to the touched area.
4. Use narrow validation plus shared static analysis.

## Contract Review

1. Identify whether the surface is public API, contract-sensitive array, or internal detail.
2. List renamed, removed, added, or retyped fields or method surfaces.
3. Check exception, default, and omission-rule changes.
4. Report compatibility risk explicitly.
