# Database Seeds

This directory contains Phinx database seeder files. Seeds are used to populate your database with test or initial data.

## Creating a Seeder

From the source directory, run:

```bash
vendor/bin/phinx seed:create MySeederName
```

This will create a new seeder file in this directory.

## Running Seeds

To run all seeders:

```bash
vendor/bin/phinx seed:run
```

To run a specific seeder:

```bash
vendor/bin/phinx seed:run -s MySeederName
```

## Seeder Structure

Each seeder file should extend `Phinx\Seed\AbstractSeed` and implement:

-   `run()` method: Define the data to insert

## Example Seeder

```php
<?php

use Phinx\Seed\AbstractSeed;

class UserSeeder extends AbstractSeed
{
    public function run()
    {
        $data = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];

        $users = $this->table('users');
        $users->insert($data)
              ->save();
    }
}
```
