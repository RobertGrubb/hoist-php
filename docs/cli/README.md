# Hoist CLI Documentation

The **Hoist CLI** is a revolutionary command-line tool that transforms the PHP development experience. It eliminates the traditional development bottlenecks and provides a seamless path from rapid prototyping to production deployment.

![CLI Tool](https://img.shields.io/badge/CLI-Tool-blue)
![Migration](https://img.shields.io/badge/Migration-Revolutionary-green)
![Code%20Generation](https://img.shields.io/badge/Code-Generation-orange)

---

## 🚀 **Overview**

The Hoist CLI solves the **biggest pain point** in modern PHP development: the complexity of setting up and managing databases during development while ensuring production readiness.

### **The Problem It Solves:**

**Before Hoist CLI:**

-   ❌ Must setup MySQL/PostgreSQL before starting development
-   ❌ Different storage systems for development vs production
-   ❌ Complex migration scripts and schema management
-   ❌ Inconsistent code scaffolding and boilerplate
-   ❌ Time-consuming setup that delays actual coding

**After Hoist CLI:**

-   ✅ **Start coding immediately** with zero-configuration FileDatabase
-   ✅ **One-command migration** to production MySQL with automatic schema generation
-   ✅ **Intelligent code generation** that matches framework patterns
-   ✅ **Consistent development workflow** from prototype to production

---

## 📋 **Command Reference**

### 🔄 **Migration Commands**

#### `migrate:to-mysql`

**The game-changing command that revolutionizes development workflow.**

Seamlessly migrates your FileDatabase development environment to production-ready MySQL with automatic schema generation and data type inference.

```bash
php hoist migrate:to-mysql [options]
```

**Options:**

-   `--database=NAME` _(required)_ - Target MySQL database name
-   `--host=HOST` _(optional)_ - MySQL host (default: localhost)
-   `--port=PORT` _(optional)_ - MySQL port (default: 3306)
-   `--user=USER` _(optional)_ - MySQL username (default: root)
-   `--password=PASS` _(optional)_ - MySQL password (default: empty)
-   `--dry-run` _(optional)_ - Preview migration without executing
-   `--force` _(optional)_ - Skip confirmation prompts

**Examples:**

```bash
# Basic migration
php hoist migrate:to-mysql --database=myapp

# With custom MySQL credentials
php hoist migrate:to-mysql --database=myapp --user=dbuser --password=secret

# Preview migration plan
php hoist migrate:to-mysql --database=myapp --dry-run

# Automated migration (no prompts)
php hoist migrate:to-mysql --database=myapp --force
```

**What it does:**

1. 🔍 **Discovers** all FileDatabase tables automatically
2. 📊 **Analyzes** data types from existing records (up to 100 samples per table)
3. 🏗️ **Generates** optimal MySQL schemas with proper column types
4. 📤 **Migrates** all data preserving relationships and integrity
5. ✅ **Validates** successful migration with record counts

**Data Type Intelligence:**

-   `string` → `VARCHAR(n)` or `TEXT` (based on content length)
-   `integer` → `INT`
-   `float` → `DECIMAL(10,2)`
-   `boolean` → `BOOLEAN`
-   `datetime` → `DATETIME` (auto-detects formats)
-   `email` → `VARCHAR(255)` (validates email patterns)
-   `url` → `TEXT` (for longer URLs)

#### `migrate:to-file` _(Coming Soon)_

Reverse migration from MySQL back to FileDatabase for development environments.

### 🛠️ **Code Generation Commands**

#### `generate:controller`

Creates a new controller with full CRUD operations and proper framework integration.

```bash
php hoist generate:controller ControllerName
```

**Examples:**

```bash
# Generate basic controller
php hoist generate:controller UserController

# Generate nested controller
php hoist generate:controller Admin\\UserController
```

**Generated Features:**

-   ✅ Full CRUD methods (index, show, create, store, edit, update, delete)
-   ✅ Proper view rendering with `$this->instance->view->render()`
-   ✅ RESTful action structure
-   ✅ Professional documentation and comments
-   ✅ Framework-aware method signatures

**Generated Structure:**

```php
class UserController extends Controller
{
    public function index()     // List all users
    public function show($id)   // Show specific user
    public function create()    // Show create form
    public function store()     // Process form submission
    public function edit($id)   // Show edit form
    public function update($id) // Process update
    public function delete($id) // Delete user
}
```

#### `generate:model`

Creates a new model with FileDatabase integration following framework patterns.

```bash
php hoist generate:model ModelName
```

**Examples:**

```bash
# Generate user model
php hoist generate:model User  # Creates UserModel.php

# Generate product model
php hoist generate:model Product  # Creates ProductModel.php
```

**Generated Features:**

-   ✅ FileDatabase integration with `new FileDatabase('app')`
-   ✅ Standard CRUD methods (get, getMany, create, save, delete)
-   ✅ Pagination and filtering support
-   ✅ Soft delete capabilities
-   ✅ Automatic timestamps (created_at, updated_at)
-   ✅ Professional documentation matching UserModel pattern

**Generated Methods:**

```php
class ProductModel
{
    public function get($where = [])           // Single record
    public function getMany($where = [])       // Multiple records with ORDER/LIMIT
    public function create($data)              // Insert new record
    public function save($where, $data)        // Update existing
    public function delete($where, $soft = true) // Delete (soft by default)
    public function count($where = [])         // Count records
    public function all()                      // All active records
    public function latest($limit = 5)         // Recent records
    public function getById($id)               // Find by ID
}
```

#### `generate:component`

Creates a new UI component following the framework's component architecture.

```bash
php hoist generate:component ComponentName
php hoist generate:component Namespace.ComponentName
```

**Examples:**

```bash
# Generate basic component
php hoist generate:component CustomButton

# Generate nested component
php hoist generate:component Form.InputField
```

**Generated Features:**

-   ✅ Function-based component returning HTML
-   ✅ Parameter handling with `$instance` and `$data`
-   ✅ Tailwind CSS integration
-   ✅ Professional structure matching existing components
-   ✅ Documentation with usage examples

**Generated Structure:**

```php
return function ($instance, $data = []) {
    $title = $data['title'] ?? 'Default Title';
    $content = $data['content'] ?? '';
    $class = $data['class'] ?? '';

    // Tailwind CSS styled HTML generation
    $html = "<div class=\"component-{name} {$class}\">...</div>";

    return $html;
};
```

### 🔧 **Development Commands**

#### `serve`

Starts a development server using PHP's built-in server.

```bash
php hoist serve [options]
```

**Options:**

-   `--host=HOST` _(optional)_ - Server host (default: localhost)
-   `--port=PORT` _(optional)_ - Server port (default: 8080)

**Examples:**

```bash
# Start on default port
php hoist serve

# Custom host and port
php hoist serve --host=0.0.0.0 --port=3000
```

#### `cache:clear`

Clears the application cache files.

```bash
php hoist cache:clear
```

Removes all cached files from:

-   `Application/Cache/data/`
-   `Application/Cache/meta/`
-   `Application/Cache/tags/`

#### `help` / `--help` / `-h`

Displays comprehensive help information.

```bash
php hoist help
php hoist --help
php hoist -h
```

---

## 🎯 **Real-World Workflows**

### **Scenario 1: Rapid Prototype to MVP**

```bash
# Day 1: Start building immediately (0 setup time)
php hoist generate:model User
php hoist generate:model Product
php hoist generate:controller UserController
php hoist generate:controller ProductController

# Week 2: Add custom components
php hoist generate:component Form.ProductForm
php hoist generate:component UI.ProductCard

# Build your MVP using FileDatabase...
# No MySQL setup, no configuration, just code!
```

### **Scenario 2: MVP to Production**

```bash
# Month 3: Ready to scale? One command!
php hoist migrate:to-mysql --database=myapp_production

# ✅ All data migrated
# ✅ Schemas generated automatically
# ✅ Production ready in seconds
```

### **Scenario 3: Development Team Onboarding**

```bash
# New developer joins team
git clone your-project
cd your-project
docker-compose up -d

# Start coding immediately - no database setup!
# FileDatabase has all the data ready to go
```

---

## 🔍 **Migration Deep Dive**

### **Automatic Schema Generation**

The migration tool analyzes your FileDatabase records and generates optimal MySQL schemas:

**Sample Analysis:**

```json
// FileDatabase record
{
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "age": 30,
    "salary": 75000.5,
    "is_active": true,
    "created_at": "2025-01-15 10:30:00",
    "bio": "Long text content..."
}
```

**Generated MySQL Schema:**

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    age INT NOT NULL,
    salary DECIMAL(10,2) NOT NULL,
    is_active BOOLEAN NOT NULL,
    created_at DATETIME NOT NULL,
    bio TEXT NULL
);
```

### **Data Type Detection Logic**

| FileDatabase Value       | Detected Type | MySQL Column           |
| ------------------------ | ------------- | ---------------------- |
| `"john@example.com"`     | Email         | `VARCHAR(255)`         |
| `"2025-01-15 10:30:00"`  | Datetime      | `DATETIME`             |
| `"2025-01-15"`           | Date          | `DATE`                 |
| `42`                     | Integer       | `INT`                  |
| `3.14159`                | Float         | `DECIMAL(10,2)`        |
| `true/false`             | Boolean       | `BOOLEAN`              |
| `"Short text"`           | String        | `VARCHAR(n)`           |
| `"Very long content..."` | Long text     | `TEXT`                 |
| `null`                   | Nullable      | Adds `NULL` constraint |

### **Migration Safety Features**

1. **Dry Run Mode**: Preview all changes before execution
2. **Data Validation**: Confirms record counts match
3. **Error Handling**: Graceful failure with detailed error messages
4. **Transaction Safety**: Uses database transactions where possible
5. **Non-Destructive**: Original FileDatabase files remain untouched

---

## 🛡️ **Best Practices**

### **Pre-Migration Checklist**

-   [ ] **Test with dry run**: `php hoist migrate:to-mysql --database=test --dry-run`
-   [ ] **Verify MySQL access**: Ensure credentials and permissions are correct
-   [ ] **Check database name**: Ensure target database name is available
-   [ ] **Review data types**: Check the dry run output for appropriate column types
-   [ ] **Backup considerations**: FileDatabase files serve as automatic backup

### **Development Workflow**

1. **Start with FileDatabase**: Begin all projects with zero-configuration storage
2. **Generate code consistently**: Use CLI generators for all scaffolding
3. **Build and iterate**: Develop features using FileDatabase
4. **Test migration early**: Run dry-run migrations during development
5. **Migrate when ready**: One command transition to production database

### **Code Generation Guidelines**

-   **Use consistent naming**: `UserController`, `ProductModel`, `Form.InputField`
-   **Follow conventions**: Generated code follows framework patterns exactly
-   **Customize after generation**: Templates provide solid foundation for customization
-   **Organize components**: Use namespaces like `Form.`, `UI.`, `Layout.` for organization

---

## 🚨 **Troubleshooting**

### **Common Migration Issues**

**Error: "Database name is required"**

```bash
# Solution: Always specify database name
php hoist migrate:to-mysql --database=myapp
```

**Error: "No such file or directory" (MySQL connection)**

```bash
# Solution: Check MySQL credentials and host
php hoist migrate:to-mysql --database=myapp --host=127.0.0.1 --user=root --password=yourpass
```

**Error: "Array to string conversion"**

-   This was a bug in earlier versions, ensure you have the latest hoist file

### **Code Generation Issues**

**Error: "Controller already exists"**

```bash
# Solution: Use different name or check existing files
ls Application/Controllers/
```

**Error: "Permission denied" when creating files**

```bash
# Solution: Check directory permissions
chmod -R 755 Application/
```

### **Development Server Issues**

**Error: "Address already in use"**

```bash
# Solution: Use different port
php hoist serve --port=8081
```

---

## 🔮 **Roadmap**

### **Upcoming Features**

-   **Reverse Migration**: MySQL → FileDatabase for development
-   **Advanced Generators**: API controllers, middleware, migrations
-   **Database Seeding**: Generate sample data for development
-   **Testing Generators**: Unit and integration test scaffolding
-   **Docker Integration**: Container-aware development tools
-   **Multi-Database**: Support for PostgreSQL and SQLite
-   **Schema Diffing**: Compare and sync database schemas
-   **Data Validation**: Enhanced migration validation and verification

---

## 📚 **See Also**

-   **[FileDatabase API](../api/FileDatabase.md)** - Understanding the development database system
-   **[Components API](../api/Components.md)** - Deep dive into component architecture
-   **[Controller API](../api/Controller.md)** - Framework controller patterns
-   **[Model API](../api/Model.md)** - Data modeling best practices

---

**The Hoist CLI represents a paradigm shift in PHP development - from configuration complexity to coding immediately. Start building the future today!** 🚀
