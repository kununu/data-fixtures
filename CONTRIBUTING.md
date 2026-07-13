# Contributing

Contributions are more than **welcome**.

We accept contributions via Pull Requests on [GitHub](https://github.com/kununu/data-fixtures).

## Development setup

Install the Composer dependencies:

```shell
composer install
```

This installs the development tooling, including [kununu/code-tools](https://github.com/kununu/code-tools),
which exposes the commands used to meet our coding standards.

## Quality gates

Quality gates are defined as Composer scripts in `composer.json` (`scripts`).
Run them before opening a pull request:

- `composer test` — run the test suite
- `composer test-coverage` — run the test suite with a coverage report
- `composer phpstan` — static analysis
- `composer cs` — coding-standards check (PHP CS Fixer, kununu standards)
- `composer sniffer` — PHP_CodeSniffer (`composer sniffer-fix` to auto-fix)
- `composer rector` — Rector in dry-run mode (`composer rector-fix` to apply)

## Pull requests

- **[kununu Coding Standards](https://github.com/kununu/code-tools/blob/main/dist/php-cs-fixer.php.dist)**
  - The kununu coding standards extend [PSR-2](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md).
  - See [kununu/code-tools](https://github.com/kununu/code-tools) for details.

- **Add tests!**
  - To ensure a high-quality code base, patches without tests cannot be accepted.

- **Document any change in behaviour**
  - Keep `README.md` and any other relevant documentation up-to-date.

- **Consider our release cycle**
  - We use semantic versioning ([SemVer 2.0.0](https://semver.org/)). Changes to
    the public API must be made with great consideration and prevented if possible.

- **Create feature branches**
  - `main` is the stable branch; create a new branch for each feature.

- **One pull request per feature**
  - If you want to do more than one thing, send multiple pull requests.

- **Be respectful**
  - Be excellent to other contributors. See our [Code of Conduct](CODE_OF_CONDUCT.md).

**Happy coding**!

---

[Back to Index](README.md)
