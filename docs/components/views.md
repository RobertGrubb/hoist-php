# 🖼️ Views

Views in Hoist PHP handle the presentation layer of your application, rendering HTML templates and managing user interfaces with clean separation from business logic.

## View Basics

### **Rendering Views**

Views are PHP files stored in `source/Application/Views/` and are rendered through the View service:

```php
// In a controller
class HomeController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Welcome to Hoist PHP',
            'message' => 'Build amazing applications with ease',
            'user' => $this->instance->auth->user(),
            'posts' => $this->instance->models->post->getRecent(5)
        ];

        $this->instance->view->render('home/index', $data);
    }
}
```

### **View Structure**

```php
<!-- source/Application/Views/home/index.php -->
<?php include APPLICATION_DIRECTORY . '/Views/includes/header.php'; ?>

<div class="hero-section">
    <h1><?= htmlspecialchars($title) ?></h1>
    <p class="lead"><?= htmlspecialchars($message) ?></p>

    <?php if ($user): ?>
        <p>Welcome back, <?= htmlspecialchars($user['name']) ?>!</p>
    <?php else: ?>
        <a href="/login" class="btn btn-primary">Get Started</a>
    <?php endif; ?>
</div>

<div class="recent-posts">
    <h2>Recent Posts</h2>
    <?php if (!empty($posts)): ?>
        <?php foreach ($posts as $post): ?>
            <?php include APPLICATION_DIRECTORY . '/Views/_components/post_card.php'; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No posts available.</p>
    <?php endif; ?>
</div>

<?php include APPLICATION_DIRECTORY . '/Views/includes/footer.php'; ?>
```

## Template Organization

### **Directory Structure**

```
source/Application/Views/
├── includes/                 # Shared includes
│   ├── header.php           # Common header
│   ├── footer.php           # Common footer
│   ├── navigation.php       # Navigation menu
│   └── meta.php             # Meta tags
├── _components/             # Reusable components
│   ├── post_card.php        # Post display component
│   ├── user_avatar.php      # User avatar component
│   ├── pagination.php       # Pagination component
│   └── alert.php            # Alert messages
├── layouts/                 # Layout templates
│   ├── app.php              # Main application layout
│   ├── admin.php            # Admin layout
│   └── auth.php             # Authentication layout
├── home/                    # Home page views
│   └── index.php            # Homepage
├── users/                   # User-related views
│   ├── index.php            # User list
│   ├── show.php             # User profile
│   ├── create.php           # User creation form
│   └── edit.php             # User edit form
└── errors/                  # Error pages
    ├── 404.php              # Not found
    ├── 500.php              # Server error
    └── maintenance.php      # Maintenance mode
```

### **Layout System**

Create a master layout (`source/Application/Views/layouts/app.php`):

```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Hoist PHP App') ?></title>

    <!-- CSS -->
    <link href="/assets/styles/app.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom styles -->
    <?php if (isset($customCss)): ?>
        <?php foreach ($customCss as $css): ?>
            <link href="<?= htmlspecialchars($css) ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Meta tags -->
    <?php if (isset($metaDescription)): ?>
        <meta name="description" content="<?= htmlspecialchars($metaDescription) ?>">
    <?php endif; ?>

    <?php if (isset($metaKeywords)): ?>
        <meta name="keywords" content="<?= htmlspecialchars($metaKeywords) ?>">
    <?php endif; ?>
</head>
<body>
    <!-- Navigation -->
    <?php include APPLICATION_DIRECTORY . '/Views/includes/navigation.php'; ?>

    <!-- Flash Messages -->
    <?php include APPLICATION_DIRECTORY . '/Views/_components/flash_messages.php'; ?>

    <!-- Main Content -->
    <main class="container mt-4">
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <?php include APPLICATION_DIRECTORY . '/Views/includes/footer.php'; ?>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/scripts/app.js"></script>

    <!-- Custom scripts -->
    <?php if (isset($customJs)): ?>
        <?php foreach ($customJs as $js): ?>
            <script src="<?= htmlspecialchars($js) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
```

Use the layout in your views:

```php
<!-- source/Application/Views/users/profile.php -->
<?php
$content = ob_start();
?>

<div class="user-profile">
    <div class="row">
        <div class="col-md-4">
            <?php include APPLICATION_DIRECTORY . '/Views/_components/user_avatar.php'; ?>
        </div>
        <div class="col-md-8">
            <h1><?= htmlspecialchars($user['name']) ?></h1>
            <p class="text-muted">Member since <?= date('F Y', strtotime($user['created_at'])) ?></p>

            <?php if ($user['bio']): ?>
                <div class="bio">
                    <h3>About</h3>
                    <p><?= nl2br(htmlspecialchars($user['bio'])) ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = $user['name'] . ' - User Profile';
$metaDescription = 'View ' . $user['name'] . '\'s profile on our platform';
include APPLICATION_DIRECTORY . '/Views/layouts/app.php';
?>
```

## Reusable Components

### **Component Creation**

Create reusable components for common UI elements:

```php
<!-- _components/post_card.php -->
<div class="card mb-3">
    <div class="card-body">
        <h5 class="card-title">
            <a href="/posts/<?= htmlspecialchars($post['slug']) ?>">
                <?= htmlspecialchars($post['title']) ?>
            </a>
        </h5>
        <p class="card-text"><?= htmlspecialchars(substr($post['excerpt'], 0, 150)) ?>...</p>
        <div class="card-meta">
            <small class="text-muted">
                By <?= htmlspecialchars($post['author_name']) ?> on
                <?= date('F j, Y', strtotime($post['created_at'])) ?>
            </small>
        </div>
    </div>
</div>
```

```php
<!-- _components/pagination.php -->
<?php if ($totalPages > 1): ?>
<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <!-- Previous Page -->
        <?php if ($currentPage > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?= $currentPage - 1 ?>">Previous</a>
            </li>
        <?php endif; ?>

        <!-- Page Numbers -->
        <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
            <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>

        <!-- Next Page -->
        <?php if ($currentPage < $totalPages): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?= $currentPage + 1 ?>">Next</a>
            </li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>
```

### **Flash Messages Component**

```php
<!-- _components/flash_messages.php -->
<?php
$session = $this->instance->session ?? null;
if ($session):
    $success = $session->getFlash('success');
    $error = $session->getFlash('error');
    $warning = $session->getFlash('warning');
    $info = $session->getFlash('info');
?>

<?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($warning): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($warning) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php if ($info): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($info) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<?php endif; ?>
```

## Form Handling

### **Form Creation**

```php
<!-- source/Application/Views/users/create.php -->
<?php
$content = ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <h2>Create New User</h2>

        <!-- Display validation errors -->
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $field => $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="/users/create" enctype="multipart/form-data">
            <!-- CSRF Token -->
            <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">

            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text"
                       class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                       id="name"
                       name="name"
                       value="<?= htmlspecialchars($oldInput['name'] ?? '') ?>"
                       required>
                <?php if (isset($errors['name'])): ?>
                    <div class="invalid-feedback">
                        <?= htmlspecialchars($errors['name']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email"
                       class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                       id="email"
                       name="email"
                       value="<?= htmlspecialchars($oldInput['email'] ?? '') ?>"
                       required>
                <?php if (isset($errors['email'])): ?>
                    <div class="invalid-feedback">
                        <?= htmlspecialchars($errors['email']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password"
                       class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>"
                       id="password"
                       name="password"
                       required>
                <?php if (isset($errors['password'])): ?>
                    <div class="invalid-feedback">
                        <?= htmlspecialchars($errors['password']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label for="avatar" class="form-label">Avatar (Optional)</label>
                <input type="file"
                       class="form-control <?= isset($errors['avatar']) ? 'is-invalid' : '' ?>"
                       id="avatar"
                       name="avatar"
                       accept="image/*">
                <?php if (isset($errors['avatar'])): ?>
                    <div class="invalid-feedback">
                        <?= htmlspecialchars($errors['avatar']) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Create User</button>
                <a href="/users" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
$title = 'Create New User';
$customJs = ['/assets/scripts/forms.js'];
include APPLICATION_DIRECTORY . '/Views/layouts/app.php';
?>
```

### **Form Helper Functions**

Create a form helper for common form elements:

```php
<!-- _components/form_helpers.php -->
<?php
function formInput($name, $label, $type = 'text', $value = '', $errors = [], $attributes = []) {
    $hasError = isset($errors[$name]);
    $class = 'form-control' . ($hasError ? ' is-invalid' : '');

    $attrString = '';
    foreach ($attributes as $key => $val) {
        $attrString .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($val) . '"';
    }

    echo '<div class="mb-3">';
    echo '<label for="' . htmlspecialchars($name) . '" class="form-label">' . htmlspecialchars($label) . '</label>';
    echo '<input type="' . htmlspecialchars($type) . '" class="' . $class . '" id="' . htmlspecialchars($name) . '" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '"' . $attrString . '>';

    if ($hasError) {
        echo '<div class="invalid-feedback">' . htmlspecialchars($errors[$name]) . '</div>';
    }

    echo '</div>';
}

function formTextarea($name, $label, $value = '', $errors = [], $attributes = []) {
    $hasError = isset($errors[$name]);
    $class = 'form-control' . ($hasError ? ' is-invalid' : '');

    $attrString = '';
    foreach ($attributes as $key => $val) {
        $attrString .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($val) . '"';
    }

    echo '<div class="mb-3">';
    echo '<label for="' . htmlspecialchars($name) . '" class="form-label">' . htmlspecialchars($label) . '</label>';
    echo '<textarea class="' . $class . '" id="' . htmlspecialchars($name) . '" name="' . htmlspecialchars($name) . '"' . $attrString . '>' . htmlspecialchars($value) . '</textarea>';

    if ($hasError) {
        echo '<div class="invalid-feedback">' . htmlspecialchars($errors[$name]) . '</div>';
    }

    echo '</div>';
}

function formSelect($name, $label, $options = [], $selected = '', $errors = [], $attributes = []) {
    $hasError = isset($errors[$name]);
    $class = 'form-select' . ($hasError ? ' is-invalid' : '');

    $attrString = '';
    foreach ($attributes as $key => $val) {
        $attrString .= ' ' . htmlspecialchars($key) . '="' . htmlspecialchars($val) . '"';
    }

    echo '<div class="mb-3">';
    echo '<label for="' . htmlspecialchars($name) . '" class="form-label">' . htmlspecialchars($label) . '</label>';
    echo '<select class="' . $class . '" id="' . htmlspecialchars($name) . '" name="' . htmlspecialchars($name) . '"' . $attrString . '>';

    foreach ($options as $value => $text) {
        $selectedAttr = ($value == $selected) ? ' selected' : '';
        echo '<option value="' . htmlspecialchars($value) . '"' . $selectedAttr . '>' . htmlspecialchars($text) . '</option>';
    }

    echo '</select>';

    if ($hasError) {
        echo '<div class="invalid-feedback">' . htmlspecialchars($errors[$name]) . '</div>';
    }

    echo '</div>';
}
?>
```

## Data Tables and Lists

### **Data Table Component**

```php
<!-- _components/data_table.php -->
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <?php foreach ($columns as $column): ?>
                    <th>
                        <?php if (isset($column['sortable']) && $column['sortable']): ?>
                            <a href="?sort=<?= htmlspecialchars($column['key']) ?>&order=<?= $sortOrder === 'asc' ? 'desc' : 'asc' ?>"
                               class="text-white text-decoration-none">
                                <?= htmlspecialchars($column['label']) ?>
                                <?php if ($sortColumn === $column['key']): ?>
                                    <i class="fas fa-sort-<?= $sortOrder === 'asc' ? 'up' : 'down' ?>"></i>
                                <?php endif; ?>
                            </a>
                        <?php else: ?>
                            <?= htmlspecialchars($column['label']) ?>
                        <?php endif; ?>
                    </th>
                <?php endforeach; ?>
                <?php if (isset($actions) && !empty($actions)): ?>
                    <th>Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data)): ?>
                <tr>
                    <td colspan="<?= count($columns) + (isset($actions) ? 1 : 0) ?>" class="text-center">
                        No data available
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($data as $row): ?>
                    <tr>
                        <?php foreach ($columns as $column): ?>
                            <td>
                                <?php
                                $value = $row[$column['key']] ?? '';
                                if (isset($column['format'])) {
                                    switch ($column['format']) {
                                        case 'date':
                                            echo date('M j, Y', strtotime($value));
                                            break;
                                        case 'datetime':
                                            echo date('M j, Y g:i A', strtotime($value));
                                            break;
                                        case 'money':
                                            echo '$' . number_format($value, 2);
                                            break;
                                        case 'boolean':
                                            echo $value ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-danger">No</span>';
                                            break;
                                        default:
                                            echo htmlspecialchars($value);
                                    }
                                } else {
                                    echo htmlspecialchars($value);
                                }
                                ?>
                            </td>
                        <?php endforeach; ?>

                        <?php if (isset($actions) && !empty($actions)): ?>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <?php foreach ($actions as $action): ?>
                                        <a href="<?= str_replace(':id', $row['id'], $action['url']) ?>"
                                           class="btn btn-<?= $action['class'] ?? 'primary' ?>"
                                           title="<?= htmlspecialchars($action['label']) ?>">
                                            <?php if (isset($action['icon'])): ?>
                                                <i class="<?= htmlspecialchars($action['icon']) ?>"></i>
                                            <?php else: ?>
                                                <?= htmlspecialchars($action['label']) ?>
                                            <?php endif; ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
```

Usage in a view:

```php
<!-- source/Application/Views/users/index.php -->
<?php
$columns = [
    ['key' => 'id', 'label' => 'ID', 'sortable' => true],
    ['key' => 'name', 'label' => 'Name', 'sortable' => true],
    ['key' => 'email', 'label' => 'Email', 'sortable' => true],
    ['key' => 'created_at', 'label' => 'Created', 'format' => 'date', 'sortable' => true],
    ['key' => 'status', 'label' => 'Status']
];

$actions = [
    ['url' => '/users/:id', 'label' => 'View', 'icon' => 'fas fa-eye', 'class' => 'info'],
    ['url' => '/users/:id/edit', 'label' => 'Edit', 'icon' => 'fas fa-edit', 'class' => 'warning'],
    ['url' => '/users/:id/delete', 'label' => 'Delete', 'icon' => 'fas fa-trash', 'class' => 'danger']
];

$data = $users; // From controller
$sortColumn = $_GET['sort'] ?? 'id';
$sortOrder = $_GET['order'] ?? 'asc';

$content = ob_start();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Users</h2>
    <a href="/users/create" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add New User
    </a>
</div>

<?php include APPLICATION_DIRECTORY . '/Views/_components/data_table.php'; ?>

<!-- Pagination -->
<?php if (isset($pagination)): ?>
    <?php
    $currentPage = $pagination['current_page'];
    $totalPages = $pagination['total_pages'];
    include APPLICATION_DIRECTORY . '/Views/_components/pagination.php';
    ?>
<?php endif; ?>

<?php
$content = ob_get_clean();
$title = 'Users';
include APPLICATION_DIRECTORY . '/Views/layouts/app.php';
?>
```

## Security and Best Practices

### **XSS Prevention**

Always escape output to prevent XSS attacks:

```php
<!-- Safe output -->
<h1><?= htmlspecialchars($title) ?></h1>
<p><?= nl2br(htmlspecialchars($description)) ?></p>

<!-- For rich content, use a whitelist approach -->
<?php
function sanitizeHtml($html) {
    // Use HTMLPurifier or similar library for rich content
    $allowed_tags = '<p><br><strong><em><ul><ol><li><a>';
    return strip_tags($html, $allowed_tags);
}
?>

<div class="content">
    <?= sanitizeHtml($post['content']) ?>
</div>
```

### **CSRF Protection**

Include CSRF tokens in forms:

```php
<!-- Include in all forms -->
<input type="hidden" name="_token" value="<?= htmlspecialchars($this->instance->security->generateCsrfToken()) ?>">

<!-- Or use a helper -->
<?php
function csrfField($instance) {
    return '<input type="hidden" name="_token" value="' . htmlspecialchars($instance->security->generateCsrfToken()) . '">';
}
?>

<form method="POST">
    <?= csrfField($this->instance) ?>
    <!-- form fields -->
</form>
```

### **Content Security Policy**

Set appropriate headers for security:

```php
<!-- In layout header -->
<?php
// Set CSP headers (usually done in controller or middleware)
if (isset($this->instance->response)) {
    $this->instance->response->setHeader('Content-Security-Policy',
        "default-src 'self'; script-src 'self' 'unsafe-inline' cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' cdn.jsdelivr.net");
}
?>
```

## Error Pages

### **Custom Error Pages**

```php
<!-- source/Application/Views/errors/404.php -->
<?php
$content = ob_start();
?>

<div class="text-center">
    <h1 class="display-1">404</h1>
    <p class="fs-3"><span class="text-danger">Oops!</span> Page not found.</p>
    <p class="lead">The page you're looking for doesn't exist.</p>
    <a href="/" class="btn btn-primary">Go Home</a>
</div>

<?php
$content = ob_get_clean();
$title = '404 - Page Not Found';
http_response_code(404);
include APPLICATION_DIRECTORY . '/Views/layouts/app.php';
?>
```

```php
<!-- source/Application/Views/errors/500.php -->
<?php
$content = ob_start();
?>

<div class="text-center">
    <h1 class="display-1">500</h1>
    <p class="fs-3"><span class="text-danger">Oops!</span> Something went wrong.</p>
    <p class="lead">We're experiencing some technical difficulties. Please try again later.</p>

    <?php if (APPLICATION_ENVIRONMENT === 'development' && isset($error)): ?>
        <div class="alert alert-danger text-start mt-4">
            <h4>Debug Information:</h4>
            <pre><?= htmlspecialchars($error) ?></pre>
        </div>
    <?php endif; ?>

    <a href="/" class="btn btn-primary">Go Home</a>
</div>

<?php
$content = ob_get_clean();
$title = '500 - Server Error';
http_response_code(500);
include APPLICATION_DIRECTORY . '/Views/layouts/app.php';
?>
```

---

Views provide the presentation layer of your Hoist PHP application. Use layouts, components, and proper security practices to create maintainable and secure user interfaces.

**Next:** [Response Management](response.md) - Learn about HTTP response handling.
