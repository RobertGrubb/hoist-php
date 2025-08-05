# Hoist PHP Framework - API Documentation

## 📚 Complete API Reference

Welcome to the comprehensive API documentation for the Hoist PHP Framework. This documentation covers all core libraries, classes, and methods available in the framework.

### 🗂️ Documentation Structure

-   **[Authentication API](./Authentication.md)** - User authentication, sessions, and security
-   **[Database API](./Database.md)** - Database connections and query operations
-   **[Router API](./Router.md)** - URL routing and request handling
-   **[Request API](./Request.md)** - HTTP request processing
-   **[Response API](./Response.md)** - HTTP response generation
-   **[Controller API](./Controller.md)** - Base controller functionality
-   **[Model API](./Model.md)** - Data model operations
-   **[View API](./View.md)** - Template rendering and views
-   **[Session API](./Session.md)** - Session management
-   **[Cache API](./Cache.md)** - Caching system
-   **[Validation API](./Validation.md)** - Input validation
-   **[Security API](./Security.md)** - Security utilities
-   **[Utilities API](./Utilities.md)** - Helper functions and utilities

### 🚀 Quick Start

1. **Framework Initialization**

```php
<?php
require_once 'Core/Bootstrap.php';

// Framework automatically initializes core services
$instance = new Instance();
```

2. **Basic Controller**

```php
<?php
class HomeController extends Controller
{
    public function index()
    {
        // Access framework services
        $user = $this->auth->user;
        $data = $this->models->user->getAllUsers();

        $this->view->render('home/index', [
            'user' => $user,
            'users' => $data
        ]);
    }
}
```

3. **Authentication Example**

```php
// Login user
if ($this->auth->login($email, $password)) {
    $this->auth->required(); // Enforce login
    $this->auth->requireGroup('admin'); // Enforce role
}
```

### 📖 Framework Architecture

The Hoist PHP Framework follows these core principles:

-   **FileDatabase-First**: Primary storage using file-based database with optional MySQL enhancement
-   **Service Container**: Centralized dependency injection through Instance container
-   **MVC Pattern**: Clean separation of concerns with Controllers, Models, and Views
-   **Security-First**: Built-in authentication, CSRF protection, and input validation
-   **Convention over Configuration**: Sensible defaults with flexibility for customization

### 🔧 Core Services Available

All controllers have automatic access to these services through `$this->`:

```php
$this->auth       // Authentication & authorization
$this->database   // Optional MySQL database (if configured)
$this->request    // HTTP request handling
$this->response   // HTTP response generation
$this->session    // Session management
$this->view       // Template rendering
$this->cache      // Caching system
$this->models     // Data models (UserModel, etc.)
$this->router     // URL routing and parameters
$this->security   // Security utilities
$this->validation // Input validation
```

### 📊 Testing

The framework includes comprehensive testing with PHPUnit:

```bash
# Run all tests
docker-compose exec server vendor/bin/phpunit

# Run specific test suite
docker-compose exec server vendor/bin/phpunit tests/Core/
docker-compose exec server vendor/bin/phpunit tests/Security/
```

### 🛡️ Security Features

-   **Authentication System**: Login/logout with session management
-   **Role-Based Access Control**: User groups and permissions
-   **CSRF Protection**: Token generation and validation
-   **Password Security**: Modern password hashing with bcrypt/Argon2
-   **Input Validation**: Comprehensive validation system
-   **Secure Sessions**: Framework-managed session security

### 🎯 Best Practices

1. **Always validate input**:

```php
$this->validation->required('email')->email();
if ($this->validation->validate($_POST)) {
    // Process valid data
}
```

2. **Use authentication properly**:

```php
public function adminFunction() {
    $this->auth->required(); // Must be logged in
    $this->auth->requireGroup('admin'); // Must be admin
    // Admin functionality here
}
```

3. **Handle errors gracefully**:

```php
if (!$user = $this->models->user->getUserById($id)) {
    $this->view->render('error/404');
    return;
}
```

### 📝 Version Information

-   **Framework Version**: 1.0.0
-   **PHP Requirement**: PHP 8.1+
-   **Testing Framework**: PHPUnit 10.0+
-   **Documentation Updated**: August 2025

### 🤝 Contributing

When contributing to the framework:

1. All new features must include comprehensive tests
2. Follow existing code style and documentation patterns
3. Update API documentation for any new public methods
4. Ensure backward compatibility when possible

---

_This documentation reflects the actual implementation of the Hoist PHP Framework and is automatically generated from the codebase._
