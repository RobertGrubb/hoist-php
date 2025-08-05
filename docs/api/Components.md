# Components API Documentation

## Overview

The Components API provides a powerful dynamic component loading and rendering system for creating reusable UI elements throughout the Hoist PHP framework. This system automatically discovers and registers PHP components from the Application Components directory, enabling modular and maintainable view architecture with full framework integration.

## Class: Components

**Location**: `Core/Libraries/Components.php`  
**Pattern**: Closure-Based Dynamic Component System  
**Usage**: Reusable UI component rendering with data injection  
**Features**: Automatic discovery, hierarchical organization, framework integration

---

## Core Architecture

### Component Structure

Components are PHP files that return closures accepting `($instance, $data)` parameters:

```php
// Application/Components/Button.php
<?php
return function($instance, $data) {
    $text = $data['text'] ?? 'Button';
    $variant = $data['variant'] ?? 'primary';
    $size = $data['size'] ?? 'md';

    return "<button class=\"btn btn-{$variant} btn-{$size}\">{$text}</button>";
};
```

### Directory Organization

```
Application/Components/
├── Form/                 # Form-related components
│   ├── Input.php        # Form.Input
│   ├── Button.php       # Form.Button
│   ├── Select.php       # Form.Select
│   └── Textarea.php     # Form.Textarea
├── Layout/              # Layout components
│   ├── Card.php         # Layout.Card
│   ├── DataTable.php    # Layout.DataTable
│   └── FeatureList.php  # Layout.FeatureList
├── UI/                  # UI elements
│   ├── Modal.php        # UI.Modal
│   ├── Alert.php        # UI.Alert
│   └── Badge.php        # UI.Badge
└── Button.php           # Top-level Button component
```

### Framework Integration

Components automatically receive the framework instance, providing access to all services:

```php
// Component with framework service access
return function($instance, $data) {
    $user = $instance->auth->getUser();
    $csrf = $instance->security->generateCSRFToken();
    $baseUrl = $instance->baseUrl;

    return "<form action=\"{$baseUrl}/api/update\" method=\"POST\">
        <input type=\"hidden\" name=\"csrf_token\" value=\"{$csrf}\">
        <!-- form content -->
    </form>";
};
```

---

## Properties

### Framework Integration

#### `$instance`

**Type**: `object`  
**Description**: Application instance providing access to all framework services

#### `$components`

**Type**: `array`  
**Description**: Array of registered component closures (name => callable mapping)

---

## Constructor

### `__construct($instance)`

Initializes the component system with automatic component discovery.

**Parameters:**

-   `$instance` (object): Application instance for service access

**Initialization Process:**

1. Stores framework instance reference
2. Scans Components directory recursively
3. Registers all valid component files
4. Creates dot notation mapping for nested components

**Example:**

```php
// Framework automatically initializes
$components = new Components($frameworkInstance);

// Available in views and controllers as:
$this->instance->components; // or $components in views
```

---

## Component Registration and Discovery

### `register()`

**Access**: `private`  
**Description**: Automatically discovers and registers all available components

**Registration Process:**

1. Scans `/Application/Components/` directory recursively
2. Includes each `.php` file and validates return value
3. Verifies returned value is callable (closure/function)
4. Registers component using dot notation naming
5. Skips invalid components with graceful error handling

### `scanDirectory($directory, $namespace = '')`

**Access**: `private`  
**Description**: Recursively scans directory structure for component files

**Parameters:**

-   `$directory` (string): Directory path to scan
-   `$namespace` (string): Current namespace prefix for dot notation

**Naming Convention:**

-   `Button.php` → `'Button'`
-   `Form/Input.php` → `'Form.Input'`
-   `Admin/User/Card.php` → `'Admin.User.Card'`

---

## Component Rendering

### `render($component, $data = [])`

Renders a registered component with provided data.

**Parameters:**

-   `$component` (string): Component name in dot notation
-   `$data` (array): Data array to pass to component function

**Returns:** `mixed` - Component output (typically HTML string) or false if not found

**Component Function Signature:**

```php
function($instance, $data) {
    // Access framework services: $instance->view, $instance->db, etc.
    // Use provided data: $data['property']
    return "rendered HTML";
}
```

**Example:**

```php
// Basic component rendering
echo $components->render('Button', [
    'text' => 'Save Changes',
    'variant' => 'primary',
    'size' => 'lg'
]);

// Form input with validation state
echo $components->render('Form.Input', [
    'name' => 'email',
    'type' => 'email',
    'label' => 'Email Address',
    'value' => $oldInput['email'] ?? '',
    'required' => true,
    'error' => $validationErrors['email'] ?? ''
]);

// Complex data table component
echo $components->render('Layout.DataTable', [
    'headers' => ['Name', 'Email', 'Role', 'Status'],
    'rows' => $users,
    'actions' => [
        [
            'title' => 'Edit User',
            'icon' => 'fas fa-edit',
            'class' => 'btn btn-sm btn-primary',
            'onclick' => 'editUser({id})'
        ],
        [
            'title' => 'Delete User',
            'icon' => 'fas fa-trash',
            'class' => 'btn btn-sm btn-danger',
            'onclick' => 'deleteUser({id})',
            'condition' => function($data) { return $data['role'] !== 'admin'; }
        ]
    ]
]);

// Modal with dynamic content
echo $components->render('UI.Modal', [
    'id' => 'confirmDialog',
    'title' => 'Confirm Action',
    'content' => 'Are you sure you want to delete this item?',
    'footer' => $components->render('Form.Button', ['text' => 'Confirm', 'variant' => 'danger'])
]);

// Framework integration in views
public function dashboard()
{
    $stats = $this->getStats();

    $this->view->render('admin/dashboard', [
        'stats_cards' => $this->instance->components->render('Layout.StatCards', ['data' => $stats])
    ]);
}
```

---

## Component Utility Methods

### `getRegisteredComponents()`

Returns an array of all registered component names.

**Returns:** `array` - Array of registered component names

**Example:**

```php
// Get all available components
$availableComponents = $components->getRegisteredComponents();

// Output: ['Button', 'Form.Input', 'Form.Button', 'Layout.Card', 'UI.Modal', ...]
print_r($availableComponents);

// Check component availability in development
if (in_array('Form.DatePicker', $components->getRegisteredComponents())) {
    echo $components->render('Form.DatePicker', $datePickerData);
} else {
    echo '<input type="date" name="date" />';
}
```

### `exists($component)`

Checks if a component is registered and available.

**Parameters:**

-   `$component` (string): Component name in dot notation

**Returns:** `bool` - True if component exists, false otherwise

**Example:**

```php
// Check component existence before rendering
if ($components->exists('Form.RichTextEditor')) {
    echo $components->render('Form.RichTextEditor', $editorConfig);
} else {
    echo $components->render('Form.Textarea', $textareaConfig);
}

// Conditional component loading
$inputComponent = $components->exists('Form.AdvancedInput') ? 'Form.AdvancedInput' : 'Form.Input';
echo $components->render($inputComponent, $inputData);

// Development safety checks
function renderSafeComponent($components, $name, $data, $fallback = '') {
    return $components->exists($name) ? $components->render($name, $data) : $fallback;
}
```

---

## Component Examples

### Form Components

#### Input Component (`Form/Input.php`)

```php
<?php
return function ($instance, $data = []) {
    $type = $data['type'] ?? 'text';
    $name = $data['name'] ?? '';
    $value = htmlspecialchars($data['value'] ?? '');
    $placeholder = $data['placeholder'] ?? '';
    $required = $data['required'] ?? false;
    $disabled = $data['disabled'] ?? false;
    $error = $data['error'] ?? '';
    $label = $data['label'] ?? '';
    $id = $data['id'] ?? $name;

    // Build classes based on state
    $baseClasses = 'block w-full px-3 py-2 border rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 sm:text-sm';
    $errorClasses = $error ? 'border-red-300 text-red-900 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-blue-500 focus:border-blue-500';
    $disabledClasses = $disabled ? 'bg-gray-50 text-gray-500 cursor-not-allowed' : 'bg-white';

    $classes = "$baseClasses $errorClasses $disabledClasses";

    $html = '';

    // Label
    if ($label) {
        $requiredMark = $required ? '<span class="text-red-500 ml-1">*</span>' : '';
        $html .= "<label for=\"{$id}\" class=\"block text-sm font-medium text-gray-700 mb-1\">{$label}{$requiredMark}</label>";
    }

    // Input field
    $html .= "<input type=\"{$type}\" name=\"{$name}\" id=\"{$id}\" value=\"{$value}\" placeholder=\"{$placeholder}\" class=\"{$classes}\"";

    if ($required) $html .= ' required';
    if ($disabled) $html .= ' disabled';

    $html .= ' />';

    // Error message
    if ($error) {
        $html .= "<p class=\"mt-1 text-sm text-red-600\"><i class=\"fas fa-exclamation-circle mr-1\"></i>{$error}</p>";
    }

    return $html;
};
```

**Usage:**

```php
// Basic input
echo $components->render('Form.Input', [
    'name' => 'username',
    'label' => 'Username',
    'placeholder' => 'Enter your username'
]);

// Input with validation error
echo $components->render('Form.Input', [
    'name' => 'email',
    'type' => 'email',
    'label' => 'Email Address',
    'value' => $formData['email'] ?? '',
    'error' => $errors['email'] ?? '',
    'required' => true
]);

// Disabled input with value
echo $components->render('Form.Input', [
    'name' => 'user_id',
    'type' => 'hidden',
    'value' => $user['id'],
    'disabled' => true
]);
```

#### Button Component (`Form/Button.php`)

```php
<?php
return function ($instance, $data = []) {
    $text = $data['text'] ?? 'Button';
    $type = $data['type'] ?? 'button';
    $variant = $data['variant'] ?? 'primary';
    $size = $data['size'] ?? 'md';
    $disabled = $data['disabled'] ?? false;
    $loading = $data['loading'] ?? false;
    $icon = $data['icon'] ?? '';
    $iconPosition = $data['iconPosition'] ?? 'left';
    $fullWidth = $data['fullWidth'] ?? false;
    $onclick = $data['onclick'] ?? '';

    // Classes configuration
    $baseClasses = 'inline-flex items-center justify-center border font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200';

    $sizeClasses = [
        'sm' => 'px-3 py-2 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base'
    ];

    $variantClasses = [
        'primary' => 'border-transparent text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
        'secondary' => 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:ring-blue-500',
        'danger' => 'border-transparent text-white bg-red-600 hover:bg-red-700 focus:ring-red-500',
        'success' => 'border-transparent text-white bg-green-600 hover:bg-green-700 focus:ring-green-500'
    ];

    $classes = $baseClasses . ' ' . $sizeClasses[$size] . ' ' . $variantClasses[$variant];

    if ($fullWidth) $classes .= ' w-full';
    if ($disabled || $loading) $classes .= ' opacity-50 cursor-not-allowed';

    $html = "<button type=\"{$type}\" class=\"{$classes}\"";
    if ($onclick) $html .= ' onclick="' . htmlspecialchars($onclick, ENT_QUOTES, 'UTF-8') . '"';
    if ($disabled) $html .= ' disabled';
    $html .= '>';

    if ($loading) {
        $html .= '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">';
        $html .= '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>';
        $html .= '<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>';
        $html .= '</svg>Loading...';
    } else {
        if ($icon && $iconPosition === 'left') {
            $html .= "<i class=\"{$icon} mr-2\"></i>";
        }
        $html .= $text;
        if ($icon && $iconPosition === 'right') {
            $html .= "<i class=\"{$icon} ml-2\"></i>";
        }
    }

    $html .= '</button>';
    return $html;
};
```

**Usage:**

```php
// Primary action button
echo $components->render('Form.Button', [
    'text' => 'Save Changes',
    'type' => 'submit',
    'variant' => 'primary',
    'icon' => 'fas fa-save'
]);

// Loading state button
echo $components->render('Form.Button', [
    'text' => 'Processing...',
    'loading' => true,
    'disabled' => true
]);

// Dangerous action with confirmation
echo $components->render('Form.Button', [
    'text' => 'Delete Account',
    'variant' => 'danger',
    'onclick' => 'confirmDeleteAccount()',
    'icon' => 'fas fa-trash'
]);
```

### Layout Components

#### Card Component (`Layout/Card.php`)

```php
<?php
return function ($instance, $data = []) {
    $content = $data['content'] ?? '';
    $title = $data['title'] ?? null;
    $footer = $data['footer'] ?? null;
    $classes = $data['classes'] ?? '';
    $headerClasses = $data['headerClasses'] ?? '';
    $bodyClasses = $data['bodyClasses'] ?? '';
    $footerClasses = $data['footerClasses'] ?? '';

    $defaultClasses = 'bg-white rounded-lg shadow-lg border border-gray-200';
    $cardClasses = $classes ? $classes : $defaultClasses;

    $html = '<div class="' . $cardClasses . '">';

    // Header
    if ($title) {
        $defaultHeaderClasses = 'px-6 py-4 border-b border-gray-200';
        $finalHeaderClasses = $headerClasses ? $headerClasses : $defaultHeaderClasses;
        $html .= '<div class="' . $finalHeaderClasses . '">';
        $html .= '<h3 class="text-lg font-semibold text-gray-900">' . htmlspecialchars($title) . '</h3>';
        $html .= '</div>';
    }

    // Body
    $defaultBodyClasses = $title || $footer ? 'px-6 py-8' : 'p-6';
    $finalBodyClasses = $bodyClasses ? $bodyClasses : $defaultBodyClasses;
    $html .= '<div class="' . $finalBodyClasses . '">';
    $html .= $content;
    $html .= '</div>';

    // Footer
    if ($footer) {
        $defaultFooterClasses = 'px-6 py-4 border-t border-gray-200 bg-gray-50';
        $finalFooterClasses = $footerClasses ? $footerClasses : $defaultFooterClasses;
        $html .= '<div class="' . $finalFooterClasses . '">';
        $html .= $footer;
        $html .= '</div>';
    }

    $html .= '</div>';
    return $html;
};
```

**Usage:**

```php
// Simple card with content
echo $components->render('Layout.Card', [
    'title' => 'User Profile',
    'content' => '<p>User profile information goes here...</p>'
]);

// Card with footer actions
echo $components->render('Layout.Card', [
    'title' => 'Account Settings',
    'content' => $settingsForm,
    'footer' => $components->render('Form.Button', ['text' => 'Save Settings', 'variant' => 'primary'])
]);

// Custom styled card
echo $components->render('Layout.Card', [
    'content' => $dashboardWidget,
    'classes' => 'bg-gradient-to-r from-blue-500 to-purple-600 text-white shadow-xl',
    'bodyClasses' => 'p-8 text-center'
]);
```

#### DataTable Component (`Layout/DataTable.php`)

```php
<?php
return function ($instance, $data = []) {
    $headers = $data['headers'] ?? [];
    $rows = $data['rows'] ?? [];
    $actions = $data['actions'] ?? [];

    if (empty($headers) || empty($rows)) {
        return '';
    }

    $html = '<div class="overflow-x-auto">';
    $html .= '<table class="min-w-full divide-y divide-gray-200">';

    // Header
    $html .= '<thead class="bg-gray-50"><tr>';
    foreach ($headers as $header) {
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">';
        $html .= htmlspecialchars($header);
        $html .= '</th>';
    }
    if (!empty($actions)) {
        $html .= '<th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>';
    }
    $html .= '</tr></thead>';

    // Body
    $html .= '<tbody class="bg-white divide-y divide-gray-200">';
    foreach ($rows as $row) {
        $html .= '<tr class="hover:bg-gray-50">';
        foreach ($row['cells'] as $cell) {
            $html .= '<td class="px-6 py-4 whitespace-nowrap">' . $cell . '</td>';
        }

        // Actions
        if (!empty($actions)) {
            $html .= '<td class="px-6 py-4 whitespace-nowrap text-sm font-medium">';
            $html .= '<div class="flex items-center space-x-2">';
            foreach ($actions as $action) {
                if (isset($action['condition']) && !$action['condition']($row['data'])) {
                    continue;
                }

                $onclick = '';
                if (isset($action['onclick'])) {
                    $onclick = $action['onclick'];
                    foreach ($row['data'] as $key => $value) {
                        $onclick = str_replace('{' . $key . '}', htmlspecialchars($value ?? ''), $onclick);
                    }
                    $onclick = ' onclick="' . $onclick . '"';
                }

                $html .= '<button class="' . $action['class'] . '" title="' . htmlspecialchars($action['title']) . '"' . $onclick . '>';
                $html .= '<i class="' . $action['icon'] . '"></i>';
                $html .= '</button>';
            }
            $html .= '</div></td>';
        }
        $html .= '</tr>';
    }
    $html .= '</tbody></table></div>';

    return $html;
};
```

**Usage:**

```php
// User management table
echo $components->render('Layout.DataTable', [
    'headers' => ['Name', 'Email', 'Role', 'Status'],
    'rows' => array_map(function($user) {
        return [
            'cells' => [
                htmlspecialchars($user['name']),
                htmlspecialchars($user['email']),
                htmlspecialchars($user['role']),
                '<span class="badge badge-' . ($user['active'] ? 'success' : 'secondary') . '">' .
                ($user['active'] ? 'Active' : 'Inactive') . '</span>'
            ],
            'data' => $user
        ];
    }, $users),
    'actions' => [
        [
            'title' => 'Edit User',
            'icon' => 'fas fa-edit',
            'class' => 'btn btn-sm btn-outline-primary',
            'onclick' => 'editUser({id})'
        ],
        [
            'title' => 'Delete User',
            'icon' => 'fas fa-trash',
            'class' => 'btn btn-sm btn-outline-danger',
            'onclick' => 'deleteUser({id})',
            'condition' => function($data) { return $data['role'] !== 'admin'; }
        ]
    ]
]);
```

### UI Components

#### Modal Component (`UI/Modal.php`)

```php
<?php
return function ($instance, $data = []) {
    $id = $data['id'] ?? 'modal-' . uniqid();
    $title = $data['title'] ?? '';
    $content = $data['content'] ?? '';
    $footer = $data['footer'] ?? '';
    $size = $data['size'] ?? 'md';
    $closable = $data['closable'] ?? true;
    $backdrop = $data['backdrop'] ?? true;

    $sizeClasses = [
        'sm' => 'max-w-md',
        'md' => 'max-w-lg',
        'lg' => 'max-w-2xl',
        'xl' => 'max-w-4xl'
    ];

    $modalClass = $sizeClasses[$size] ?? $sizeClasses['md'];

    $html = '<div id="' . $id . '" class="fixed inset-0 z-50 overflow-y-auto hidden" role="dialog" aria-modal="true">';
    $html .= '<div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">';
    $html .= '<div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"' . ($backdrop ? ' onclick="closeModal(\'' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '\')"' : '') . '></div>';
    $html .= '<span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>';
    $html .= '<div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:w-full ' . $modalClass . '">';

    // Header
    if ($title || $closable) {
        $html .= '<div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-200">';
        $html .= '<div class="flex items-center justify-between">';
        if ($title) {
            $html .= '<h3 class="text-lg leading-6 font-medium text-gray-900">' . htmlspecialchars($title) . '</h3>';
        }
        if ($closable) {
            $html .= '<button type="button" class="bg-white rounded-md text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500" onclick="closeModal(\'' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '\')">';
            $html .= '<span class="sr-only">Close</span><i class="fas fa-times text-xl"></i>';
            $html .= '</button>';
        }
        $html .= '</div></div>';
    }

    // Body
    $html .= '<div class="bg-white px-4 pt-5 pb-4 sm:p-6">' . $content . '</div>';

    // Footer
    if ($footer) {
        $html .= '<div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-200">' . $footer . '</div>';
    }

    $html .= '</div></div></div>';

    // JavaScript (added once)
    if (!isset($GLOBALS['modal_js_added'])) {
        $html .= '<script>
            window.openModal = function(modalId) {
                document.getElementById(modalId).classList.remove("hidden");
                document.body.style.overflow = "hidden";
            };
            window.closeModal = function(modalId) {
                document.getElementById(modalId).classList.add("hidden");
                document.body.style.overflow = "auto";
            };
            document.addEventListener("keydown", function(e) {
                if (e.key === "Escape") {
                    const openModals = document.querySelectorAll("[id^=\'modal-\']:not(.hidden), [id$=\'Modal\']:not(.hidden)");
                    openModals.forEach(modal => window.closeModal(modal.id));
                }
            });
        </script>';
        $GLOBALS['modal_js_added'] = true;
    }

    return $html;
};
```

**Usage:**

```php
// Confirmation dialog
echo $components->render('UI.Modal', [
    'id' => 'confirmDelete',
    'title' => 'Confirm Deletion',
    'content' => '<p>Are you sure you want to delete this item? This action cannot be undone.</p>',
    'footer' =>
        $components->render('Form.Button', ['text' => 'Cancel', 'variant' => 'secondary', 'onclick' => 'closeModal("confirmDelete")']) . ' ' .
        $components->render('Form.Button', ['text' => 'Delete', 'variant' => 'danger', 'onclick' => 'confirmDelete()'])
]);

// Form modal
echo $components->render('UI.Modal', [
    'id' => 'editUser',
    'title' => 'Edit User',
    'size' => 'lg',
    'content' => $editUserForm,
    'footer' => $components->render('Form.Button', ['text' => 'Save Changes', 'type' => 'submit', 'variant' => 'primary'])
]);
```

---

## Framework Integration Examples

### Controller Integration

```php
class UserController extends Controller
{
    public function index()
    {
        $users = $this->instance->fileDatabase
            ->table('users')
            ->where('status', '=', 'active')
            ->all();

        // Render page with components
        $this->view->render('users/index', [
            'userTable' => $this->instance->components->render('Layout.DataTable', [
                'headers' => ['Name', 'Email', 'Role'],
                'rows' => $this->formatUsersForTable($users),
                'actions' => $this->getUserTableActions()
            ]),
            'addUserButton' => $this->instance->components->render('Form.Button', [
                'text' => 'Add New User',
                'variant' => 'primary',
                'icon' => 'fas fa-plus',
                'onclick' => 'openModal("addUserModal")'
            ])
        ]);
    }

    public function create()
    {
        // Form handling with component-generated forms
        $form = $this->instance->components->render('Form.UserForm', [
            'action' => '/users/store',
            'method' => 'POST',
            'csrf_token' => $this->instance->security->generateCSRFToken()
        ]);

        $this->view->render('users/create', ['form' => $form]);
    }

    private function formatUsersForTable($users)
    {
        return array_map(function($user) {
            return [
                'cells' => [
                    htmlspecialchars($user['name']),
                    htmlspecialchars($user['email']),
                    $this->instance->components->render('UI.Badge', [
                        'text' => ucfirst($user['role']),
                        'variant' => $user['role'] === 'admin' ? 'primary' : 'secondary'
                    ])
                ],
                'data' => $user
            ];
        }, $users);
    }

    private function getUserTableActions()
    {
        return [
            [
                'title' => 'Edit',
                'icon' => 'fas fa-edit',
                'class' => 'btn btn-sm btn-outline-primary',
                'onclick' => 'editUser({id})'
            ],
            [
                'title' => 'Delete',
                'icon' => 'fas fa-trash',
                'class' => 'btn btn-sm btn-outline-danger',
                'onclick' => 'deleteUser({id})',
                'condition' => function($data) {
                    return $data['role'] !== 'admin';
                }
            ]
        ];
    }
}
```

### View Integration

```php
<!-- users/index.php -->
<?= $view->render('includes/header', ['title' => 'User Management']); ?>

<div class="container mx-auto px-4 py-8">
    <!-- Page Header -->
    <?= $components->render('Layout.Card', [
        'content' => '
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
                    <p class="text-gray-600">Manage system users and their permissions</p>
                </div>
                <div>' . $addUserButton . '</div>
            </div>
        '
    ]) ?>

    <!-- Filters -->
    <?= $components->render('Layout.Card', [
        'title' => 'Filters',
        'content' => '
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                ' . $components->render('Form.Input', [
                    'name' => 'search',
                    'placeholder' => 'Search users...',
                    'icon' => 'fas fa-search'
                ]) . '
                ' . $components->render('Form.Select', [
                    'name' => 'role',
                    'options' => ['all' => 'All Roles', 'admin' => 'Admin', 'user' => 'User'],
                    'placeholder' => 'Filter by role'
                ]) . '
                ' . $components->render('Form.Select', [
                    'name' => 'status',
                    'options' => ['all' => 'All Status', 'active' => 'Active', 'inactive' => 'Inactive'],
                    'placeholder' => 'Filter by status'
                ]) . '
            </div>
        '
    ]) ?>

    <!-- Users Table -->
    <?= $components->render('Layout.Card', [
        'title' => 'Users (' . count($users) . ')',
        'content' => $userTable
    ]) ?>

    <!-- Add User Modal -->
    <?= $components->render('UI.Modal', [
        'id' => 'addUserModal',
        'title' => 'Add New User',
        'size' => 'lg',
        'content' => $userForm
    ]) ?>
</div>

<?= $view->render('includes/footer'); ?>
```

### Complex Component Composition

```php
// Dashboard with multiple component types
public function dashboard()
{
    $stats = $this->getDashboardStats();
    $recentUsers = $this->getRecentUsers();
    $systemHealth = $this->getSystemHealth();

    $dashboardContent =
        // Stats row
        '<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">' .
        $this->instance->components->render('Layout.StatCard', [
            'title' => 'Total Users',
            'value' => $stats['total_users'],
            'icon' => 'fas fa-users',
            'color' => 'blue',
            'trend' => '+12%'
        ]) .
        $this->instance->components->render('Layout.StatCard', [
            'title' => 'Active Sessions',
            'value' => $stats['active_sessions'],
            'icon' => 'fas fa-circle',
            'color' => 'green',
            'trend' => '+5%'
        ]) .
        $this->instance->components->render('Layout.StatCard', [
            'title' => 'Revenue',
            'value' => '$' . number_format($stats['revenue'], 2),
            'icon' => 'fas fa-dollar-sign',
            'color' => 'yellow',
            'trend' => '+18%'
        ]) .
        $this->instance->components->render('Layout.StatCard', [
            'title' => 'System Load',
            'value' => $systemHealth['cpu_usage'] . '%',
            'icon' => 'fas fa-server',
            'color' => $systemHealth['cpu_usage'] > 80 ? 'red' : 'blue'
        ]) .
        '</div>' .

        // Content grid
        '<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">' .
        $this->instance->components->render('Layout.Card', [
            'title' => 'Recent Users',
            'content' => $this->instance->components->render('Layout.DataTable', [
                'headers' => ['Name', 'Email', 'Joined'],
                'rows' => array_map(function($user) {
                    return [
                        'cells' => [
                            htmlspecialchars($user['name']),
                            htmlspecialchars($user['email']),
                            date('M j, Y', strtotime($user['created_at']))
                        ],
                        'data' => $user
                    ];
                }, $recentUsers)
            ])
        ]) .
        $this->instance->components->render('Layout.Card', [
            'title' => 'System Health',
            'content' => $this->instance->components->render('Layout.FeatureList', [
                'features' => [
                    [
                        'title' => 'Database Connection',
                        'description' => $systemHealth['database'] ? 'Connected' : 'Error'
                    ],
                    [
                        'title' => 'Cache System',
                        'description' => $systemHealth['cache'] ? 'Running' : 'Down'
                    ],
                    [
                        'title' => 'File Permissions',
                        'description' => $systemHealth['permissions'] ? 'OK' : 'Issues'
                    ]
                ]
            ])
        ]) .
        '</div>';

    $this->view->render('admin/dashboard', [
        'content' => $dashboardContent
    ]);
}
```

---

## Best Practices

### Component Design Principles

```php
// 1. Single Responsibility - Each component has one clear purpose
// ✅ Good: Separate Input and Button components
echo $components->render('Form.Input', $inputData);
echo $components->render('Form.Button', $buttonData);

// ❌ Bad: One component doing everything
echo $components->render('Form.Everything', $allData);

// 2. Data Validation - Always validate and provide defaults
return function($instance, $data) {
    $title = $data['title'] ?? '';
    $content = $data['content'] ?? '';

    if (empty($title)) {
        return ''; // Graceful degradation
    }

    return "<h3>{$title}</h3><p>{$content}</p>";
};

// 3. Framework Integration - Use framework services when appropriate
return function($instance, $data) {
    $user = $instance->auth->getUser();
    $hasPermission = $instance->auth->hasPermission('admin');

    if (!$hasPermission) {
        return ''; // Don't render for unauthorized users
    }

    return "<div class=\"admin-only\">{$data['content']}</div>";
};

// 4. Escape Output - Always sanitize user data
return function($instance, $data) {
    $title = htmlspecialchars($data['title'] ?? '');
    $content = htmlspecialchars($data['content'] ?? '');

    return "<div><h3>{$title}</h3><p>{$content}</p></div>";
};
```

### Error Handling

```php
// Component-level error handling
return function($instance, $data) {
    try {
        $user = $data['user'] ?? null;

        if (!$user || !isset($user['id'])) {
            return '<div class="alert alert-warning">User data not available</div>';
        }

        // Render user card
        return "<div class=\"user-card\">...</div>";

    } catch (Exception $e) {
        error_log("User card component error: " . $e->getMessage());
        return '<div class="alert alert-error">Unable to load user information</div>';
    }
};

// Application-level error handling
if (!$components->exists('Layout.DataTable')) {
    // Fallback to basic HTML table
    echo "<table>...</table>";
} else {
    echo $components->render('Layout.DataTable', $tableData);
}
```

### Performance Optimization

```php
// 1. Component Caching for Expensive Operations
return function($instance, $data) {
    $cacheKey = 'complex_widget_' . md5(serialize($data));

    if ($cached = $instance->cache->get($cacheKey)) {
        return $cached;
    }

    $result = $this->performExpensiveOperation($data);
    $instance->cache->set($cacheKey, $result, 3600); // Cache for 1 hour

    return $result;
};

// 2. Conditional Component Loading
$components = [];
if ($user['role'] === 'admin') {
    $components['admin_panel'] = $components->render('Admin.Panel', $adminData);
}
if ($user['subscription'] === 'premium') {
    $components['premium_features'] = $components->render('Premium.Features', $premiumData);
}

// 3. Lazy Loading for Large Datasets
return function($instance, $data) {
    $limit = $data['limit'] ?? 10;
    $offset = $data['offset'] ?? 0;

    // Only load what's needed
    $items = array_slice($data['items'], $offset, $limit);

    return $this->renderItems($items);
};
```

---

## Framework Integration

The Components API seamlessly integrates with all framework components:

-   **Views**: Direct access via `$components` variable in all templates
-   **Controllers**: Access through `$this->instance->components` for dynamic rendering
-   **Models**: Component rendering for data presentation and formatting
-   **Authentication**: Role-based component visibility and access control
-   **Validation**: Error state rendering and form validation display
-   **Cache**: Component output caching for performance optimization

The Components system provides the foundation for building scalable, maintainable applications with reusable UI elements that maintain consistency across the entire application while providing the flexibility to adapt to specific use cases.
