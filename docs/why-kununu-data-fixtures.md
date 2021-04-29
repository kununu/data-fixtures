# Why kununu/data-fixtures
----------------------------------

At kununu we have the need to easily load fixtures for any of the storages that our services usually rely on: MySQL, Elasticsearch, Memcached, etc. We looked into community packages and there are definitily great options however we realized that they do not fullfill our requirements.

One example of a great alternative is the [doctrine data-fixtures](https://github.com/doctrine/data-fixtures) package.  Although it is a great package, it requires you to have a an ObjectManager in place, for example, by using the their [ORM](https://github.com/doctrine/orm).
In some kununu projects we only use the [Doctrine DBAL](https://github.com/doctrine/dbal) so that means that we are not able to use the Doctrine data-fixtures package. Another reason that led us to not going for this package was that in order to ease the writing of the fixtures we would end up with [anemic domain models](https://www.martinfowler.com/bliki/AnemicDomainModel.html) with a bunch of setters in our entities.

Also, the need to have data fixtures for other storages, for example Elasticsearch and Memcached, urged the need to build this package.