# Why kununu/data-fixtures

At kununu we have the need to easily load fixtures for any of the storages that our services usually rely on: MySQL, Elasticsearch, Memcached, etc.

We looked into community packages and there are definitely great options however we realized that they do not fulfill all of our requirements:

- **We don't want to depend on Doctrine ORM**
  - [Doctrine ORM](https://www.doctrine-project.org/projects/orm.html) it's great, still in order to ease the writing of the fixtures we can easily end up with Anemic Domain Models with a bunch of setters in our entities.
  - Another point that we had into consideration is that we have services that do not rely on Doctrine ORM and use the Doctrine DBAL directly.
  - With this two points we had no other choice than drop [doctrine/data-fixtures](https://github.com/doctrine/data-fixtures) as an option.
- **Database schema is not touched when loading fixtures**
  - When we use Doctrine ORM we map our entities and generate migrations using Doctrine Migrations out of those mappings.
  - Still, we have cases which Doctrine ORM does not support, like Virtual Columns, and as such we cannot recreate the database easily when we load fixtures.
  - With this point we had no other choice than drop [liip/LiipTestFixturesBundle](https://github.com/liip/LiipTestFixturesBundle) as an option.
- **We really want to hit the database**
  - The approach of the package [dmaicher/doctrine-test-bundle](https://github.com/dmaicher/doctrine-test-bundle) is to begin a transaction before every test case and roll it back again after the test finishes.
  - This is definitely a valid approach, still by actually hit the database we are sure we cover the whole integration even if it means a small performance penalty.
- **We would like to have the same approach for all storages**
  - No matter the storage that we load fixtures into, we want to have the same behavior making it easier to reason about.

---

[Back to Index](../README.md)
