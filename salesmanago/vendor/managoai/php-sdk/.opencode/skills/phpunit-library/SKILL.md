---
name: phpunit-library
description: Use when selecting or running PHPUnit validation in this repository.
compatibility: opencode
metadata:
  domain: testing
  repo: api-sso-util
---

# PHPUnit Library

- Static analysis first, then the narrowest relevant PHPUnit command.
- Prefer path-based unit test execution with `phpunit.xml`.
- Use method filters for narrow regressions when practical.
- Do not rely on missing env vars or legacy local credentials.
- Report skipped feature validation clearly.
