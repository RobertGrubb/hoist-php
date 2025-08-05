# Hoist PHP Framework

A lightweight, modern PHP MVC framework designed for rapid development with **zero configuration**, **interactive UI components**, and **production-ready features** out of the box.

![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.1-blue)
![License](https://img.shields.io/badge/License-MIT-green)
![Framework](https://img.shields.io/badge/Framework-MVC-orange)
![Components](https://img.shields.io/badge/UI-Components-purple)

### 📚 **Complete API Documentation**

Hoist includes **comprehensive API documentation** for all framework components:

### 💾 **Primary Storage System**

-   **[FileDatabase API](docs/api/FileDatabase.md)** - Zero-configuration JSON database with SQL-like queries (default storage)

### 🧩 **Component System (The Bread and Butter!)**

-   **[Components API](docs/api/Components.md)** - Dynamic component system for reusable UI elements and modular architecture

### 🔗 **Core Framework APIs**

-   **[Authentication API](docs/api/Authentication.md)** - User authentication, sessions, and role-based access
-   **[Database API](docs/api/Database.md)** - MySQL and FileDatabase operations with query building
-   **[Model API](docs/api/Model.md)** - Active Record pattern with security features and validation
-   **[Request API](docs/api/Request.md)** - HTTP request handling, data validation, and file uploads
-   **[Response API](docs/api/Response.md)** - JSON responses, redirects, and content delivery
-   **[Router API](docs/api/Router.md)** - URL routing, parameter extraction, and nested controllers-Components-purple)

## ✨ What Makes Hoist Special?

Hoist isn't just another PHP framework - it's a **complete development ecosystem** that gets you from idea to production faster than ever before. With **zero configuration setup**, **interactive UI components**, and **enterprise-grade security**, Hoist eliminates the complexity while delivering professional results.

🚀 **Start building in under 60 seconds**  
🎨 **Beautiful UI components included**  
🔐 **Security-first architecture**  
📦 **Docker-ready deployment**  
🗂️ **Flexible database options**

---

## 🎯 Key Features

### 🎨 **Interactive UI Component System**

-   **30+ Pre-built Components**: Forms, modals, tables, badges, cards, and more
-   **Tailwind CSS Integration**: Beautiful, responsive design out of the box
-   **JavaScript Interactivity**: Modal dialogs, confirmations, dynamic tables
-   **Organized Architecture**: Logical separation into Form/, UI/, and Layout/ components

### 🚀 **Zero Configuration Development**

-   **Convention-based Routing**: `/users/create` → `UsersController::create()`
-   **Automatic Component Loading**: Render components with simple syntax
-   **FileDatabase System**: Start building without database setup
-   **Docker Container**: Single command deployment

### 🏗️ **Modern MVC Architecture**

-   **Service Injection**: Clean dependency management throughout
-   **Nested Controllers**: Organize code in logical subdirectories
-   **Flexible Models**: Dual FileDatabase/MySQL support
-   **Secure Views**: Built-in XSS protection and validation

### 🛡️ **Enterprise Security**

-   **Input Validation**: 30+ validation rules with custom messages
-   **XSS Protection**: Automatic output escaping and cleaning
-   **Authentication System**: Role-based access with session management
-   **CSRF Protection**: Built-in security against cross-site attacks

### 📊 **Production Features**

-   **Admin Panel**: Complete user management interface
-   **Caching System**: Redis, Memcached, and file-based options
-   **Error Handling**: Graceful degradation with detailed debugging
-   **RESTful APIs**: JSON responses with proper HTTP status codes

---

## 🎮 Interactive Demo

Visit the **live admin panel** to see Hoist's capabilities:

1. **Start the application**: `docker-compose up -d`
2. **Visit**: http://localhost:8080
3. **Login as admin**: Navigate to user authentication demo
4. **Explore admin panel**: See interactive tables, modals, and confirmations

**Admin Panel Features:**

-   ✅ Interactive user management with modals
-   ✅ Dynamic data tables with action buttons
-   ✅ Confirmation dialogs for destructive actions
-   ✅ Real-time statistics and activity feeds
-   ✅ Role-based access control
-   ✅ Responsive design on all devices

---

## 🚀 Quick Start

### One-Command Setup

```bash
git clone https://github.com/RobertGrubb/hoist-php.git
cd hoist-php
docker-compose up -d
```

**That's it!** Open http://localhost:8080 and start building.

### Your First Component

Create an interactive user interface in minutes:

```php
// In any controller
$this->instance->view->render('dashboard', [
    'users' => $users
]);
```

```php
<!-- In your view -->
<!-- Render a beautiful data table -->
<?= $components->render('Layout.DataTable', [
    'headers' => ['Name', 'Email', 'Status'],
    'rows' => array_map(function($user) {
        return [
            'data' => $user,
            'cells' => [
                htmlspecialchars($user['name']),
                htmlspecialchars($user['email']),
                $components->render('UI.Badge', [
                    'text' => $user['status'],
                    'color' => $user['status'] === 'active' ? 'green' : 'red'
                ])
            ]
        ];
    }, $users),
    'actions' => [
        [
            'icon' => 'fas fa-edit',
            'class' => 'text-blue-600 hover:text-blue-900',
            'title' => 'Edit',
            'onclick' => 'openModal(\'editUserModal\'); loadUser(\'{id}\')'
        ]
    ]
]) ?>

<!-- Add a modal dialog -->
<?= $components->render('UI.Modal', [
    'id' => 'editUserModal',
    'title' => 'Edit User',
    'content' => '<!-- Your form content -->'
]) ?>
```

---

## 🎨 UI Component Gallery

### 📝 **Form Components**

Create beautiful, accessible forms with zero custom CSS:

```php
// Text input with validation styling
<?= $components->render('Form.Input', [
    'type' => 'email',
    'name' => 'email',
    'label' => 'Email Address',
    'placeholder' => 'Enter your email',
    'required' => true
]) ?>

// Select dropdown with options
<?= $components->render('Form.Select', [
    'name' => 'role',
    'label' => 'User Role',
    'options' => ['user' => 'User', 'admin' => 'Administrator'],
    'required' => true
]) ?>

// Styled button with icon
<?= $components->render('Form.Button', [
    'text' => 'Save Changes',
    'icon' => 'fas fa-save',
    'variant' => 'primary',
    'onclick' => 'handleSave()'
]) ?>
```

### 🎯 **UI Components**

Interactive elements that bring your interface to life:

```php
// Status badges with colors
<?= $components->render('UI.Badge', [
    'text' => 'Active',
    'icon' => 'fas fa-check-circle',
    'color' => 'green'
]) ?>

// Information cards
<?= $components->render('UI.Card', [
    'title' => 'User Statistics',
    'content' => 'Your dashboard content here'
]) ?>

// Modal dialogs with JavaScript integration
<?= $components->render('UI.Modal', [
    'id' => 'confirmDialog',
    'title' => 'Confirm Action',
    'size' => 'lg',
    'content' => 'Modal content with forms or information'
]) ?>

// Confirmation dialogs
<?= $components->render('UI.Confirmation', [
    'id' => 'deleteConfirm',
    'title' => 'Delete Item',
    'message' => 'This action cannot be undone.',
    'variant' => 'danger',
    'confirmAction' => 'handleDelete()'
]) ?>
```

### 📊 **Layout Components**

Display data beautifully with built-in interactivity:

```php
// Interactive data tables
<?= $components->render('Layout.DataTable', [
    'headers' => ['User', 'Email', 'Role', 'Actions'],
    'rows' => $userRows,
    'actions' => [
        ['icon' => 'fas fa-eye', 'onclick' => 'viewUser(\'{id}\')'],
        ['icon' => 'fas fa-edit', 'onclick' => 'editUser(\'{id}\')'],
        ['icon' => 'fas fa-trash', 'onclick' => 'deleteUser(\'{id}\')']
    ]
]) ?>

// Statistics cards
<?= $components->render('Layout.AdminStatCard', [
    'title' => 'Total Users',
    'value' => count($users),
    'icon' => 'fas fa-users',
    'color' => 'blue'
]) ?>

// Feature showcase cards
<?= $components->render('Layout.FeatureCard', [
    'title' => 'Zero Configuration',
    'description' => 'Start building immediately with smart defaults',
    'icon' => 'fas fa-rocket',
    'color' => 'blue'
]) ?>
```

---

## 🏗️ Architecture Excellence

### 📁 **Organized Component Structure**

```
Application/Components/
├── Form/                    # Input elements
│   ├── Input.php           # Text inputs, email, password
│   ├── Button.php          # Interactive buttons
│   ├── Select.php          # Dropdown selections
│   └── Checkbox.php        # Toggle inputs
├── UI/                     # Interface elements
│   ├── Modal.php           # Dialog windows
│   ├── Confirmation.php    # Action confirmations
│   ├── Badge.php           # Status indicators
│   ├── Card.php            # Content containers
│   └── Alert.php           # Notification messages
└── Layout/                 # Display components
    ├── DataTable.php       # Interactive tables
    ├── FeatureCard.php     # Feature showcases
    ├── AdminStatCard.php   # Dashboard statistics
    └── DefinitionList.php  # Key-value displays
```

### 🔧 **Service-Driven Components**

Every component follows the clean service injection pattern:

```php
return function ($instance, $data = []) {
    // Access to all framework services
    $auth = $instance->auth;
    $request = $instance->request;
    $validation = $instance->validation;

    // Component logic with security and validation
    $content = htmlspecialchars($data['content'] ?? '');

    return $html;
};
```

### 🎯 **Smart Routing with Nested Controllers**

```
URL Pattern                →  Controller Location
/                         →  Controllers/IndexController::index()
/users                    →  Controllers/UsersController::index()
/admin/users              →  Controllers/Admin/UsersController::index()
/admin/users/edit         →  Controllers/Admin/UsersController::edit()
/api/v1/users             →  Controllers/Api/V1Controller::users()
/dashboard/analytics      →  Controllers/Dashboard/AnalyticsController::index()
```

---

## 🗂️ **Flexible Database System**

### 🗃️ **FileDatabase (Zero Setup)**

Perfect for development and rapid prototyping:

```php
// Automatic JSON storage
$users = $this->instance->models->user->all();
$user = $this->instance->models->user->create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'created_at' => date('Y-m-d H:i:s')
]);
```

### 🐬 **MySQL (Production Ready)**

Seamless upgrade path for complex applications:

```php
// Automatic detection and fallback
class UserModel extends Model {
    public function getAll() {
        if ($this->instance->database->hasMySQL()) {
            return $this->instance->database->client->select('users', '*');
        }
        return $this->fileDatabase->all('users');
    }
}
```

---

## 🛡️ **Security First**

### 🔒 **Input Validation & Sanitization**

```php
// Built-in validation with 30+ rules
$validated = $this->instance->request->validate([
    'email' => 'required|email|unique:users',
    'password' => 'required|min:8|strong',
    'age' => 'required|integer|min:18|max:120'
]);

// Automatic XSS protection
$safeContent = $this->instance->cleaner->clean($userInput);
```

### 🔐 **Authentication & Authorization**

```php
// Role-based access control
$this->instance->auth->requireGroup('admin');

// Session management
if ($this->instance->auth->login($email, $password)) {
    $user = $this->instance->auth->user();
}
```

---

## 📊 **Production Features**

### ⚡ **High-Performance Caching**

```php
// Multi-tier caching with automatic fallback
$data = $this->instance->cache->remember('expensive.query', 3600, function() {
    return $this->instance->models->analytics->getComplexData();
});

// Tagged cache for group operations
$this->instance->cache->tags(['users'])->flush();
```

### 🔄 **RESTful API Support**

```php
// Automatic content negotiation
public function api() {
    $data = $this->instance->models->user->all();

    if ($this->instance->request->wantsJson()) {
        return $this->instance->response->json($data);
    }

    $this->instance->view->render('users/index', ['users' => $data]);
}
```

### 📈 **Built-in Admin Panel**

Complete administrative interface with:

-   ✅ User management with modals and confirmations
-   ✅ Real-time statistics and activity monitoring
-   ✅ Role-based access control
-   ✅ Interactive data tables with action buttons
-   ✅ Responsive design for mobile administration

---

## 🚀 **Deployment Options**

### 🐳 **Docker (Recommended)**

```bash
# Development
docker-compose up -d

# Production
docker build -t my-app .
docker run -d -p 80:80 my-app
```

### 🌐 **Traditional Hosting**

```bash
# Upload files and set document root to source/public/
chmod -R 755 Application/Database/
```

---

## 📚 **Complete API Documentation**

Hoist includes **comprehensive API documentation** for all framework components:

### � **Primary Storage System**

-   **[FileDatabase API](docs/api/FileDatabase.md)** - Zero-configuration JSON database with SQL-like queries (default storage)

### �🔗 **Core Framework APIs**

-   **[Authentication API](docs/api/Authentication.md)** - User authentication, sessions, and role-based access
-   **[Database API](docs/api/Database.md)** - MySQL and FileDatabase operations with query building
-   **[Model API](docs/api/Model.md)** - Active Record pattern with security features and validation
-   **[Request API](docs/api/Request.md)** - HTTP request handling, data validation, and file uploads
-   **[Response API](docs/api/Response.md)** - JSON responses, redirects, and content delivery
-   **[Router API](docs/api/Router.md)** - URL routing, parameter extraction, and nested controllers

### 🎨 **UI and Presentation**

-   **[View API](docs/api/View.md)** - Template rendering, component integration, and data passing
-   **[Controller API](docs/api/Controller.md)** - Base controller functionality and MVC patterns

### 🛡️ **Security and Validation**

-   **[Security API](docs/api/Security.md)** - CSRF protection, form security, and request validation
-   **[Validation API](docs/api/Validation.md)** - Input validation with 50+ rules and custom messages
-   **[Session API](docs/api/Session.md)** - Session management, flash data, and state persistence

### ⚡ **Performance and Utilities**

-   **[Cache API](docs/api/Cache.md)** - Multi-driver caching with Redis, Memcached, and file support
-   **[Utilities API](docs/api/Utilities.md)** - Helper functions for UUIDs, HTTP requests, and data processing

### 📖 **Documentation Features**

-   ✅ **Complete method documentation** with parameters and return types
-   ✅ **Real-world examples** for every API method
-   ✅ **Security best practices** and implementation guidelines
-   ✅ **Framework integration** patterns and advanced usage
-   ✅ **Enterprise-grade standards** with comprehensive coverage

**[📚 Browse All API Documentation →](docs/api/README.md)**

---

## 🤝 **Contributing**

We welcome contributions! Hoist is built by developers, for developers.

```bash
# Development setup
git clone https://github.com/RobertGrubb/hoist-php.git
cd hoist-php
docker-compose up -d

# Make your changes
git checkout -b feature/amazing-feature
# ... your improvements ...
git commit -m "Add amazing feature"
git push origin feature/amazing-feature
```

**Contribution Areas:**

-   🎨 New UI components
-   🔧 Framework enhancements
-   📚 Documentation improvements
-   🧪 Test coverage expansion
-   🌟 Example applications

---

## 📝 **License**

MIT License - build amazing things with Hoist!

---

## 🚀 **Get Started Today**

```bash
git clone https://github.com/RobertGrubb/hoist-php.git
cd hoist-php
docker-compose up -d
```

**Open http://localhost:8080 and start building the future! 🌟**

---

**Built with ❤️ by developers who believe great software should be simple, secure, and beautiful.**
