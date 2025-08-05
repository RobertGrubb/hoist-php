# View API Documentation

## Overview

The View API provides comprehensive template rendering and view management for the Hoist PHP framework. It offers flexible template processing with variable injection, output buffering, and built-in optimization features for production-ready web applications.

## Class: View

**Location**: `Core/Libraries/View.php`  
**Access**: Available as `$this->instance->view` in controllers  
**Version**: 1.0.0 with optimization features  
**Template Engine**: PHP-based with variable extraction

---

## Properties

### Private Properties

#### `$instance`

**Type**: `object`  
**Description**: Framework service container instance for accessing framework services within templates

#### `$viewsDirectory`

**Type**: `string`  
**Default**: `/Views/`  
**Description**: Base directory path for view templates relative to APPLICATION_DIRECTORY

---

## Constructor

### `__construct($instance)`

Initializes the view rendering system with framework integration.

**Parameters:**

-   `$instance` (object): Framework service container instance

**Setup:**

-   Framework service access
-   Template directory configuration
-   Rendering environment preparation

**Example:**

```php
// View is automatically instantiated by the framework
// Access via controllers: $this->instance->view->render()
```

---

## Core Method

### `render($template, $args = [], $return = false)`

Renders a template file with variable injection and output control.

**Parameters:**

-   `$template` (string): Template filename (without .php extension)
-   `$args` (array): Associative array of variables to inject into template
-   `$return` (bool): True to return content, false to output directly

**Returns:** `string|void` - Rendered content if `$return` is true, void otherwise

**Template Location:** `APPLICATION_DIRECTORY/Views/{$template}.php`

**Automatic Variable Injection:**

-   `$instance`: Framework service container
-   `$baseUrl`: Application base URL
-   `$security`: Security service
-   `$session`: Session service
-   `$request`: Request service
-   `$view`: Current view instance
-   `$auth`: Authentication service
-   `$components`: Component service
-   `$utilities`: Utilities library
-   `$templateArgs`: Original arguments array

---

## Rendering Process

### 1. Template Validation

Validates template file existence before rendering:

```php
// Template file must exist
// Path: APPLICATION_DIRECTORY/Views/template_name.php

$this->instance->view->render('user/profile', $data);
// Looks for: Views/user/profile.php
```

### 2. Variable Extraction

Variables are extracted into template scope:

```php
// Controller code
$this->instance->view->render('dashboard', [
    'user' => $userData,
    'stats' => $dashboardStats,
    'notifications' => $userNotifications
]);

// In template (Views/dashboard.php)
// Variables are available as: $user, $stats, $notifications
echo $user['name'];
echo count($notifications);
```

### 3. Output Control

Choose between direct output and content capture:

```php
// Direct output (default)
$this->instance->view->render('homepage', $data);

// Content capture for nested rendering
$content = $this->instance->view->render('sidebar', $data, true);
```

### 4. Optimization

Built-in output optimization for production:

-   HTML comment removal (preserves IE conditional comments)
-   CSS/JS comment removal (safe contexts only)
-   Whitespace compression and normalization
-   Line break optimization

---

## Usage Examples

### Basic Template Rendering

```php
class HomeController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Welcome to Our Site',
            'user' => $this->instance->auth->user,
            'recent_posts' => $this->models->post->getRecent(5),
            'page_meta' => [
                'description' => 'Homepage description',
                'keywords' => 'home, welcome, site'
            ]
        ];

        $this->instance->view->render('home/index', $data);
    }
}
```

**Template File** (`Views/home/index.php`):

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo htmlspecialchars($title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($page_meta['description']); ?>">
    <base href="<?php echo $baseUrl; ?>">
</head>
<body>
    <header>
        <?php if ($auth->check()): ?>
            <p>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</p>
        <?php else: ?>
            <a href="<?php echo $baseUrl; ?>login">Login</a>
        <?php endif; ?>
    </header>

    <main>
        <h1><?php echo htmlspecialchars($title); ?></h1>

        <?php if (!empty($recent_posts)): ?>
            <section class="recent-posts">
                <h2>Recent Posts</h2>
                <?php foreach ($recent_posts as $post): ?>
                    <article>
                        <h3><?php echo htmlspecialchars($post['title']); ?></h3>
                        <p><?php echo htmlspecialchars($post['excerpt']); ?></p>
                        <a href="<?php echo $baseUrl; ?>posts/<?php echo $post['id']; ?>">Read More</a>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>
```

### Nested Template Rendering

```php
class DashboardController extends Controller
{
    public function index()
    {
        // Render sidebar content
        $sidebarData = [
            'navigation' => $this->models->navigation->getUserMenu(),
            'notifications' => $this->models->notification->getUnread()
        ];
        $sidebar = $this->instance->view->render('components/sidebar', $sidebarData, true);

        // Render main content
        $mainData = [
            'stats' => $this->models->analytics->getUserStats(),
            'recent_activity' => $this->models->activity->getRecent()
        ];
        $mainContent = $this->instance->view->render('dashboard/main', $mainData, true);

        // Render layout with nested content
        $this->instance->view->render('layouts/dashboard', [
            'title' => 'Dashboard',
            'sidebar' => $sidebar,
            'main_content' => $mainContent,
            'user' => $this->instance->auth->user
        ]);
    }
}
```

**Layout Template** (`Views/layouts/dashboard.php`):

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <title><?php echo htmlspecialchars($title); ?> - Dashboard</title>
    <link rel="stylesheet" href="<?php echo $baseUrl; ?>assets/styles/dashboard.css">
</head>
<body class="dashboard">
    <div class="dashboard-layout">
        <aside class="sidebar">
            <?php echo $sidebar; ?>
        </aside>

        <main class="main-content">
            <header class="dashboard-header">
                <h1><?php echo htmlspecialchars($title); ?></h1>
                <div class="user-info">
                    <?php echo htmlspecialchars($user['name']); ?>
                </div>
            </header>

            <div class="content">
                <?php echo $main_content; ?>
            </div>
        </main>
    </div>

    <script src="<?php echo $baseUrl; ?>assets/scripts/dashboard.js"></script>
</body>
</html>
```

### API Response Templates

```php
class ApiController extends Controller
{
    public function getUserData()
    {
        $userId = $this->router->param('id');
        $user = $this->models->user->get($userId);

        if (!$user) {
            $this->instance->response->sendError('User not found', 404);
            return;
        }

        if ($this->request->wantsJson()) {
            $this->instance->response->sendJson($user);
        } else {
            // Render HTML view for web requests
            $this->instance->view->render('api/user', [
                'user' => $user,
                'format' => 'html'
            ]);
        }
    }
}
```

### Form Rendering with CSRF Protection

```php
class UserController extends Controller
{
    public function editProfile()
    {
        $this->instance->auth->required();

        $user = $this->instance->auth->user;

        $this->instance->view->render('user/edit_profile', [
            'user' => $user,
            'csrf_token' => $this->instance->security->getCSRFToken(),
            'countries' => $this->models->country->getAllActive(),
            'validation_errors' => $this->session->getFlashData('validation_errors', [])
        ]);
    }

    public function updateProfile()
    {
        $this->instance->auth->required();

        if (!$this->instance->security->validateCSRF()) {
            $this->session->setFlashData('error', 'Invalid form submission');
            $this->instance->response->redirect('/profile/edit');
            return;
        }

        // Process form...
    }
}
```

**Edit Profile Template** (`Views/user/edit_profile.php`):

```php
<div class="profile-edit">
    <h2>Edit Profile</h2>

    <?php if ($session->getFlashData('error')): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($session->getFlashData('error')); ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo $baseUrl; ?>profile/update" method="POST">
        <?php echo $security->getCSRFField(); ?>

        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name"
                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
            <?php if (isset($validation_errors['name'])): ?>
                <span class="error"><?php echo htmlspecialchars($validation_errors['name']); ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email"
                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
            <?php if (isset($validation_errors['email'])): ?>
                <span class="error"><?php echo htmlspecialchars($validation_errors['email']); ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="country">Country:</label>
            <select id="country" name="country">
                <option value="">Select Country</option>
                <?php foreach ($countries as $country): ?>
                    <option value="<?php echo $country['code']; ?>"
                            <?php echo ($user['country'] === $country['code']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($country['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">Update Profile</button>
        <a href="<?php echo $baseUrl; ?>profile" class="btn btn-secondary">Cancel</a>
    </form>
</div>
```

### Email Template Rendering

```php
class EmailService extends Library
{
    public function sendWelcomeEmail($user)
    {
        $emailContent = $this->instance->view->render('emails/welcome', [
            'user' => $user,
            'activation_link' => $this->generateActivationLink($user['id']),
            'company_name' => 'Your Company',
            'support_email' => 'support@yourcompany.com'
        ], true); // Return content for email

        return $this->sendEmail(
            $user['email'],
            'Welcome to Our Platform',
            $emailContent
        );
    }

    public function sendPasswordReset($user, $resetToken)
    {
        $emailContent = $this->instance->view->render('emails/password_reset', [
            'user' => $user,
            'reset_link' => $this->baseUrl . 'reset-password?token=' . $resetToken,
            'expiry_hours' => 24
        ], true);

        return $this->sendEmail(
            $user['email'],
            'Password Reset Request',
            $emailContent
        );
    }
}
```

**Email Template** (`Views/emails/welcome.php`):

```php
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome to <?php echo htmlspecialchars($company_name); ?></title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #f8f9fa; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        .button { display: inline-block; padding: 12px 24px; background: #007bff;
                  color: white; text-decoration: none; border-radius: 4px; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;
                  font-size: 0.9em; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to <?php echo htmlspecialchars($company_name); ?>!</h1>
        </div>

        <div class="content">
            <p>Hello <?php echo htmlspecialchars($user['name']); ?>,</p>

            <p>Thank you for joining our platform! We're excited to have you on board.</p>

            <p>To get started, please activate your account by clicking the button below:</p>

            <p style="text-align: center;">
                <a href="<?php echo htmlspecialchars($activation_link); ?>" class="button">
                    Activate Account
                </a>
            </p>

            <p>If the button doesn't work, you can copy and paste this link into your browser:</p>
            <p style="word-break: break-all; color: #666;">
                <?php echo htmlspecialchars($activation_link); ?>
            </p>

            <p>If you have any questions, feel free to contact our support team at
               <a href="mailto:<?php echo htmlspecialchars($support_email); ?>">
                   <?php echo htmlspecialchars($support_email); ?>
               </a>
            </p>
        </div>

        <div class="footer">
            <p>Best regards,<br>The <?php echo htmlspecialchars($company_name); ?> Team</p>
            <p><small>This email was sent to <?php echo htmlspecialchars($user['email']); ?></small></p>
        </div>
    </div>
</body>
</html>
```

### Component-Based Templates

```php
class ComponentController extends Controller
{
    public function renderUserCard($userId)
    {
        $user = $this->models->user->get($userId);

        return $this->instance->view->render('components/user_card', [
            'user' => $user,
            'show_actions' => $this->instance->auth->hasPermission('manage_users'),
            'current_user_id' => $this->instance->auth->user['id']
        ], true);
    }

    public function userList()
    {
        $users = $this->models->user->getMany(['status' => 'active']);

        $userCards = [];
        foreach ($users as $user) {
            $userCards[] = $this->renderUserCard($user['id']);
        }

        $this->instance->view->render('user/list', [
            'title' => 'User Directory',
            'user_cards' => $userCards,
            'total_users' => count($users)
        ]);
    }
}
```

**Component Template** (`Views/components/user_card.php`):

```php
<div class="user-card" data-user-id="<?php echo $user['id']; ?>">
    <div class="user-avatar">
        <?php if (!empty($user['avatar'])): ?>
            <img src="<?php echo $baseUrl . $user['avatar']; ?>"
                 alt="<?php echo htmlspecialchars($user['name']); ?>">
        <?php else: ?>
            <div class="avatar-placeholder">
                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="user-info">
        <h3><?php echo htmlspecialchars($user['name']); ?></h3>
        <p class="email"><?php echo htmlspecialchars($user['email']); ?></p>

        <?php if (!empty($user['title'])): ?>
            <p class="title"><?php echo htmlspecialchars($user['title']); ?></p>
        <?php endif; ?>

        <div class="user-meta">
            <span class="status status-<?php echo $user['status']; ?>">
                <?php echo ucfirst($user['status']); ?>
            </span>

            <?php if (!empty($user['last_login'])): ?>
                <span class="last-login">
                    Last seen: <?php echo date('M j, Y', strtotime($user['last_login'])); ?>
                </span>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($show_actions && $user['id'] != $current_user_id): ?>
        <div class="user-actions">
            <a href="<?php echo $baseUrl; ?>users/<?php echo $user['id']; ?>/edit"
               class="btn btn-sm btn-secondary">Edit</a>

            <?php if ($user['status'] === 'active'): ?>
                <button class="btn btn-sm btn-warning" onclick="suspendUser(<?php echo $user['id']; ?>)">
                    Suspend
                </button>
            <?php else: ?>
                <button class="btn btn-sm btn-success" onclick="activateUser(<?php echo $user['id']; ?>)">
                    Activate
                </button>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
```

---

## Template Organization

### Directory Structure

```
Views/
├── layouts/              # Main page layouts
│   ├── main.php         # Standard page layout
│   ├── dashboard.php    # Dashboard layout
│   └── minimal.php      # Minimal layout
├── components/          # Reusable components
│   ├── header.php       # Site header
│   ├── sidebar.php      # Navigation sidebar
│   ├── user_card.php    # User card component
│   └── pagination.php   # Pagination component
├── user/               # User-related templates
│   ├── profile.php     # User profile display
│   ├── edit.php        # Profile editing form
│   └── list.php        # User directory
├── auth/               # Authentication templates
│   ├── login.php       # Login form
│   ├── register.php    # Registration form
│   └── forgot.php      # Password reset form
├── emails/             # Email templates
│   ├── welcome.php     # Welcome email
│   ├── password_reset.php # Password reset email
│   └── notification.php # General notifications
├── admin/              # Admin interface templates
│   ├── dashboard.php   # Admin dashboard
│   ├── users.php       # User management
│   └── settings.php    # System settings
└── error/              # Error page templates
    ├── 404.php         # Not found page
    ├── 500.php         # Server error page
    └── maintenance.php # Maintenance mode page
```

### Naming Conventions

-   **Files**: Use descriptive names with underscores (`user_profile.php`)
-   **Directories**: Use singular nouns (`user/`, not `users/`)
-   **Variables**: Use snake_case for consistency (`$user_data`)
-   **Template paths**: Use forward slashes (`user/profile`)

---

## Security Features

### XSS Protection

Always escape output in templates:

```php
<!-- Safe output -->
<h1><?php echo htmlspecialchars($title); ?></h1>
<p><?php echo htmlspecialchars($description, ENT_QUOTES, 'UTF-8'); ?></p>

<!-- For HTML content (when trusted) -->
<div><?php echo $utilities->sanitizeHTML($trusted_content); ?></div>

<!-- URL encoding -->
<a href="<?php echo $baseUrl; ?>search?q=<?php echo urlencode($search_query); ?>">Search</a>
```

### CSRF Protection

Include CSRF tokens in forms:

```php
<form method="POST" action="<?php echo $baseUrl; ?>user/update">
    <?php echo $security->getCSRFField(); ?>
    <!-- form fields -->
</form>
```

### Content Security Policy

Set CSP headers in templates:

```php
<!-- In layout template -->
<?php
$security->setCSPHeader([
    'default-src' => "'self'",
    'script-src' => "'self' 'unsafe-inline'",
    'style-src' => "'self' 'unsafe-inline'"
]);
?>
```

---

## Performance Optimization

### Built-in Optimization

The View system includes automatic optimization:

```php
// Optimization features (applied automatically in production)
// - HTML comment removal (preserves IE conditional comments)
// - CSS/JS comment removal (safe contexts only)
// - Whitespace compression
// - Line break optimization
```

### Template Caching Strategy

```php
class CachedViewController extends Controller
{
    public function homepage()
    {
        $cacheKey = 'homepage_' . $this->request->get('page', 1);

        $content = $this->instance->cache->get($cacheKey);

        if (!$content) {
            $data = $this->getHomepageData();
            $content = $this->instance->view->render('home/index', $data, true);
            $this->instance->cache->set($cacheKey, $content, 3600); // Cache for 1 hour
        }

        echo $content;
    }
}
```

### Partial Rendering

```php
public function updateUserList()
{
    $users = $this->models->user->getRecent();

    $html = $this->instance->view->render('partials/user_list', [
        'users' => $users
    ], true);

    $this->instance->response->sendJson(['html' => $html]);
}
```

---

## Error Handling

### Template Not Found

```php
// Automatic error handling
$this->instance->view->render('nonexistent/template', $data);
// Triggers: "View file does not exist: /path/to/Views/nonexistent/template.php"
```

### Graceful Fallbacks

```php
class SafeViewController extends Controller
{
    public function renderWithFallback($template, $data, $fallback = 'error/generic')
    {
        if (file_exists(APPLICATION_DIRECTORY . '/Views/' . $template . '.php')) {
            return $this->instance->view->render($template, $data, true);
        } else {
            error_log("Template not found: $template");
            return $this->instance->view->render($fallback, [
                'message' => 'Content temporarily unavailable'
            ], true);
        }
    }
}
```

---

## Best Practices

### 1. Variable Naming

```php
// Use descriptive variable names
$this->instance->view->render('dashboard', [
    'dashboard_stats' => $stats,
    'user_notifications' => $notifications,
    'recent_activity' => $activity
]);
```

### 2. Template Organization

```php
// Group related templates
$this->instance->view->render('user/profile', $data);      // User profile
$this->instance->view->render('user/settings', $data);     // User settings
$this->instance->view->render('admin/users', $data);       // Admin user list
```

### 3. Security First

```php
// Always escape output
<?php echo htmlspecialchars($user_input); ?>

// Use framework security features
<?php echo $security->getCSRFField(); ?>
```

### 4. Component Reuse

```php
// Create reusable components
$sidebar = $this->instance->view->render('components/sidebar', $sidebarData, true);
$header = $this->instance->view->render('components/header', $headerData, true);
```

---

## Framework Integration

The View API integrates seamlessly with all framework components:

-   **Authentication**: Access user data and permissions in templates
-   **Security**: Built-in CSRF protection and XSS prevention
-   **Session**: Flash messages and session data access
-   **Request**: Request data and HTTP information
-   **Utilities**: Helper functions for common template tasks
-   **Components**: Access to framework components and services

The View API provides flexible, secure template rendering with comprehensive framework integration for building modern web applications.
