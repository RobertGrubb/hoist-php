# 🚀 Quick Start Guide

Get your first Hoist PHP application running in under 5 minutes!

## Prerequisites

-   **PHP 8.1+** with extensions: `json`, `fileinfo`, `mbstring`
-   **Web server** (Apache, Nginx, or PHP built-in server)
-   **Composer** (optional, for dependency management)

## Installation Methods

### Option 1: Docker (Recommended)

```bash
# Clone the repository
git clone https://github.com/YourUsername/hoist-php.git
cd hoist-php

# Start with Docker
docker-compose up -d

# Your app is now running at http://localhost:8080
```

### Option 2: Manual Setup

```bash
# Clone the repository
git clone https://github.com/YourUsername/hoist-php.git
cd hoist-php

# Set permissions (Linux/Mac)
chmod -R 755 source/
chmod -R 777 source/Application/Database/
chmod -R 777 source/public/uploads/

# Start PHP development server
cd source/public
php -S localhost:8000
```

## Your First Application

### 1. Create a Controller

Create `source/Application/Controllers/HelloController.php`:

```php
<?php

class HelloController extends Controller
{
    public function index()
    {
        $this->instance->view->render('hello/index', [
            'message' => 'Hello, World!',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    public function api()
    {
        return $this->instance->response->json([
            'message' => 'Hello from API!',
            'timestamp' => time(),
            'framework' => 'Hoist PHP'
        ]);
    }
}
```

### 2. Add Routes

Edit `source/Application/Routes.php`:

```php
<?php

return [
    ['method' => 'GET', 'url' => '/hello', 'target' => 'HelloController@index'],
    ['method' => 'GET', 'url' => '/api/hello', 'target' => 'HelloController@api'],
];
```

### 3. Create a View

Create `source/Application/Views/hello/index.php`:

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hello Hoist!</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-align: center;
        }
        .container {
            background: rgba(255, 255, 255, 0.1);
            padding: 40px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
        }
        h1 { font-size: 3em; margin-bottom: 20px; }
        .timestamp { font-size: 1.2em; opacity: 0.8; }
    </style>
</head>
<body>
    <div class="container">
        <h1><?= htmlspecialchars($message) ?></h1>
        <p class="timestamp">Generated at: <?= htmlspecialchars($timestamp) ?></p>
        <p>🎉 Your Hoist PHP application is running!</p>
    </div>
</body>
</html>
```

### 4. Test Your Application

Visit these URLs:

-   **Web Page**: `http://localhost:8000/hello`
-   **API Endpoint**: `http://localhost:8000/api/hello`

## Understanding What Just Happened

### **MVC in Action**

1. **Route** (`/hello`) matched in `Routes.php`
2. **Controller** (`HelloController@index`) processed the request
3. **View** (`hello/index.php`) rendered the response

### **Framework Features Used**

-   ✅ **Routing**: Clean URL handling
-   ✅ **Controllers**: Request processing
-   ✅ **Views**: Template rendering with data injection
-   ✅ **Response**: JSON API responses

## Next Steps

### **Explore Core Features**

-   [📊 Work with Data](../components/models.md) - Learn about FileDatabase and MySQL
-   [🔐 Add Authentication](../advanced/authentication.md) - User login and security
-   [⚡ Enable Caching](../advanced/caching.md) - Boost performance

### **Build Real Applications**

-   [🏢 Todo Application](../examples/todo-app.md) - Complete CRUD example
-   [📱 REST API](../examples/api-development.md) - Build modern APIs
-   [🛒 E-commerce Site](../examples/ecommerce.md) - Advanced features

### **Production Deployment**

-   [🚀 Deploy to Production](../deployment/deployment.md)
-   [⚙️ Performance Optimization](../deployment/performance.md)
-   [📊 Monitoring & Logging](../deployment/monitoring.md)

## Common Patterns

### **Form Handling**

```php
public function contact()
{
    if ($this->request->method() === 'post') {
        $validated = $this->request->validate([
            'name' => 'required|min:2',
            'email' => 'required|email',
            'message' => 'required|min:10'
        ]);

        // Process form...
        return $this->instance->response->sendSuccess('Message sent!');
    }

    $this->instance->view->render('contact/form');
}
```

### **Database Operations**

```php
public function users()
{
    // FileDatabase (default)
    $users = $this->instance->models->user->getAll();

    // MySQL (if configured)
    $users = $this->instance->database->client->select('users', '*');

    return $this->instance->response->json($users);
}
```

### **Caching**

```php
public function dashboard()
{
    $stats = $this->instance->cache->remember('dashboard.stats', 3600, function() {
        return $this->calculateExpensiveStats();
    });

    $this->instance->view->render('dashboard', ['stats' => $stats]);
}
```

## Troubleshooting

### **Common Issues**

**Blank Page?**

-   Check PHP error logs: `tail -f /var/log/php_errors.log`
-   Verify file permissions: `chmod 755` for directories, `644` for files
-   Ensure PHP extensions are installed

**Route Not Found?**

-   Check `Routes.php` syntax
-   Verify controller and method names
-   Ensure web server mod_rewrite is enabled

**Database Errors?**

-   Check `source/Application/Database/` permissions (777)
-   Verify FileDatabase JSON syntax
-   For MySQL: check connection credentials

### **Getting Help**

-   📖 Check [FAQ](../reference/faq.md)
-   🐛 Review [Troubleshooting Guide](../reference/troubleshooting.md)
-   💬 Join community discussions

---

**🎉 Congratulations!** You've built your first Hoist PHP application. Ready to explore more? Check out the [Components Guide](../components/) to dive deeper into the framework features.
