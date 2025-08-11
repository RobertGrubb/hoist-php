# DatabaseAdapter API Documentation

## Overview

The DatabaseAdapter provides a **revolutionary unified interface** that maintains FileDatabase's beloved chainable syntax while automatically routing queries to the appropriate backend (FileDatabase for development, MySQL for production). This enables **true seamless migration** with zero code changes.

## Class: DatabaseAdapter

**Location**: `Core/Libraries/DatabaseAdapter.php`  
**Access**: Available as `$this->instance->db` in controllers  
**Pattern**: Unified interface with automatic backend detection

---

## 🚀 The Revolutionary Benefit

**Before DatabaseAdapter:**

```php
// Development (FileDatabase)
$users = $this->instance->fileDatabase->table('users')
                                      ->where('status', '=', 'active')
                                      ->all();

// After migration to production (MySQL) - DIFFERENT CODE REQUIRED!
$users = $this->instance->database->client->select('users', '*', [
    'status' => 'active'
]);
```

**After DatabaseAdapter:**

```php
// Development AND Production - SAME CODE!
$users = $this->instance->db->table('users')
                            ->where('status', '=', 'active')
                            ->all();
```

## Methods

### Backend Detection

#### `getBackend()`

Returns the current active backend.

**Returns:** `string` - Either 'mysql' or 'filedb'

**Example:**

```php
$backend = $this->instance->db->getBackend();
echo "Using: {$backend}"; // "Using: mysql" or "Using: filedb"
```

#### `isMySQL()`

Checks if MySQL backend is active.

**Returns:** `bool` - True if using MySQL, false if using FileDatabase

#### `isFileDatabase()`

Checks if FileDatabase backend is active.

**Returns:** `bool` - True if using FileDatabase, false if using MySQL

### Unified Query Interface

#### `table($tableName)`

Creates a query builder for the specified table with automatic backend routing.

**Parameters:**

-   `$tableName` (string) - Name of the table to query

**Returns:** `MySQLQueryBuilder|FileDatabase` - Backend-appropriate query builder

**Example:**

```php
$query = $this->instance->db->table('users');
// Returns MySQLQueryBuilder if MySQL available, FileDatabase otherwise
```

## Query Building (Identical API)

All query methods maintain FileDatabase's chainable syntax regardless of backend:

### WHERE Clauses

```php
$users = $this->instance->db->table('users')
                            ->where('status', '=', 'active')
                            ->where('age', '>=', 18)
                            ->where('name', 'LIKE', 'John')
                            ->all();
```

**Supported Operators:**

-   `=` - Exact equality
-   `!=` - Not equal
-   `>` - Greater than
-   `>=` - Greater than or equal
-   `<` - Less than
-   `<=` - Less than or equal
-   `LIKE` - Partial string matching

### ORDER BY

```php
$users = $this->instance->db->table('users')
                            ->order('name', 'ASC')
                            ->all();

$latest = $this->instance->db->table('posts')
                             ->order('created_at', 'DESC')
                             ->all(10);
```

### Query Execution

#### `all($limit = null)`

Returns all matching records.

```php
// All records
$users = $this->instance->db->table('users')->all();

// Limited results
$recent = $this->instance->db->table('posts')->all(10);
```

#### `get()` / `first()`

Returns first matching record.

```php
$user = $this->instance->db->table('users')
                           ->where('id', '=', 123)
                           ->get();
```

#### `last()`

Returns last matching record.

```php
$newest = $this->instance->db->table('users')
                             ->order('created_at', 'ASC')
                             ->last();
```

### Data Modification

#### `insert($data)`

Inserts new record with automatic data processing.

```php
$userId = $this->instance->db->table('users')->insert([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'preferences' => ['email', 'sms'], // Arrays auto-converted to JSON
    'metadata' => ['theme' => 'dark'],  // Objects auto-converted to JSON
    'active' => true                     // Booleans auto-converted for MySQL
]);
```

#### `update($data)`

Updates existing records (requires WHERE clause).

```php
$affected = $this->instance->db->table('users')
                               ->where('id', '=', 123)
                               ->update([
                                   'last_login' => date('Y-m-d H:i:s'),
                                   'status' => 'active'
                               ]);
```

## Complete Usage Examples

### User Management

```php
class UserController extends Controller
{
    public function index()
    {
        // Works with both FileDatabase and MySQL!
        $users = $this->instance->db->table('users')
                                    ->where('status', '=', 'active')
                                    ->order('name', 'ASC')
                                    ->all();

        $this->instance->view->render('users/index', ['users' => $users]);
    }

    public function create()
    {
        $userData = $this->request->only(['name', 'email', 'role']);

        // Same syntax for both backends
        $userId = $this->instance->db->table('users')->insert(array_merge($userData, [
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'preferences' => $this->request->post('preferences', [])
        ]));

        $this->instance->response->sendJson(['user_id' => $userId], 201);
    }

    public function update($id)
    {
        $updateData = $this->request->only(['name', 'email', 'status']);

        // Identical update syntax
        $affected = $this->instance->db->table('users')
                                       ->where('id', '=', $id)
                                       ->update($updateData);

        if ($affected > 0) {
            $this->instance->response->sendJson(['message' => 'User updated']);
        } else {
            $this->instance->response->sendJson(['error' => 'User not found'], 404);
        }
    }
}
```

### Product Catalog

```php
class ProductController extends Controller
{
    public function search()
    {
        $category = $this->request->get('category');
        $maxPrice = $this->request->get('max_price');

        $query = $this->instance->db->table('products')
                                    ->where('status', '=', 'active');

        if ($category) {
            $query->where('category', '=', $category);
        }

        if ($maxPrice) {
            $query->where('price', '<=', $maxPrice);
        }

        $products = $query->order('name', 'ASC')->all(50);

        $this->instance->response->sendJson($products);
    }
}
```

## Migration Benefits

### Zero Code Changes

When migrating from FileDatabase to MySQL:

1. **Before Migration (Development)**:

    ```php
    // Code uses FileDatabase automatically
    $users = $this->instance->db->table('users')->where('active', '=', true)->all();
    ```

2. **Run Migration**:

    ```bash
    php hoist migrate:to-mysql --database=myapp
    ```

3. **After Migration (Production)**:
    ```php
    // EXACT SAME CODE - now uses MySQL automatically!
    $users = $this->instance->db->table('users')->where('active', '=', true)->all();
    ```

### Automatic Data Processing

The adapter handles data type conversions automatically:

-   **Arrays/Objects**: Converted to JSON strings for MySQL
-   **Booleans**: Converted to integers (1/0) for MySQL
-   **Null values**: Handled appropriately for both backends
-   **Strings/Numbers**: Passed through unchanged

### Performance Optimization

-   **FileDatabase**: Optimized for development and simple queries
-   **MySQL**: Leverages relational database performance and features
-   **Automatic selection**: Uses the best backend for your environment

## Error Handling

```php
try {
    $userId = $this->instance->db->table('users')->insert($userData);

    if ($userId) {
        echo "User created with ID: {$userId}";
    } else {
        echo "Failed to create user";
    }
} catch (Exception $e) {
    error_log("Database error: " . $e->getMessage());
    // Handle error appropriately
}
```

## Integration with Models

Update your models to use the unified interface:

```php
class UserModel
{
    private $db;

    public function __construct($instance)
    {
        $this->db = $instance->db; // Use unified interface
    }

    public function getActiveUsers()
    {
        return $this->db->table('users')
                        ->where('status', '=', 'active')
                        ->order('created_at', 'DESC')
                        ->all();
    }

    public function findByEmail($email)
    {
        return $this->db->table('users')
                        ->where('email', '=', $email)
                        ->get();
    }
}
```

## Best Practices

1. **Use the unified interface**: Always use `$this->instance->db` instead of direct FileDatabase or MySQL calls

2. **Consistent data structures**: Design your data to work well with both backends

3. **Handle arrays/objects**: Let the adapter handle JSON conversion automatically

4. **Error handling**: Always check return values and handle exceptions

5. **Migration testing**: Test your application with both backends during development

---

**The DatabaseAdapter makes HOIST the first framework to deliver true "write once, scale anywhere" capabilities for PHP development!** 🚀
