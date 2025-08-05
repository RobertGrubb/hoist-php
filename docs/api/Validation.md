# Validation API Documentation

## Overview

The Validation API provides a comprehensive input validation system for the Hoist PHP framework. It supports both fluent interface validation and batch validation with extensive rule support, custom error messages, conditional validation, array validation, and advanced error handling. The system includes 50+ built-in validation rules, security features, and framework integration.

## Class: Validation

**Location**: `Core/Libraries/Validation.php`  
**Pattern**: Fluent Interface with Batch Processing  
**Usage**: Input validation for forms, APIs, and data processing  
**Features**: 50+ rules, custom messages, conditional validation, security protection

---

## Properties

### Validation State

#### `$value`

**Type**: `mixed`  
**Description**: Current value being validated in fluent interface

#### `$errors`

**Type**: `array`  
**Description**: Collection of validation errors for current validation chain

#### `$data`

**Type**: `array`  
**Description**: All validation data for batch validation

#### `$customMessages`

**Type**: `array`  
**Description**: Custom error messages for specific fields and rules

#### `$customRules`

**Type**: `array`  
**Description**: Registered custom validation rules

#### `$instance`

**Type**: `Instance`  
**Description**: Application instance for accessing framework services

---

## Constructor

### `__construct($instance)`

Initializes the comprehensive validation service.

**Parameters:**

-   `$instance` (Instance): Main application service container

**Example:**

```php
// Validation is automatically available in controllers
// Access via: $this->libraries->validation
```

---

## Fluent Interface Validation

### `set($value = null)`

Sets the value to be validated and starts the validation chain.

**Parameters:**

-   `$value` (mixed): The value to validate

**Returns:** `Validation` - Self for method chaining

**Example:**

```php
// Basic fluent validation
$result = $this->libraries->validation
    ->set($email)
    ->required()
    ->email()
    ->maxLength(100)
    ->validate('Email');

// Complex validation chain
$result = $this->libraries->validation
    ->set($password)
    ->required()
    ->minLength(8)
    ->regex('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)/')
    ->validate('Password');

// Numeric validation
$result = $this->libraries->validation
    ->set($age)
    ->required()
    ->numeric()
    ->validate('Age');

// URL validation
$result = $this->libraries->validation
    ->set($website)
    ->url()
    ->validate('Website');
```

### `validate($fieldName, $events = [])`

Executes fluent validation and returns results.

**Parameters:**

-   `$fieldName` (string): Display name for the field
-   `$events` (array): Event configuration for handling validation results

**Returns:** `array` - Validation result with 'valid' status and 'errors' array

**Event Options:**

-   `onFail.redirect`: Redirect URL on validation failure

**Example:**

```php
// Basic validation execution
$result = $this->libraries->validation
    ->set($email)
    ->required()
    ->email()
    ->validate('Email Address');

if (!$result['valid']) {
    foreach ($result['errors'] as $error) {
        echo $error . "\n";
    }
}

// With redirect on failure
$result = $this->libraries->validation
    ->set($formData['username'])
    ->required()
    ->minLength(3)
    ->maxLength(20)
    ->validate('Username', [
        'onFail' => ['redirect' => '/register']
    ]);

// Controller usage
public function validateUserInput()
{
    $email = $this->request->post('email');

    $result = $this->libraries->validation
        ->set($email)
        ->required()
        ->email()
        ->validate('Email');

    if ($result['valid']) {
        // Process valid email
        $this->response->sendJson(['message' => 'Valid email']);
    } else {
        // Handle validation errors
        $this->response->sendError('Validation failed', 422, [
            'errors' => $result['errors']
        ]);
    }
}
```

---

## Batch Validation

### `validateBatch($rules, $data, $customMessages = [])`

Validates multiple fields using rule strings.

**Parameters:**

-   `$rules` (array): Validation rules for each field
-   `$data` (array): Data to validate
-   `$customMessages` (array): Custom error messages

**Returns:** `array` - Validation results with valid status, errors, and data

**Rule String Format:** `'rule1|rule2:param1,param2|rule3'`

**Example:**

```php
// Basic batch validation
$result = $this->libraries->validation->validateBatch([
    'name' => 'required|min:2|max:50|alpha_spaces',
    'email' => 'required|email|unique:users,email',
    'password' => 'required|min:8|password_strength:medium',
    'age' => 'required|numeric|between:18,120',
    'website' => 'url',
    'phone' => 'phone'
], $formData);

// With custom error messages
$result = $this->libraries->validation->validateBatch([
    'name' => 'required|min:2|max:50',
    'email' => 'required|email|unique:users,email',
    'password' => 'required|min:8|password_strength:strong',
    'confirm_password' => 'required|same:password'
], $data, [
    'name.required' => 'Please enter your full name',
    'name.min' => 'Name must be at least 2 characters',
    'email.unique' => 'This email is already registered',
    'password.password_strength' => 'Password must contain uppercase, lowercase, numbers and symbols',
    'confirm_password.same' => 'Password confirmation must match'
]);

// Conditional validation
$result = $this->libraries->validation->validateBatch([
    'payment_method' => 'required|in:card,paypal,bank',
    'card_number' => 'required_if:payment_method,card|credit_card',
    'card_expiry' => 'required_if:payment_method,card|date:m/Y',
    'paypal_email' => 'required_if:payment_method,paypal|email',
    'bank_account' => 'required_if:payment_method,bank|numeric'
], $paymentData);

// File validation
$result = $this->libraries->validation->validateBatch([
    'avatar' => 'file_type:jpg,png,gif|file_size:2048|image',
    'document' => 'required|file_type:pdf,doc,docx|file_size:10240'
], $_FILES);

// Controller implementation
public function registerUser()
{
    $userData = $this->request->only(['name', 'email', 'password', 'confirm_password']);

    $result = $this->libraries->validation->validateBatch([
        'name' => 'required|min:2|max:100|alpha_spaces',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8|password_strength:medium',
        'confirm_password' => 'required|same:password'
    ], $userData, [
        'name.required' => 'Please enter your name',
        'email.unique' => 'This email is already taken',
        'password.password_strength' => 'Password needs uppercase, lowercase and numbers'
    ]);

    if ($result['valid']) {
        // Create user
        $userId = $this->models->user->create([
            'name' => $result['data']['name'],
            'email' => $result['data']['email'],
            'password' => password_hash($result['data']['password'], PASSWORD_DEFAULT)
        ]);

        $this->response->sendJson(['user_id' => $userId], 201);
    } else {
        $this->response->sendError('Validation failed', 422, [
            'errors' => $result['errors']
        ]);
    }
}
```

---

## Core Validation Rules

### Basic Rules

#### `required`

Validates that a field is not empty.

```php
'name' => 'required'
// Fluent: ->required()
```

#### `email`

Validates email format using PHP's filter_var.

```php
'email' => 'email'
// Fluent: ->email()
```

#### `numeric`

Validates that the field is numeric.

```php
'price' => 'numeric'
// Fluent: ->numeric()
```

#### `integer`

Validates that the field is an integer.

```php
'quantity' => 'integer'
```

### String Rules

#### `min:length`

Validates minimum string length.

```php
'password' => 'min:8'
// Fluent: ->minLength(8)
```

#### `max:length`

Validates maximum string length.

```php
'title' => 'max:100'
// Fluent: ->maxLength(100)
```

#### `between:min,max`

Validates string length between min and max.

```php
'username' => 'between:3,20'
```

#### `alpha`

Validates alphabetic characters only.

```php
'first_name' => 'alpha'
```

#### `alpha_num`

Validates alphanumeric characters only.

```php
'username' => 'alpha_num'
```

#### `alpha_spaces`

Validates alphabetic characters and spaces.

```php
'full_name' => 'alpha_spaces'
```

### URL and Format Rules

#### `url`

Validates URL format.

```php
'website' => 'url'
// Fluent: ->url()
```

#### `ip`

Validates IP address format.

```php
'server_ip' => 'ip'
```

#### `json`

Validates JSON format.

```php
'config' => 'json'
```

#### `regex:pattern`

Validates against regular expression.

```php
'phone' => 'regex:/^\d{3}-\d{3}-\d{4}$/'
// Fluent: ->regex('/^\d{3}-\d{3}-\d{4}$/')
```

### Date and Time Rules

#### `date:format`

Validates date format (default: Y-m-d).

```php
'birth_date' => 'date'
'event_date' => 'date:m/d/Y'
```

#### `timezone`

Validates timezone identifier.

```php
'user_timezone' => 'timezone'
```

### Comparison Rules

#### `in:value1,value2,value3`

Validates value exists in list.

```php
'status' => 'in:active,inactive,pending'
```

#### `not_in:value1,value2,value3`

Validates value does not exist in list.

```php
'username' => 'not_in:admin,root,system'
```

#### `same:field`

Validates field matches another field.

```php
'password_confirmation' => 'same:password'
```

#### `different:field`

Validates field is different from another field.

```php
'new_password' => 'different:current_password'
```

### Conditional Rules

#### `required_if:field,value`

Required if another field has specific value.

```php
'credit_card' => 'required_if:payment_method,card'
```

#### `confirmed`

Validates field has confirmation field.

```php
'password' => 'confirmed'  // Looks for password_confirmation
```

---

## Advanced Validation Rules

### Financial and Security

#### `credit_card`

Validates credit card number using Luhn algorithm.

```php
'card_number' => 'credit_card'
```

#### `phone`

Validates phone number format (7-15 digits).

```php
'phone_number' => 'phone'
```

#### `password_strength:level`

Validates password strength (weak, medium, strong).

```php
'password' => 'password_strength:medium'
// medium: 8+ chars, uppercase, lowercase, numbers
// strong: 12+ chars, uppercase, lowercase, numbers, symbols
```

### File Validation

#### `file_type:ext1,ext2,ext3`

Validates file extension.

```php
'avatar' => 'file_type:jpg,png,gif'
'document' => 'file_type:pdf,doc,docx'
```

#### `file_size:kb`

Validates file size in kilobytes.

```php
'image' => 'file_size:2048'  // Max 2MB
'video' => 'file_size:51200' // Max 50MB
```

#### `image`

Validates that file is an image.

```php
'profile_photo' => 'image'
```

### Data Type Rules

#### `array`

Validates that field is an array.

```php
'tags' => 'array'
```

#### `boolean`

Validates boolean value.

```php
'is_active' => 'boolean'
```

### Database Rules

#### `unique:table,column,except`

Validates unique value in database.

```php
'email' => 'unique:users,email'
'email' => 'unique:users,email,123'  // Except ID 123
```

#### `exists:table,column`

Validates value exists in database.

```php
'category_id' => 'exists:categories,id'
'country' => 'exists:countries,code'
```

---

## Fluent Interface Methods

### Basic Fluent Methods

#### `required()`

Field is required validation.

```php
$validation->set($value)->required()
```

#### `email()`

Email format validation.

```php
$validation->set($email)->email()
```

#### `numeric()`

Numeric value validation.

```php
$validation->set($price)->numeric()
```

#### `minLength($length)`

Minimum length validation.

```php
$validation->set($password)->minLength(8)
```

#### `maxLength($length)`

Maximum length validation.

```php
$validation->set($title)->maxLength(100)
```

#### `url()`

URL format validation.

```php
$validation->set($website)->url()
```

#### `regex($pattern)`

Regular expression validation.

```php
$validation->set($phone)->regex('/^\d{3}-\d{3}-\d{4}$/')
```

---

## Custom Rules and Extensibility

### `addRule($name, $callback, $message = null)`

Registers a custom validation rule.

**Parameters:**

-   `$name` (string): Rule name
-   `$callback` (callable): Validation function
-   `$message` (string): Default error message

**Example:**

```php
// Register custom rule
$this->libraries->validation->addRule('strong_password', function($value, $parameters, $field, $data) {
    return strlen($value) >= 12 &&
           preg_match('/[a-z]/', $value) &&
           preg_match('/[A-Z]/', $value) &&
           preg_match('/[0-9]/', $value) &&
           preg_match('/[^a-zA-Z0-9]/', $value);
}, 'The :field must be a strong password with uppercase, lowercase, numbers and symbols.');

// Use custom rule
$result = $this->libraries->validation->validateBatch([
    'password' => 'required|strong_password'
], $data);

// Advanced custom rule with parameters
$this->libraries->validation->addRule('divisible_by', function($value, $parameters, $field, $data) {
    $divisor = $parameters[0] ?? 1;
    return is_numeric($value) && ($value % $divisor) === 0;
}, 'The :field must be divisible by :param0.');

// Usage
$result = $this->libraries->validation->validateBatch([
    'quantity' => 'required|numeric|divisible_by:5'
], $data);

// Custom rule with database validation
$this->libraries->validation->addRule('valid_category', function($value, $parameters, $field, $data) {
    // Access framework instance through closure
    $instance = $this; // In real implementation, pass instance

    if ($instance->fileDatabase) {
        $category = $instance->fileDatabase->table('categories')
            ->where('id', '=', $value)
            ->where('status', '=', 'active')
            ->first();
        return $category !== null;
    }

    return false;
}, 'The selected :field is not a valid active category.');
```

---

## Complete Validation Examples

### User Registration Form

```php
public function validateRegistration()
{
    $userData = $this->request->only([
        'name', 'email', 'password', 'confirm_password',
        'phone', 'birth_date', 'terms_accepted'
    ]);

    $result = $this->libraries->validation->validateBatch([
        'name' => 'required|min:2|max:100|alpha_spaces',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8|password_strength:medium',
        'confirm_password' => 'required|same:password',
        'phone' => 'required|phone',
        'birth_date' => 'required|date:Y-m-d',
        'terms_accepted' => 'required|boolean'
    ], $userData, [
        'name.required' => 'Please enter your full name',
        'name.alpha_spaces' => 'Name can only contain letters and spaces',
        'email.unique' => 'This email address is already registered',
        'password.password_strength' => 'Password must contain uppercase, lowercase and numbers',
        'confirm_password.same' => 'Password confirmation does not match',
        'phone.phone' => 'Please enter a valid phone number',
        'terms_accepted.required' => 'You must accept the terms of service'
    ]);

    if ($result['valid']) {
        // Create user account
        $userId = $this->models->user->create([
            'name' => $result['data']['name'],
            'email' => $result['data']['email'],
            'password' => password_hash($result['data']['password'], PASSWORD_DEFAULT),
            'phone' => $result['data']['phone'],
            'birth_date' => $result['data']['birth_date'],
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->response->sendJson([
            'message' => 'Registration successful',
            'user_id' => $userId
        ], 201);
    } else {
        $this->response->sendError('Registration failed', 422, [
            'errors' => $result['errors']
        ]);
    }
}
```

### Product Creation with File Upload

```php
public function validateProductCreation()
{
    $productData = $this->request->only([
        'name', 'description', 'price', 'category_id', 'tags', 'is_featured'
    ]);

    // Include file data
    $fileData = $_FILES;
    $allData = array_merge($productData, $fileData);

    $result = $this->libraries->validation->validateBatch([
        'name' => 'required|min:3|max:200',
        'description' => 'required|min:10|max:2000',
        'price' => 'required|numeric|min:0.01',
        'category_id' => 'required|exists:categories,id',
        'tags' => 'array',
        'is_featured' => 'boolean',
        'main_image' => 'required|image|file_size:5120',
        'gallery' => 'array',
        'manual' => 'file_type:pdf|file_size:10240'
    ], $allData, [
        'name.min' => 'Product name must be at least 3 characters',
        'description.min' => 'Please provide a detailed description (minimum 10 characters)',
        'price.min' => 'Price must be greater than 0',
        'category_id.exists' => 'Please select a valid category',
        'main_image.required' => 'Please upload a main product image',
        'main_image.file_size' => 'Main image must be smaller than 5MB',
        'manual.file_type' => 'Manual must be a PDF file'
    ]);

    if ($result['valid']) {
        // Process file uploads
        $mainImagePath = $this->libraries->uploader->upload($fileData['main_image'], 'products');

        // Create product
        $productId = $this->models->product->create([
            'name' => $result['data']['name'],
            'description' => $result['data']['description'],
            'price' => $result['data']['price'],
            'category_id' => $result['data']['category_id'],
            'main_image' => $mainImagePath,
            'is_featured' => $result['data']['is_featured'] ?? false,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->response->sendJson([
            'message' => 'Product created successfully',
            'product_id' => $productId
        ], 201);
    } else {
        $this->response->sendError('Product validation failed', 422, [
            'errors' => $result['errors']
        ]);
    }
}
```

### API Request Validation

```php
public function validateApiRequest()
{
    $apiData = $this->request->getJsonData();

    $result = $this->libraries->validation->validateBatch([
        'api_key' => 'required|min:32|max:64',
        'endpoint' => 'required|url',
        'method' => 'required|in:GET,POST,PUT,DELETE',
        'headers' => 'array',
        'payload' => 'json',
        'timeout' => 'numeric|between:1,300',
        'retry_count' => 'integer|between:0,5'
    ], $apiData, [
        'api_key.required' => 'API key is required for authentication',
        'api_key.min' => 'API key appears to be invalid (too short)',
        'endpoint.url' => 'Please provide a valid API endpoint URL',
        'method.in' => 'HTTP method must be GET, POST, PUT, or DELETE',
        'payload.json' => 'Payload must be valid JSON',
        'timeout.between' => 'Timeout must be between 1 and 300 seconds',
        'retry_count.between' => 'Retry count must be between 0 and 5'
    ]);

    if ($result['valid']) {
        // Process API request
        $response = $this->libraries->http->request([
            'url' => $result['data']['endpoint'],
            'method' => $result['data']['method'],
            'headers' => $result['data']['headers'] ?? [],
            'body' => $result['data']['payload'] ?? '',
            'timeout' => $result['data']['timeout'] ?? 30
        ]);

        $this->response->sendJson($response);
    } else {
        $this->response->sendError('Invalid API request parameters', 400, [
            'errors' => $result['errors']
        ]);
    }
}
```

---

## Error Handling and Messages

### Default Error Messages

The validation system includes comprehensive default error messages that can be customized:

```php
// Access default messages
$messages = [
    'required' => 'The :field field is required.',
    'email' => 'The :field must be a valid email address.',
    'min' => 'The :field must be at least :min characters.',
    'max' => 'The :field must not exceed :max characters.',
    'unique' => 'The :field has already been taken.',
    'password_strength' => 'The :field does not meet strength requirements.'
];
```

### Message Placeholders

Error messages support various placeholders:

-   `:field` - Field name (formatted)
-   `:value` - Field value
-   `:min` - Minimum value parameter
-   `:max` - Maximum value parameter
-   `:size` - Size parameter
-   `:other` - Other field name
-   `:values` - List of allowed values
-   `:types` - List of allowed types

### Custom Error Handling

```php
// Global custom messages
$customMessages = [
    'email.required' => 'We need your email address to contact you',
    'password.min' => 'Password is too short - use at least 8 characters',
    'name.alpha_spaces' => 'Name can only contain letters and spaces'
];

$result = $this->libraries->validation->validateBatch($rules, $data, $customMessages);

// Handle validation errors
if (!$result['valid']) {
    foreach ($result['errors'] as $field => $fieldErrors) {
        foreach ($fieldErrors as $error) {
            error_log("Validation error for {$field}: {$error}");
        }
    }
}
```

---

## Best Practices

### 1. Security-First Validation

```php
// Always validate and sanitize user input
$result = $this->libraries->validation->validateBatch([
    'content' => 'required|max:5000',
    'user_id' => 'required|exists:users,id',
    'is_published' => 'boolean'
], $data);

// Additional security checks
if ($result['valid']) {
    // XSS protection
    $content = htmlspecialchars($result['data']['content'], ENT_QUOTES, 'UTF-8');

    // Authorization check
    if (!$this->instance->auth->canEditPost($result['data']['user_id'])) {
        $this->response->sendError('Unauthorized', 403);
        return;
    }
}
```

### 2. Comprehensive Form Validation

```php
// Cover all possible input scenarios
$result = $this->libraries->validation->validateBatch([
    'required_field' => 'required',
    'optional_email' => 'email',  // Allow empty but validate if provided
    'conditional_field' => 'required_if:type,premium',
    'numeric_range' => 'numeric|between:1,100'
], $data);
```

### 3. User-Friendly Error Messages

```php
// Provide clear, actionable error messages
$customMessages = [
    'email.email' => 'Please enter a valid email address (e.g., user@example.com)',
    'password.password_strength' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number',
    'phone.phone' => 'Please enter a valid phone number with area code'
];
```

### 4. Performance Optimization

```php
// Use specific validation rules to avoid unnecessary checks
$result = $this->libraries->validation->validateBatch([
    'id' => 'integer|exists:users,id',  // More specific than just 'numeric'
    'email' => 'email|unique:users,email',  // Email format before database check
    'status' => 'in:active,inactive'  // Limit to expected values
], $data);
```

---

## Framework Integration

The Validation API seamlessly integrates with all framework components:

-   **Request**: Automatic access to form and JSON data
-   **Response**: Structured error responses for APIs
-   **Database**: Built-in unique and exists validation
-   **FileDatabase**: Alternative storage validation support
-   **Session**: Flash message support for form errors
-   **Security**: XSS and injection protection
-   **Authentication**: User permission validation integration

The Validation API provides enterprise-grade input validation with comprehensive rule support, security features, and user-friendly error handling for robust application development.
