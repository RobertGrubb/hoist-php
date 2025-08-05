# Model API Documentation

## Overview

The Model API provides the base class for all database models in the Hoist PHP framework. It implements the Active Record pattern with comprehensive security features, multi-tenancy support, and robust data operations. All application models extend this class to inherit standard CRUD operations and advanced security filtering.

## Class: Model

**Location**: `Core/Libraries/Model.php`  
**Pattern**: Active Record with security enhancements  
**Usage**: Extend this class for all application models  
**Features**: CRUD operations, hidden field filtering, guard deletion, multi-tenant support

---

## Properties

### Framework Dependencies

#### `$instance`

**Type**: `Instance`  
**Description**: Application instance container for service access

#### `$database`

**Type**: `Database`  
**Description**: Primary database connection service (MySQL via Medoo ORM)

#### `$fileDatabase`

**Type**: `FileDatabase`  
**Description**: File-based database service for structured data storage

#### `$models`

**Type**: `object`  
**Description**: All registered application models for cross-model operations

#### `$libraries`

**Type**: `object`  
**Description**: All registered application libraries for service access

### Model Configuration

#### `$table`

**Type**: `string|false`  
**Required**: Yes (must be set in child classes)  
**Description**: Database table name associated with this model

**Example:**

```php
class UserModel extends Model
{
    public $table = 'users';
}
```

#### `$hiddenFields`

**Type**: `array`  
**Default**: `[]`  
**Description**: Fields to hide from query results (passwords, tokens, sensitive data)

**Example:**

```php
class UserModel extends Model
{
    public $table = 'users';
    public $hiddenFields = ['password', 'reset_token', 'api_secret'];
}
```

#### `$opts`

**Type**: `array`  
**Description**: Query options for modifying behavior (method chaining support)

**Internal Use**: Managed automatically by the options() method

---

## Constructor

### `__construct($instance)`

Initializes the model with application dependencies.

**Parameters:**

-   `$instance` (Instance): Application service container

**Automatic Setup:**

-   Database connections (SQL and File)
-   Access to all models and libraries
-   Service container integration

**Example:**

```php
// Models are automatically instantiated by the framework
// Access via controllers: $this->models->user->method()
```

### `instantiate()`

Model initialization hook for child classes.

**Called**: After construction  
**Override**: Implement in child classes for custom initialization  
**Return**: `void`

**Example:**

```php
class UserModel extends Model
{
    public $table = 'users';
    public $hiddenFields = ['password', 'reset_token'];

    public function instantiate()
    {
        // Set default ordering
        $this->defaultOrder = ['created_at' => 'DESC'];

        // Initialize custom properties
        $this->validationRules = [
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8'
        ];
    }
}
```

---

## Query Options

### `options($opts = [])`

Sets options for modifying query behavior with method chaining.

**Parameters:**

-   `$opts` (array): Options to modify query behavior

**Returns:** `self` - For method chaining

**Available Options:**

-   `includeHiddenFields`: Include normally hidden fields in results
-   `customWhere`: Custom where conditions
-   `customOrder`: Custom ordering

**Example:**

```php
// Include hidden fields for admin users
$user = $this->models->user->options(['includeHiddenFields' => true])
    ->get(['id' => 123]);

// Method chaining with multiple options
$users = $this->models->user->options([
    'includeHiddenFields' => true,
    'customOrder' => ['last_login' => 'DESC']
])->getMany(['status' => 'active']);

// Temporary access to sensitive data
public function getUserForAdmin($userId)
{
    return $this->models->user->options(['includeHiddenFields' => true])
        ->get($userId);
}
```

---

## Data Retrieval Methods

### `get($where = null, $select = '*')`

Retrieves a single record from the database table.

**Parameters:**

-   `$where` (int|array|null): Filter conditions
    -   `int`: Treated as ID lookup (`WHERE id = $where`)
    -   `array`: Complex where conditions for Medoo
    -   `null`: Throws exception (filter required)
-   `$select` (string): Fields to select (`*` for all visible fields)

**Returns:** `array|null` - Single record or null if not found

**Throws:** `Exception` - If table not set or where condition missing

**Example:**

```php
// Get by ID
$user = $this->models->user->get(123);

// Get with complex where conditions
$user = $this->models->user->get([
    'email' => 'user@example.com',
    'status' => 'active'
]);

// Select specific fields
$userProfile = $this->models->user->get(123, 'id, name, email, created_at');

// Complex queries with Medoo syntax
$recentUser = $this->models->user->get([
    'status' => 'active',
    'created_at[>]' => date('Y-m-d', strtotime('-30 days')),
    'ORDER' => ['created_at' => 'DESC']
]);

// Controller usage
public function showUser()
{
    $userId = $this->router->param('id');
    $user = $this->models->user->get($userId);

    if (!$user) {
        $this->response->sendError('User not found', 404);
        return;
    }

    $this->response->sendJson($user);
}
```

### `getMany($where = null, $select = '*')`

Retrieves multiple records from the database table.

**Parameters:**

-   `$where` (array|null): Filter conditions array for Medoo
-   `$select` (string): Fields to select (`*` for all visible fields)

**Returns:** `array` - Array of records (empty array if none found)

**Throws:** `Exception` - If table not set, where condition missing, or invalid format

**Example:**

```php
// Get all active users
$activeUsers = $this->models->user->getMany(['status' => 'active']);

// Get with ordering and limits
$recentUsers = $this->models->user->getMany([
    'status' => 'active',
    'ORDER' => ['created_at' => 'DESC'],
    'LIMIT' => 10
]);

// Complex filtering
$premiumUsers = $this->models->user->getMany([
    'AND' => [
        'status' => 'active',
        'subscription_type' => 'premium',
        'created_at[>]' => date('Y-m-d', strtotime('-1 year'))
    ],
    'ORDER' => ['last_login' => 'DESC']
]);

// Pagination support
public function getUsers()
{
    $page = $this->request->get('page', 1);
    $limit = $this->request->get('limit', 20);
    $offset = ($page - 1) * $limit;

    $users = $this->models->user->getMany([
        'status' => 'active',
        'ORDER' => ['created_at' => 'DESC'],
        'LIMIT' => [$offset, $limit]
    ]);

    $total = $this->models->user->count(['status' => 'active']);

    $this->response->sendJson([
        'users' => $users,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => ceil($total / $limit),
            'total_records' => $total
        ]
    ]);
}
```

### `count($where = null)`

Counts records in the database table.

**Parameters:**

-   `$where` (array|null): Filter conditions array for Medoo

**Returns:** `int` - Number of matching records

**Throws:** `Exception` - If table not set or where condition missing

**Example:**

```php
// Count all active users
$activeCount = $this->models->user->count(['status' => 'active']);

// Count with complex conditions
$premiumCount = $this->models->user->count([
    'AND' => [
        'status' => 'active',
        'subscription_type' => 'premium',
        'created_at[>]' => date('Y-m-d', strtotime('-30 days'))
    ]
]);

// Analytics and reporting
public function getDashboardStats()
{
    $stats = [
        'total_users' => $this->models->user->count(['status[!]' => 'deleted']),
        'active_users' => $this->models->user->count(['status' => 'active']),
        'new_users_today' => $this->models->user->count([
            'created_at[>]' => date('Y-m-d 00:00:00')
        ]),
        'premium_users' => $this->models->user->count([
            'subscription_type' => 'premium'
        ])
    ];

    return $stats;
}
```

---

## Data Modification Methods

### `save($where, $data)`

Updates existing records in the database table.

**Parameters:**

-   `$where` (int|array): Update conditions
    -   `int`: Update record with this ID
    -   `array`: Complex where conditions for Medoo
-   `$data` (array): Associative array of field => value pairs to update

**Returns:** `bool` - True on success, false on failure

**Throws:** `Exception` - If table not set

**Example:**

```php
// Update by ID
$success = $this->models->user->save(123, [
    'name' => 'Updated Name',
    'email' => 'new@example.com',
    'updated_at' => date('Y-m-d H:i:s')
]);

// Update with complex where conditions
$success = $this->models->user->save([
    'status' => 'pending',
    'created_at[<]' => date('Y-m-d', strtotime('-7 days'))
], [
    'status' => 'expired'
]);

// Controller usage with validation
public function updateUser()
{
    $userId = $this->router->param('id');
    $userData = $this->request->only(['name', 'email', 'phone']);

    // Validate input
    if (empty($userData['email']) || !filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
        $this->response->sendError('Invalid email address', 400);
        return;
    }

    // Check if user exists
    $existingUser = $this->models->user->get($userId);
    if (!$existingUser) {
        $this->response->sendError('User not found', 404);
        return;
    }

    // Add timestamp
    $userData['updated_at'] = date('Y-m-d H:i:s');

    // Update user
    if ($this->models->user->save($userId, $userData)) {
        $updatedUser = $this->models->user->get($userId);
        $this->response->sendJson($updatedUser);
    } else {
        $this->response->sendError('Failed to update user', 500);
    }
}
```

### `create($data)`

Creates new records in the database table.

**Parameters:**

-   `$data` (array): Associative array of field => value pairs to insert

**Returns:** `mixed` - ID of newly created record, or false on failure

**Throws:** `Exception` - If table not set

**Example:**

```php
// Create new user
$userId = $this->models->user->create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => password_hash('secure123', PASSWORD_DEFAULT),
    'status' => 'active',
    'created_at' => date('Y-m-d H:i:s')
]);

// Controller usage with validation
public function createUser()
{
    $userData = $this->request->only(['name', 'email', 'password']);

    // Validate required fields
    $errors = [];
    if (empty($userData['name'])) {
        $errors[] = 'Name is required';
    }
    if (empty($userData['email']) || !filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required';
    }
    if (empty($userData['password']) || strlen($userData['password']) < 8) {
        $errors[] = 'Password must be at least 8 characters';
    }

    if (!empty($errors)) {
        $this->response->sendError('Validation failed', 422, ['details' => $errors]);
        return;
    }

    // Check for existing email
    $existing = $this->models->user->get(['email' => $userData['email']]);
    if ($existing) {
        $this->response->sendError('Email already exists', 409);
        return;
    }

    // Prepare user data
    $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
    $userData['status'] = 'active';
    $userData['created_at'] = date('Y-m-d H:i:s');

    // Create user
    $userId = $this->models->user->create($userData);

    if ($userId) {
        $newUser = $this->models->user->get($userId);
        $this->response->sendJson($newUser, 201);
    } else {
        $this->response->sendError('Failed to create user', 500);
    }
}
```

### `delete($where)`

Deletes records from the database table.

**Parameters:**

-   `$where` (int|array): Delete conditions
    -   `int`: Delete record with this ID
    -   `array`: Complex where conditions for Medoo

**Returns:** `bool` - True on success, false on failure

**Throws:** `Exception` - If table not set

**Example:**

```php
// Delete by ID
$success = $this->models->user->delete(123);

// Delete with complex conditions
$success = $this->models->user->delete([
    'status' => 'inactive',
    'last_login[<]' => date('Y-m-d', strtotime('-2 years'))
]);

// Soft delete implementation
public function softDeleteUser($userId)
{
    return $this->models->user->save($userId, [
        'status' => 'deleted',
        'deleted_at' => date('Y-m-d H:i:s')
    ]);
}

// Controller usage with authorization
public function deleteUser()
{
    $userId = $this->router->param('id');

    // Check authorization
    if (!$this->instance->auth->hasPermission('delete_users')) {
        $this->response->sendError('Insufficient permissions', 403);
        return;
    }

    // Check if user exists
    $user = $this->models->user->get($userId);
    if (!$user) {
        $this->response->sendError('User not found', 404);
        return;
    }

    // Prevent self-deletion
    if ($userId == $this->instance->auth->user['id']) {
        $this->response->sendError('Cannot delete your own account', 400);
        return;
    }

    // Use guard delete for safety
    $result = $this->models->user->guardDelete([
        [
            'table' => 'posts',
            'where' => ['author_id' => $userId],
            'customTitle' => 'blog posts'
        ],
        [
            'table' => 'comments',
            'where' => ['user_id' => $userId],
            'customTitle' => 'user comments'
        ]
    ], $userId);

    if ($result['success']) {
        $this->response->sendSuccess(null, 'User deleted successfully');
    } else {
        $this->response->sendError($result['message'], 400, ['blocking' => $result['errors']]);
    }
}
```

---

## Advanced Deletion

### `guardDelete($guards, $id, $associationsToDelete = [])`

Performs safe deletion with dependency checking and cleanup.

**Parameters:**

-   `$guards` (array): Dependency checks to perform
-   `$id` (int): ID of the record to delete
-   `$associationsToDelete` (array): Associated records to clean up

**Returns:** `array` - Response with success/error status and messages

**Guard Array Structure:**

```php
[
    'table' => 'table_name',           // Table to check for dependencies
    'where' => ['foreign_key' => $id], // Where conditions
    'customTitle' => 'Custom Name'     // Optional: custom name for errors
]
```

**Association Array Structure:**

```php
[
    'table' => 'table_name',           // Table containing associated records
    'where' => ['foreign_key' => $id]  // Where conditions for cleanup
]
```

**Example:**

```php
// Safe user deletion with dependency checking
public function safeDeleteUser($userId)
{
    $result = $this->models->user->guardDelete([
        // Check for blog posts
        [
            'table' => 'posts',
            'where' => ['author_id' => $userId],
            'customTitle' => 'blog posts'
        ],
        // Check for active orders
        [
            'table' => 'orders',
            'where' => [
                'user_id' => $userId,
                'status' => ['pending', 'processing']
            ],
            'customTitle' => 'active orders'
        ],
        // Check for admin roles
        [
            'table' => 'user_roles',
            'where' => [
                'user_id' => $userId,
                'role' => 'admin'
            ],
            'customTitle' => 'administrative roles'
        ]
    ], $userId, [
        // Clean up user sessions
        [
            'table' => 'user_sessions',
            'where' => ['user_id' => $userId]
        ],
        // Clean up user preferences
        [
            'table' => 'user_preferences',
            'where' => ['user_id' => $userId]
        ]
    ]);

    return $result;
}

// Company deletion with complex dependencies
public function deleteCompany($companyId)
{
    $result = $this->models->company->guardDelete([
        // Check for active employees
        [
            'table' => 'users',
            'where' => [
                'company_id' => $companyId,
                'status' => 'active'
            ],
            'customTitle' => 'active employees'
        ],
        // Check for pending projects
        [
            'table' => 'projects',
            'where' => [
                'company_id' => $companyId,
                'status[!]' => 'completed'
            ],
            'customTitle' => 'active projects'
        ],
        // Check for outstanding invoices
        [
            'table' => 'invoices',
            'where' => [
                'company_id' => $companyId,
                'status' => ['pending', 'overdue']
            ],
            'customTitle' => 'unpaid invoices'
        ]
    ], $companyId, [
        // Clean up company settings
        [
            'table' => 'company_settings',
            'where' => ['company_id' => $companyId]
        ],
        // Archive company documents
        [
            'table' => 'documents',
            'where' => ['company_id' => $companyId]
        ]
    ]);

    if ($result['success']) {
        // Additional cleanup
        $this->models->analytics->logCompanyDeletion($companyId);
        $this->clearCompanyCache($companyId);
    }

    return $result;
}
```

---

## Security Features

### Hidden Field Filtering

Automatically removes sensitive fields from query results:

```php
class UserModel extends Model
{
    public $table = 'users';
    public $hiddenFields = [
        'password',
        'reset_token',
        'api_secret',
        'two_factor_secret',
        'remember_token'
    ];
}

// These fields are automatically stripped from results
$user = $this->models->user->get(123);
// Returns user data WITHOUT password, tokens, etc.

// Include hidden fields when needed (admin functions)
$userWithSecrets = $this->models->user->options(['includeHiddenFields' => true])
    ->get(123);
// Returns ALL fields including sensitive data
```

### SQL Injection Protection

Uses Medoo ORM with prepared statements:

```php
// Safe parameterized queries
$users = $this->models->user->getMany([
    'email[~]' => $searchTerm,  // LIKE query with escaping
    'status' => $status,        // Exact match with escaping
    'created_at[>]' => $date    // Comparison with escaping
]);

// Complex safe queries
$results = $this->models->user->getMany([
    'OR' => [
        'name[~]' => $query,
        'email[~]' => $query
    ],
    'AND' => [
        'status' => 'active',
        'created_at[>]' => $startDate
    ]
]);
```

### Error Handling

Comprehensive error logging without data exposure:

```php
// Errors are logged but not exposed to users
try {
    $result = $this->models->user->create($userData);
} catch (Exception $e) {
    // Error is logged in system logs
    error_log("User creation failed: " . $e->getMessage());

    // User receives generic error message
    return false;
}
```

---

## Complete Model Examples

### User Model with Authentication

```php
class UserModel extends Model
{
    public $table = 'users';
    public $hiddenFields = ['password', 'reset_token', 'api_secret'];

    public function instantiate()
    {
        $this->defaultOrder = ['created_at' => 'DESC'];
    }

    public function getByEmail($email)
    {
        return $this->get(['email' => $email]);
    }

    public function getActiveUsers()
    {
        return $this->getMany([
            'status' => 'active',
            'ORDER' => ['last_login' => 'DESC']
        ]);
    }

    public function createUser($userData)
    {
        // Hash password before storage
        if (isset($userData['password'])) {
            $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        }

        $userData['created_at'] = date('Y-m-d H:i:s');
        $userData['status'] = 'active';

        return $this->create($userData);
    }

    public function updateLastLogin($userId)
    {
        return $this->save($userId, [
            'last_login' => date('Y-m-d H:i:s'),
            'login_count' => $this->database->client->query(
                "UPDATE users SET login_count = login_count + 1 WHERE id = ?",
                [$userId]
            )->rowCount()
        ]);
    }

    public function softDelete($userId)
    {
        return $this->save($userId, [
            'status' => 'deleted',
            'deleted_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getUserStats($userId)
    {
        $user = $this->get($userId);
        if (!$user) return null;

        return [
            'user' => $user,
            'post_count' => $this->models->post->count(['author_id' => $userId]),
            'comment_count' => $this->models->comment->count(['user_id' => $userId]),
            'last_activity' => $this->getLastActivity($userId)
        ];
    }
}
```

### Post Model with Categories

```php
class PostModel extends Model
{
    public $table = 'posts';
    public $hiddenFields = ['draft_content'];

    public function getPublished()
    {
        return $this->getMany([
            'status' => 'published',
            'published_at[<=]' => date('Y-m-d H:i:s'),
            'ORDER' => ['published_at' => 'DESC']
        ]);
    }

    public function getByCategory($categoryId)
    {
        return $this->getMany([
            'category_id' => $categoryId,
            'status' => 'published',
            'ORDER' => ['published_at' => 'DESC']
        ]);
    }

    public function getByAuthor($authorId)
    {
        return $this->getMany([
            'author_id' => $authorId,
            'ORDER' => ['created_at' => 'DESC']
        ]);
    }

    public function createPost($postData, $authorId)
    {
        $postData['author_id'] = $authorId;
        $postData['created_at'] = date('Y-m-d H:i:s');

        if ($postData['status'] === 'published' && !isset($postData['published_at'])) {
            $postData['published_at'] = date('Y-m-d H:i:s');
        }

        return $this->create($postData);
    }

    public function publish($postId)
    {
        return $this->save($postId, [
            'status' => 'published',
            'published_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getPostWithAuthor($postId)
    {
        $post = $this->get($postId);
        if (!$post) return null;

        $author = $this->models->user->get($post['author_id'], 'id, name, email');
        $post['author'] = $author;

        return $post;
    }

    public function deletePost($postId)
    {
        // Use guard delete to check for comments
        return $this->guardDelete([
            [
                'table' => 'comments',
                'where' => ['post_id' => $postId],
                'customTitle' => 'user comments'
            ]
        ], $postId, [
            // Clean up post tags
            [
                'table' => 'post_tags',
                'where' => ['post_id' => $postId]
            ]
        ]);
    }
}
```

---

## Best Practices

### 1. Security Configuration

```php
class SensitiveModel extends Model
{
    public $table = 'sensitive_data';
    public $hiddenFields = [
        'password',
        'secret_key',
        'private_token',
        'sensitive_info'
    ];
}
```

### 2. Input Validation

```php
public function createRecord($data)
{
    // Always validate input before database operations
    if (empty($data['required_field'])) {
        throw new Exception('Required field missing');
    }

    // Sanitize input
    $data['email'] = filter_var($data['email'], FILTER_SANITIZE_EMAIL);

    return $this->create($data);
}
```

### 3. Error Handling

```php
public function safeOperation($data)
{
    try {
        return $this->create($data);
    } catch (Exception $e) {
        // Log for debugging
        error_log("Operation failed: " . $e->getMessage());

        // Return user-friendly response
        return false;
    }
}
```

### 4. Performance Optimization

```php
// Use specific field selection for performance
$userList = $this->models->user->getMany(
    ['status' => 'active'],
    'id, name, email'  // Only select needed fields
);

// Use counting instead of full data retrieval
$userCount = $this->models->user->count(['status' => 'active']);
```

---

## Framework Integration

The Model API seamlessly integrates with all framework components:

-   **Database**: Primary MySQL operations via Medoo ORM
-   **FileDatabase**: Alternative file-based storage support
-   **Authentication**: User model integration with auth system
-   **Validation**: Input validation support for data integrity
-   **Caching**: Model-level caching for performance optimization
-   **Security**: Built-in protection against SQL injection and data exposure

The Model API provides enterprise-grade data management with comprehensive security features and robust CRUD operations for scalable application development.
