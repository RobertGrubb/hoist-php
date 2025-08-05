# Security API Documentation

## Overview

The Security API provides essential security features for the Hoist PHP framework, with a primary focus on Cross-Site Request Forgery (CSRF) protection. It automatically manages CSRF tokens throughout the request lifecycle and provides utilities for secure form handling with cryptographically secure token generation and transparent validation.

## Class: Security

**Location**: `Core/Libraries/Security.php`  
**Pattern**: Automatic Protection Service  
**Usage**: CSRF protection for forms and POST requests  
**Features**: Automatic token generation, validation, rotation, session integration

---

## Properties

### Security Configuration

#### `$instance`

**Type**: `Instance`  
**Description**: Application instance for accessing framework services (request, session, response)

#### `$csrfTokenSessionKey`

**Type**: `string`  
**Value**: `'APPLICATION_CSRF_TOKEN'`  
**Description**: Session key used to store the CSRF token

#### `$csrfPostKey`

**Type**: `string`  
**Value**: `'_csrf'`  
**Description**: POST parameter key expected to contain the CSRF token

---

## Constructor

### `__construct($instance)`

Initializes security service and begins CSRF protection.

**Parameters:**

-   `$instance` (Instance): Main application service container

**Automatic Setup:**

-   CSRF token generation
-   Automatic POST request validation
-   Session integration
-   Request filtering activation

**Example:**

```php
// Security is automatically initialized in the framework
// Access via: $this->instance->security or $security in views
```

---

## CSRF Protection Methods

### `csrfInput()`

Generates HTML for CSRF token inclusion in forms.

**Returns:** `string` - HTML hidden input field with CSRF token

**Generated HTML Format:**

```html
<input type="hidden" name="_csrf" value="[64-character-token]" />
```

**Example:**

```php
// In PHP templates/views
<form method="POST" action="/submit">
    <?= $this->instance->security->csrfInput(); ?>
    <input type="text" name="username" required>
    <input type="password" name="password" required>
    <button type="submit">Login</button>
</form>

// In view files (security is auto-available)
<form method="POST" action="/register">
    <?= $security->csrfInput(); ?>
    <input type="text" name="name" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Register</button>
</form>

// Complex form with multiple sections
<form method="POST" action="/profile/update">
    <?= $security->csrfInput(); ?>

    <fieldset>
        <legend>Personal Information</legend>
        <input type="text" name="first_name" value="<?= $user['first_name'] ?>">
        <input type="text" name="last_name" value="<?= $user['last_name'] ?>">
        <input type="email" name="email" value="<?= $user['email'] ?>">
    </fieldset>

    <fieldset>
        <legend>Settings</legend>
        <select name="timezone">
            <option value="UTC">UTC</option>
            <option value="America/New_York">Eastern</option>
            <option value="America/Los_Angeles">Pacific</option>
        </select>
        <input type="checkbox" name="notifications" value="1">
    </fieldset>

    <button type="submit">Update Profile</button>
</form>

// AJAX form submission
<form id="contactForm" method="POST" action="/contact">
    <?= $security->csrfInput(); ?>
    <input type="text" name="name" placeholder="Your Name" required>
    <input type="email" name="email" placeholder="Your Email" required>
    <textarea name="message" placeholder="Your Message" required></textarea>
    <button type="submit">Send Message</button>
</form>

<script>
document.getElementById('contactForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('/contact', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Message sent successfully!');
            this.reset();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Network error occurred');
    });
});
</script>
```

### `assignToken()`

Generates and assigns a new cryptographically secure CSRF token.

**Returns:** `void` - Token is stored directly in session

**Token Properties:**

-   256 bits of entropy (32 random bytes)
-   Hexadecimal encoding for safe transmission
-   Cryptographically secure random generation
-   64 characters long

**Automatic Calls:**

-   Initial security service setup
-   Successful CSRF token validation
-   Manual token refresh

**Example:**

```php
// Manual token refresh (if needed)
$this->instance->security->assignToken();

// Access current token value
$currentToken = $_SESSION['APPLICATION_CSRF_TOKEN'];

// Check if token exists
if (isset($_SESSION['APPLICATION_CSRF_TOKEN'])) {
    echo "CSRF protection is active";
}
```

---

## CSRF Protection Workflow

### Automatic Protection Process

The Security API provides transparent CSRF protection through the following workflow:

#### 1. Token Initialization

```php
// Automatic token generation on first request
if (!isset($_SESSION['APPLICATION_CSRF_TOKEN'])) {
    $this->assignToken(); // Creates 64-character secure token
}
```

#### 2. Form Integration

```php
// Include in all POST forms
<form method="POST" action="/submit">
    <?= $security->csrfInput(); ?>
    <!-- form fields -->
</form>

// Results in HTML:
<input type="hidden" name="_csrf" value="a1b2c3d4...64chars" />
```

#### 3. POST Request Validation

```php
// Automatic validation on POST requests
if ($_POST && isset($_POST['_csrf'])) {
    if (hash_equals($_SESSION['APPLICATION_CSRF_TOKEN'], $_POST['_csrf'])) {
        // Valid token - allow request to proceed
        $this->assignToken(); // Generate new token for next request
    } else {
        // Invalid token - redirect to error page
        header('Location: /error');
        exit;
    }
}
```

#### 4. Token Rotation

After successful validation, a new token is automatically generated to prevent replay attacks.

---

## Security Features

### Cryptographically Secure Token Generation

```php
// Token generation using secure random bytes
$token = bin2hex(random_bytes(32)); // 256 bits of entropy

// Properties:
// - 32 random bytes = 256 bits of entropy
// - Converted to 64-character hexadecimal string
// - Cryptographically secure using random_bytes()
// - Suitable for preventing brute force attacks
```

### Timing-Safe Token Comparison

```php
// Prevents timing attacks
if (hash_equals($storedToken, $submittedToken)) {
    // Tokens match - safe comparison
    // hash_equals prevents timing-based attacks
}
```

### Automatic Request Filtering

```php
// All POST requests are automatically validated
// No manual intervention required
// Invalid requests are automatically blocked
```

---

## Controller Integration

### Basic Form Handling

```php
class UserController extends Controller
{
    public function showLoginForm()
    {
        // CSRF token automatically available in view
        $this->view->render('auth/login');
    }

    public function processLogin()
    {
        // CSRF validation happens automatically
        // This method only executes if token is valid

        $username = $this->request->post('username');
        $password = $this->request->post('password');

        if ($this->instance->auth->login($username, $password)) {
            $this->response->redirect('/dashboard');
        } else {
            $this->response->redirect('/login?error=invalid');
        }
    }

    public function updateProfile()
    {
        // Automatic CSRF protection
        $profileData = $this->request->only([
            'first_name', 'last_name', 'email', 'timezone'
        ]);

        // Validate and update user profile
        $result = $this->libraries->validation->validateBatch([
            'first_name' => 'required|min:2|max:50',
            'last_name' => 'required|min:2|max:50',
            'email' => 'required|email|unique:users,email,' . $this->instance->auth->user['id'],
            'timezone' => 'required|timezone'
        ], $profileData);

        if ($result['valid']) {
            $success = $this->models->user->save($this->instance->auth->user['id'], [
                'first_name' => $result['data']['first_name'],
                'last_name' => $result['data']['last_name'],
                'email' => $result['data']['email'],
                'timezone' => $result['data']['timezone'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if ($success) {
                $this->instance->session->setFlashData('success', 'Profile updated successfully');
                $this->response->redirect('/profile');
            } else {
                $this->response->sendError('Failed to update profile', 500);
            }
        } else {
            $this->response->sendError('Validation failed', 422, ['errors' => $result['errors']]);
        }
    }
}
```

### API Endpoint Protection

```php
class ApiController extends Controller
{
    public function processPayment()
    {
        // CSRF protection applies to all POST requests
        // API endpoints are automatically protected

        $paymentData = $this->request->getJsonData();

        // Additional API security checks
        $apiKey = $this->request->header('X-API-Key');
        if (!$this->validateApiKey($apiKey)) {
            $this->response->sendError('Invalid API key', 401);
            return;
        }

        // Process payment with CSRF protection
        $result = $this->processPaymentData($paymentData);
        $this->response->sendJson($result);
    }

    public function uploadFile()
    {
        // File uploads with CSRF protection
        if (!isset($_FILES['file'])) {
            $this->response->sendError('No file uploaded', 400);
            return;
        }

        $file = $_FILES['file'];

        // Validate file with security checks
        $result = $this->libraries->validation->validateBatch([
            'file' => 'required|file_type:jpg,png,gif|file_size:5120|image'
        ], ['file' => $file]);

        if ($result['valid']) {
            $uploadPath = $this->libraries->uploader->upload($file, 'uploads');
            $this->response->sendJson(['path' => $uploadPath], 201);
        } else {
            $this->response->sendError('File validation failed', 422, [
                'errors' => $result['errors']
            ]);
        }
    }
}
```

---

## View Integration

### Template Usage

```php
<!-- login.php view -->
<div class="login-container">
    <h2>Login to Your Account</h2>

    <?php if ($this->request->get('error')): ?>
        <div class="alert alert-error">
            Invalid username or password
        </div>
    <?php endif; ?>

    <form method="POST" action="/auth/login" class="login-form">
        <?= $security->csrfInput(); ?>

        <div class="form-group">
            <label for="username">Username or Email</label>
            <input type="text" id="username" name="username" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div class="form-group">
            <input type="checkbox" id="remember" name="remember" value="1">
            <label for="remember">Remember me</label>
        </div>

        <button type="submit" class="btn btn-primary">Login</button>
    </form>

    <p><a href="/auth/forgot-password">Forgot your password?</a></p>
</div>

<!-- contact.php view -->
<div class="contact-section">
    <h2>Contact Us</h2>

    <form method="POST" action="/contact/send" class="contact-form">
        <?= $security->csrfInput(); ?>

        <div class="form-row">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>

            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="subject">Subject</label>
            <select id="subject" name="subject" required>
                <option value="">Select a subject</option>
                <option value="general">General Inquiry</option>
                <option value="support">Technical Support</option>
                <option value="billing">Billing Question</option>
                <option value="partnership">Partnership Opportunity</option>
            </select>
        </div>

        <div class="form-group">
            <label for="message">Message</label>
            <textarea id="message" name="message" rows="6" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Send Message</button>
    </form>
</div>
```

### JavaScript Integration

```javascript
// CSRF-aware AJAX requests
function makeSecureRequest(url, data, method = "POST") {
    // Get CSRF token from hidden input
    const csrfToken = document.querySelector('input[name="_csrf"]').value;

    // Add CSRF token to data
    if (method === "POST") {
        data._csrf = csrfToken;
    }

    return fetch(url, {
        method: method,
        headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest",
        },
        body: JSON.stringify(data),
    });
}

// Form submission with CSRF protection
document.getElementById("dynamicForm").addEventListener("submit", function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch(this.action, {
        method: "POST",
        body: formData, // FormData automatically includes CSRF token
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                showSuccessMessage(data.message);
            } else {
                showErrorMessage(data.message);
            }
        })
        .catch((error) => {
            console.error("Request failed:", error);
            showErrorMessage("Request failed. Please try again.");
        });
});

// Dynamic form creation with CSRF
function createSecureForm(action, fields) {
    const form = document.createElement("form");
    form.method = "POST";
    form.action = action;

    // Add CSRF token
    const csrfInput = document.createElement("input");
    csrfInput.type = "hidden";
    csrfInput.name = "_csrf";
    csrfInput.value = document.querySelector('input[name="_csrf"]').value;
    form.appendChild(csrfInput);

    // Add other fields
    fields.forEach((field) => {
        const input = document.createElement("input");
        input.type = field.type;
        input.name = field.name;
        input.value = field.value;
        form.appendChild(input);
    });

    return form;
}
```

---

## Error Handling

### CSRF Validation Failures

When CSRF validation fails, the framework automatically:

1. **Redirects to Error Page**: Invalid requests are redirected to `/error`
2. **Blocks Request Processing**: No controller methods are executed
3. **Maintains Security**: User sessions remain intact
4. **Logs Security Events**: Failed validations can be tracked

### Error Page Integration

```php
// ErrorController.php
class ErrorController extends Controller
{
    public function index()
    {
        $error = $this->request->get('type', 'general');

        switch($error) {
            case 'csrf':
                $message = 'Security token mismatch. Please try again.';
                break;
            case '404':
                $message = 'Page not found.';
                break;
            default:
                $message = 'An error occurred. Please try again.';
        }

        $this->view->render('error/index', [
            'message' => $message,
            'error_type' => $error
        ]);
    }
}

// error/index.php view
<div class="error-container">
    <h1>Oops! Something went wrong</h1>
    <p><?= $message ?></p>

    <?php if ($error_type === 'csrf'): ?>
        <p>For your security, we couldn't process your request. This usually happens when:</p>
        <ul>
            <li>Your session has expired</li>
            <li>You opened the form in multiple browser tabs</li>
            <li>You navigated back to a form after submitting it</li>
        </ul>
        <p><a href="javascript:history.back()">Go back and try again</a></p>
    <?php endif; ?>

    <p><a href="/">Return to homepage</a></p>
</div>
```

---

## Advanced Security Patterns

### Multi-Step Form Protection

```php
// Multi-step form with CSRF protection at each step
class WizardController extends Controller
{
    public function step1()
    {
        $this->view->render('wizard/step1');
    }

    public function processStep1()
    {
        // CSRF protection automatic
        $step1Data = $this->request->only(['personal_info']);

        // Store in session for next step
        $this->instance->session->set('wizard_step1', $step1Data);
        $this->response->redirect('/wizard/step2');
    }

    public function step2()
    {
        if (!$this->instance->session->get('wizard_step1')) {
            $this->response->redirect('/wizard/step1');
        }

        $this->view->render('wizard/step2');
    }

    public function processStep2()
    {
        // CSRF protection on each step
        $step2Data = $this->request->only(['payment_info']);

        $this->instance->session->set('wizard_step2', $step2Data);
        $this->response->redirect('/wizard/review');
    }
}
```

### API Token and CSRF Combination

```php
class SecureApiController extends Controller
{
    public function sensitiveOperation()
    {
        // Dual protection: API token + CSRF
        $apiToken = $this->request->header('Authorization');

        if (!$this->validateApiToken($apiToken)) {
            $this->response->sendError('Invalid API token', 401);
            return;
        }

        // CSRF protection still applies for web-based API calls
        // Automatic validation ensures request authenticity

        $data = $this->request->getJsonData();
        $result = $this->processSensitiveData($data);

        $this->response->sendJson($result);
    }
}
```

---

## Best Practices

### 1. Consistent Form Protection

```php
// Always include CSRF tokens in forms
<form method="POST" action="/any-action">
    <?= $security->csrfInput(); ?>
    <!-- form fields -->
</form>
```

### 2. AJAX Request Integration

```javascript
// Include CSRF token in AJAX requests
const formData = new FormData();
formData.append("_csrf", document.querySelector('input[name="_csrf"]').value);
formData.append("data", JSON.stringify(requestData));
```

### 3. Error Handling

```php
// Provide user-friendly error messages
// Don't expose security implementation details
if ($csrfError) {
    $message = 'Security verification failed. Please refresh and try again.';
}
```

### 4. Token Management

```php
// Don't manually manipulate CSRF tokens
// Let the framework handle token lifecycle
// Trust the automatic rotation system
```

---

## Framework Integration

The Security API seamlessly integrates with all framework components:

-   **Request**: Automatic POST validation and token extraction
-   **Response**: Error handling and redirect management
-   **Session**: Secure token storage and session integration
-   **View**: Automatic security variable availability
-   **Router**: Request filtering and protection activation
-   **Authentication**: Compatible with user session management

The Security API provides transparent CSRF protection with minimal configuration, ensuring robust security for all forms and POST requests in the framework.
