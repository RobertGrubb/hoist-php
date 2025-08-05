# 📊 Database & Storage

Hoist PHP provides flexible database options to suit different application needs, from rapid prototyping with FileDatabase to production-ready SQL databases.

## Database Drivers

### **FileDatabase (Default)**

JSON-based storage for rapid development and simple applications.

**Benefits:**

-   ✅ Zero configuration
-   ✅ No server setup required
-   ✅ Version control friendly
-   ✅ Perfect for prototyping
-   ✅ Automatic backup capabilities

**Structure:**

```
source/Application/Database/
├── app/                    # Default namespace
│   ├── users.json         # User records
│   ├── posts.json         # Blog posts
│   └── settings.json      # App settings
├── cache/                 # Cache namespace
│   └── sessions.json      # Session data
└── logs/                  # Logs namespace
    └── activity.json      # Activity logs
```

### **SQL Databases**

Traditional relational databases for complex applications.

**Supported:**

-   MySQL / MariaDB
-   PostgreSQL
-   SQLite
-   SQL Server (with appropriate drivers)

## Configuration

### **Database Settings**

```php
// Core/Libraries/Settings.php
'database' => [
    'default' => 'file', // or 'mysql', 'postgres', etc.

    'connections' => [
        'file' => [
            'driver' => 'json',
            'path' => 'Application/Database/',
            'namespace' => 'app'
        ],

        'mysql' => [
            'driver' => 'mysql',
            'host' => 'localhost',
            'port' => 3306,
            'database' => 'hoist_app',
            'username' => 'root',
            'password' => 'secret',
            'charset' => 'utf8mb4',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        ],

        'postgres' => [
            'driver' => 'pgsql',
            'host' => 'localhost',
            'port' => 5432,
            'database' => 'hoist_app',
            'username' => 'postgres',
            'password' => 'secret',
            'charset' => 'utf8'
        ],

        'sqlite' => [
            'driver' => 'sqlite',
            'database' => 'Application/Database/app.sqlite'
        ]
    ]
]
```

### **Environment-Based Configuration**

```php
// .env file
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=hoist_production
DB_USERNAME=app_user
DB_PASSWORD=secure_password

// Settings.php
'database' => [
    'default' => $_ENV['DB_CONNECTION'] ?? 'file',
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? 3306,
            'database' => $_ENV['DB_DATABASE'] ?? 'hoist_app',
            'username' => $_ENV['DB_USERNAME'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? ''
        ]
    ]
]
```

## FileDatabase Usage

### **Basic Operations**

```php
class UserModel extends Model
{
    protected $table = 'users';

    public function createUser($data)
    {
        // Insert new record
        return $this->create([
            'id' => $this->generateId(),
            'name' => $data['name'],
            'email' => $data['email'],
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getAllUsers()
    {
        // Get all records
        return $this->getAll();
    }

    public function findUser($id)
    {
        // Find by ID
        return $this->find($id);
    }

    public function updateUser($id, $data)
    {
        // Update record
        return $this->update($id, [
            'name' => $data['name'],
            'email' => $data['email'],
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function deleteUser($id)
    {
        // Delete record
        return $this->delete($id);
    }
}
```

### **Advanced Querying**

```php
class PostModel extends Model
{
    protected $table = 'posts';

    public function getPublishedPosts()
    {
        return $this->where('status', 'published')
                   ->orderBy('created_at', 'desc')
                   ->get();
    }

    public function getPostsByAuthor($authorId)
    {
        return $this->where('author_id', $authorId)
                   ->where('status', '!=', 'deleted')
                   ->get();
    }

    public function searchPosts($query)
    {
        $results = [];
        $posts = $this->getAll();

        foreach ($posts as $post) {
            if (stripos($post['title'], $query) !== false ||
                stripos($post['content'], $query) !== false) {
                $results[] = $post;
            }
        }

        return $results;
    }

    public function getPostStats()
    {
        $posts = $this->getAll();
        $stats = [
            'total' => count($posts),
            'published' => 0,
            'draft' => 0,
            'by_author' => []
        ];

        foreach ($posts as $post) {
            $stats[$post['status']]++;
            $stats['by_author'][$post['author_id']] =
                ($stats['by_author'][$post['author_id']] ?? 0) + 1;
        }

        return $stats;
    }
}
```

### **File Structure Management**

```php
class FileDatabase
{
    public function setNamespace($namespace)
    {
        // Switch to different namespace
        $this->namespace = $namespace;
        $this->basePath = $this->basePath . $namespace . '/';
    }

    public function backup($table = null)
    {
        // Backup specific table or all tables
        $backupDir = $this->basePath . '../backups/' . date('Y-m-d/');
        $this->createDirectory($backupDir);

        if ($table) {
            copy(
                $this->getTablePath($table),
                $backupDir . $table . '_' . time() . '.json'
            );
        } else {
            // Backup all tables
            foreach ($this->getAllTables() as $tableName) {
                copy(
                    $this->getTablePath($tableName),
                    $backupDir . $tableName . '_' . time() . '.json'
                );
            }
        }
    }

    public function import($filePath, $table)
    {
        // Import data from external JSON file
        if (file_exists($filePath)) {
            $data = json_decode(file_get_contents($filePath), true);
            $this->writeTable($table, $data);
            return true;
        }
        return false;
    }
}
```

## SQL Database Usage

### **Direct Database Access**

```php
class UserController extends Controller
{
    public function index()
    {
        // Using Medoo query builder
        $users = $this->instance->database->client->select('users', [
            'id', 'name', 'email', 'created_at'
        ], [
            'status' => 'active',
            'ORDER' => ['created_at' => 'DESC'],
            'LIMIT' => 20
        ]);

        return $this->instance->response->json($users);
    }

    public function store()
    {
        $validated = $this->request->validate([
            'name' => 'required|min:2',
            'email' => 'required|email|unique:users'
        ]);

        // Insert new user
        $userId = $this->instance->database->client->insert('users', [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => password_hash($this->request->input('password'), PASSWORD_DEFAULT),
            'created_at' => date('Y-m-d H:i:s')
        ]);

        if ($userId) {
            $user = $this->instance->database->client->get('users', '*', ['id' => $userId]);
            return $this->instance->response->json($user, 201);
        }

        return $this->instance->response->sendError('Failed to create user');
    }
}
```

### **Advanced SQL Queries**

```php
class ReportController extends Controller
{
    public function userActivity()
    {
        // Complex query with joins
        $results = $this->instance->database->client->select('users', [
            '[>]posts' => ['id' => 'user_id'],
            '[>]comments' => ['id' => 'user_id']
        ], [
            'users.id',
            'users.name',
            'users.email',
            'post_count' => 'COUNT(posts.id)',
            'comment_count' => 'COUNT(comments.id)'
        ], [
            'users.status' => 'active',
            'GROUP' => ['users.id']
        ]);

        return $this->instance->response->json($results);
    }

    public function monthlyStats()
    {
        // Raw SQL for complex analytics
        $sql = "
            SELECT
                DATE_FORMAT(created_at, '%Y-%m') as month,
                COUNT(*) as user_count,
                AVG(age) as avg_age
            FROM users
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month DESC
        ";

        $results = $this->instance->database->client->query($sql)->fetchAll();

        return $this->instance->response->json($results);
    }
}
```

### **Transactions**

```php
class OrderController extends Controller
{
    public function createOrder()
    {
        // Start transaction
        $this->instance->database->client->action(function($database) {
            // Create order
            $orderId = $database->insert('orders', [
                'user_id' => $this->instance->auth->id(),
                'total' => $this->request->input('total'),
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Add order items
            foreach ($this->request->input('items') as $item) {
                $database->insert('order_items', [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price']
                ]);

                // Update inventory
                $database->update('products', [
                    'stock[-]' => $item['quantity']
                ], [
                    'id' => $item['product_id']
                ]);
            }

            // Send confirmation email
            $this->sendOrderConfirmation($orderId);

            return $orderId;
        });
    }
}
```

## Model Patterns

### **Base Model Class**

```php
abstract class BaseModel extends Model
{
    protected $table;
    protected $primaryKey = 'id';
    protected $timestamps = true;
    protected $fillable = [];
    protected $guarded = ['id'];

    public function create($data)
    {
        // Add timestamps
        if ($this->timestamps) {
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        // Filter fillable fields
        if (!empty($this->fillable)) {
            $data = array_intersect_key($data, array_flip($this->fillable));
        }

        // Remove guarded fields
        foreach ($this->guarded as $field) {
            unset($data[$field]);
        }

        return parent::create($data);
    }

    public function update($id, $data)
    {
        if ($this->timestamps) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        return parent::update($id, $data);
    }

    public function softDelete($id)
    {
        return $this->update($id, [
            'deleted_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function restore($id)
    {
        return $this->update($id, [
            'deleted_at' => null
        ]);
    }
}
```

### **Relationship Methods**

```php
class UserModel extends BaseModel
{
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password'];

    public function posts()
    {
        return $this->hasMany('PostModel', 'user_id');
    }

    public function profile()
    {
        return $this->hasOne('ProfileModel', 'user_id');
    }

    // Custom relationship implementation
    public function getPostsWithComments($userId)
    {
        $posts = $this->instance->models->post->where('user_id', $userId)->get();

        foreach ($posts as &$post) {
            $post['comments'] = $this->instance->models->comment
                ->where('post_id', $post['id'])
                ->orderBy('created_at', 'asc')
                ->get();
        }

        return $posts;
    }
}

class PostModel extends BaseModel
{
    protected $table = 'posts';
    protected $fillable = ['title', 'content', 'user_id', 'status'];

    public function author()
    {
        return $this->belongsTo('UserModel', 'user_id');
    }

    public function comments()
    {
        return $this->hasMany('CommentModel', 'post_id');
    }

    public function tags()
    {
        return $this->belongsToMany('TagModel', 'post_tags', 'post_id', 'tag_id');
    }
}
```

## Data Migration

### **FileDatabase Migration**

```php
class DatabaseMigrator
{
    public function migrateFromV1ToV2()
    {
        // Migrate user table structure
        $users = $this->instance->database->getTable('users');

        foreach ($users as &$user) {
            // Add new fields
            $user['avatar'] = null;
            $user['verified_at'] = null;

            // Transform existing data
            if (isset($user['full_name'])) {
                $user['name'] = $user['full_name'];
                unset($user['full_name']);
            }

            // Update timestamp format
            if (isset($user['created'])) {
                $user['created_at'] = date('Y-m-d H:i:s', strtotime($user['created']));
                unset($user['created']);
            }
        }

        $this->instance->database->writeTable('users', $users);
    }

    public function seedData()
    {
        // Seed initial data
        $defaultUsers = [
            [
                'id' => 1,
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'role' => 'admin',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'id' => 2,
                'name' => 'Demo User',
                'email' => 'demo@example.com',
                'role' => 'user',
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];

        $this->instance->database->writeTable('users', $defaultUsers);
    }
}
```

### **SQL Database Migration**

```php
class SqlMigrator
{
    public function createTables()
    {
        // Users table
        $this->instance->database->client->query("
            CREATE TABLE IF NOT EXISTS users (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                role ENUM('admin', 'user') DEFAULT 'user',
                avatar VARCHAR(255) NULL,
                verified_at TIMESTAMP NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        ");

        // Posts table
        $this->instance->database->client->query("
            CREATE TABLE IF NOT EXISTS posts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                title VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                status ENUM('draft', 'published', 'archived') DEFAULT 'draft',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            )
        ");

        // Add indexes
        $this->instance->database->client->query("
            CREATE INDEX idx_posts_user_id ON posts(user_id);
            CREATE INDEX idx_posts_status ON posts(status);
        ");
    }

    public function addColumn($table, $column, $definition)
    {
        $this->instance->database->client->query("
            ALTER TABLE {$table} ADD COLUMN {$column} {$definition}
        ");
    }
}
```

## Performance Optimization

### **FileDatabase Optimization**

```php
class OptimizedFileDatabase extends FileDatabase
{
    private $indexCache = [];

    public function createIndex($table, $field)
    {
        $data = $this->getTable($table);
        $index = [];

        foreach ($data as $record) {
            $value = $record[$field] ?? null;
            if ($value !== null) {
                $index[$value][] = $record['id'];
            }
        }

        $this->indexCache[$table][$field] = $index;
        $this->saveIndex($table, $field, $index);
    }

    public function findByIndex($table, $field, $value)
    {
        $index = $this->loadIndex($table, $field);
        $ids = $index[$value] ?? [];

        return array_map(function($id) use ($table) {
            return $this->find($table, $id);
        }, $ids);
    }

    public function optimizeTable($table)
    {
        // Remove deleted records
        $data = $this->getTable($table);
        $optimized = array_filter($data, function($record) {
            return !isset($record['deleted_at']);
        });

        // Reindex
        $reindexed = array_values($optimized);
        $this->writeTable($table, $reindexed);

        return count($data) - count($reindexed);
    }
}
```

### **Database Connection Pooling**

```php
class DatabaseManager
{
    private $connections = [];
    private $maxConnections = 10;

    public function getConnection($name = 'default')
    {
        if (!isset($this->connections[$name])) {
            $this->connections[$name] = $this->createConnection($name);
        }

        return $this->connections[$name];
    }

    public function closeConnections()
    {
        foreach ($this->connections as $connection) {
            $connection = null; // Close PDO connection
        }
        $this->connections = [];
    }
}
```

---

Hoist PHP's flexible database system grows with your application, from simple FileDatabase for prototypes to full SQL databases for production systems.

**Next:** [Advanced Features](../advanced/) - Explore caching, authentication, and more.
