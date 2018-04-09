Database Abstraction Layer mocking helpers for PHPUnit
======================================================

A mock-object library for database queries testing, without having to initialize in-memory
database from fixtures. Rather, every query executed by a tested code can be set to return
a pre-defined result set, affected rows count or last insert ID. All with a familiar interface
similar to PHPUnit Mock Objects.

Installation
------------

Pick your version! Version numbering follows major PHPUnit version numbers, so for a given
PHPUnit N.x, the installation command would look like this:

```sh
composer require --dev czukowski/phpunit-mock-db "~N.0"
```

Usage
-----

Use `Cz\PHPUnit\MockDB\MockTrait` trait in a test case class, this will enable methods for
creating database mock instances. A 'fake' driver for a database abstraction layer used in the
tested code must be used, which implements both `Cz\PHPUnit\MockDB\DatabaseDriverInterface`
interface and that of the database abstraction layer's, additionally `getDatabaseDriver` method
must be implemented by the test case class, that returns an instance of that driver.

Note: this trait doesn't consider more than one database connection to be in use. If it is required
to have multiple database connections mocked, a custom implementation should be used instead, that
either picks a correct database instance to assign the mock instance to, or one that doesn't assign
the mock instance to anywhere and leaves it up to the test method.

### Examples:

Return a pre-defined result set on _any_ database query:

```php
$this->createDatabaseMock()
    ->expects($this->any())
    ->willReturnResultSet([
        ['id' => 1, 'name' => 'foo'],
        ['id' => 2, 'name' => 'bar'],
    ]);
```

Return a pre-defined result set on _any_ database query and expect it to be executed exactly once:

```php
$this->createDatabaseMock()
    ->expects($this->once())
    ->willReturnResultSet([
        ['id' => 1, 'name' => 'foo'],
        ['id' => 2, 'name' => 'bar'],
    ]);
```

Return a pre-defined result set on each specific database query, expecting each query to be executed
exactly once (note: the order in which the queries are expected doesn't have to be same as the order
in which the queries are executed, also note that whitespace will be ignored in queries, so they can
be loaded from well-formatted files, which could be especially useful for long and complex queries):

```php
$mock = $this->createDatabaseMock();
$mock->expects($this->once())
    ->query('SELECT * FROM `t1`')
    ->willReturnResultSet([['id' => 1, 'name' => 'foo']]);
$mock->expects($this->once())
    ->query('SELECT * FROM `t2`')
    ->willReturnResultSet([['id' => 2, 'name' => 'bar']]);
```

Expect mixed queries, some at specific invocations (note: SELECT query is set to return an empty
result set):

```php
$mock = $this->createDatabaseMock();
$mock->expects($this->at(1))
    ->query('INSERT INTO `t1` VALUES (1, "foo")')
    ->willSetLastInsertId(1);
$mock->expects($this->at(2))
    ->query('INSERT INTO `t1` VALUES (2, "bar")')
    ->willSetLastInsertId(2);
$mock->expects($this->once())
    ->query('SELECT * FROM `t1`')
    ->willReturnResultSet([]);
```

Expect same query executed exactly three times and return different last insert IDs on each
consecutive call:

```php
$this->createDatabaseMock()
    ->expects($this->exactly(3))
    ->query('INSERT INTO `t1` VALUES ("a", "b", "c")')
    ->willSetLastInsertId(1, 2, 3);
```

Return affected rows count:

```php
$this->createDatabaseMock()
    ->expects($this->exactly(2))
    ->query('UPDATE `t1` SET `foo` = "bar" WHERE `id` = 1')
    ->willSetAffectedRows(1);
```

Match SQL query using PHPUnit constraint (note: whitespace will not be ignored when using default
PHPUnit constraints):

```php
$this->createDatabaseMock()
    ->expects($this->once())
    ->query($this->stringStartsWith('SELECT'))
    ->willReturnResultSet([['id' => 1, 'name' => 'foo']]);
```

Set up different outcomes on consecutive calls for INSERT queries using a consecutive calls stub
builder:

```php
$this->createDatabaseMock()
    ->expects($this->exactly(4))
    ->query($this->stringStartsWith('INSERT'))
    ->onConsecutiveCalls()
    ->willSetLastInsertId(1)
    ->willSetLastInsertId(2)
    ->willThrowException(new RuntimeException('Deadlock'))
    ->willSetLastInsertId(3);
```

Set up custom callbacks to handle database queries (callbacks don't have to return anything):

```php
$mock = $this->createDatabaseMock();
$mock->expects($this->any())
    ->query($this->stringStartsWith('INSERT'))
    ->willInvokeCallback(function ($invocation) {
        $invocation->setLastInsertId(1);
    });
$mock->expects($this->any())
    ->query($this->stringStartsWith('UPDATE'))
    ->willInvokeCallback(function ($invocation) {
        $invocation->setAffectedRows(0);
    });
$mock->expects($this->any())
    ->query($this->stringStartsWith('SELECT'))
    ->willInvokeCallback(function ($invocation) {
        $invocation->setResultSet([]);
    });
```

By default, mock object is set to throw an exception if an unknown (unmatched) query is executed,
but this can be disabled:

```php
$mock = $this->createDatabaseMock()
$mock->setRequireMatch(FALSE)
    ->expects(...)
```

License
-------

This work is released under the MIT License. See LICENSE.md for details.
