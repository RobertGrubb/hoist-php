# Database Overview

Hoist PHP provides a dual-database architecture that combines the power of Medoo ORM for SQL databases with a JSON-based FileDatabase system for development and lightweight data storage.

## Database Architecture

### Primary Database: Medoo ORM

Hoist PHP uses **Medoo** as its primary database layer, providing:

-   **MySQL/MariaDB Support** - Production-ready relational database support
-   **Prepared Statements** - Built-in SQL injection protection
-   **Simple Query Interface** - Intuitive methods for database operations
-   **Lightweight** - Minimal overhead with maximum functionality

### Secondary Database: FileDatabase

For development, testing, and lightweight applications:

-   **JSON File Storage** - Human-readable data files
-   **No Database Setup** - Works out of the box without configuration
-   **SQL-like Interface** - Similar query patterns to traditional databases
-   **Version Control Friendly** - JSON files can be tracked in git

## Getting Started

### Accessing Databases

In controllers and models, access databases through the instance container:

```php
class UserController extends Controller
{
    public function index()
    {
        // Primary database (Medoo)
        $users = $this->instance->database->client->select('users', '*', [
            'status' => 'active'
        ]);

        // File database
        $users = $this->instance->fileDatabase
            ->table('users')
            ->where('status', '=', 'active')
            ->all();
    }
}
```

### Database Configuration

The Database class wraps Medoo with connection management:

```php
// Accessing the Medoo client directly
$medoo = $this->instance->database->client;

// Standard Medoo operations
$users = $medoo->select('users', '*', ['status' => 'active']);
$userId = $medoo->insert('users', ['name' => 'John', 'email' => 'john@example.com']);
$medoo->update('users', ['status' => 'inactive'], ['id' => $userId]);
$medoo->delete('users', ['status' => 'inactive']);
```

## FileDatabase Operations

The FileDatabase provides a comprehensive query interface for JSON file storage:

### Basic Operations

```php
$fileDb = $this->instance->fileDatabase;

// Select all active users
$users = $fileDb->table('users')
    ->where('status', '=', 'active')
    ->all();

// Get a single user by ID
$user = $fileDb->table('users')
    ->where('id', '=', 123)
    ->first();

// Get the newest user
$newest = $fileDb->table('users')
    ->order('created_at', 'DESC')
    ->first();

// Count active users
$activeUsers = $fileDb->table('users')
    ->where('status', '=', 'active')
    ->all();
$count = count($activeUsers);
```

### Data Modification

```php
// Insert new user
$userId = $fileDb->table('users')->insert([
    'name' => 'Jane Doe',
    'email' => 'jane@example.com',
    'status' => 'active',
    'created_at' => time()
]);

// Update existing user
$affected = $fileDb->table('users')
    ->where('id', '=', $userId)
    ->update([
        'last_login' => time(),
        'status' => 'verified'
    ]);
```

### Supported Operators

FileDatabase supports comprehensive filtering:

```php
// Equality and inequality
$fileDb->table('products')->where('price', '=', 99.99)->all();
$fileDb->table('products')->where('status', '!=', 'disabled')->all();

// Comparison operators
$fileDb->table('products')->where('price', '>', 50)->all();
$fileDb->table('products')->where('price', '<=', 100)->all();

// String matching
$fileDb->table('products')->where('name', 'LIKE', 'Phone')->all();

// Chaining conditions (AND logic)
$fileDb->table('products')
    ->where('category', '=', 'electronics')
    ->where('price', '<', 200)
    ->where('in_stock', '=', true)
    ->all();
```

### Sorting and Limiting

```php
// Sort by name ascending
$products = $fileDb->table('products')
    ->order('name', 'ASC')
    ->all();

// Sort by price descending
$expensive = $fileDb->table('products')
    ->order('price', 'DESC')
    ->all();

// Limit results (useful for pagination)
$recent = $fileDb->table('orders')
    ->order('created_at', 'DESC')
    ->all(10); // Get 10 most recent orders
```

## Medoo Integration

Access Medoo's full capabilities through the database client:

### Select Queries

```php
$medoo = $this->instance->database->client;

// Basic select
$users = $medoo->select('users', '*', ['status' => 'active']);

// Select specific fields
$users = $medoo->select('users', ['id', 'name', 'email'], ['status' => 'active']);

// Complex conditions
$users = $medoo->select('users', '*', [
    'AND' => [
        'status' => 'active',
        'age[>]' => 18,
        'city' => ['New York', 'Los Angeles', 'Chicago']
    ],
    'ORDER' => ['created_at' => 'DESC'],
    'LIMIT' => 50
]);
```

### Data Modification

```php
// Insert single record
$userId = $medoo->insert('users', [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'status' => 'active'
]);

// Insert multiple records
$medoo->insert('users', [
    ['name' => 'User 1', 'email' => 'user1@example.com'],
    ['name' => 'User 2', 'email' => 'user2@example.com']
]);

// Update records
$medoo->update('users',
    ['status' => 'verified'],
    ['id' => $userId]
);

// Delete records
$medoo->delete('users', ['status' => 'inactive']);
```

### Joins and Advanced Queries

```php
// Inner join
$data = $medoo->select('users', [
    '[>]orders' => ['id' => 'user_id']
], [
    'users.name',
    'orders.total',
    'orders.status'
], [
    'users.status' => 'active'
]);

// Count records
$count = $medoo->count('users', ['status' => 'active']);

// Check if record exists
$exists = $medoo->has('users', ['email' => 'test@example.com']);
```

## File Organization

### FileDatabase Structure

```
Application/
  Database/
    app/                # Database name (configurable)
      users.json        # Users table
      products.json     # Products table
      orders.json       # Orders table
```

### JSON File Format

```json
[
    {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "status": "active",
        "created_at": 1704067200
    },
    {
        "id": 2,
        "name": "Jane Smith",
        "email": "jane@example.com",
        "status": "active",
        "created_at": 1704153600
    }
]
```

## Choosing Between Databases

### Use FileDatabase When:

-   **Development/Testing** - Quick setup without database configuration
-   **Prototyping** - Rapid development and iteration
-   **Small Applications** - Limited data requirements
-   **Configuration Storage** - Application settings and preferences
-   **Mock Data** - Testing with predefined datasets

### Use Medoo/MySQL When:

-   **Production Applications** - Scalable, reliable data storage
-   **Complex Queries** - Advanced SQL features and joins
-   **Large Datasets** - Performance requirements
-   **Multi-user Systems** - Concurrent access and transactions
-   **Data Integrity** - Foreign keys and constraints

## Best Practices

### Database Selection

```php
class UserModel extends Model
{
    public $table = 'users';

    public function getActiveUsers()
    {
        if (ENVIRONMENT === 'development') {
            // Use FileDatabase for development
            return $this->fileDatabase
                ->table($this->table)
                ->where('status', '=', 'active')
                ->all();
        } else {
            // Use MySQL for production
            return $this->database->client->select($this->table, '*', [
                'status' => 'active'
            ]);
        }
    }
}
```

### Error Handling

```php
try {
    $userId = $this->instance->database->client->insert('users', $userData);
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    return false;
}
```

### Data Validation

```php
// Always validate data before database operations
$userData = [
    'name' => trim($_POST['name']),
    'email' => filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)
];

if (empty($userData['name']) || !$userData['email']) {
    throw new Exception('Invalid user data');
}

$userId = $this->instance->database->client->insert('users', $userData);
```

## Summary

Hoist PHP's database architecture provides flexibility without complexity:

-   **Medoo ORM** handles production database needs with security and performance
-   **FileDatabase** enables rapid development and prototyping
-   **Consistent API** across both systems for easy switching
-   **No query builder complexity** - direct, simple database operations

For more information, see the [Medoo documentation](https://medoo.in/) for advanced SQL features.
