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

License
-------

This work is released under the MIT License. See LICENSE.md for details.
