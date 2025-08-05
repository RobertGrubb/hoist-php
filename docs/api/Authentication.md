# Authentication API Documentation

## Overview

The Authentication API provides comprehensive user authentication, session management, and role-based authorization for the Hoist PHP Framework.

## Class: Auth

**Location**: `Core/Libraries/Auth.php`  
**Dependencies**: Instance, Request, Session, UserModel  
**Access**: Available as `$this->auth` in controllers

---

## Properties

### Public Properties

#### `$userGroup`

-   **Type**: `string`
-   **Default**: `'guest'`
-   **Description**: Current user's group/role identifier for access control
-   **Values**: `'guest'`, `'user'`, `'admin'`, `'moderator'`, or custom groups

#### `$user`

-   **Type**: `array|false`
-   **Default**: `false`
-   **Description**: Complete authenticated user data (excluding password) or false if not logged in

---

## Methods

### Constructor

#### `__construct($instance, $request, $baseUrl)`

Initializes authentication system with deferred session restoration.

**Parameters:**

-   `$instance` (Instance): Framework service container
-   `$request` (Request): HTTP request handler
-   `$baseUrl` (string): Base URL for redirects

**Usage:**

```php
// Automatically handled by framework - no direct instantiation needed
```

---

### Session Management

#### `initializeSession()`

Initializes user session after models are available.

**Returns:** `void`

**Description:** Called automatically by framework after model registration. Handles session restoration and user identity loading.

**Example:**

```php
// Called automatically - handles session restoration
$this->auth->initializeSession();
```

#### `logout()`

Logs out current user and destroys session.

**Returns:** `bool` - Always returns true

**Description:** Performs complete logout with session destruction.

**Example:**

```php
public function logout() {
    if ($this->auth->logout()) {
        $this->session->setFlashData('success', 'Logged out successfully');
        $this->instance->redirect('/');
    }
}
```

---

### Authentication Operations

#### `login($email, $password)`

Authenticates user with email and password credentials.

**Parameters:**

-   `$email` (string): User's email address
-   `$password` (string): Plain text password

**Returns:** `bool` - True on successful authentication, false on failure

**Security Features:**

-   Uses modern `password_verify()` for secure validation
-   Blocks deleted/suspended accounts
-   Establishes secure session

**Example:**

```php
public function loginUser() {
    $email = $this->request->post('email');
    $password = $this->request->post('password');

    if ($this->auth->login($email, $password)) {
        $this->session->setFlashData('success', 'Welcome back!');
        $this->instance->redirect('/dashboard');
    } else {
        $this->session->setFlashData('error', 'Invalid credentials');
        $this->instance->redirect('/login');
    }
}
```

#### `loginWithCreatedAccount($userData)`

Authenticates user with newly created account data.

**Parameters:**

-   `$userData` (array): Complete user data from registration

**Returns:** `bool` - True on success, false if account is deleted

**Usage:** Typically used after user registration to automatically log in the new user.

**Example:**

```php
// After successful registration
$newUser = $this->models->user->createUser($registrationData);
if ($this->auth->loginWithCreatedAccount($newUser)) {
    $this->session->setFlashData('success', 'Account created and logged in!');
    $this->instance->redirect('/welcome');
}
```

---

### Access Control

#### `required()`

Enforces authentication requirement with automatic redirect.

**Returns:** `void` - Terminates execution if not authenticated

**Description:** Validates user is logged in, redirects to base URL with error message if not.

**Example:**

```php
public function userProfile() {
    $this->auth->required(); // Must be logged in
    // Protected functionality here
    $this->view->render('user/profile');
}
```

#### `requireGroup($group)`

Enforces group membership requirement with automatic redirect.

**Parameters:**

-   `$group` (string|array): Required group name or array of group names

**Returns:** `void` - Redirects to access denied page if authorization fails

**Example:**

```php
public function adminPanel() {
    $this->auth->required(); // Must be logged in
    $this->auth->requireGroup('admin'); // Must be admin
    // Admin functionality here
}

public function moderateContent() {
    $this->auth->required();
    $this->auth->requireGroup(['admin', 'moderator']); // Either role allowed
    // Content moderation functionality
}
```

---

### Role-Based Authorization

#### `is($group = null)`

Checks if current user belongs to specified group(s).

**Parameters:**

-   `$group` (string|array|null): Group name, array of groups, or null

**Returns:** `bool` - True if user belongs to group(s), false otherwise

**Special Values:**

-   `'All'`: Returns true for any authenticated user
-   `null` or empty: Returns false

**Examples:**

```php
// Single group check
if ($this->auth->is('admin')) {
    // Administrative functionality
}

// Multi-group check
if ($this->auth->is(['admin', 'moderator'])) {
    // Management functionality
}

// Universal access check
if ($this->auth->is('All')) {
    // Any authenticated user
}

// Conditional features in templates
<?php if ($this->auth->is('premium')): ?>
    <div class="premium-content">Premium features here</div>
<?php endif; ?>
```

---

### User Identity Management

#### `loadIdentity($id)`

Loads and refreshes user identity from database by ID.

**Parameters:**

-   `$id` (int): User ID to load

**Returns:** `bool` - True on successful load, false if UserModel not available

**Description:** Retrieves fresh user data and updates last_online timestamp.

**Example:**

```php
// Typically called internally during session restoration
if ($this->auth->loadIdentity($userId)) {
    // User data loaded successfully
}
```

---

### Password Security

#### `validatePassword($password, $passwordHash)`

Validates plain text password against stored hash.

**Parameters:**

-   `$password` (string): Plain text password to validate
-   `$passwordHash` (string): Stored password hash

**Returns:** `bool` - True if password matches, false otherwise

**Security Features:**

-   Uses modern `password_verify()` for new hashes
-   Backward compatible with legacy SHA1 hashes
-   Constant-time comparison prevents timing attacks

**Example:**

```php
// Verify current password before allowing change
public function changePassword() {
    $currentPassword = $this->request->post('current_password');
    $newPassword = $this->request->post('new_password');

    if ($this->auth->validatePassword($currentPassword, $this->auth->user['password'])) {
        $newHash = $this->auth->generatePasswordHash($newPassword);
        $this->models->user->save(['id' => $this->auth->user['id']], ['password' => $newHash]);
        $this->session->setFlashData('success', 'Password updated successfully');
    } else {
        $this->session->setFlashData('error', 'Current password incorrect');
    }
}
```

#### `generatePasswordHash($password)`

Generates secure password hash for storage.

**Parameters:**

-   `$password` (string): Plain text password to hash

**Returns:** `string` - Secure hash for storage

**Security Features:**

-   Uses PHP's `PASSWORD_DEFAULT` (Argon2ID or bcrypt)
-   Automatic salt generation
-   Resistant to rainbow table attacks

**Example:**

```php
// During user registration
public function register() {
    $password = $this->request->post('password');
    $passwordHash = $this->auth->generatePasswordHash($password);

    $userData = [
        'email' => $this->request->post('email'),
        'password' => $passwordHash,
        'user_group_id' => 'user'
    ];

    $newUser = $this->models->user->createUser($userData);
}
```

---

## Usage Patterns

### Basic Authentication Flow

```php
class LoginController extends Controller
{
    public function index()
    {
        // Show login form
        $this->view->render('auth/login');
    }

    public function authenticate()
    {
        $email = $this->request->post('email');
        $password = $this->request->post('password');

        if ($this->auth->login($email, $password)) {
            $this->instance->redirect('/dashboard');
        } else {
            $this->session->setFlashData('error', 'Invalid credentials');
            $this->instance->redirect('/login');
        }
    }

    public function logout()
    {
        $this->auth->logout();
        $this->session->setFlashData('success', 'Logged out successfully');
        $this->instance->redirect('/');
    }
}
```

### Protected Controller Methods

```php
class AdminController extends Controller
{
    public function before()
    {
        // Apply to all methods in this controller
        $this->auth->required();
        $this->auth->requireGroup('admin');
    }

    public function dashboard()
    {
        // Admin dashboard - automatically protected by before()
        $this->view->render('admin/dashboard');
    }

    public function users()
    {
        // User management - automatically protected
        $users = $this->models->user->getAllUsers();
        $this->view->render('admin/users', ['users' => $users]);
    }
}
```

### Role-Based Content

```php
class DashboardController extends Controller
{
    public function index()
    {
        $this->auth->required(); // Must be logged in

        $data = [
            'user' => $this->auth->user,
            'isAdmin' => $this->auth->is('admin'),
            'canModerate' => $this->auth->is(['admin', 'moderator']),
            'isPremium' => $this->auth->is('premium')
        ];

        $this->view->render('dashboard/index', $data);
    }
}
```

### Template Usage

```php
<!-- In view templates -->
<?php if ($this->auth->is('admin')): ?>
    <a href="/admin" class="btn btn-primary">Admin Panel</a>
<?php endif; ?>

<?php if ($this->auth->is(['admin', 'moderator'])): ?>
    <button class="btn btn-warning">Moderate Content</button>
<?php endif; ?>

<?php if ($this->auth->user): ?>
    <p>Welcome, <?= htmlspecialchars($this->auth->user['email']) ?>!</p>
    <a href="/logout">Logout</a>
<?php else: ?>
    <a href="/login">Login</a>
<?php endif; ?>
```

---

## Security Considerations

1. **Password Storage**: Framework uses modern password hashing (Argon2ID/bcrypt)
2. **Session Security**: Complete session destruction on logout prevents fixation
3. **Access Control**: Always use `required()` and `requireGroup()` for protection
4. **Input Validation**: Validate all user input before authentication
5. **HTTPS**: Use HTTPS in production for secure credential transmission

---

## Error Handling

The Authentication API handles errors gracefully:

-   **Invalid credentials**: Returns false, no exceptions thrown
-   **Missing UserModel**: Returns false, continues operation
-   **Deleted accounts**: Blocked from authentication
-   **Session errors**: Automatic cleanup and redirect
-   **Access denied**: Automatic redirect with flash message

---

## Framework Integration

The Auth class integrates seamlessly with other framework components:

-   **Session**: Uses framework session for flash messages
-   **UserModel**: Integrates with FileDatabase user storage
-   **Router**: Parameters available for user identification
-   **View**: User data available in all templates
-   **Request**: Processes login form data securely
