# Phinx Migration Commands Quick Reference

## Configuration

-   Configuration file: `phinx.php`
-   Migrations directory: `Database/migrations/`
-   Seeds directory: `Database/seeds/`

## Migration Commands

### Check Status

```bash
vendor/bin/phinx status
```

### Create Migration

```bash
vendor/bin/phinx create CreateUsersTable
vendor/bin/phinx create AddEmailToUsers
vendor/bin/phinx create CreatePostsTable
```

### Run Migrations

```bash
# Run all pending migrations
vendor/bin/phinx migrate

# Run to specific version
vendor/bin/phinx migrate -t 20231201120000

# Run migrations for specific environment
vendor/bin/phinx migrate -e production
```

### Rollback Migrations

```bash
# Rollback last migration
vendor/bin/phinx rollback

# Rollback to specific version
vendor/bin/phinx rollback -t 20231201120000

# Rollback all migrations
vendor/bin/phinx rollback -t 0
```

## Seed Commands

### Create Seeder

```bash
vendor/bin/phinx seed:create UserSeeder
vendor/bin/phinx seed:create PostSeeder
```

### Run Seeds

```bash
# Run all seeders
vendor/bin/phinx seed:run

# Run specific seeder
vendor/bin/phinx seed:run -s UserSeeder

# Run seeders for specific environment
vendor/bin/phinx seed:run -e development
```

## Environment Management

### Available Environments

-   `development` (default)
-   `testing`
-   `production`

### Specify Environment

Add `-e environment_name` to any command:

```bash
vendor/bin/phinx migrate -e production
vendor/bin/phinx status -e testing
```

## Environment Variables

Configure these in your `.env` file:

-   `DB_HOST` - Database host (default: localhost)
-   `DB_PORT` - Database port (default: 3306)
-   `DB_NAME` - Database name
-   `DB_USER` - Database username
-   `DB_PASS` - Database password
