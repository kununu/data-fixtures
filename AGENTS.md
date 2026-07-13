# AGENTS.md

## What this is

`kununu/data-fixtures` is a PHP library that loads data fixtures into any storage
mechanism for use in tests and development environments. It is installed as a
Composer package (`kununu/data-fixtures`) and is not intended for production use.

Its design is based on the [Doctrine data-fixtures](https://github.com/doctrine/data-fixtures)
package.

## Domain

The library provides loaders, executors and purgers that manage fixture classes
implementing `FixtureInterface`. Fixtures are loaded from a directory, file or
class name, then executed against a storage backend. Supported storage types:
Doctrine DBAL connections, PSR-6 cache pools, DynamoDB, Elasticsearch, OpenSearch
and the Symfony HTTP Client.

## Code layout

- `src/` — library source (PSR-4 `Kununu\DataFixtures\`)
  - `Adapter/` — storage-specific fixture interfaces
  - `Executor/` — executors that run fixtures against a backend
  - `Loader/` — loaders that discover and register fixtures
  - `Purger/` — purgers that clear a storage backend before loading
  - `Exception/` — library exceptions
  - `Tools/` — supporting utilities
  - `FixtureInterface.php`, `InitializableFixtureInterface.php` — core contracts
- `tests/` — PHPUnit tests (PSR-4 `Kununu\DataFixtures\Tests\`)
- `docs/` — fixture-type guides and design notes

## Quality gates

Run via Composer scripts (defined in `composer.json` `scripts`):

- `composer test` — run the test suite
- `composer phpstan` — static analysis
- `composer cs` — coding-standards check (PHP CS Fixer, kununu standards)
- `composer sniffer` — PHP_CodeSniffer
- `composer rector` — Rector in dry-run mode

CI runs on `.github/workflows/continuous-integration.yml`; quality is also gated
by SonarCloud.

## Hard constraints

- PHP version is pinned in `composer.json` (`require.php`).
- Public API changes follow semantic versioning ([SemVer 2.0.0](https://semver.org/)).
- All code changes require tests.
- This package must not be used in production mode; consumers own the fixtures
  they load.
