# Utilities API Documentation

## Overview

The Utilities API provides a comprehensive collection of static helper methods for common programming tasks in the Hoist PHP framework. These utilities cover string manipulation, data generation, HTTP requests, time formatting, and data processing with cryptographically secure random generation and robust HTTP client functionality.

## Class: Utilities

**Location**: `Core/Libraries/Utilities.php`  
**Pattern**: Static Helper Methods  
**Usage**: General utility functions for data processing  
**Features**: UUID generation, random data, HTTP requests, time formatting, string processing

---

## Unique Identifier Generation

### `uuidv4()`

Generates a RFC 4122 compliant UUID version 4.

**Returns:** `string` - A valid UUID v4 string (36 characters including hyphens)

**Format:** `xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx`

**Example:**

```php
// Generate unique identifiers
$userId = Utilities::uuidv4();
// Returns: "f47ac10b-58cc-4372-a567-0e02b2c3d479"

$sessionId = Utilities::uuidv4();
// Returns: "6ba7b810-9dad-11d1-80b4-00c04fd430c8"

// Database primary key generation
$id = Utilities::uuidv4();
$result = $this->models->user->create([
    'id' => $id,
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// API request tracking
$requestId = Utilities::uuidv4();
error_log("Processing API request: {$requestId}");

// File naming with uniqueness
$uploadId = Utilities::uuidv4();
$fileName = $uploadId . '_' . $originalFileName;

// Session token generation
class SessionManager
{
    public function createSession($userId)
    {
        $sessionToken = Utilities::uuidv4();
        $this->models->session->create([
            'id' => $sessionToken,
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days'))
        ]);

        return $sessionToken;
    }
}

// Unique reference numbers
public function generateOrderNumber()
{
    $prefix = 'ORD';
    $uuid = strtoupper(str_replace('-', '', Utilities::uuidv4()));
    return $prefix . substr($uuid, 0, 8);
}
```

---

## Random Data Generation

### `generateConfirmationCode()`

Generates a cryptographically secure random 8-digit confirmation code.

**Returns:** `int` - An 8-digit random number (11,111,112 to 99,999,999)

**Example:**

```php
// Email verification codes
$verificationCode = Utilities::generateConfirmationCode();
// Returns: 47382951

$this->models->user->save($userId, [
    'verification_code' => $verificationCode,
    'verification_expires' => date('Y-m-d H:i:s', strtotime('+1 hour'))
]);

$this->libraries->email->send($userEmail, 'Verify Your Account', [
    'code' => $verificationCode,
    'expires' => '1 hour'
]);

// SMS confirmation codes
$smsCode = Utilities::generateConfirmationCode();
$this->libraries->sms->send($phoneNumber, "Your confirmation code is: {$smsCode}");

// Two-factor authentication
public function generateTwoFactorCode($userId)
{
    $code = Utilities::generateConfirmationCode();

    $this->models->twoFactor->create([
        'user_id' => $userId,
        'code' => $code,
        'expires_at' => date('Y-m-d H:i:s', strtotime('+5 minutes')),
        'used' => false
    ]);

    return $code;
}

// Password reset codes
public function requestPasswordReset($email)
{
    $user = $this->models->user->get(['email' => $email]);
    if (!$user) return false;

    $resetCode = Utilities::generateConfirmationCode();

    $this->models->passwordReset->create([
        'user_id' => $user['id'],
        'code' => $resetCode,
        'expires_at' => date('Y-m-d H:i:s', strtotime('+30 minutes'))
    ]);

    $this->libraries->email->send($email, 'Password Reset Request', [
        'code' => $resetCode,
        'expires' => '30 minutes'
    ]);

    return $resetCode;
}

// Account activation codes
public function sendActivationCode($userId)
{
    $activationCode = Utilities::generateConfirmationCode();

    $this->models->user->save($userId, [
        'activation_code' => $activationCode,
        'activation_expires' => date('Y-m-d H:i:s', strtotime('+24 hours'))
    ]);

    return $activationCode;
}
```

### `generateRandomPassword()`

Generates a secure 20-character random password with mixed character types.

**Returns:** `string` - A 20-character secure password

**Character Set:** a-z, A-Z, 0-9, and various symbols

**Example:**

```php
// Temporary password generation
$tempPassword = Utilities::generateRandomPassword();
// Returns: "Kj8mP2nQ9rT4vX7zA!$%"

// New user account creation
public function createUserWithTempPassword($userData)
{
    $tempPassword = Utilities::generateRandomPassword();
    $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);

    $userId = $this->models->user->create([
        'name' => $userData['name'],
        'email' => $userData['email'],
        'password' => $hashedPassword,
        'must_change_password' => true,
        'created_at' => date('Y-m-d H:i:s')
    ]);

    // Send temporary password via email
    $this->libraries->email->send($userData['email'], 'Account Created', [
        'temporary_password' => $tempPassword,
        'login_url' => $_ENV['BASE_URL'] . '/login'
    ]);

    return $userId;
}

// API key generation
public function generateApiKey($userId)
{
    $apiKey = 'api_' . Utilities::generateRandomPassword();

    $this->models->apiKey->create([
        'user_id' => $userId,
        'key' => $apiKey,
        'created_at' => date('Y-m-d H:i:s'),
        'last_used' => null,
        'active' => true
    ]);

    return $apiKey;
}

// Secure token creation
public function createSecureToken($type, $userId)
{
    $token = $type . '_' . Utilities::generateRandomPassword();

    $this->models->token->create([
        'token' => $token,
        'type' => $type,
        'user_id' => $userId,
        'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
        'created_at' => date('Y-m-d H:i:s')
    ]);

    return $token;
}

// Password reset with secure generation
public function resetUserPassword($userId)
{
    $newPassword = Utilities::generateRandomPassword();
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $this->models->user->save($userId, [
        'password' => $hashedPassword,
        'must_change_password' => true,
        'password_reset_at' => date('Y-m-d H:i:s')
    ]);

    return $newPassword;
}
```

### `generateRandomString($length = 10)`

Generates a random string with alphanumeric characters and basic symbols.

**Parameters:**

-   `$length` (int): The desired length of the random string (default: 10)

**Returns:** `string` - A random string of the specified length

**Character Set:** 0-9, a-z, A-Z, !, @

**Example:**

```php
// Session identifiers
$sessionId = Utilities::generateRandomString(32);
// Returns: "aB3kL9mN2pQ4rS7tU8vW1xY5zC6dE!"

// Cache keys
$cacheKey = 'user_data_' . Utilities::generateRandomString(8);
$this->libraries->cache->set($cacheKey, $userData, 3600);

// File naming
$uploadDir = 'uploads/';
$randomName = Utilities::generateRandomString(16);
$extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
$fileName = $randomName . '.' . $extension;

// Temporary identifiers
public function createTempLink($resourceId)
{
    $tempId = Utilities::generateRandomString(20);

    $this->models->tempLink->create([
        'temp_id' => $tempId,
        'resource_id' => $resourceId,
        'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')),
        'created_at' => date('Y-m-d H:i:s')
    ]);

    return $_ENV['BASE_URL'] . '/temp/' . $tempId;
}

// Form tokens
public function generateFormToken()
{
    $token = Utilities::generateRandomString(24);
    $_SESSION['form_token'] = $token;
    return $token;
}

// Download tokens
public function createDownloadToken($fileId, $userId)
{
    $token = Utilities::generateRandomString(32);

    $this->models->downloadToken->create([
        'token' => $token,
        'file_id' => $fileId,
        'user_id' => $userId,
        'expires_at' => date('Y-m-d H:i:s', strtotime('+10 minutes')),
        'created_at' => date('Y-m-d H:i:s')
    ]);

    return $token;
}

// Invitation codes
public function generateInvitationCode($length = 12)
{
    return strtoupper(Utilities::generateRandomString($length));
}
```

### `generateRandomSymbols($length = 3)`

Generates a random string composed of special symbols only.

**Parameters:**

-   `$length` (int): The desired length of the symbol string (default: 3)

**Returns:** `string` - A random string of special symbols

**Character Set:** @, $, %, &, ^, \*, !, ?

**Example:**

```php
// Password complexity enhancement
$basePassword = 'MyPassword123';
$symbols = Utilities::generateRandomSymbols(4);
$complexPassword = $basePassword . $symbols;
// Results in: "MyPassword123$%!&"

// Security challenge generation
$challenge = Utilities::generateRandomSymbols(6);
$_SESSION['security_challenge'] = $challenge;

// Symbol-based tokens
public function generateSecurityToken()
{
    $alphanumeric = Utilities::generateRandomString(15);
    $symbols = Utilities::generateRandomSymbols(5);
    return $alphanumeric . $symbols;
}

// Special character requirements
public function enhancePassword($password)
{
    if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
        $password .= Utilities::generateRandomSymbols(2);
    }
    return $password;
}

// Custom separator generation
public function createCustomSeparator()
{
    return Utilities::generateRandomSymbols(1);
}
```

---

## String Processing and Formatting

### `truncate($string, $length, $html = true)`

Truncates a string to a specified length with optional HTML tooltip.

**Parameters:**

-   `$string` (string): The text to truncate
-   `$length` (int): Maximum length before truncation
-   `$html` (bool): Whether to include HTML tooltip (default: true)

**Returns:** `string` - Truncated string with optional HTML tooltip

**Example:**

```php
// Table cell content display
$title = "This is a very long product title that needs to be shortened for display";
$shortTitle = Utilities::truncate($title, 30);
// Returns: <span title="This is a very long product title that needs to be shortened for display">This is a very long product t&hellip;</span>

// Plain text truncation
$description = "Long product description...";
$shortDesc = Utilities::truncate($description, 50, false);
// Returns: "Long product description that exceeds limit..."

// Card summaries and previews
public function renderProductCard($product)
{
    return [
        'id' => $product['id'],
        'name' => Utilities::truncate($product['name'], 40),
        'description' => Utilities::truncate($product['description'], 100),
        'price' => $product['price']
    ];
}

// List item descriptions
foreach ($articles as $article) {
    echo '<li>';
    echo '<h3>' . $article['title'] . '</h3>';
    echo '<p>' . Utilities::truncate($article['content'], 150) . '</p>';
    echo '</li>';
}

// Comment snippets
public function getCommentPreview($comment)
{
    return [
        'id' => $comment['id'],
        'author' => $comment['author_name'],
        'preview' => Utilities::truncate($comment['content'], 80),
        'created_at' => $comment['created_at']
    ];
}

// Email subject lines
public function formatEmailSubject($subject)
{
    return Utilities::truncate($subject, 60, false);
}

// Search result summaries
public function formatSearchResults($results)
{
    $formatted = [];
    foreach ($results as $result) {
        $formatted[] = [
            'title' => Utilities::truncate($result['title'], 50),
            'excerpt' => Utilities::truncate($result['content'], 200),
            'url' => $result['url']
        ];
    }
    return $formatted;
}
```

### `slugify($text, $divider = '-')`

Converts text to a URL-friendly slug format.

**Parameters:**

-   `$text` (string): The text to convert to slug format
-   `$divider` (string): Character to use as word separator (default: '-')

**Returns:** `string` - Clean URL-safe slug, or 'n-a' if empty

**Example:**

```php
// URL slug generation for pages/posts
$title = "Hello World! This is a Test";
$slug = Utilities::slugify($title);
// Returns: "hello-world-this-is-a-test"

// File name sanitization
$fileName = "My Document (Final Version).pdf";
$safeFileName = Utilities::slugify($fileName, '_') . '.pdf';
// Returns: "my_document_final_version.pdf"

// SEO-friendly identifiers
$productName = "Café & Restaurant Equipment";
$productSlug = Utilities::slugify($productName);
// Returns: "cafe-restaurant-equipment"

// Category and tag slugs
public function createCategory($name)
{
    $slug = Utilities::slugify($name);

    return $this->models->category->create([
        'name' => $name,
        'slug' => $slug,
        'created_at' => date('Y-m-d H:i:s')
    ]);
}

// API endpoint naming
public function generateApiEndpoint($entityName)
{
    $slug = Utilities::slugify($entityName);
    return '/api/v1/' . $slug;
}

// Custom divider usage
$title = "Special @#$% Characters";
$underscoreSlug = Utilities::slugify($title, '_');
// Returns: "special_characters"

$dotSlug = Utilities::slugify($title, '.');
// Returns: "special.characters"

// Automatic routing
class Router
{
    public function generateRoute($title)
    {
        $slug = Utilities::slugify($title);
        return "/posts/{$slug}";
    }
}

// Content management
public function savePost($postData)
{
    $slug = Utilities::slugify($postData['title']);

    // Ensure unique slug
    $counter = 1;
    $originalSlug = $slug;
    while ($this->models->post->get(['slug' => $slug])) {
        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }

    $postData['slug'] = $slug;
    return $this->models->post->create($postData);
}
```

### `encodeURIComponent($str)`

Encodes a string for safe inclusion in URLs (JavaScript encodeURIComponent equivalent).

**Parameters:**

-   `$str` (string): The string to encode for URL inclusion

**Returns:** `string` - URL-encoded string safe for use in URLs

**Preserved Characters:** ! \* ' ( )

**Example:**

```php
// Query parameter encoding
$searchTerm = "hello world & more!";
$encoded = Utilities::encodeURIComponent($searchTerm);
// Returns: "hello%20world%20%26%20more!"

$searchUrl = "/search?q=" . Utilities::encodeURIComponent($searchTerm);

// JavaScript compatibility
$jsData = [
    'user_name' => "John O'Malley",
    'description' => "Product & Service Description (New!)"
];

foreach ($jsData as $key => $value) {
    $jsData[$key] = Utilities::encodeURIComponent($value);
}

// API parameter encoding
public function buildApiUrl($endpoint, $params)
{
    $url = $_ENV['API_BASE_URL'] . $endpoint;

    if (!empty($params)) {
        $encodedParams = [];
        foreach ($params as $key => $value) {
            $encodedParams[] = $key . '=' . Utilities::encodeURIComponent($value);
        }
        $url .= '?' . implode('&', $encodedParams);
    }

    return $url;
}

// URL fragment encoding
public function createBookmarkUrl($title, $section)
{
    $slug = Utilities::slugify($title);
    $fragment = Utilities::encodeURIComponent($section);
    return "/articles/{$slug}#{$fragment}";
}

// Form redirect URLs
public function createRedirectUrl($returnUrl)
{
    $encoded = Utilities::encodeURIComponent($returnUrl);
    return "/login?return=" . $encoded;
}
```

---

## Time and Date Formatting

### `timeAgo($date, $timezone = 'America/New_York')`

Converts a date to a human-readable "time ago" format.

**Parameters:**

-   `$date` (string): The date to compare (any format accepted by date_create)
-   `$timezone` (string): Target timezone (default: 'America/New_York')

**Returns:** `string` - Human-readable time difference string

**Formats:**

-   "X second(s) ago" (< 1 minute)
-   "X minute(s) ago" (< 1 hour)
-   "X hour(s) ago" (< 1 day)
-   "X day(s) ago" (< 1 month)
-   "X month(s) ago" (< 1 year)
-   "X year(s) ago" (>= 1 year)

**Example:**

```php
// Social media post timestamps
$postDate = '2024-08-05 14:30:00';
$timeAgo = Utilities::timeAgo($postDate);
// Returns: "2 hours ago" (depending on current time)

// Comment and activity feeds
public function formatComments($comments)
{
    foreach ($comments as &$comment) {
        $comment['time_ago'] = Utilities::timeAgo($comment['created_at']);
    }
    return $comments;
}

// Last login displays
public function getUserActivity($userId)
{
    $user = $this->models->user->get($userId);
    return [
        'name' => $user['name'],
        'last_login' => Utilities::timeAgo($user['last_login']),
        'member_since' => Utilities::timeAgo($user['created_at'])
    ];
}

// Content publication dates
foreach ($articles as $article) {
    echo '<article>';
    echo '<h2>' . $article['title'] . '</h2>';
    echo '<time>Published ' . Utilities::timeAgo($article['published_at']) . '</time>';
    echo '<p>' . $article['excerpt'] . '</p>';
    echo '</article>';
}

// User activity tracking
public function getActivityFeed($userId)
{
    $activities = $this->models->activity->getMany(['user_id' => $userId]);

    $feed = [];
    foreach ($activities as $activity) {
        $feed[] = [
            'action' => $activity['action'],
            'time_ago' => Utilities::timeAgo($activity['created_at']),
            'details' => $activity['details']
        ];
    }

    return $feed;
}

// Custom timezone handling
public function getLocalizedTimeAgo($date, $userTimezone)
{
    return Utilities::timeAgo($date, $userTimezone);
}

// Dashboard notifications
public function getNotifications($userId)
{
    $notifications = $this->models->notification->getMany(['user_id' => $userId]);

    foreach ($notifications as &$notification) {
        $notification['time_ago'] = Utilities::timeAgo($notification['created_at']);
    }

    return $notifications;
}
```

### `ordinalSuffix($n)`

Generates the appropriate ordinal suffix for a number.

**Parameters:**

-   `$n` (int): The number to get the ordinal suffix for

**Returns:** `string` - The ordinal suffix (st, nd, rd, or th)

**Example:**

```php
// Date formatting
$day = 21;
$suffix = Utilities::ordinalSuffix($day);
echo $day . $suffix . " of August"; // "21st of August"

// Ranking displays
public function formatRankings($users)
{
    $rankings = [];
    foreach ($users as $index => $user) {
        $position = $index + 1;
        $suffix = Utilities::ordinalSuffix($position);

        $rankings[] = [
            'position' => $position . $suffix,
            'name' => $user['name'],
            'score' => $user['score']
        ];
    }
    return $rankings;
}

// Sequential numbering
for ($i = 1; $i <= 10; $i++) {
    $suffix = Utilities::ordinalSuffix($i);
    echo "The {$i}{$suffix} item in the list\n";
}
// Output: "The 1st item...", "The 2nd item...", etc.

// Anniversary celebrations
public function getAnniversaryMessage($years)
{
    $suffix = Utilities::ordinalSuffix($years);
    return "Celebrating our {$years}{$suffix} anniversary!";
}

// Event scheduling
public function formatEventDate($day, $month, $year)
{
    $suffix = Utilities::ordinalSuffix($day);
    $monthName = date('F', mktime(0, 0, 0, $month, 1));
    return "{$day}{$suffix} of {$monthName}, {$year}";
}

// Competition results
public function announceWinner($position, $name)
{
    $suffix = Utilities::ordinalSuffix($position);
    return "Congratulations to {$name} for finishing in {$position}{$suffix} place!";
}
```

---

## Array Manipulation and Sorting

### `array_sort_numeric($array, $on, $order = 'ASC')`

Sorts an array numerically by a specified field.

**Parameters:**

-   `$array` (array): The multidimensional array to sort
-   `$on` (string): The field name to sort by
-   `$order` (string): Sort order - currently hardcoded to DESC

**Returns:** `array` - The sorted array

**Note:** Currently sorts in descending order regardless of $order parameter

**Example:**

```php
// Sorting user records by score
$users = [
    ['name' => 'Alice', 'score' => 85],
    ['name' => 'Bob', 'score' => 92],
    ['name' => 'Charlie', 'score' => 78]
];

$sortedUsers = Utilities::array_sort_numeric($users, 'score');
// Returns: Bob (92), Alice (85), Charlie (78)

// Ordering products by price
$products = [
    ['name' => 'Widget A', 'price' => 29.99],
    ['name' => 'Widget B', 'price' => 39.99],
    ['name' => 'Widget C', 'price' => 19.99]
];

$sortedProducts = Utilities::array_sort_numeric($products, 'price');
// Returns: Widget B (39.99), Widget A (29.99), Widget C (19.99)

// Ranking items by popularity
public function getPopularPosts()
{
    $posts = $this->models->post->getMany(['status' => 'published']);
    return Utilities::array_sort_numeric($posts, 'view_count');
}

// Date-based sorting using timestamps
$events = [
    ['title' => 'Event A', 'timestamp' => strtotime('2024-08-10')],
    ['title' => 'Event B', 'timestamp' => strtotime('2024-08-05')],
    ['title' => 'Event C', 'timestamp' => strtotime('2024-08-15')]
];

$sortedEvents = Utilities::array_sort_numeric($events, 'timestamp');

// Analytics data sorting
public function getTopCategories()
{
    $categories = $this->models->category->getMany();

    // Add post count to each category
    foreach ($categories as &$category) {
        $category['post_count'] = $this->models->post->count(['category_id' => $category['id']]);
    }

    return Utilities::array_sort_numeric($categories, 'post_count');
}

// Leaderboard generation
public function generateLeaderboard()
{
    $players = $this->models->player->getMany();
    return Utilities::array_sort_numeric($players, 'total_points');
}
```

---

## HTTP Request Handling

### `httpRequest($do, $vars = [], $methodRequest = 'GET', $headers = [])`

Performs flexible HTTP requests using cURL with comprehensive options.

**Parameters:**

-   `$do` (string): Target URL for the request
-   `$vars` (array): Data to send (query params for GET, body for POST/PUT/DELETE)
-   `$methodRequest` (string): HTTP method: GET, POST, PUT, DELETE, POST_NOT_JSON
-   `$headers` (array): Additional HTTP headers to include

**Returns:** `object` - Response object with data, or error object with details

**Example:**

```php
// API integrations and webhooks
$response = Utilities::httpRequest('https://api.example.com/users', [
    'page' => 1,
    'limit' => 10
], 'GET');

if (isset($response->error)) {
    error_log("API Error: " . $response->error);
} else {
    $users = $response->data;
}

// POST request with JSON payload
$userData = [
    'name' => 'John Doe',
    'email' => 'john@example.com'
];

$response = Utilities::httpRequest('https://api.example.com/users', $userData, 'POST', [
    'Authorization: Bearer ' . $apiToken
]);

// Form-encoded POST request
$formData = [
    'username' => 'johndoe',
    'password' => 'secret123'
];

$response = Utilities::httpRequest('https://api.example.com/login', $formData, 'POST_NOT_JSON');

// External service communication
public function syncDataWithExternalService($data)
{
    $response = Utilities::httpRequest($_ENV['EXTERNAL_API_URL'] . '/sync', $data, 'POST', [
        'X-API-Key: ' . $_ENV['EXTERNAL_API_KEY'],
        'X-Timestamp: ' . time()
    ]);

    if (isset($response->error)) {
        error_log("Sync failed: " . $response->error);
        return false;
    }

    return $response;
}

// Third-party authentication
public function validateExternalToken($token)
{
    $response = Utilities::httpRequest('https://oauth.provider.com/validate', [
        'token' => $token
    ], 'POST');

    return !isset($response->error) && $response->valid === true;
}

// Microservice communication
public function callUserService($action, $data)
{
    $serviceUrl = $_ENV['USER_SERVICE_URL'] . '/' . $action;

    $response = Utilities::httpRequest($serviceUrl, $data, 'POST', [
        'Content-Type: application/json',
        'X-Service-Token: ' . $_ENV['SERVICE_TOKEN']
    ]);

    return $response;
}

// PUT request for updates
public function updateExternalResource($resourceId, $updateData)
{
    $url = "https://api.example.com/resources/{$resourceId}";

    $response = Utilities::httpRequest($url, $updateData, 'PUT', [
        'Authorization: Bearer ' . $this->getApiToken()
    ]);

    return !isset($response->error);
}

// DELETE request
public function deleteExternalResource($resourceId)
{
    $url = "https://api.example.com/resources/{$resourceId}";

    $response = Utilities::httpRequest($url, [], 'DELETE', [
        'Authorization: Bearer ' . $this->getApiToken()
    ]);

    return !isset($response->error);
}
```

### `fireAndForget($url, $data)`

Executes an asynchronous "fire and forget" HTTP POST request.

**Parameters:**

-   `$url` (string): Relative URL path (will be prefixed with BASE_URL)
-   `$data` (array): Data to send as JSON payload

**Returns:** `bool` - True if command executed successfully, false otherwise

**Example:**

```php
// Webhook notifications
$webhookData = [
    'event' => 'user_registered',
    'user_id' => $userId,
    'timestamp' => time()
];

$success = Utilities::fireAndForget('/webhooks/user-registered', $webhookData);

// Background task triggering
public function processLargeDataset($datasetId)
{
    // Trigger background processing
    Utilities::fireAndForget('/background/process-dataset', [
        'dataset_id' => $datasetId,
        'initiated_by' => $this->instance->auth->user['id']
    ]);

    return ['message' => 'Processing started in background'];
}

// Analytics event tracking
public function trackUserAction($action, $details)
{
    Utilities::fireAndForget('/analytics/track', [
        'user_id' => $this->instance->auth->user['id'],
        'action' => $action,
        'details' => $details,
        'timestamp' => time(),
        'ip_address' => $_SERVER['REMOTE_ADDR']
    ]);
}

// Email queue processing
public function queueEmail($emailData)
{
    // Add to immediate queue processing
    $queued = $this->models->emailQueue->create($emailData);

    // Trigger background processor
    if ($queued) {
        Utilities::fireAndForget('/email/process-queue', [
            'queue_id' => $queued
        ]);
    }
}

// Cache invalidation
public function invalidateUserCache($userId)
{
    // Trigger cache clearing without waiting
    Utilities::fireAndForget('/cache/invalidate', [
        'type' => 'user',
        'user_id' => $userId
    ]);
}

// Notification distribution
public function sendNotification($notificationData)
{
    // Store notification
    $notificationId = $this->models->notification->create($notificationData);

    // Trigger distribution without blocking
    Utilities::fireAndForget('/notifications/distribute', [
        'notification_id' => $notificationId
    ]);
}

// Log aggregation
public function logApplicationEvent($event, $data)
{
    Utilities::fireAndForget('/logging/aggregate', [
        'event' => $event,
        'data' => $data,
        'timestamp' => microtime(true),
        'server' => $_SERVER['SERVER_NAME']
    ]);
}
```

---

## URL Processing and Domain Extraction

### `get_domain($url)`

Extracts the domain name from a given URL.

**Parameters:**

-   `$url` (string): The URL to extract domain from

**Returns:** `string|false` - The domain name, or false if invalid URL

**Example:**

```php
// URL validation and processing
$domain = Utilities::get_domain('https://www.example.com/path/to/page');
// Returns: "example.com"

$domain = Utilities::get_domain('api.example.com');
// Returns: "api.example.com"

// Domain-based routing or filtering
public function validateAllowedDomain($url)
{
    $domain = Utilities::get_domain($url);
    $allowedDomains = ['example.com', 'api.example.com', 'cdn.example.com'];

    return in_array($domain, $allowedDomains);
}

// Analytics and tracking
public function trackReferrer()
{
    if (isset($_SERVER['HTTP_REFERER'])) {
        $referrerDomain = Utilities::get_domain($_SERVER['HTTP_REFERER']);

        $this->models->analytics->create([
            'referrer_domain' => $referrerDomain,
            'page' => $_SERVER['REQUEST_URI'],
            'user_id' => $this->instance->auth->user['id'] ?? null,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}

// Security policy enforcement
public function validateImageSource($imageUrl)
{
    $domain = Utilities::get_domain($imageUrl);
    $trustedDomains = ['images.example.com', 'cdn.example.com'];

    if (!in_array($domain, $trustedDomains)) {
        throw new Exception('Image source not allowed: ' . $domain);
    }

    return true;
}

// Link validation
public function validateExternalLinks($content)
{
    preg_match_all('/href="([^"]*)"/', $content, $matches);

    $invalidDomains = [];
    foreach ($matches[1] as $url) {
        $domain = Utilities::get_domain($url);

        if ($domain && !$this->isDomainSafe($domain)) {
            $invalidDomains[] = $domain;
        }
    }

    return empty($invalidDomains) ? true : $invalidDomains;
}

// Domain grouping for analytics
public function groupTrafficByDomain()
{
    $visits = $this->models->visit->getMany();
    $domainGroups = [];

    foreach ($visits as $visit) {
        if ($visit['referrer']) {
            $domain = Utilities::get_domain($visit['referrer']);
            if ($domain) {
                $domainGroups[$domain] = ($domainGroups[$domain] ?? 0) + 1;
            }
        }
    }

    return $domainGroups;
}

// Email domain validation
public function validateEmailDomain($email)
{
    $domain = substr(strrchr($email, "@"), 1);
    $extractedDomain = Utilities::get_domain('http://' . $domain);

    $blockedDomains = ['tempmail.com', 'guerrillamail.com'];
    return !in_array($extractedDomain, $blockedDomains);
}
```

---

## Complete Utility Examples

### Data Processing Pipeline

```php
class DataProcessor
{
    public function processUserData($rawData)
    {
        $processedData = [];

        foreach ($rawData as $user) {
            // Generate unique ID
            $user['uuid'] = Utilities::uuidv4();

            // Create URL-friendly username
            $user['slug'] = Utilities::slugify($user['name']);

            // Format timestamps
            $user['joined_ago'] = Utilities::timeAgo($user['created_at']);

            // Truncate bio for display
            $user['bio_short'] = Utilities::truncate($user['bio'], 100);

            // Generate temporary password if needed
            if (empty($user['password'])) {
                $user['temp_password'] = Utilities::generateRandomPassword();
            }

            $processedData[] = $user;
        }

        // Sort by registration date (timestamp)
        return Utilities::array_sort_numeric($processedData, 'created_timestamp');
    }
}
```

### External API Integration

```php
class ExternalApiClient
{
    private $apiKey;
    private $baseUrl;

    public function __construct($apiKey, $baseUrl)
    {
        $this->apiKey = $apiKey;
        $this->baseUrl = $baseUrl;
    }

    public function createUser($userData)
    {
        // Add unique request ID
        $userData['request_id'] = Utilities::uuidv4();

        $response = Utilities::httpRequest($this->baseUrl . '/users', $userData, 'POST', [
            'Authorization: Bearer ' . $this->apiKey,
            'X-Request-ID: ' . $userData['request_id']
        ]);

        if (isset($response->error)) {
            error_log("API Error: " . $response->error);
            return false;
        }

        return $response;
    }

    public function syncData($data)
    {
        // Fire and forget for non-critical sync
        Utilities::fireAndForget('/api/sync', [
            'data' => $data,
            'sync_id' => Utilities::uuidv4(),
            'timestamp' => time()
        ]);
    }
}
```

### Content Management System

```php
class ContentManager
{
    public function createArticle($articleData)
    {
        // Generate URL slug
        $articleData['slug'] = Utilities::slugify($articleData['title']);

        // Ensure unique slug
        $counter = 1;
        $originalSlug = $articleData['slug'];
        while ($this->models->article->get(['slug' => $articleData['slug']])) {
            $articleData['slug'] = $originalSlug . '-' . $counter;
            $counter++;
        }

        // Generate UUID for article
        $articleData['uuid'] = Utilities::uuidv4();

        // Create excerpt from content
        $articleData['excerpt'] = Utilities::truncate(strip_tags($articleData['content']), 200, false);

        // Add timestamps
        $articleData['created_at'] = date('Y-m-d H:i:s');

        return $this->models->article->create($articleData);
    }

    public function getArticleList()
    {
        $articles = $this->models->article->getMany(['status' => 'published']);

        foreach ($articles as &$article) {
            $article['time_ago'] = Utilities::timeAgo($article['published_at']);
            $article['excerpt'] = Utilities::truncate($article['content'], 150);
        }

        return Utilities::array_sort_numeric($articles, 'published_timestamp');
    }
}
```

---

## Best Practices

### 1. Use Appropriate Methods for Context

```php
// Use UUID for permanent identifiers
$userId = Utilities::uuidv4();

// Use confirmation codes for temporary verification
$verificationCode = Utilities::generateConfirmationCode();

// Use random strings for session tokens
$sessionToken = Utilities::generateRandomString(32);
```

### 2. Handle HTTP Request Errors

```php
$response = Utilities::httpRequest($url, $data, 'POST');

if (isset($response->error)) {
    error_log("HTTP Error: " . $response->error);
    return false;
}

// Process successful response
return $response;
```

### 3. Validate URL Processing

```php
$domain = Utilities::get_domain($url);
if ($domain === false) {
    throw new Exception('Invalid URL provided');
}

// Proceed with domain validation
if (!$this->isAllowedDomain($domain)) {
    throw new Exception('Domain not allowed: ' . $domain);
}
```

### 4. Optimize String Processing

```php
// Use truncation for display optimization
$displayTitle = Utilities::truncate($title, 50);

// Use slugification for URL generation
$urlSlug = Utilities::slugify($title);

// Combine for complete content processing
$processedContent = [
    'title' => $title,
    'slug' => Utilities::slugify($title),
    'excerpt' => Utilities::truncate($content, 200, false)
];
```

---

## Framework Integration

The Utilities API provides essential helper functions that integrate throughout the framework:

-   **Authentication**: UUID generation for session tokens
-   **Content Management**: Slug generation and text processing
-   **API Integration**: HTTP request handling and domain validation
-   **User Interface**: Time formatting and string truncation
-   **Security**: Random data generation for tokens and codes
-   **Data Processing**: Array sorting and manipulation utilities

The Utilities API offers comprehensive static helper methods for common programming tasks with robust error handling and security-conscious implementations.
