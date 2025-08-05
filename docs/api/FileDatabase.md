# FileDatabase API Documentation

## Overview

The FileDatabase API provides a lightweight, JSON-based database system for the Hoist PHP framework. It serves as the primary storage solution that enables zero-configuration development, offering SQL-like query capabilities with file-based persistence. Perfect for development, prototyping, and applications requiring simple data storage without database server dependencies.

## Class: FileDatabase

**Location**: `Core/Libraries/FileDatabase.php`  
**Pattern**: JSON File Storage with SQL-like Interface  
**Usage**: Primary data storage system with optional MySQL enhancement  
**Features**: Method chaining, WHERE clauses, ORDER BY, CRUD operations

---

## Properties

### Core State Management

#### `$databaseDirectory`

**Type**: `string|false`  
**Description**: Path to the database directory containing JSON table files

#### `$table`

**Type**: `string|false`  
**Description**: Currently selected table name for query operations

#### `$wheres`

**Type**: `array`  
**Description**: Array of WHERE clause conditions for filtering

#### `$orders`

**Type**: `array|false`  
**Description**: ORDER BY configuration for result sorting

#### `$records`

**Type**: `array|null`  
**Description**: Raw data records loaded from JSON table file

#### `$queryResults`

**Type**: `array|null`  
**Description**: Filtered and processed query results

---

## Constructor

### `__construct($database = null)`

Initializes the file database with directory validation.

**Parameters:**

-   `$database` (string): Database name (directory name)

**Directory Structure:**

```
Application/Database/
├── myapp/              # Database directory
│   ├── users.json     # Table file
│   ├── products.json  # Table file
│   └── orders.json    # Table file
```

**Example:**

```php
// Initialize FileDatabase
$db = new FileDatabase('myapp');

// Framework integration (automatic)
$this->instance->fileDatabase; // Available in controllers/models
```

---

## Table Selection and Data Loading

### `table($table = null)`

Selects a table and loads its data for querying.

**Parameters:**

-   `$table` (string): Table name to load (without .json extension)

**Returns:** `FileDatabase` - Self for method chaining

**Example:**

```php
// Basic table selection
$users = $db->table('users')->all();

// Method chaining
$activeUsers = $db->table('users')
    ->where('status', '=', 'active')
    ->all();

// Multiple table operations
$products = $db->table('products')
    ->where('category', '=', 'electronics')
    ->order('price', 'ASC')
    ->all();

$orders = $db->table('orders')
    ->where('status', '=', 'pending')
    ->all();

// Framework integration
public function getUsers()
{
    $users = $this->instance->fileDatabase
        ->table('users')
        ->where('status', '=', 'active')
        ->order('created_at', 'DESC')
        ->all();

    return $users;
}

// Table existence handling
$newTable = $db->table('new_table'); // Creates empty array if file doesn't exist
```

---

## Query Building and Filtering

### `where($field, $operator, $value)`

Adds a WHERE clause condition to the query.

**Parameters:**

-   `$field` (string): Field name to filter on
-   `$operator` (string): Comparison operator
-   `$value` (mixed): Value to compare against

**Supported Operators:**

-   `'='` - Exact equality matching
-   `'!='` - Not equal comparison
-   `'<'` - Less than comparison
-   `'>'` - Greater than comparison
-   `'<='` - Less than or equal
-   `'>='` - Greater than or equal
-   `'LIKE'` - Partial string matching (case-sensitive)

**Returns:** `FileDatabase` - Self for method chaining

**Example:**

```php
// Single condition
$activeUsers = $db->table('users')
    ->where('status', '=', 'active')
    ->all();

// Multiple conditions (AND logic)
$premiumUsers = $db->table('users')
    ->where('status', '=', 'active')
    ->where('subscription', '=', 'premium')
    ->where('age', '>=', 18)
    ->all();

// Comparison operators
$expensiveProducts = $db->table('products')
    ->where('price', '>', 100)
    ->where('category', '!=', 'clearance')
    ->all();

// String matching
$searchResults = $db->table('posts')
    ->where('title', 'LIKE', 'PHP')
    ->where('content', 'LIKE', 'framework')
    ->all();

// Date filtering
$recentOrders = $db->table('orders')
    ->where('created_at', '>=', '2024-01-01')
    ->where('status', '!=', 'cancelled')
    ->all();

// Numeric ranges
$targetAgeGroup = $db->table('users')
    ->where('age', '>=', 25)
    ->where('age', '<=', 35)
    ->all();

// Boolean filtering
$publishedPosts = $db->table('posts')
    ->where('published', '=', true)
    ->where('featured', '=', false)
    ->all();

// Complex business logic
public function getEligibleUsers()
{
    return $this->instance->fileDatabase
        ->table('users')
        ->where('status', '=', 'active')
        ->where('email_verified', '=', true)
        ->where('last_login', '>=', date('Y-m-d', strtotime('-30 days')))
        ->where('subscription_expires', '>', date('Y-m-d'))
        ->all();
}
```

### `order($field, $direction = 'ASC')`

Sets the ORDER BY clause for result sorting.

**Parameters:**

-   `$field` (string): Field name to sort by
-   `$direction` (string): Sort direction ('ASC' or 'DESC')

**Returns:** `FileDatabase` - Self for method chaining

**Example:**

```php
// Ascending order (default)
$usersByName = $db->table('users')
    ->order('name', 'ASC')
    ->all();

// Descending order
$newestUsers = $db->table('users')
    ->order('created_at', 'DESC')
    ->all();

// Combined with filtering
$topProducts = $db->table('products')
    ->where('category', '=', 'electronics')
    ->order('rating', 'DESC')
    ->all(10); // Limit to top 10

// Multiple sorting criteria (last call wins)
$sortedOrders = $db->table('orders')
    ->where('status', '=', 'completed')
    ->order('total', 'DESC') // Sort by total value, highest first
    ->all();

// Date-based sorting
$recentActivity = $db->table('activity_log')
    ->where('user_id', '=', $userId)
    ->order('timestamp', 'DESC')
    ->all(20);

// Alphabetical sorting
$categorizedProducts = $db->table('products')
    ->where('in_stock', '=', true)
    ->order('name', 'ASC')
    ->all();

// Price-based sorting
$affordableOptions = $db->table('products')
    ->where('price', '<=', 50)
    ->order('price', 'ASC')
    ->all();

// Framework integration
public function getProductsByPopularity()
{
    return $this->instance->fileDatabase
        ->table('products')
        ->where('status', '=', 'active')
        ->order('view_count', 'DESC')
        ->all();
}
```

---

## Query Execution and Result Retrieval

### `all($limit = null)`

Executes the query and returns all matching records.

**Parameters:**

-   `$limit` (int|null): Maximum number of records to return

**Returns:** `array` - Array of matching records

**Example:**

```php
// Get all records
$allUsers = $db->table('users')->all();

// Get filtered records
$activeUsers = $db->table('users')
    ->where('status', '=', 'active')
    ->all();

// Get limited results for pagination
$recentPosts = $db->table('posts')
    ->where('published', '=', true)
    ->order('created_at', 'DESC')
    ->all(10); // First 10 results

// Complex query with multiple conditions
$eligibleProducts = $db->table('products')
    ->where('category', '=', 'electronics')
    ->where('price', '>=', 50)
    ->where('price', '<=', 500)
    ->where('in_stock', '=', true)
    ->order('rating', 'DESC')
    ->all(20);

// Search functionality
public function searchProducts($query, $category = null, $limit = 50)
{
    $search = $this->instance->fileDatabase
        ->table('products')
        ->where('name', 'LIKE', $query)
        ->where('status', '=', 'active');

    if ($category) {
        $search->where('category', '=', $category);
    }

    return $search->order('name', 'ASC')->all($limit);
}

// Pagination implementation
public function getUsersPage($page = 1, $perPage = 20)
{
    $offset = ($page - 1) * $perPage;

    $users = $this->instance->fileDatabase
        ->table('users')
        ->where('status', '=', 'active')
        ->order('created_at', 'DESC')
        ->all();

    return array_slice($users, $offset, $perPage);
}

// Analytics queries
public function getTopSellingProducts($timeframe = '30 days')
{
    $startDate = date('Y-m-d', strtotime("-{$timeframe}"));

    return $this->instance->fileDatabase
        ->table('order_items')
        ->where('created_at', '>=', $startDate)
        ->order('quantity_sold', 'DESC')
        ->all(10);
}
```

### `get()` / `first()`

Executes the query and returns the first matching record.

**Returns:** `array|false` - First matching record or false if none found

**Example:**

```php
// Get single user by ID
$user = $db->table('users')
    ->where('id', '=', 123)
    ->get();

// Alternative syntax
$user = $db->table('users')
    ->where('id', '=', 123)
    ->first();

// Get user by email
$user = $db->table('users')
    ->where('email', '=', 'john@example.com')
    ->get();

// Get most recent order
$latestOrder = $db->table('orders')
    ->where('user_id', '=', $userId)
    ->order('created_at', 'DESC')
    ->get();

// Get highest rated product
$topProduct = $db->table('products')
    ->where('category', '=', 'electronics')
    ->order('rating', 'DESC')
    ->get();

// Authentication lookup
public function findUserByEmail($email)
{
    return $this->instance->fileDatabase
        ->table('users')
        ->where('email', '=', $email)
        ->where('status', '=', 'active')
        ->get();
}

// Settings retrieval
public function getUserSettings($userId)
{
    $settings = $this->instance->fileDatabase
        ->table('user_settings')
        ->where('user_id', '=', $userId)
        ->get();

    return $settings ?: $this->getDefaultSettings();
}

// Validation checks
public function emailExists($email, $excludeUserId = null)
{
    $query = $this->instance->fileDatabase
        ->table('users')
        ->where('email', '=', $email);

    if ($excludeUserId) {
        $query->where('id', '!=', $excludeUserId);
    }

    return $query->get() !== false;
}

// Error handling
$user = $db->table('users')->where('id', '=', 999)->get();
if ($user === false) {
    echo "User not found";
} else {
    echo "Welcome, " . $user['name'];
}
```

### `last()`

Executes the query and returns the last matching record.

**Returns:** `array|false` - Last matching record or false if none found

**Example:**

```php
// Get newest user
$newestUser = $db->table('users')
    ->order('created_at', 'ASC')
    ->last();

// Get highest score
$topScore = $db->table('game_scores')
    ->where('game_id', '=', $gameId)
    ->order('score', 'ASC')
    ->last();

// Get latest order for user
$lastOrder = $db->table('orders')
    ->where('user_id', '=', $userId)
    ->order('created_at', 'ASC')
    ->last();

// Get final entry in sequence
$lastLogEntry = $db->table('activity_log')
    ->where('session_id', '=', $sessionId)
    ->order('timestamp', 'ASC')
    ->last();

// Recent activity tracking
public function getLastUserActivity($userId)
{
    return $this->instance->fileDatabase
        ->table('user_activity')
        ->where('user_id', '=', $userId)
        ->order('timestamp', 'ASC')
        ->last();
}

// Sequence tracking
public function getLastSequenceNumber($type)
{
    $lastRecord = $this->instance->fileDatabase
        ->table('sequences')
        ->where('type', '=', $type)
        ->order('sequence_number', 'ASC')
        ->last();

    return $lastRecord ? $lastRecord['sequence_number'] : 0;
}
```

---

## Data Modification Operations

### `insert(array $data)`

Inserts a new record into the table with automatic ID generation.

**Parameters:**

-   `$data` (array): Associative array of field => value pairs

**Returns:** `int` - Generated ID of the new record

**ID Generation:**

-   Automatically generates unique integer IDs
-   Finds highest existing ID and increments
-   Handles empty tables (starts with ID 1)

**Example:**

```php
// Basic user creation
$userId = $db->table('users')->insert([
    'name' => 'Jane Smith',
    'email' => 'jane@example.com',
    'status' => 'active',
    'created_at' => date('Y-m-d H:i:s')
]);
echo "New user ID: {$userId}";

// Product creation
$productId = $db->table('products')->insert([
    'name' => 'Wireless Headphones',
    'description' => 'High-quality bluetooth headphones',
    'price' => 99.99,
    'category' => 'electronics',
    'in_stock' => true,
    'stock_quantity' => 50,
    'created_at' => time()
]);

// Order creation with details
$orderId = $db->table('orders')->insert([
    'user_id' => $userId,
    'status' => 'pending',
    'total' => 149.98,
    'items' => json_encode([
        ['product_id' => 1, 'quantity' => 2, 'price' => 74.99]
    ]),
    'shipping_address' => json_encode($shippingData),
    'created_at' => date('Y-m-d H:i:s')
]);

// Blog post creation
$postId = $db->table('posts')->insert([
    'title' => 'Getting Started with FileDatabase',
    'content' => $postContent,
    'author_id' => $authorId,
    'category' => 'tutorials',
    'tags' => 'database,php,framework',
    'published' => false,
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s')
]);

// Framework integration
public function createUser($userData)
{
    // Add timestamps and defaults
    $userData['created_at'] = date('Y-m-d H:i:s');
    $userData['status'] = $userData['status'] ?? 'active';
    $userData['email_verified'] = false;

    $userId = $this->instance->fileDatabase
        ->table('users')
        ->insert($userData);

    // Create related records
    $this->instance->fileDatabase
        ->table('user_settings')
        ->insert([
            'user_id' => $userId,
            'theme' => 'default',
            'notifications' => true,
            'created_at' => date('Y-m-d H:i:s')
        ]);

    return $userId;
}

// Batch creation
public function seedTestData()
{
    $users = [
        ['name' => 'Alice Johnson', 'email' => 'alice@test.com'],
        ['name' => 'Bob Wilson', 'email' => 'bob@test.com'],
        ['name' => 'Carol Davis', 'email' => 'carol@test.com']
    ];

    $userIds = [];
    foreach ($users as $userData) {
        $userData['status'] = 'active';
        $userData['created_at'] = date('Y-m-d H:i:s');

        $userIds[] = $this->instance->fileDatabase
            ->table('users')
            ->insert($userData);
    }

    return $userIds;
}

// Complex data structures
$configId = $db->table('app_config')->insert([
    'key' => 'email_settings',
    'value' => json_encode([
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => 587,
        'smtp_user' => 'app@example.com',
        'templates' => [
            'welcome' => 'welcome_email.html',
            'reset' => 'password_reset.html'
        ]
    ]),
    'type' => 'json',
    'created_at' => date('Y-m-d H:i:s')
]);
```

### `update(array $data)`

Updates existing records matching WHERE conditions.

**Parameters:**

-   `$data` (array): Associative array of field => value pairs to update

**Returns:** `int` - Number of records affected by the update

**Requirements:**

-   At least one WHERE clause must be specified
-   Prevents accidental mass updates
-   Cannot update the 'id' field

**Example:**

```php
// Update single user
$affected = $db->table('users')
    ->where('id', '=', 123)
    ->update([
        'status' => 'inactive',
        'updated_at' => date('Y-m-d H:i:s')
    ]);
echo "Updated {$affected} user(s)";

// Bulk status update
$affected = $db->table('products')
    ->where('category', '=', 'electronics')
    ->where('in_stock', '=', false)
    ->update([
        'status' => 'discontinued',
        'updated_at' => date('Y-m-d H:i:s')
    ]);

// Price adjustment
$affected = $db->table('products')
    ->where('price', '<', 10)
    ->update([
        'price' => 9.99,
        'sale_price' => 7.99,
        'updated_at' => date('Y-m-d H:i:s')
    ]);

// Order status progression
$affected = $db->table('orders')
    ->where('status', '=', 'pending')
    ->where('payment_status', '=', 'completed')
    ->update([
        'status' => 'processing',
        'processed_at' => date('Y-m-d H:i:s')
    ]);

// User profile updates
public function updateUserProfile($userId, $profileData)
{
    $profileData['updated_at'] = date('Y-m-d H:i:s');

    $affected = $this->instance->fileDatabase
        ->table('users')
        ->where('id', '=', $userId)
        ->where('status', '=', 'active')
        ->update($profileData);

    if ($affected === 0) {
        throw new Exception('User not found or inactive');
    }

    return $affected;
}

// Conditional updates
public function activateExpiredTrials()
{
    $expiredDate = date('Y-m-d', strtotime('-30 days'));

    return $this->instance->fileDatabase
        ->table('users')
        ->where('subscription_type', '=', 'trial')
        ->where('created_at', '<=', $expiredDate)
        ->update([
            'subscription_type' => 'free',
            'trial_expired' => true,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
}

// Settings management
public function updateUserSettings($userId, $settings)
{
    $affected = $this->instance->fileDatabase
        ->table('user_settings')
        ->where('user_id', '=', $userId)
        ->update(array_merge($settings, [
            'updated_at' => date('Y-m-d H:i:s')
        ]));

    if ($affected === 0) {
        // Create settings if they don't exist
        return $this->instance->fileDatabase
            ->table('user_settings')
            ->insert(array_merge($settings, [
                'user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]));
    }

    return $affected;
}

// Batch updates with validation
public function markOrdersAsShipped($orderIds)
{
    $affected = 0;

    foreach ($orderIds as $orderId) {
        $result = $this->instance->fileDatabase
            ->table('orders')
            ->where('id', '=', $orderId)
            ->where('status', '=', 'processing')
            ->update([
                'status' => 'shipped',
                'shipped_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

        $affected += $result;
    }

    return $affected;
}

// Error handling for updates
try {
    $affected = $db->table('users')
        ->where('id', '=', $userId)
        ->update($updateData);

    if ($affected === 0) {
        echo "No records were updated";
    } else {
        echo "Successfully updated {$affected} record(s)";
    }
} catch (Exception $e) {
    error_log("Update failed: " . $e->getMessage());
    return false;
}
```

---

## Complete Usage Examples

### User Management System

```php
class UserManager
{
    private $db;

    public function __construct($fileDatabase)
    {
        $this->db = $fileDatabase;
    }

    public function createUser($userData)
    {
        // Validate email uniqueness
        $existing = $this->db->table('users')
            ->where('email', '=', $userData['email'])
            ->get();

        if ($existing) {
            throw new Exception('Email already exists');
        }

        // Create user with defaults
        $userData['status'] = 'active';
        $userData['created_at'] = date('Y-m-d H:i:s');
        $userData['email_verified'] = false;

        return $this->db->table('users')->insert($userData);
    }

    public function getActiveUsers($limit = null)
    {
        return $this->db->table('users')
            ->where('status', '=', 'active')
            ->order('created_at', 'DESC')
            ->all($limit);
    }

    public function searchUsers($query)
    {
        return $this->db->table('users')
            ->where('name', 'LIKE', $query)
            ->where('status', '=', 'active')
            ->order('name', 'ASC')
            ->all();
    }

    public function updateUserProfile($userId, $updates)
    {
        $updates['updated_at'] = date('Y-m-d H:i:s');

        return $this->db->table('users')
            ->where('id', '=', $userId)
            ->update($updates);
    }

    public function deactivateUser($userId)
    {
        return $this->db->table('users')
            ->where('id', '=', $userId)
            ->update([
                'status' => 'inactive',
                'deactivated_at' => date('Y-m-d H:i:s')
            ]);
    }
}
```

### Product Catalog System

```php
class ProductCatalog
{
    private $db;

    public function __construct($fileDatabase)
    {
        $this->db = $fileDatabase;
    }

    public function addProduct($productData)
    {
        $productData['created_at'] = date('Y-m-d H:i:s');
        $productData['status'] = 'active';
        $productData['view_count'] = 0;

        return $this->db->table('products')->insert($productData);
    }

    public function getProductsByCategory($category, $limit = 20)
    {
        return $this->db->table('products')
            ->where('category', '=', $category)
            ->where('status', '=', 'active')
            ->where('in_stock', '=', true)
            ->order('name', 'ASC')
            ->all($limit);
    }

    public function searchProducts($query, $filters = [])
    {
        $search = $this->db->table('products')
            ->where('name', 'LIKE', $query)
            ->where('status', '=', 'active');

        if (isset($filters['category'])) {
            $search->where('category', '=', $filters['category']);
        }

        if (isset($filters['min_price'])) {
            $search->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $search->where('price', '<=', $filters['max_price']);
        }

        return $search->order('name', 'ASC')->all();
    }

    public function getFeaturedProducts($limit = 10)
    {
        return $this->db->table('products')
            ->where('featured', '=', true)
            ->where('status', '=', 'active')
            ->where('in_stock', '=', true)
            ->order('view_count', 'DESC')
            ->all($limit);
    }

    public function updateStock($productId, $quantity)
    {
        return $this->db->table('products')
            ->where('id', '=', $productId)
            ->update([
                'stock_quantity' => $quantity,
                'in_stock' => $quantity > 0,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }
}
```

### Order Management System

```php
class OrderManager
{
    private $db;

    public function __construct($fileDatabase)
    {
        $this->db = $fileDatabase;
    }

    public function createOrder($orderData)
    {
        $orderData['status'] = 'pending';
        $orderData['created_at'] = date('Y-m-d H:i:s');
        $orderData['order_number'] = $this->generateOrderNumber();

        return $this->db->table('orders')->insert($orderData);
    }

    public function getUserOrders($userId, $status = null)
    {
        $query = $this->db->table('orders')
            ->where('user_id', '=', $userId);

        if ($status) {
            $query->where('status', '=', $status);
        }

        return $query->order('created_at', 'DESC')->all();
    }

    public function updateOrderStatus($orderId, $status)
    {
        $updates = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Add status-specific timestamps
        switch ($status) {
            case 'processing':
                $updates['processed_at'] = date('Y-m-d H:i:s');
                break;
            case 'shipped':
                $updates['shipped_at'] = date('Y-m-d H:i:s');
                break;
            case 'delivered':
                $updates['delivered_at'] = date('Y-m-d H:i:s');
                break;
        }

        return $this->db->table('orders')
            ->where('id', '=', $orderId)
            ->update($updates);
    }

    public function getPendingOrders()
    {
        return $this->db->table('orders')
            ->where('status', '=', 'pending')
            ->where('payment_status', '=', 'completed')
            ->order('created_at', 'ASC')
            ->all();
    }

    private function generateOrderNumber()
    {
        $lastOrder = $this->db->table('orders')
            ->order('id', 'DESC')
            ->get();

        $nextId = $lastOrder ? $lastOrder['id'] + 1 : 1;
        return 'ORD-' . date('Y') . '-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
    }
}
```

---

## Integration with Framework

### Model Integration

```php
class UserModel extends Model
{
    public $table = 'users';

    public function getActiveUsers()
    {
        // FileDatabase operations
        return $this->fileDatabase->table($this->table)
            ->where('status', '=', 'active')
            ->order('created_at', 'DESC')
            ->all();
    }

    public function findByEmail($email)
    {
        return $this->fileDatabase->table($this->table)
            ->where('email', '=', $email)
            ->get();
    }
}
```

### Controller Integration

```php
class UserController extends Controller
{
    public function index()
    {
        $users = $this->instance->fileDatabase
            ->table('users')
            ->where('status', '=', 'active')
            ->order('name', 'ASC')
            ->all();

        $this->view->render('users/index', ['users' => $users]);
    }

    public function create()
    {
        $userData = $this->request->only(['name', 'email', 'role']);

        $userId = $this->instance->fileDatabase
            ->table('users')
            ->insert(array_merge($userData, [
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s')
            ]));

        $this->response->sendJson(['user_id' => $userId], 201);
    }
}
```

---

## Error Handling and Best Practices

### Error Handling

```php
try {
    $user = $db->table('users')
        ->where('id', '=', $userId)
        ->get();

    if ($user === false) {
        throw new Exception('User not found');
    }

    // Process user data
} catch (Exception $e) {
    error_log('Database error: ' . $e->getMessage());
    return false;
}
```

### Best Practices

```php
// 1. Always use WHERE clauses for updates
$db->table('users')
    ->where('id', '=', $userId) // Required for updates
    ->update($data);

// 2. Validate data before insertion
if (empty($userData['email']) || !filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
    throw new Exception('Invalid email address');
}

// 3. Use consistent field naming
$userData = [
    'created_at' => date('Y-m-d H:i:s'),
    'updated_at' => date('Y-m-d H:i:s'),
    'status' => 'active'
];

// 4. Implement data validation
public function validateUserData($data)
{
    $required = ['name', 'email'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new Exception("Field '{$field}' is required");
        }
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Invalid email format');
    }

    return true;
}

// 5. Use transactions conceptually
public function transferCredits($fromUserId, $toUserId, $amount)
{
    // Get current balances
    $fromUser = $this->db->table('users')->where('id', '=', $fromUserId)->get();
    $toUser = $this->db->table('users')->where('id', '=', $toUserId)->get();

    if (!$fromUser || !$toUser) {
        throw new Exception('User not found');
    }

    if ($fromUser['credits'] < $amount) {
        throw new Exception('Insufficient credits');
    }

    // Update balances
    $this->db->table('users')
        ->where('id', '=', $fromUserId)
        ->update(['credits' => $fromUser['credits'] - $amount]);

    $this->db->table('users')
        ->where('id', '=', $toUserId)
        ->update(['credits' => $toUser['credits'] + $amount]);

    // Log transaction
    $this->db->table('credit_transactions')->insert([
        'from_user_id' => $fromUserId,
        'to_user_id' => $toUserId,
        'amount' => $amount,
        'created_at' => date('Y-m-d H:i:s')
    ]);
}
```

---

## Framework Integration

The FileDatabase API seamlessly integrates with all framework components:

-   **Models**: Primary data storage with automatic service injection
-   **Controllers**: Direct access for rapid development and prototyping
-   **Authentication**: User data storage and session management
-   **Caching**: File-based fallback for cache storage
-   **Validation**: Data validation before storage operations
-   **Migration**: Easy transition to MySQL when needed

The FileDatabase provides a robust, zero-configuration storage solution that enables immediate development while maintaining the flexibility to scale to traditional databases as applications grow.
