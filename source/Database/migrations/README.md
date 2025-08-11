# Database Migrations

This directory contains Phinx database migration files. Migrations are used to version control your database schema changes.

## Creating a Migration

From the source directory, run:

```bash
vendor/bin/phinx create MyMigrationName
```

This will create a new migration file in this directory with a timestamp prefix.

## Running Migrations

To run all pending migrations:

```bash
vendor/bin/phinx migrate
```

To rollback the last migration:

```bash
vendor/bin/phinx rollback
```

## Migration Structure

Each migration file should extend `Phinx\Migration\AbstractMigration` and implement:

-   `up()` method: Define the changes to apply
-   `down()` method: Define how to reverse the changes

## Example Migration

```php
<?php

use Phinx\Migration\AbstractMigration;

class CreateUsersTable extends AbstractMigration
{
    public function up()
    {
        $table = $this->table('users');
        $table->addColumn('name', 'string')
              ->addColumn('email', 'string')
              ->addColumn('created_at', 'datetime')
              ->addColumn('updated_at', 'datetime')
              ->addIndex(['email'], ['unique' => true])
              ->create();
    }

    public function down()
    {
        $this->table('users')->drop()->save();
    }
}
```
