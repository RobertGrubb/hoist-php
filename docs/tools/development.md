# 🔧 Developer Tools

Comprehensive toolkit for efficient development with Hoist PHP framework.

## Getting Started

### **Development Environment Setup**

**VS Code Extensions (Recommended):**

```json
{
    "recommendations": [
        "bmewburn.vscode-intelephense-client",
        "xdebug.php-debug",
        "ms-vscode.vscode-json",
        "formulahendry.auto-rename-tag",
        "bradlc.vscode-tailwindcss",
        "ms-vscode.live-server"
    ]
}
```

**PHP Configuration (php.ini):**

```ini
; Development settings
display_errors = On
error_reporting = E_ALL
log_errors = On
error_log = /var/log/php_errors.log

; Debugging
xdebug.mode = debug,develop
xdebug.start_with_request = yes
xdebug.client_host = localhost
xdebug.client_port = 9003

; File uploads
upload_max_filesize = 100M
post_max_size = 100M
max_file_uploads = 20

; Memory and execution
memory_limit = 512M
max_execution_time = 300
```

## Request Demo System

### **Interactive Testing Interface**

Access the comprehensive demo at `/request-demo` to test all Request class features:

**Available Demos:**

-   ✅ Basic form handling with validation
-   ✅ File upload with progress tracking
-   ✅ AJAX/JSON API requests
-   ✅ Security features (CSRF, sanitization)
-   ✅ Advanced input types (arrays, nested data)
-   ✅ Error handling demonstrations

**Demo Controller Features:**

```php
class RequestDemoController extends Controller
{
    public function index()
    {
        // Interactive demo page with all features
    }

    public function apiForm()
    {
        // Test API endpoints with live data
    }

    public function fileUpload()
    {
        // File upload testing with validation
    }

    public function securityDemo()
    {
        // Security feature demonstrations
    }
}
```

## Built-in CLI Tools

### **Framework CLI Helper**

Create `cli.php` in your project root:

```php
#!/usr/bin/env php
<?php

require_once 'source/Core/Bootstrap.php';

class HoistCLI
{
    private $instance;

    public function __construct()
    {
        $this->instance = new Instance();
    }

    public function run($args)
    {
        $command = $args[1] ?? 'help';

        switch ($command) {
            case 'make:controller':
                $this->makeController($args[2] ?? null);
                break;

            case 'make:model':
                $this->makeModel($args[2] ?? null);
                break;

            case 'cache:clear':
                $this->clearCache();
                break;

            case 'db:seed':
                $this->seedDatabase();
                break;

            case 'serve':
                $this->startServer($args[2] ?? '8000');
                break;

            default:
                $this->showHelp();
        }
    }

    private function makeController($name)
    {
        if (!$name) {
            echo "Please provide a controller name.\n";
            return;
        }

        $template = '<?php

class ' . $name . ' extends Controller
{
    public function index()
    {
        $this->instance->view->render("' . strtolower(str_replace('Controller', '', $name)) . '/index");
    }

    public function show($id)
    {
        // Show specific record
    }

    public function store()
    {
        // Create new record
    }

    public function update($id)
    {
        // Update existing record
    }

    public function destroy($id)
    {
        // Delete record
    }
}';

        $path = 'source/Application/Controllers/' . $name . '.php';
        file_put_contents($path, $template);
        echo "Controller created: {$path}\n";
    }

    private function makeModel($name)
    {
        if (!$name) {
            echo "Please provide a model name.\n";
            return;
        }

        $table = strtolower(str_replace('Model', '', $name)) . 's';

        $template = '<?php

class ' . $name . ' extends Model
{
    protected $table = "' . $table . '";

    public function getAll()
    {
        return parent::getAll();
    }

    public function find($id)
    {
        return parent::find($id);
    }

    public function create($data)
    {
        $data["created_at"] = date("Y-m-d H:i:s");
        return parent::create($data);
    }

    public function update($id, $data)
    {
        $data["updated_at"] = date("Y-m-d H:i:s");
        return parent::update($id, $data);
    }
}';

        $path = 'source/Application/Models/' . $name . '.php';
        file_put_contents($path, $template);
        echo "Model created: {$path}\n";
    }

    private function clearCache()
    {
        $this->instance->cache->flush();
        echo "Cache cleared successfully.\n";
    }

    private function seedDatabase()
    {
        echo "Seeding database...\n";

        // Create default admin user
        $adminExists = $this->instance->models->user->where('email', 'admin@example.com')->first();

        if (!$adminExists) {
            $this->instance->models->user->create([
                'name' => 'Administrator',
                'email' => 'admin@example.com',
                'password' => password_hash('admin123', PASSWORD_DEFAULT),
                'role' => 'admin'
            ]);
            echo "Admin user created (admin@example.com / admin123)\n";
        }

        echo "Database seeded successfully.\n";
    }

    private function startServer($port)
    {
        echo "Starting development server on localhost:{$port}...\n";
        echo "Press Ctrl+C to stop.\n\n";

        chdir('source/public');
        passthru("php -S localhost:{$port}");
    }

    private function showHelp()
    {
        echo "Hoist PHP CLI Tool\n\n";
        echo "Available commands:\n";
        echo "  make:controller <name>  Create a new controller\n";
        echo "  make:model <name>       Create a new model\n";
        echo "  cache:clear             Clear application cache\n";
        echo "  db:seed                 Seed database with default data\n";
        echo "  serve [port]            Start development server (default: 8000)\n";
        echo "  help                    Show this help message\n\n";
    }
}

// Run CLI
$cli = new HoistCLI();
$cli->run($argv);
```

**Usage:**

```bash
# Make executable
chmod +x cli.php

# Create new controller
./cli.php make:controller UserController

# Create new model
./cli.php make:model UserModel

# Start development server
./cli.php serve 8080

# Clear cache
./cli.php cache:clear

# Seed database
./cli.php db:seed
```

## Debugging Tools

### **Debug Helper Class**

Create `source/Core/Libraries/Debug.php`:

```php
<?php

class Debug
{
    private static $enabled = true;
    private static $queries = [];
    private static $timers = [];

    public static function enable($enabled = true)
    {
        self::$enabled = $enabled;
    }

    public static function dump($var, $label = null)
    {
        if (!self::$enabled) return;

        echo '<div style="background: #f8f9fa; border: 1px solid #dee2e6; padding: 15px; margin: 10px 0; font-family: monospace;">';
        if ($label) {
            echo '<strong style="color: #495057;">' . htmlspecialchars($label) . ':</strong><br>';
        }
        echo '<pre style="margin: 5px 0 0 0; color: #212529;">';
        var_dump($var);
        echo '</pre></div>';
    }

    public static function dd($var, $label = null)
    {
        self::dump($var, $label);
        die();
    }

    public static function logQuery($sql, $params = [], $time = null)
    {
        if (!self::$enabled) return;

        self::$queries[] = [
            'sql' => $sql,
            'params' => $params,
            'time' => $time,
            'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
        ];
    }

    public static function startTimer($name)
    {
        self::$timers[$name] = microtime(true);
    }

    public static function endTimer($name)
    {
        if (isset(self::$timers[$name])) {
            $time = microtime(true) - self::$timers[$name];
            echo "<div style='padding: 5px; background: #e3f2fd; border-left: 3px solid #2196f3;'>Timer '{$name}': " . round($time * 1000, 2) . "ms</div>";
            unset(self::$timers[$name]);
        }
    }

    public static function showQueries()
    {
        if (!self::$enabled || empty(self::$queries)) return;

        echo '<div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 10px 0;">';
        echo '<h4 style="margin: 0 0 10px 0; color: #856404;">Database Queries (' . count(self::$queries) . ')</h4>';

        foreach (self::$queries as $i => $query) {
            echo '<div style="margin-bottom: 10px; padding: 10px; background: white; border-left: 3px solid #ffc107;">';
            echo '<strong>Query ' . ($i + 1) . ':</strong><br>';
            echo '<code style="background: #f8f9fa; padding: 2px 4px;">' . htmlspecialchars($query['sql']) . '</code>';

            if (!empty($query['params'])) {
                echo '<br><strong>Parameters:</strong> ' . json_encode($query['params']);
            }

            if ($query['time']) {
                echo '<br><strong>Time:</strong> ' . round($query['time'] * 1000, 2) . 'ms';
            }
            echo '</div>';
        }
        echo '</div>';
    }

    public static function showMemoryUsage()
    {
        if (!self::$enabled) return;

        $current = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);

        echo '<div style="background: #e8f5e8; border: 1px solid #4caf50; padding: 10px; margin: 10px 0;">';
        echo '<strong>Memory Usage:</strong> ' . self::formatBytes($current);
        echo ' | <strong>Peak:</strong> ' . self::formatBytes($peak);
        echo '</div>';
    }

    private static function formatBytes($size)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $size >= 1024 && $i < 3; $i++) {
            $size /= 1024;
        }
        return round($size, 2) . ' ' . $units[$i];
    }
}
```

### **Debug Toolbar**

Add to your layout template:

```php
<?php if ($_ENV['APP_DEBUG'] ?? false): ?>
<div id="debug-toolbar" style="position: fixed; bottom: 0; left: 0; right: 0; background: #2c3e50; color: white; padding: 10px; font-family: monospace; font-size: 12px; z-index: 9999;">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <div>
            <strong>Hoist PHP Debug</strong> |
            Time: <?= round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, 2) ?>ms |
            Memory: <?= round(memory_get_peak_usage(true) / 1024 / 1024, 2) ?>MB
        </div>
        <div>
            <button onclick="document.getElementById('debug-toolbar').style.display='none'" style="background: #e74c3c; color: white; border: none; padding: 2px 8px; cursor: pointer;">×</button>
        </div>
    </div>
</div>

<?php
Debug::showQueries();
Debug::showMemoryUsage();
?>
<?php endif; ?>
```

## Testing Framework

### **Simple Test Runner**

Create `tests/TestRunner.php`:

```php
<?php

class TestRunner
{
    private $tests = [];
    private $passed = 0;
    private $failed = 0;

    public function addTest($name, $callback)
    {
        $this->tests[] = ['name' => $name, 'callback' => $callback];
    }

    public function run()
    {
        echo "Running tests...\n\n";

        foreach ($this->tests as $test) {
            try {
                $test['callback']();
                $this->passed++;
                echo "✓ " . $test['name'] . "\n";
            } catch (Exception $e) {
                $this->failed++;
                echo "✗ " . $test['name'] . " - " . $e->getMessage() . "\n";
            }
        }

        echo "\n" . ($this->passed + $this->failed) . " tests run, ";
        echo $this->passed . " passed, " . $this->failed . " failed.\n";

        return $this->failed === 0;
    }

    public function assertEquals($expected, $actual, $message = null)
    {
        if ($expected !== $actual) {
            throw new Exception($message ?: "Expected '$expected', got '$actual'");
        }
    }

    public function assertTrue($condition, $message = null)
    {
        if (!$condition) {
            throw new Exception($message ?: "Assertion failed");
        }
    }

    public function assertFalse($condition, $message = null)
    {
        if ($condition) {
            throw new Exception($message ?: "Expected false, got true");
        }
    }
}

// Example test file
require_once '../source/Core/Bootstrap.php';

$test = new TestRunner();

$test->addTest('User Model Basic Operations', function() use ($test) {
    $instance = new Instance();
    $userModel = $instance->models->user;

    // Test create
    $userData = [
        'name' => 'Test User',
        'email' => 'test@example.com'
    ];
    $user = $userModel->create($userData);
    $test->assertTrue($user !== false, 'User creation failed');

    // Test find
    $foundUser = $userModel->find($user['id']);
    $test->assertEquals('Test User', $foundUser['name'], 'User name mismatch');

    // Test update
    $updated = $userModel->update($user['id'], ['name' => 'Updated User']);
    $test->assertTrue($updated, 'User update failed');

    // Test delete
    $deleted = $userModel->delete($user['id']);
    $test->assertTrue($deleted, 'User deletion failed');
});

$test->addTest('Request Class Validation', function() use ($test) {
    // Mock request data
    $_POST = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'age' => '25'
    ];

    $instance = new Instance();
    $request = $instance->request;

    // Test input retrieval
    $test->assertEquals('John Doe', $request->input('name'));
    $test->assertEquals('default', $request->input('nonexistent', 'default'));

    // Test validation
    try {
        $validated = $request->validate([
            'name' => 'required|min:2',
            'email' => 'required|email',
            'age' => 'required|integer'
        ]);
        $test->assertTrue(true, 'Validation should pass');
    } catch (ValidationException $e) {
        $test->assertTrue(false, 'Validation should not fail');
    }
});

// Run tests
$success = $test->run();
exit($success ? 0 : 1);
```

## Performance Profiling

### **Performance Monitor**

```php
class PerformanceMonitor
{
    private static $checkpoints = [];
    private static $startTime;

    public static function start()
    {
        self::$startTime = microtime(true);
        self::$checkpoints = [];
    }

    public static function checkpoint($name)
    {
        self::$checkpoints[] = [
            'name' => $name,
            'time' => microtime(true),
            'memory' => memory_get_usage(true)
        ];
    }

    public static function report()
    {
        if (empty(self::$checkpoints)) return;

        echo '<div style="background: #f8f9fa; padding: 15px; margin: 10px 0; border: 1px solid #dee2e6;">';
        echo '<h4>Performance Report</h4>';
        echo '<table style="width: 100%; border-collapse: collapse;">';
        echo '<tr style="background: #e9ecef;"><th>Checkpoint</th><th>Time (ms)</th><th>Memory (MB)</th><th>Diff (ms)</th></tr>';

        $lastTime = self::$startTime;
        foreach (self::$checkpoints as $checkpoint) {
            $time = round(($checkpoint['time'] - self::$startTime) * 1000, 2);
            $diff = round(($checkpoint['time'] - $lastTime) * 1000, 2);
            $memory = round($checkpoint['memory'] / 1024 / 1024, 2);

            echo '<tr>';
            echo '<td style="padding: 5px; border: 1px solid #dee2e6;">' . $checkpoint['name'] . '</td>';
            echo '<td style="padding: 5px; border: 1px solid #dee2e6;">' . $time . '</td>';
            echo '<td style="padding: 5px; border: 1px solid #dee2e6;">' . $memory . '</td>';
            echo '<td style="padding: 5px; border: 1px solid #dee2e6;">' . $diff . '</td>';
            echo '</tr>';

            $lastTime = $checkpoint['time'];
        }
        echo '</table></div>';
    }
}

// Usage in your application
PerformanceMonitor::start();
PerformanceMonitor::checkpoint('Bootstrap Complete');
// ... your code ...
PerformanceMonitor::checkpoint('Database Query');
// ... more code ...
PerformanceMonitor::checkpoint('View Rendered');
PerformanceMonitor::report();
```

## IDE Integration

### **PhpStorm Configuration**

Create `.phpstorm.meta.php`:

```php
<?php

namespace PHPSTORM_META {

    // Enable autocompletion for Instance properties
    override(\Instance::__get(0), map([
        'auth' => \Auth::class,
        'cache' => \Cache::class,
        'database' => \Database::class,
        'models' => \ModelContainer::class,
        'request' => \Request::class,
        'response' => \Response::class,
        'router' => \Router::class,
        'session' => \Session::class,
        'settings' => \Settings::class,
        'view' => \View::class,
    ]));

    // Model autocompletion
    override(\ModelContainer::__get(0), map([
        'user' => \UserModel::class,
        'post' => \PostModel::class,
    ]));
}
```

### **VS Code Snippets**

Create `.vscode/snippets.json`:

```json
{
    "Hoist Controller": {
        "prefix": "hoist-controller",
        "body": [
            "<?php",
            "",
            "class ${1:Controller}Controller extends Controller",
            "{",
            "    public function index()",
            "    {",
            "        $data = $this->instance->models->${2:model}->getAll();",
            "        $this->instance->view->render('${3:view}', ['data' => $data]);",
            "    }",
            "    ",
            "    public function show($$id)",
            "    {",
            "        $item = $this->instance->models->${2:model}->find($$id);",
            "        if (!$item) {",
            "            return $this->instance->response->sendError('Not found', 404);",
            "        }",
            "        $this->instance->view->render('${3:view}/show', ['item' => $item]);",
            "    }",
            "}"
        ],
        "description": "Create a Hoist PHP controller"
    },

    "Hoist Model": {
        "prefix": "hoist-model",
        "body": [
            "<?php",
            "",
            "class ${1:Model}Model extends Model",
            "{",
            "    protected $table = '${2:table}';",
            "    ",
            "    public function getAll()",
            "    {",
            "        return parent::getAll();",
            "    }",
            "    ",
            "    public function create($data)",
            "    {",
            "        $data['created_at'] = date('Y-m-d H:i:s');",
            "        return parent::create($data);",
            "    }",
            "}"
        ],
        "description": "Create a Hoist PHP model"
    }
}
```

## Development Workflow

### **Git Hooks**

Create `.git/hooks/pre-commit`:

```bash
#!/bin/bash

# Run PHP syntax check
echo "Checking PHP syntax..."
find source/ -name "*.php" -exec php -l {} \; | grep -v "No syntax errors detected"

if [ $? -eq 0 ]; then
    echo "PHP syntax errors found. Commit aborted."
    exit 1
fi

# Run tests
echo "Running tests..."
php tests/TestRunner.php

if [ $? -ne 0 ]; then
    echo "Tests failed. Commit aborted."
    exit 1
fi

echo "All checks passed. Proceeding with commit."
exit 0
```

### **Environment Management**

Create `env.example`:

```bash
# Application
APP_NAME="Hoist PHP App"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=file
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=hoist_app
DB_USERNAME=root
DB_PASSWORD=

# Cache
CACHE_DRIVER=file
CACHE_PREFIX=hoist_

# Session
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Security
APP_KEY=your-secret-key-here
CSRF_TOKEN_NAME=_token

# External Services
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
```

---

These developer tools provide a comprehensive development environment for building robust Hoist PHP applications efficiently.

**Next:** [Examples](../examples/) - See real-world application examples.
