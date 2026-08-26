# AGENTS.md

Guidance for coding agents working in `api-sso-util`.

## Repo Snapshot
- Language: PHP
- Package type: Composer library
- Minimum PHP: `>=7.4`
- Source root: `src/`
- Test root: `tests/`
- Main namespace: `SALESmanago\`
- Test namespace: `Tests\`
- Tooling: PHPUnit, PHPStan, PHPCS with PHPCompatibility
- CI file: `bitbucket-pipelines.yml`

## Start Here
- Use this file for the repository overview and operating assumptions.
- Load detailed workflow guidance from `.opencode/rules/` through `opencode.json`.
- Use `.opencode/skills/` for task-specific working modes.
- Use `.opencode/commands/` for reusable plan, trace, compatibility-review, and verification flows.

## Instruction Files
- No previous `AGENTS.md` existed.
- No `.cursor/rules/` directory found.
- No `.cursorrules` file found.
- No `.github/copilot-instructions.md` file found.

## Setup
Install dependencies first:

```bash
composer install
```

CI runs `composer install` before each validation step.

## Build / Package
There is no dedicated build or packaging step.
Treat this as a library repo where the main checks are dependency install, tests, static analysis, and compatibility checks.

## Test Commands
Composer scripts from `composer.json`:

```bash
composer test-features
composer test-unit
composer test-unit-coverege
composer test-api-v3
```

- `test-unit-coverege` is misspelled in the repo; use the exact name.
- `composer test-features` runs `tests/Feature`.
- `composer test-unit` runs `tests/Unit` by path.
- `composer test-api-v3` runs the `FeatureApiV3` testsuite.

Useful direct PHPUnit commands:

```bash
./vendor/bin/phpunit --configuration=phpunit.xml tests/Feature
./vendor/bin/phpunit --configuration=phpunit.xml tests/Unit
./vendor/bin/phpunit --configuration=phpunit.xml --testsuite=FeatureApiV2
./vendor/bin/phpunit --configuration=phpunit.xml --testsuite=FeatureApiV3
```

## Single Test Runs
Run one file:

```bash
./vendor/bin/phpunit --configuration=phpunit.xml tests/Unit/Helper/Mapper/BuilderTest.php
./vendor/bin/phpunit --configuration=phpunit.xml tests/Feature/User/Vendor/LoginTest.php
```

Run one test method:

```bash
./vendor/bin/phpunit --configuration=phpunit.xml --filter testMethodName path/to/Test.php
```

```bash
./vendor/bin/phpunit --configuration=phpunit.xml --filter testDoNotCallSetApiKeyToConfigurationIfApiV3KeyNotPresent tests/Feature/Services/Api/V3/AuthServiceTest.php
./vendor/bin/phpunit --configuration=phpunit.xml --filter testLogin tests/Feature/User/Vendor/LoginTest.php
./vendor/bin/phpunit --configuration=phpunit.xml --testsuite=FeatureApiV3 --filter ProductServiceTest
```

Important caveat:
- `phpunit.xml` does not define a `Unit` testsuite.
- CI still contains `--testsuite Unit`.
- For unit tests, prefer path-based invocation with `phpunit.xml`.

## Test Environment
Feature tests may require these env vars:
- `userEmail`
- `userPass`
- `appEndpoint`
- `emailId`
- `ApiV3Endpoint`
- `ApiV3Key`

Rules:
- Do not invent secrets.
- Do not overwrite user-provided credentials.
- Do not rely on `dev.phpunit.xml`; it contains environment-specific legacy credentials.
- If credentials are missing, prefer unit tests or report that the feature test could not be run.

## Static Analysis / Lint

```bash
vendor/bin/phpstan analyse -l 0 src/ tests/
vendor/bin/phpstan analyse --memory-limit=1G --error-format=table --configuration=phpstan-deprecation.neon
vendor/bin/phpstan analyse --configuration=phpstan-804.neon --memory-limit=1G --error-format=table
vendor/bin/phpcs --config-set installed_paths vendor/phpcompatibility/php-compatibility
vendor/bin/phpcs -p --standard=PHPCompatibility --runtime-set testVersion=8.4- --extensions=php --ignore=vendor,build,tmp,storage src
```

## Architecture and Layout
- Keep PSR-4 path and namespace alignment.
- Production code lives under `src/` with `SALESmanago\...` namespaces.
- Tests live under `tests/` with `Tests\...` namespaces.
- The common layering is controllers -> services -> models/entities/helpers.
- Entities and collections are mutable and setter-driven.
- Do not refactor entities into immutable value objects unless asked.

## Formatting
- Use 4 spaces for indentation.
- Preserve the local style of the file you touch.
- The repo is not consistently PSR-12; avoid broad formatting-only rewrites.
- Brace placement is mixed; match the surrounding file.
- Keep docblocks where the file already uses them.
- Do not add `declare(strict_types=1)` opportunistically.

## Imports, Types, and Naming
- Put `use` statements below the `namespace` declaration.
- Match the local import style instead of reordering the whole file.
- Built-in classes may be imported or fully qualified; stay consistent within the file.
- Remove redundant imports only when you are already cleaning the file and are sure they are unused.
- Older files rely on PHPDoc and untyped properties or parameters.
- Newer files sometimes use native parameter, property, and return types.
- Prefer the style already established in the file you are editing.
- Avoid sweeping type-system changes across unrelated files.
- In legacy code, PHPDoc may be less disruptive than adding native types.
- Classes, interfaces, and traits: `PascalCase`
- Methods: `camelCase`
- Constants: `UPPER_SNAKE_CASE`
- Test methods: `test...`
- Preserve historical names such as `cUrlClientConfiguration` and `cUrlMultiConfigurationTrait`.
- For new locals, prefer descriptive `camelCase` names.

## Error Handling
- Prefer existing project exception types where nearby code already uses them.
- Common domain exceptions are `SALESmanago\Exception\Exception` and `SALESmanago\Exception\ApiV3Exception`.
- Preserve existing exception flow unless the task explicitly requires behavior changes.
- Be careful with code that swallows exceptions, returns `false`, or echoes errors; changing that can be breaking.
- Keep new error messages actionable and consistent with nearby code.

## Testing Style
- PHPUnit 9 is the framework.
- Tests are split into `Feature` and `Unit`.
- Shared bases include `tests/Feature/TestCaseFeature.php` and `tests/Unit/TestCaseUnit.php`.
- Faker is used in both feature and unit tests.
- Feature tests are often integration-style and may talk to real services.
- Clean up side effects when tests create external state.
- `phpstan-804.neon` disallows `var_dump`, `dd`, `die`, `exit`, `print_r`, `STDIN`, `STDOUT`, and `STDERR`.

## Repo Pitfalls
- Style drift between old and new files is normal.
- Some file paths and namespaces are inconsistent; do not assume they always match perfectly.
- Singleton-style global configuration is common, so tests can leak state if not reset carefully.
- Many entities use fluent setters and array-based serialization; preserve those patterns.
- Minimize drive-by refactors while doing task-focused work.

## Core Working Rules
- Make the smallest change that fits the current architecture.
- Prefer compatibility over modernization unless the task explicitly asks for a refactor.
- Treat public methods, exception behavior, API V3 request payloads, export-related arrays, and auth/config arrays as review-sensitive.
- Prefer path-scoped validation and static analysis before broader checks.
- If env-dependent tests cannot run, report that clearly instead of guessing.

## Where Detailed Guidance Lives
- Workflow and safety rules: `.opencode/rules/`
- Task-specific working modes: `.opencode/skills/`
- Reusable commands: `.opencode/commands/`
- Specialized reviewers and tracers: `.opencode/agents/`

## Recommended Commands

| Scenario | Command |
|---|---|
| Plan a new addition | `/plan-feature` |
| Plan the smallest safe legacy change | `/legacy-change-plan` |
| Trace an API V3 request flow | `/trace-api-v3-flow` |
| Trace auth/config propagation | `/trace-auth` |
| Trace mapper or serializer output | `/trace-payload` |
| Review contact payload compatibility | `/trace-contact-payload` |
| Review event transfer behavior | `/review-event-transfer` |
| Review export contract impact | `/review-export-flow` |
| Review exception contract changes | `/review-exception-contract` |
| Review shared config/auth state | `/review-config-state` |
| Review PHP 7.4 compatibility | `/review-php74-compatibility` |
| Review BC risk | `/bc-review` |
| Pick the narrowest validation | `/test-plan` |
| Build a final verification checklist | `/verify-after` |

See `.opencode/rules/80-dev-loop.md` for the default workflow.
