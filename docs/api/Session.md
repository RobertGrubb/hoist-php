# Session API Documentation

## Overview

The Session API provides advanced session management for the Hoist PHP framework with specialized support for flash data and persistent state management. It extends PHP's native session handling with features optimized for modern web application development patterns.

## Class: Session

**Location**: `Core/Libraries/Session.php`  
**Access**: Available as `$this->session` in controllers  
**Version**: 1.0.0 with flash data lifecycle management  
**Features**: Flash data, state persistence, automatic cleanup

---

## Core Concepts

### Flash Data

**Purpose**: Temporary data that persists for exactly one request cycle  
**Lifecycle**: Set → Store → Retrieve → Auto-clear  
**Use Cases**: Status messages, validation errors, notifications, PRG pattern

### State Data

**Purpose**: Persistent session storage for long-term data  
**Lifecycle**: Set → Store → Persist until explicitly removed  
**Use Cases**: User preferences, shopping carts, multi-step forms, application state

---

## Properties

### Private Properties

#### `$instance`

**Type**: `Instance`  
**Description**: Framework service container for cross-service communication

#### `$flashData`

**Type**: `array`  
**Description**: Current request's flash data retrieved from previous request

#### `$newFlashData`

**Type**: `array`  
**Description**: New flash data to be saved for the next request

#### `$stateData`

**Type**: `array`  
**Description**: Persistent state data for long-term session storage

#### `$flashDataKey`

**Type**: `string`  
**Default**: `'SITE_FLASH_DATA'`  
**Description**: Session key for flash data storage

#### `$stateDataKey`

**Type**: `string`  
**Default**: `'SITE_STATE_DATA'`  
**Description**: Session key for state data storage

---

## Constructor

### `__construct($instance)`

Initializes session management with automatic data retrieval.

**Parameters:**

-   `$instance` (Instance): Framework service container

**Initialization Process:**

1. Stores framework instance for service access
2. Retrieves existing flash data from session
3. Loads persistent state data from session
4. Prepares internal state for session operations

**Example:**

```php
// Session is automatically instantiated by the framework
// Access via controllers: $this->session->method()
```

---

## Flash Data Methods

### `setFlashData($key, $value)`

Sets flash data that will be available in the next request only.

**Parameters:**

-   `$key` (string): Unique identifier for the flash data
-   `$value` (mixed): Data to store (string, array, object, etc.)

**Returns:** `void`

**Example:**

```php
// Success messages
$this->session->setFlashData('success', 'Profile updated successfully!');

// Error messages
$this->session->setFlashData('error', 'Invalid credentials provided');

// Validation errors
$this->session->setFlashData('validation_errors', [
    'email' => 'Invalid email format',
    'password' => 'Password must be at least 8 characters'
]);

// Complex data structures
$this->session->setFlashData('form_data', [
    'step' => 2,
    'completed_fields' => ['name', 'email'],
    'next_action' => 'verify_phone'
]);

// Controller usage with POST-Redirect-GET pattern
public function updateProfile()
{
    if ($this->request->method() === 'POST') {
        $userData = $this->request->post();

        if ($this->models->user->update($this->auth->user['id'], $userData)) {
            $this->session->setFlashData('success', 'Profile updated successfully!');
        } else {
            $this->session->setFlashData('error', 'Failed to update profile');
        }

        // Redirect to prevent form resubmission
        $this->response->redirect('/profile');
    }
}
```

### `getFlashData($key = null)`

Retrieves flash data from the current request cycle.

**Parameters:**

-   `$key` (string|null): Flash data key to retrieve, null for all data

**Returns:** `mixed` - Single value, all flash data array, or false if not found

**Example:**

```php
// Single value retrieval
$message = $this->session->getFlashData('success');
if ($message) {
    echo "<div class='alert alert-success'>{$message}</div>";
}

// Error handling
$errors = $this->session->getFlashData('validation_errors');
if ($errors && is_array($errors)) {
    foreach ($errors as $field => $error) {
        echo "<span class='error'>{$error}</span>";
    }
}

// All flash data
$allFlash = $this->session->getFlashData();
foreach ($allFlash as $key => $value) {
    echo "Flash {$key}: {$value}<br>";
}

// Template integration
public function showProfile()
{
    $this->view->render('user/profile', [
        'user' => $this->auth->user,
        'success_message' => $this->session->getFlashData('success'),
        'error_message' => $this->session->getFlashData('error'),
        'validation_errors' => $this->session->getFlashData('validation_errors', [])
    ]);
}
```

### `updateFlashData()`

Commits new flash data to session for next request availability.

**Returns:** `void`

**Called:** Automatically during framework shutdown (manual calling rarely needed)

**Example:**

```php
// Usually automatic, but can be called manually if needed
$this->session->setFlashData('message', 'Data saved');
$this->session->updateFlashData(); // Manual commit
```

---

## State Data Methods

### `setStateData($key, $value)`

Sets persistent state data that survives multiple requests.

**Parameters:**

-   `$key` (string): Unique identifier for the state data
-   `$value` (mixed): Data to store persistently (any serializable type)

**Returns:** `void`

**Example:**

```php
// User preferences
$this->session->setStateData('language', 'en');
$this->session->setStateData('timezone', 'America/New_York');
$this->session->setStateData('theme', 'dark');

// Shopping cart data
$this->session->setStateData('cart_items', [
    ['id' => 123, 'quantity' => 2, 'price' => 29.99],
    ['id' => 456, 'quantity' => 1, 'price' => 49.99]
]);

// Multi-step form progress
$this->session->setStateData('registration_step', 3);
$this->session->setStateData('form_data', [
    'personal_info' => $personalData,
    'contact_info' => $contactData,
    'preferences' => $preferences
]);

// Application state
$this->session->setStateData('last_visited_page', '/dashboard');
$this->session->setStateData('search_filters', [
    'category' => 'electronics',
    'price_range' => [100, 500]
]);

// E-commerce controller example
public function addToCart()
{
    $productId = $this->request->post('product_id');
    $quantity = $this->request->post('quantity', 1);

    $cart = $this->session->getStateData('cart_items') ?: [];

    // Add or update cart item
    $found = false;
    foreach ($cart as &$item) {
        if ($item['id'] == $productId) {
            $item['quantity'] += $quantity;
            $found = true;
            break;
        }
    }

    if (!$found) {
        $product = $this->models->product->get($productId);
        $cart[] = [
            'id' => $productId,
            'name' => $product['name'],
            'price' => $product['price'],
            'quantity' => $quantity
        ];
    }

    $this->session->setStateData('cart_items', $cart);
    $this->session->setFlashData('success', 'Item added to cart');

    $this->response->redirect('/cart');
}
```

### `getStateData($key = null)`

Retrieves persistent state data from session storage.

**Parameters:**

-   `$key` (string|null): State data key to retrieve, null for all data

**Returns:** `mixed` - Single value, all state data array, or false if not found

**Example:**

```php
// Single value access with defaults
$userTheme = $this->session->getStateData('theme') ?: 'light';
$language = $this->session->getStateData('language') ?: 'en';

// Complex data structures
$cartItems = $this->session->getStateData('cart_items');
if ($cartItems && is_array($cartItems)) {
    foreach ($cartItems as $item) {
        echo "Product: {$item['name']} - Quantity: {$item['quantity']}<br>";
    }
}

// All state data
$allState = $this->session->getStateData();

// Conditional processing for multi-step forms
public function showRegistrationStep()
{
    $step = $this->session->getStateData('registration_step') ?: 1;
    $formData = $this->session->getStateData('form_data') ?: [];

    switch ($step) {
        case 1:
            $this->view->render('registration/step1', ['data' => $formData]);
            break;
        case 2:
            $this->view->render('registration/step2', ['data' => $formData]);
            break;
        case 3:
            $this->view->render('registration/step3', ['data' => $formData]);
            break;
        default:
            $this->response->redirect('/register');
    }
}

// User preference loading
public function loadUserPreferences()
{
    return [
        'theme' => $this->session->getStateData('theme') ?: 'default',
        'language' => $this->session->getStateData('language') ?: 'en',
        'timezone' => $this->session->getStateData('timezone') ?: 'UTC',
        'notifications' => $this->session->getStateData('notifications') ?: true
    ];
}
```

### `removeStateData($key)`

Removes persistent state data by key from session storage.

**Parameters:**

-   `$key` (string): State data key to remove from session

**Returns:** `void`

**Example:**

```php
// Clear user preferences
$this->session->removeStateData('user_theme');
$this->session->removeStateData('language');

// Clear shopping cart after order completion
$this->session->removeStateData('cart_items');
$this->session->removeStateData('cart_totals');

// Clear multi-step form data after completion
$this->session->removeStateData('registration_step');
$this->session->removeStateData('form_data');

// Security cleanup
$this->session->removeStateData('password_reset_token');
$this->session->removeStateData('two_factor_temp');

// Logout cleanup
public function logout()
{
    // Clear user-specific state data
    $this->session->removeStateData('user_preferences');
    $this->session->removeStateData('cart_items');
    $this->session->removeStateData('recent_searches');

    // Destroy authentication
    $this->auth->logout();

    $this->session->setFlashData('success', 'You have been logged out');
    $this->response->redirect('/');
}
```

---

## Complete Usage Examples

### Form Validation with Flash Data

```php
class UserController extends Controller
{
    public function register()
    {
        if ($this->request->method() === 'POST') {
            $userData = $this->request->only(['name', 'email', 'password']);

            // Validate input
            $errors = [];
            if (empty($userData['name'])) {
                $errors['name'] = 'Name is required';
            }
            if (empty($userData['email']) || !filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Valid email is required';
            }
            if (empty($userData['password']) || strlen($userData['password']) < 8) {
                $errors['password'] = 'Password must be at least 8 characters';
            }

            if (!empty($errors)) {
                // Set flash data for errors and form data
                $this->session->setFlashData('validation_errors', $errors);
                $this->session->setFlashData('form_data', $userData);
                $this->response->redirect('/register');
                return;
            }

            // Create user
            try {
                $userId = $this->models->user->create($userData);
                $this->session->setFlashData('success', 'Account created successfully!');
                $this->response->redirect('/login');
            } catch (Exception $e) {
                $this->session->setFlashData('error', 'Registration failed. Please try again.');
                $this->session->setFlashData('form_data', $userData);
                $this->response->redirect('/register');
            }
        } else {
            // Show registration form
            $this->view->render('auth/register', [
                'validation_errors' => $this->session->getFlashData('validation_errors', []),
                'form_data' => $this->session->getFlashData('form_data', []),
                'error_message' => $this->session->getFlashData('error'),
                'success_message' => $this->session->getFlashData('success')
            ]);
        }
    }
}
```

**Registration Template** (`Views/auth/register.php`):

```php
<div class="registration-form">
    <h2>Create Account</h2>

    <?php if ($success_message): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($success_message); ?>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-error">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo $baseUrl; ?>register">
        <?php echo $security->getCSRFField(); ?>

        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name"
                   value="<?php echo htmlspecialchars($form_data['name'] ?? ''); ?>" required>
            <?php if (isset($validation_errors['name'])): ?>
                <span class="error"><?php echo htmlspecialchars($validation_errors['name']); ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email"
                   value="<?php echo htmlspecialchars($form_data['email'] ?? ''); ?>" required>
            <?php if (isset($validation_errors['email'])): ?>
                <span class="error"><?php echo htmlspecialchars($validation_errors['email']); ?></span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            <?php if (isset($validation_errors['password'])): ?>
                <span class="error"><?php echo htmlspecialchars($validation_errors['password']); ?></span>
            <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary">Create Account</button>
    </form>
</div>
```

### Shopping Cart with State Data

```php
class CartController extends Controller
{
    public function index()
    {
        $cartItems = $this->session->getStateData('cart_items') ?: [];
        $cartTotals = $this->calculateTotals($cartItems);

        $this->view->render('cart/index', [
            'cart_items' => $cartItems,
            'cart_totals' => $cartTotals,
            'success_message' => $this->session->getFlashData('success'),
            'error_message' => $this->session->getFlashData('error')
        ]);
    }

    public function add()
    {
        $productId = $this->request->post('product_id');
        $quantity = (int)$this->request->post('quantity', 1);

        if (!$productId || $quantity <= 0) {
            $this->session->setFlashData('error', 'Invalid product or quantity');
            $this->response->redirect('/cart');
            return;
        }

        $product = $this->models->product->get($productId);
        if (!$product) {
            $this->session->setFlashData('error', 'Product not found');
            $this->response->redirect('/cart');
            return;
        }

        $cart = $this->session->getStateData('cart_items') ?: [];

        // Check if item already in cart
        $found = false;
        foreach ($cart as &$item) {
            if ($item['product_id'] == $productId) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $cart[] = [
                'product_id' => $productId,
                'name' => $product['name'],
                'price' => $product['price'],
                'quantity' => $quantity,
                'image' => $product['image']
            ];
        }

        $this->session->setStateData('cart_items', $cart);
        $this->session->setFlashData('success', "{$product['name']} added to cart");

        $this->response->redirect('/cart');
    }

    public function update()
    {
        $updates = $this->request->post('quantities', []);
        $cart = $this->session->getStateData('cart_items') ?: [];

        foreach ($cart as $index => &$item) {
            if (isset($updates[$index])) {
                $newQuantity = (int)$updates[$index];
                if ($newQuantity <= 0) {
                    unset($cart[$index]);
                } else {
                    $item['quantity'] = $newQuantity;
                }
            }
        }

        $cart = array_values($cart); // Reindex array
        $this->session->setStateData('cart_items', $cart);
        $this->session->setFlashData('success', 'Cart updated successfully');

        $this->response->redirect('/cart');
    }

    public function clear()
    {
        $this->session->removeStateData('cart_items');
        $this->session->setFlashData('success', 'Cart cleared');
        $this->response->redirect('/cart');
    }

    private function calculateTotals($cartItems)
    {
        $subtotal = 0;
        $itemCount = 0;

        foreach ($cartItems as $item) {
            $subtotal += $item['price'] * $item['quantity'];
            $itemCount += $item['quantity'];
        }

        $tax = $subtotal * 0.08; // 8% tax
        $total = $subtotal + $tax;

        return [
            'subtotal' => $subtotal,
            'tax' => $tax,
            'total' => $total,
            'item_count' => $itemCount
        ];
    }
}
```

### Multi-Step Form with Progress Tracking

```php
class RegistrationWizardController extends Controller
{
    public function step1()
    {
        if ($this->request->method() === 'POST') {
            $data = $this->request->only(['first_name', 'last_name', 'email']);

            // Validate step 1 data
            if ($this->validateStep1($data)) {
                // Save progress and move to step 2
                $formData = $this->session->getStateData('registration_form') ?: [];
                $formData['step1'] = $data;

                $this->session->setStateData('registration_form', $formData);
                $this->session->setStateData('registration_step', 2);

                $this->response->redirect('/register/step2');
            } else {
                $this->session->setFlashData('error', 'Please correct the errors below');
            }
        }

        $formData = $this->session->getStateData('registration_form', []);
        $this->view->render('registration/step1', [
            'form_data' => $formData['step1'] ?? [],
            'current_step' => 1,
            'total_steps' => 3
        ]);
    }

    public function step2()
    {
        // Ensure step 1 is completed
        $currentStep = $this->session->getStateData('registration_step');
        if ($currentStep < 2) {
            $this->response->redirect('/register/step1');
            return;
        }

        if ($this->request->method() === 'POST') {
            $data = $this->request->only(['address', 'city', 'state', 'zip']);

            if ($this->validateStep2($data)) {
                $formData = $this->session->getStateData('registration_form');
                $formData['step2'] = $data;

                $this->session->setStateData('registration_form', $formData);
                $this->session->setStateData('registration_step', 3);

                $this->response->redirect('/register/step3');
            } else {
                $this->session->setFlashData('error', 'Please correct the errors below');
            }
        }

        $formData = $this->session->getStateData('registration_form', []);
        $this->view->render('registration/step2', [
            'form_data' => $formData['step2'] ?? [],
            'current_step' => 2,
            'total_steps' => 3
        ]);
    }

    public function step3()
    {
        // Ensure previous steps are completed
        $currentStep = $this->session->getStateData('registration_step');
        if ($currentStep < 3) {
            $this->response->redirect('/register/step' . ($currentStep ?: 1));
            return;
        }

        if ($this->request->method() === 'POST') {
            $data = $this->request->only(['newsletter', 'terms_accepted']);

            if ($this->validateStep3($data)) {
                // Complete registration
                $formData = $this->session->getStateData('registration_form');
                $formData['step3'] = $data;

                // Combine all form data
                $userData = array_merge(
                    $formData['step1'],
                    $formData['step2'],
                    $formData['step3']
                );

                // Create user account
                if ($this->models->user->create($userData)) {
                    // Clear registration data
                    $this->session->removeStateData('registration_form');
                    $this->session->removeStateData('registration_step');

                    $this->session->setFlashData('success', 'Registration completed successfully!');
                    $this->response->redirect('/login');
                } else {
                    $this->session->setFlashData('error', 'Registration failed. Please try again.');
                }
            }
        }

        $formData = $this->session->getStateData('registration_form', []);
        $this->view->render('registration/step3', [
            'form_data' => $formData['step3'] ?? [],
            'current_step' => 3,
            'total_steps' => 3,
            'summary' => [
                'personal' => $formData['step1'] ?? [],
                'address' => $formData['step2'] ?? []
            ]
        ]);
    }

    public function restart()
    {
        // Clear all registration progress
        $this->session->removeStateData('registration_form');
        $this->session->removeStateData('registration_step');

        $this->session->setFlashData('info', 'Registration restarted');
        $this->response->redirect('/register/step1');
    }
}
```

### User Preferences Management

```php
class PreferencesController extends Controller
{
    public function index()
    {
        $this->auth->required();

        $preferences = [
            'theme' => $this->session->getStateData('theme') ?: 'light',
            'language' => $this->session->getStateData('language') ?: 'en',
            'timezone' => $this->session->getStateData('timezone') ?: 'UTC',
            'notifications' => $this->session->getStateData('notifications') ?: true,
            'items_per_page' => $this->session->getStateData('items_per_page') ?: 20
        ];

        $this->view->render('preferences/index', [
            'preferences' => $preferences,
            'success_message' => $this->session->getFlashData('success'),
            'error_message' => $this->session->getFlashData('error')
        ]);
    }

    public function update()
    {
        $this->auth->required();

        if ($this->request->method() === 'POST') {
            $preferences = $this->request->only([
                'theme', 'language', 'timezone', 'notifications', 'items_per_page'
            ]);

            // Validate preferences
            $validThemes = ['light', 'dark', 'auto'];
            $validLanguages = ['en', 'es', 'fr', 'de'];

            if (!in_array($preferences['theme'], $validThemes)) {
                $this->session->setFlashData('error', 'Invalid theme selection');
                $this->response->redirect('/preferences');
                return;
            }

            if (!in_array($preferences['language'], $validLanguages)) {
                $this->session->setFlashData('error', 'Invalid language selection');
                $this->response->redirect('/preferences');
                return;
            }

            // Save preferences to session
            foreach ($preferences as $key => $value) {
                if ($key === 'notifications') {
                    $value = (bool)$value;
                } elseif ($key === 'items_per_page') {
                    $value = max(10, min(100, (int)$value));
                }

                $this->session->setStateData($key, $value);
            }

            // Optionally save to database for logged-in users
            if ($this->auth->check()) {
                $this->models->user->save($this->auth->user['id'], [
                    'preferences' => json_encode($preferences)
                ]);
            }

            $this->session->setFlashData('success', 'Preferences updated successfully');
            $this->response->redirect('/preferences');
        }
    }

    public function reset()
    {
        $this->auth->required();

        // Clear all preference state data
        $this->session->removeStateData('theme');
        $this->session->removeStateData('language');
        $this->session->removeStateData('timezone');
        $this->session->removeStateData('notifications');
        $this->session->removeStateData('items_per_page');

        $this->session->setFlashData('success', 'Preferences reset to defaults');
        $this->response->redirect('/preferences');
    }
}
```

---

## Session Lifecycle Patterns

### 1. Flash Data Lifecycle

```
Request 1: setFlashData('message', 'Hello') → Store in session
Request 2: getFlashData('message') → Returns 'Hello' → Auto-clear
Request 3: getFlashData('message') → Returns false (cleared)
```

### 2. State Data Lifecycle

```
Request 1: setStateData('theme', 'dark') → Store in session
Request 2: getStateData('theme') → Returns 'dark'
Request 3: getStateData('theme') → Returns 'dark' (persists)
Request N: removeStateData('theme') → Permanently deleted
```

### 3. POST-Redirect-GET Pattern

```php
// POST request processing
if ($this->request->method() === 'POST') {
    // Process form data
    if ($success) {
        $this->session->setFlashData('success', 'Operation completed');
    } else {
        $this->session->setFlashData('error', 'Operation failed');
        $this->session->setFlashData('form_data', $this->request->post());
    }

    // Redirect to prevent form resubmission
    $this->response->redirect('/same-page');
}

// GET request display
$this->view->render('form', [
    'success_message' => $this->session->getFlashData('success'),
    'error_message' => $this->session->getFlashData('error'),
    'form_data' => $this->session->getFlashData('form_data', [])
]);
```

---

## Security Considerations

### 1. Data Validation

```php
// Always validate session data before use
$theme = $this->session->getStateData('theme');
if (!in_array($theme, ['light', 'dark', 'auto'])) {
    $theme = 'light'; // Safe default
}
```

### 2. Sensitive Data Handling

```php
// Never store sensitive data in sessions
// ❌ Don't do this:
$this->session->setStateData('password', $userPassword);
$this->session->setStateData('credit_card', $cardNumber);

// ✅ Do this instead:
$this->session->setStateData('user_id', $userId);
$this->session->setStateData('is_authenticated', true);
```

### 3. Session Cleanup

```php
public function logout()
{
    // Clear sensitive session data
    $this->session->removeStateData('cart_items');
    $this->session->removeStateData('user_preferences');
    $this->session->removeStateData('search_history');

    // Standard logout process
    $this->auth->logout();
}
```

---

## Best Practices

### 1. Use Flash Data for Temporary Messages

```php
// ✅ Good: Flash data for status messages
$this->session->setFlashData('success', 'Profile updated');

// ❌ Bad: State data for temporary messages
$this->session->setStateData('success', 'Profile updated');
```

### 2. Use State Data for Persistent Information

```php
// ✅ Good: State data for user preferences
$this->session->setStateData('theme', 'dark');

// ❌ Bad: Flash data for persistent information
$this->session->setFlashData('theme', 'dark');
```

### 3. Provide Default Values

```php
// ✅ Good: Always provide sensible defaults
$itemsPerPage = $this->session->getStateData('items_per_page') ?: 20;
$theme = $this->session->getStateData('theme') ?: 'light';

// ❌ Bad: No fallback handling
$itemsPerPage = $this->session->getStateData('items_per_page');
```

### 4. Clean Up When Appropriate

```php
// ✅ Good: Clean up completed processes
public function completeOrder()
{
    // Process order...

    // Clear cart after successful order
    $this->session->removeStateData('cart_items');
    $this->session->removeStateData('shipping_info');
}
```

---

## Framework Integration

The Session API integrates seamlessly with other framework components:

-   **Authentication**: Session-based login state management
-   **Validation**: Flash data integration for error display
-   **Request**: POST-Redirect-GET pattern support
-   **Response**: Redirect handling with flash data
-   **View**: Template integration for message display
-   **Security**: CSRF token storage and validation

The Session API provides robust session management with specialized features for modern web application development patterns and secure data handling.
