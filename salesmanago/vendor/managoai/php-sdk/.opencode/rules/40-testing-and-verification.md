# Testing And Verification

- Default workflow: static analysis first, then the narrowest relevant test.
- Prefer path-scoped PHPUnit runs over broad suites for isolated changes.
- Prefer direct file or method filters for unit-level changes.
- Use `vendor/bin/phpstan analyse -l 0 src/ tests/` as the default shared validation when practical.
- Do not run integration-style feature tests that require missing env vars.
- If credentials or endpoints are missing, report the skipped verification clearly instead of guessing.
- Do not claim unrun checks as complete.
- If the change is config- or contract-sensitive, explain why the chosen validation scope is sufficient or limited.
