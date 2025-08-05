# Database API Documentation

## Overview

The Database API provides optional MySQL/MariaDB connectivity with FileDatabase as the primary storage system. Built on the Medoo ORM, it offers secure query operations while maintaining graceful fallback to FileDatabase-only mode.

## Class: Database

**Location**: `Core/Libraries/Database.php`  
**Dependencies**: Medoo ORM  
**Access**: Available as `$this->database` in controllers  
**Primary Storage**: FileDatabase (MySQL enhancement optional)

---

## Properties

### Public Properties

#### `$client`

-   **Type**: `Medoo|null`
-   **Description**: Active Medoo database client for query operations
-   **Value**: Medoo instance when connected, null in FileDatabase-only mode

#### `$isConnected`

-   **Type**: `bool`
-   **Description**: Database connection status flag
-   **Value**: True if MySQL connection active, false for FileDatabase-only mode

---

## Methods

### Constructor

#### `__construct($data = [])`

Initializes database connection with optional MySQL support.

**Parameters:**

-   `$data` (array): Optional database connection configuration

**Configuration Parameters:**

-   `host` (string): Database server hostname (required for MySQL)
-   `user` (string): Database username (required for MySQL)
-   `password` (string): Database password (optional, defaults to empty)
-   `dbname` (string): Database name (required for MySQL)
-   `port` (string): Database port (optional, defaults to 3306)
-   `charset` (string): Character encoding (optional, defaults to utf8mb4)
-   `logs` (bool): Enable query logging (optional, defaults to false)

**Operating Modes:**

-   **FileDatabase-Only**: When MySQL parameters missing/empty
-   **MySQL Enhanced**: When complete MySQL configuration provided

**Examples:**

```php
// FileDatabase-Only Mode (Default)
$db = new Database(); // No MySQL connection attempted

// MySQL Enhancement Mode
$db = new Database([
    'host' => 'localhost',
    'user' => 'dbuser',
    'password' => 'password',
    'dbname' => 'my_app_db'
]);

// Environment-Based Configuration
$db = new Database([
    'host' => $_ENV['DB_HOST'] ?? '',
    'user' => $_ENV['DB_USER'] ?? '',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'dbname' => $_ENV['DB_NAME'] ?? ''
]);
```

---

### Connection Management

#### `hasMySQL()`

Checks if MySQL database connection is available and active.

**Returns:** `bool` - True if MySQL connection active, false for FileDatabase-only

**Description:** Provides convenient method to determine available database operations.

**Example:**

```php
if ($this->database->hasMySQL()) {
    // MySQL operations available
    $users = $this->database->client->select('users', '*', ['status' => 'active']);
} else {
    // FileDatabase operations only
    $users = $this->models->user->getAllUsers();
}
```

---

## MySQL Query Operations (When Connected)

When MySQL connection is available, the framework provides access to the full Medoo ORM API through `$this->database->client`.

### Basic Query Operations

#### Select Operations

```php
// Simple select
$users = $this->database->client->select('users', '*');

// Select with conditions
$activeUsers = $this->database->client->select('users', '*', [
    'status' => 'active',
    'created_at[>]' => '2024-01-01'
]);

// Select specific columns
$userEmails = $this->database->client->select('users', ['id', 'email'], [
    'status' => 'active'
]);

// Select with LIMIT
$recentUsers = $this->database->client->select('users', '*', [
    'ORDER' => ['created_at' => 'DESC'],
    'LIMIT' => 10
]);
```

#### Insert Operations

```php
// Insert single record
$this->database->client->insert('users', [
    'email' => 'user@example.com',
    'password' => password_hash('password', PASSWORD_DEFAULT),
    'status' => 'active',
    'created_at' => date('Y-m-d H:i:s')
]);

// Get last insert ID
$userId = $this->database->client->id();

// Insert multiple records
$this->database->client->insert('users', [
    ['email' => 'user1@example.com', 'status' => 'active'],
    ['email' => 'user2@example.com', 'status' => 'pending']
]);
```

#### Update Operations

```php
// Update with conditions
$this->database->client->update('users', [
    'last_login' => date('Y-m-d H:i:s'),
    'login_count[+]' => 1  // Increment counter
], [
    'id' => $userId
]);

// Update multiple records
$this->database->client->update('users', [
    'status' => 'verified'
], [
    'email_verified' => 1,
    'status' => 'pending'
]);
```

#### Delete Operations

```php
// Delete with conditions
$this->database->client->delete('users', [
    'status' => 'deleted',
    'deleted_at[<]' => date('Y-m-d H:i:s', strtotime('-30 days'))
]);

// Soft delete (mark as deleted)
$this->database->client->update('users', [
    'status' => 'deleted',
    'deleted_at' => date('Y-m-d H:i:s')
], [
    'id' => $userId
]);
```

### Advanced Query Operations

#### Joins

```php
// Inner join
$userPosts = $this->database->client->select('users', [
    '[>]posts' => ['id' => 'user_id']
], [
    'users.id',
    'users.email',
    'posts.title',
    'posts.created_at'
], [
    'users.status' => 'active'
]);

// Left join with alias
$usersWithPostCount = $this->database->client->select('users', [
    '[<]posts (p)' => ['id' => 'user_id']
], [
    'users.id',
    'users.email',
    'post_count' => 'COUNT(p.id)'
], [
    'GROUP' => 'users.id'
]);
```

#### Aggregation

```php
// Count records
$userCount = $this->database->client->count('users', [
    'status' => 'active'
]);

// Sum values
$totalRevenue = $this->database->client->sum('orders', 'amount', [
    'status' => 'completed',
    'created_at[>]' => '2024-01-01'
]);

// Average
$averageAge = $this->database->client->avg('users', 'age', [
    'status' => 'active'
]);

// Min/Max
$oldestUser = $this->database->client->min('users', 'birth_date');
$newestPost = $this->database->client->max('posts', 'created_at');
```

#### Complex Conditions

```php
// OR conditions
$users = $this->database->client->select('users', '*', [
    'OR' => [
        'status' => 'active',
        'last_login[>]' => date('Y-m-d', strtotime('-7 days'))
    ]
]);

// AND/OR combination
$posts = $this->database->client->select('posts', '*', [
    'AND' => [
        'OR' => [
            'status' => 'published',
            'status' => 'featured'
        ],
        'created_at[>]' => '2024-01-01'
    ]
]);

// Complex WHERE with subquery
$activeUsersWithPosts = $this->database->client->select('users', '*', [
    'status' => 'active',
    'id' => $this->database->client->select('posts', 'user_id', [
        'status' => 'published'
    ])
]);
```

---

## Practical Usage Patterns

### Hybrid Storage Strategy

```php
class UserController extends Controller
{
    public function getUsers()
    {
        if ($this->database->hasMySQL()) {
            // Use MySQL for complex queries with joins
            $users = $this->database->client->select('users', [
                '[>]user_profiles' => ['id' => 'user_id'],
                '[>]user_stats' => ['id' => 'user_id']
            ], [
                'users.id',
                'users.email',
                'user_profiles.first_name',
                'user_profiles.last_name',
                'user_stats.post_count',
                'user_stats.login_count'
            ], [
                'users.status' => 'active'
            ]);
        } else {
            // Fallback to FileDatabase
            $users = $this->models->user->getAllUsers();
        }

        return $users;
    }
}
```

### Environment-Based Configuration

```php
// In bootstrap or configuration
$dbConfig = [];

// Only add MySQL config if environment variables are set
if (!empty($_ENV['DB_HOST']) && !empty($_ENV['DB_USER']) && !empty($_ENV['DB_NAME'])) {
    $dbConfig = [
        'host' => $_ENV['DB_HOST'],
        'user' => $_ENV['DB_USER'],
        'password' => $_ENV['DB_PASSWORD'] ?? '',
        'dbname' => $_ENV['DB_NAME'],
        'port' => $_ENV['DB_PORT'] ?? '3306'
    ];
}

$database = new Database($dbConfig);
```

### Transactional Operations

```php
public function transferCredits($fromUserId, $toUserId, $amount)
{
    if (!$this->database->hasMySQL()) {
        // FileDatabase doesn't support transactions
        throw new Exception('Transactions require MySQL connection');
    }

    try {
        $this->database->client->action(function($database) use ($fromUserId, $toUserId, $amount) {
            // Deduct from sender
            $database->update('users', [
                'credits[-]' => $amount
            ], [
                'id' => $fromUserId,
                'credits[>=]' => $amount  // Ensure sufficient balance
            ]);

            // Add to receiver
            $database->update('users', [
                'credits[+]' => $amount
            ], [
                'id' => $toUserId
            ]);

            // Log transaction
            $database->insert('transactions', [
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'amount' => $amount,
                'type' => 'transfer',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        });

        return true;
    } catch (Exception $e) {
        return false;
    }
}
```

### Query Logging and Debugging

```php
// Enable logging during development
$database = new Database([
    'host' => 'localhost',
    'user' => 'dev_user',
    'password' => 'dev_pass',
    'dbname' => 'dev_db',
    'logs' => true  // Enable query logging
]);

// View last executed query
$users = $database->client->select('users', '*');
$lastQuery = $database->client->last(); // Get last SQL query
error_log("Last Query: " . $lastQuery);

// View query log
$queryLog = $database->client->log(); // Get all logged queries
foreach ($queryLog as $query) {
    error_log("Query: " . $query);
}
```

---

## Security Features

### Prepared Statements

All Medoo operations use PDO prepared statements automatically:

```php
// Safe from SQL injection - parameters are automatically bound
$user = $this->database->client->get('users', '*', [
    'email' => $_POST['email'], // Automatically escaped
    'status' => 'active'
]);
```

### Input Validation

Always validate input before database operations:

```php
public function updateUser($userId, $data)
{
    // Validate input
    if (!is_numeric($userId) || empty($data['email'])) {
        return false;
    }

    // Sanitize data
    $data['email'] = filter_var($data['email'], FILTER_SANITIZE_EMAIL);

    if ($this->database->hasMySQL()) {
        return $this->database->client->update('users', $data, ['id' => $userId]);
    } else {
        return $this->models->user->updateUser($userId, $data);
    }
}
```

---

## Error Handling

The Database class handles errors gracefully:

```php
public function createUser($userData)
{
    if ($this->database->hasMySQL()) {
        try {
            $this->database->client->insert('users', $userData);
            $userId = $this->database->client->id();

            if ($userId) {
                return $this->database->client->get('users', '*', ['id' => $userId]);
            }
        } catch (Exception $e) {
            error_log('Database error: ' . $e->getMessage());
            return false;
        }
    } else {
        // Fallback to FileDatabase
        return $this->models->user->createUser($userData);
    }

    return false;
}
```

---

## Performance Considerations

### Connection Efficiency

```php
// Connection is established once during initialization
// Reuse the same database instance throughout request lifecycle

// Good: Reuse existing connection
if ($this->database->hasMySQL()) {
    $users = $this->database->client->select('users', '*');
    $posts = $this->database->client->select('posts', '*');
}

// Avoid: Creating multiple database instances
// $db1 = new Database($config);
// $db2 = new Database($config); // Unnecessary connection
```

### Query Optimization

```php
// Use appropriate indexes and LIMIT queries
$recentPosts = $this->database->client->select('posts', '*', [
    'status' => 'published',
    'ORDER' => ['created_at' => 'DESC'],
    'LIMIT' => 20
]);

// Use specific column selection when possible
$userNames = $this->database->client->select('users', ['id', 'name'], [
    'status' => 'active'
]);
```

---

## Framework Integration

The Database class integrates with other framework components:

-   **FileDatabase**: Primary storage system with optional MySQL enhancement
-   **Models**: UserModel and other models can use both storage systems
-   **Configuration**: Environment-based configuration support
-   **Error Handling**: Graceful fallback to FileDatabase-only mode
-   **Security**: Integration with framework validation and security systems

The Database API provides flexible data storage options while maintaining the framework's FileDatabase-first architecture and ensuring applications work reliably regardless of MySQL availability.
