# 📊 Models

Models in Hoist PHP handle data management, business logic, and database interactions. They provide a clean abstraction layer between your controllers and data storage.

## Model Basics

### **Creating Models**

Models extend the base `Model` class and are stored in `source/Application/Models/`:

```php
<?php

class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'email', 'password'];
    protected $hidden = ['password']; // Hidden from JSON output

    public function create($data)
    {
        // Add timestamps
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return parent::create($data);
    }

    public function update($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        return parent::update($id, $data);
    }

    public function findByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    public function getActiveUsers()
    {
        return $this->where('status', 'active')
                   ->orderBy('created_at', 'desc')
                   ->get();
    }
}
```

### **Model Usage in Controllers**

```php
class UserController extends Controller
{
    public function index()
    {
        $users = $this->instance->models->user->getActiveUsers();
        $this->instance->view->render('users/index', ['users' => $users]);
    }

    public function store()
    {
        $validated = $this->request->validate([
            'name' => 'required|min:2',
            'email' => 'required|email|unique:users'
        ]);

        $user = $this->instance->models->user->create($validated);

        return $this->instance->response->redirect('/users/' . $user['id']);
    }
}
```

## FileDatabase Operations

### **Basic CRUD Operations**

```php
class PostModel extends Model
{
    protected $table = 'posts';

    // Create
    public function createPost($data)
    {
        return $this->create([
            'id' => $this->generateId(),
            'title' => $data['title'],
            'content' => $data['content'],
            'author_id' => $data['author_id'],
            'status' => 'draft',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    // Read
    public function getAllPosts()
    {
        return $this->getAll();
    }

    public function getPost($id)
    {
        return $this->find($id);
    }

    // Update
    public function updatePost($id, $data)
    {
        return $this->update($id, array_merge($data, [
            'updated_at' => date('Y-m-d H:i:s')
        ]));
    }

    // Delete
    public function deletePost($id)
    {
        return $this->delete($id);
    }

    // Soft delete
    public function softDelete($id)
    {
        return $this->update($id, [
            'deleted_at' => date('Y-m-d H:i:s')
        ]);
    }
}
```

### **Advanced Querying**

```php
class ProductModel extends Model
{
    protected $table = 'products';

    public function searchProducts($query, $category = null, $minPrice = null, $maxPrice = null)
    {
        $products = $this->getAll();
        $results = [];

        foreach ($products as $product) {
            // Skip deleted products
            if (isset($product['deleted_at'])) {
                continue;
            }

            // Search in title and description
            if ($query &&
                stripos($product['title'], $query) === false &&
                stripos($product['description'], $query) === false) {
                continue;
            }

            // Filter by category
            if ($category && $product['category'] !== $category) {
                continue;
            }

            // Filter by price range
            if ($minPrice && $product['price'] < $minPrice) {
                continue;
            }

            if ($maxPrice && $product['price'] > $maxPrice) {
                continue;
            }

            $results[] = $product;
        }

        return $results;
    }

    public function getPopularProducts($limit = 10)
    {
        $products = $this->getAll();

        // Sort by view count or sales
        usort($products, function($a, $b) {
            return ($b['views'] ?? 0) - ($a['views'] ?? 0);
        });

        return array_slice($products, 0, $limit);
    }

    public function getCategorySummary()
    {
        $products = $this->getAll();
        $summary = [];

        foreach ($products as $product) {
            if (isset($product['deleted_at'])) continue;

            $category = $product['category'];
            if (!isset($summary[$category])) {
                $summary[$category] = [
                    'count' => 0,
                    'total_value' => 0,
                    'avg_price' => 0
                ];
            }

            $summary[$category]['count']++;
            $summary[$category]['total_value'] += $product['price'];
            $summary[$category]['avg_price'] =
                $summary[$category]['total_value'] / $summary[$category]['count'];
        }

        return $summary;
    }
}
```

## MySQL Integration

### **SQL Database Operations**

```php
class OrderModel extends Model
{
    protected $table = 'orders';

    public function createOrder($orderData, $items)
    {
        // Use transaction for data integrity
        return $this->instance->database->client->action(function($database) use ($orderData, $items) {
            // Create order
            $orderId = $database->insert('orders', [
                'user_id' => $orderData['user_id'],
                'total' => $orderData['total'],
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Add order items
            foreach ($items as $item) {
                $database->insert('order_items', [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);

                // Update product stock
                $database->update('products', [
                    'stock[-]' => $item['quantity']
                ], [
                    'id' => $item['product_id']
                ]);
            }

            return $orderId;
        });
    }

    public function getOrdersWithItems($userId = null)
    {
        $where = $userId ? ['orders.user_id' => $userId] : [];

        return $this->instance->database->client->select('orders', [
            '[>]order_items' => ['id' => 'order_id'],
            '[>]products' => ['order_items.product_id' => 'id'],
            '[>]users' => ['user_id' => 'id']
        ], [
            'orders.id',
            'orders.total',
            'orders.status',
            'orders.created_at',
            'users.name(customer_name)',
            'products.title(product_title)',
            'order_items.quantity',
            'order_items.price'
        ], $where);
    }

    public function getMonthlyStats()
    {
        return $this->instance->database->client->query(
            "SELECT
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as order_count,
                SUM(total) as total_sales,
                AVG(total) as avg_order_value
            FROM orders
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC"
        )->fetchAll();
    }
}
```

### **Hybrid Approach (FileDatabase + MySQL)**

```php
class UserModel extends Model
{
    protected $table = 'users';

    public function find($id)
    {
        // Try MySQL first, fallback to FileDatabase
        if ($this->instance->database->hasMySQL()) {
            return $this->instance->database->client->get($this->table, '*', ['id' => $id]);
        }

        return $this->fileDatabase->get($this->table, ['id' => $id]);
    }

    public function create($data)
    {
        if ($this->instance->database->hasMySQL()) {
            $id = $this->instance->database->client->insert($this->table, $data);
            return $this->find($id);
        }

        return $this->fileDatabase->insert($this->table, $data);
    }

    public function getAll()
    {
        if ($this->instance->database->hasMySQL()) {
            return $this->instance->database->client->select($this->table, '*');
        }

        return $this->fileDatabase->all($this->table);
    }
}
```

## Relationships

### **One-to-Many Relationships**

```php
class UserModel extends Model
{
    protected $table = 'users';

    public function posts($userId)
    {
        return $this->instance->models->post->where('user_id', $userId)->get();
    }

    public function getUserWithPosts($userId)
    {
        $user = $this->find($userId);
        if ($user) {
            $user['posts'] = $this->posts($userId);
        }
        return $user;
    }
}

class PostModel extends Model
{
    protected $table = 'posts';

    public function author($postId)
    {
        $post = $this->find($postId);
        return $post ? $this->instance->models->user->find($post['user_id']) : null;
    }

    public function getPostWithAuthor($postId)
    {
        $post = $this->find($postId);
        if ($post) {
            $post['author'] = $this->author($postId);
        }
        return $post;
    }
}
```

### **Many-to-Many Relationships**

```php
class PostModel extends Model
{
    protected $table = 'posts';

    public function tags($postId)
    {
        // Get post tags through pivot table
        $postTags = $this->instance->models->postTag->where('post_id', $postId)->get();
        $tags = [];

        foreach ($postTags as $postTag) {
            $tag = $this->instance->models->tag->find($postTag['tag_id']);
            if ($tag) {
                $tags[] = $tag;
            }
        }

        return $tags;
    }

    public function attachTag($postId, $tagId)
    {
        return $this->instance->models->postTag->create([
            'post_id' => $postId,
            'tag_id' => $tagId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function detachTag($postId, $tagId)
    {
        return $this->instance->models->postTag->deleteWhere([
            'post_id' => $postId,
            'tag_id' => $tagId
        ]);
    }
}
```

## Data Validation and Sanitization

### **Model-Level Validation**

```php
class UserModel extends Model
{
    protected $table = 'users';
    protected $rules = [
        'name' => 'required|min:2|max:100',
        'email' => 'required|email',
        'password' => 'required|min:8'
    ];

    public function create($data)
    {
        // Validate data
        $this->validate($data);

        // Hash password
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Add timestamps
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return parent::create($data);
    }

    private function validate($data)
    {
        $validator = new Validation();
        $result = $validator->validate($data, $this->rules);

        if (!$result['valid']) {
            throw new ValidationException($result['errors']);
        }
    }

    public function updatePassword($userId, $currentPassword, $newPassword)
    {
        $user = $this->find($userId);

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            throw new ValidationException(['current_password' => 'Current password is incorrect']);
        }

        return $this->update($userId, [
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
            'password_updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
```

## Caching in Models

### **Model-Level Caching**

```php
class ProductModel extends Model
{
    protected $table = 'products';

    public function getAllWithCache()
    {
        return $this->instance->cache->remember('products.all', 3600, function() {
            return $this->getAll();
        });
    }

    public function find($id)
    {
        $cacheKey = "product.{$id}";

        return $this->instance->cache->remember($cacheKey, 1800, function() use ($id) {
            return parent::find($id);
        });
    }

    public function update($id, $data)
    {
        $result = parent::update($id, $data);

        if ($result) {
            // Clear cache
            $this->instance->cache->forget("product.{$id}");
            $this->instance->cache->forget('products.all');
        }

        return $result;
    }

    public function getPopularProducts($limit = 10)
    {
        $cacheKey = "products.popular.{$limit}";

        return $this->instance->cache->remember($cacheKey, 1800, function() use ($limit) {
            $products = $this->getAll();

            // Sort by popularity score
            usort($products, function($a, $b) {
                $scoreA = ($a['views'] ?? 0) * 0.3 + ($a['sales'] ?? 0) * 0.7;
                $scoreB = ($b['views'] ?? 0) * 0.3 + ($b['sales'] ?? 0) * 0.7;
                return $scoreB - $scoreA;
            });

            return array_slice($products, 0, $limit);
        });
    }
}
```

## Events and Observers

### **Model Events**

```php
class UserModel extends Model
{
    protected $table = 'users';

    public function create($data)
    {
        // Before create event
        $this->beforeCreate($data);

        $user = parent::create($data);

        // After create event
        $this->afterCreate($user);

        return $user;
    }

    protected function beforeCreate(&$data)
    {
        // Normalize email
        if (isset($data['email'])) {
            $data['email'] = strtolower(trim($data['email']));
        }

        // Generate username if not provided
        if (!isset($data['username']) && isset($data['email'])) {
            $data['username'] = $this->generateUsername($data['email']);
        }
    }

    protected function afterCreate($user)
    {
        // Send welcome email
        $this->sendWelcomeEmail($user);

        // Log user registration
        $this->instance->models->activityLog->log([
            'user_id' => $user['id'],
            'action' => 'user_registered',
            'data' => ['email' => $user['email']]
        ]);

        // Clear related caches
        $this->instance->cache->forget('users.count');
        $this->instance->cache->tags(['users'])->flush();
    }

    private function generateUsername($email)
    {
        $base = explode('@', $email)[0];
        $username = preg_replace('/[^a-zA-Z0-9]/', '', $base);

        // Ensure uniqueness
        $counter = 1;
        $originalUsername = $username;

        while ($this->findByUsername($username)) {
            $username = $originalUsername . $counter;
            $counter++;
        }

        return $username;
    }

    private function sendWelcomeEmail($user)
    {
        // Email sending logic
    }
}
```

## Best Practices

### **1. Repository Pattern**

```php
class UserRepository
{
    private $userModel;
    private $cache;

    public function __construct($instance)
    {
        $this->userModel = $instance->models->user;
        $this->cache = $instance->cache;
    }

    public function findActiveUser($id)
    {
        $cacheKey = "active_user.{$id}";

        return $this->cache->remember($cacheKey, 1800, function() use ($id) {
            $user = $this->userModel->find($id);
            return ($user && $user['status'] === 'active') ? $user : null;
        });
    }

    public function searchUsers($query, $filters = [])
    {
        // Complex search logic
        $users = $this->userModel->getAll();

        // Apply filters
        if (isset($filters['status'])) {
            $users = array_filter($users, function($user) use ($filters) {
                return $user['status'] === $filters['status'];
            });
        }

        // Apply search
        if ($query) {
            $users = array_filter($users, function($user) use ($query) {
                return stripos($user['name'], $query) !== false ||
                       stripos($user['email'], $query) !== false;
            });
        }

        return array_values($users);
    }
}
```

### **2. Data Transformation**

```php
class UserModel extends Model
{
    protected $table = 'users';

    public function toArray($user)
    {
        // Transform model data for API responses
        return [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'avatar' => $this->getAvatarUrl($user),
            'created_at' => $user['created_at'],
            'is_active' => $user['status'] === 'active'
        ];
    }

    public function toPublicArray($user)
    {
        // Public-safe version (no email)
        return [
            'id' => (int)$user['id'],
            'name' => $user['name'],
            'avatar' => $this->getAvatarUrl($user),
            'joined' => date('Y', strtotime($user['created_at']))
        ];
    }

    private function getAvatarUrl($user)
    {
        return $user['avatar'] ?? '/assets/images/default-avatar.png';
    }
}
```

### **3. Scopes and Filters**

```php
class PostModel extends Model
{
    protected $table = 'posts';

    public function published()
    {
        return array_filter($this->getAll(), function($post) {
            return $post['status'] === 'published' &&
                   strtotime($post['published_at']) <= time();
        });
    }

    public function byAuthor($authorId)
    {
        return array_filter($this->getAll(), function($post) use ($authorId) {
            return $post['author_id'] == $authorId;
        });
    }

    public function recent($limit = 10)
    {
        $posts = $this->published();

        usort($posts, function($a, $b) {
            return strtotime($b['published_at']) - strtotime($a['published_at']);
        });

        return array_slice($posts, 0, $limit);
    }

    // Chainable scopes
    public function scope($scope, ...$args)
    {
        switch ($scope) {
            case 'published':
                return $this->published();
            case 'byAuthor':
                return $this->byAuthor($args[0]);
            case 'recent':
                return $this->recent($args[0] ?? 10);
            default:
                return $this->getAll();
        }
    }
}
```

---

Models provide the foundation for clean, maintainable data management in your Hoist PHP applications. Use them to encapsulate business logic, maintain data integrity, and provide a consistent interface to your data layer.

**Next:** [Views](views.md) - Learn about template rendering and presentation.
