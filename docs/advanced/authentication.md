# 🔐 Authentication & Authorization

Hoist PHP provides a comprehensive authentication and authorization system with support for multiple authentication methods, role-based access control, and security features like session management and password policies.

## Authentication Basics

### **User Authentication Setup**

```php
class Auth
{
    private $session;
    private $userModel;
    private $config;

    public function __construct(Session $session, UserModel $userModel, $config = [])
    {
        $this->session = $session;
        $this->userModel = $userModel;
        $this->config = array_merge([
            'password_min_length' => 8,
            'session_lifetime' => 3600,
            'max_login_attempts' => 5,
            'lockout_duration' => 900, // 15 minutes
            'remember_token_lifetime' => 2592000, // 30 days
        ], $config);
    }

    public function attempt($credentials, $remember = false)
    {
        $email = $credentials['email'] ?? null;
        $password = $credentials['password'] ?? null;

        if (!$email || !$password) {
            return false;
        }

        // Check for too many failed attempts
        if ($this->hasTooManyLoginAttempts($email)) {
            throw new TooManyAttemptsException('Too many login attempts. Please try again later.');
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            $this->incrementLoginAttempts($email);
            return false;
        }

        // Check if user is active
        if ($user['status'] !== 'active') {
            throw new InactiveUserException('Account is not active.');
        }

        // Clear login attempts
        $this->clearLoginAttempts($email);

        // Log the user in
        $this->login($user, $remember);

        return true;
    }

    public function login($user, $remember = false)
    {
        // Store user in session
        $this->session->put('user_id', $user['id']);
        $this->session->put('user_email', $user['email']);
        $this->session->regenerate();

        // Update last login
        $this->userModel->update($user['id'], [
            'last_login_at' => date('Y-m-d H:i:s'),
            'last_login_ip' => $_SERVER['REMOTE_ADDR'] ?? null
        ]);

        // Handle remember me
        if ($remember) {
            $this->setRememberToken($user);
        }

        // Log the event
        $this->logAuthEvent('login', $user['id']);
    }

    public function logout()
    {
        $userId = $this->id();

        // Clear remember token
        if ($this->user()) {
            $this->clearRememberToken();
        }

        // Clear session
        $this->session->forget(['user_id', 'user_email']);
        $this->session->invalidate();

        // Log the event
        if ($userId) {
            $this->logAuthEvent('logout', $userId);
        }
    }

    public function check()
    {
        return $this->session->has('user_id');
    }

    public function guest()
    {
        return !$this->check();
    }

    public function id()
    {
        return $this->session->get('user_id');
    }

    public function user()
    {
        if (!$this->check()) {
            return null;
        }

        $userId = $this->id();
        return $this->userModel->find($userId);
    }
}
```

### **Password Management**

```php
class PasswordManager
{
    private $config;

    public function __construct($config = [])
    {
        $this->config = array_merge([
            'min_length' => 8,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_numbers' => true,
            'require_symbols' => true,
            'max_age_days' => 90,
            'history_count' => 5
        ], $config);
    }

    public function hash($password)
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64 MB
            'time_cost' => 4,       // 4 iterations
            'threads' => 3          // 3 threads
        ]);
    }

    public function verify($password, $hash)
    {
        return password_verify($password, $hash);
    }

    public function needsRehash($hash)
    {
        return password_needs_rehash($hash, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536,
            'time_cost' => 4,
            'threads' => 3
        ]);
    }

    public function validate($password)
    {
        $errors = [];

        if (strlen($password) < $this->config['min_length']) {
            $errors[] = "Password must be at least {$this->config['min_length']} characters long";
        }

        if ($this->config['require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }

        if ($this->config['require_lowercase'] && !preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }

        if ($this->config['require_numbers'] && !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }

        if ($this->config['require_symbols'] && !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = 'Password must contain at least one special character';
        }

        // Check for common weak patterns
        if ($this->isCommonPassword($password)) {
            $errors[] = 'Password is too common, please choose a stronger password';
        }

        return empty($errors) ? true : $errors;
    }

    public function generate($length = 16)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $password;
    }

    private function isCommonPassword($password)
    {
        $commonPasswords = [
            'password', '123456', '123456789', 'qwerty', 'abc123',
            'password123', 'admin', 'letmein', 'welcome', 'monkey'
        ];

        return in_array(strtolower($password), $commonPasswords);
    }
}
```

## Role-Based Access Control

### **Permission System**

```php
class Permission
{
    private $db;
    private $cache;

    public function __construct(DatabaseInterface $db, $cache = null)
    {
        $this->db = $db;
        $this->cache = $cache;
    }

    public function can($user, $permission, $resource = null)
    {
        $userId = is_array($user) ? $user['id'] : $user;

        // Check cache first
        $cacheKey = "user_permissions_{$userId}";
        $userPermissions = $this->cache ? $this->cache->get($cacheKey) : null;

        if ($userPermissions === null) {
            $userPermissions = $this->getUserPermissions($userId);
            if ($this->cache) {
                $this->cache->set($cacheKey, $userPermissions, 3600); // Cache for 1 hour
            }
        }

        // Check direct permissions
        if (in_array($permission, $userPermissions['direct'])) {
            return $this->checkResourcePermission($userId, $permission, $resource);
        }

        // Check role-based permissions
        foreach ($userPermissions['roles'] as $role => $permissions) {
            if (in_array($permission, $permissions)) {
                return $this->checkResourcePermission($userId, $permission, $resource);
            }
        }

        return false;
    }

    public function cannot($user, $permission, $resource = null)
    {
        return !$this->can($user, $permission, $resource);
    }

    public function hasRole($user, $role)
    {
        $userId = is_array($user) ? $user['id'] : $user;

        $roles = $this->db->table('user_roles')
            ->select(['role'])
            ->where('user_id', $userId)
            ->get();

        return in_array($role, array_column($roles, 'role'));
    }

    public function assignRole($userId, $role)
    {
        // Check if role exists
        $roleExists = $this->db->table('roles')
            ->where('name', $role)
            ->first();

        if (!$roleExists) {
            throw new InvalidArgumentException("Role '{$role}' does not exist");
        }

        // Check if user already has role
        $existing = $this->db->table('user_roles')
            ->where('user_id', $userId)
            ->where('role', $role)
            ->first();

        if (!$existing) {
            $this->db->table('user_roles')->insert([
                'user_id' => $userId,
                'role' => $role,
                'assigned_at' => date('Y-m-d H:i:s'),
                'assigned_by' => Instance::get()->auth->id()
            ]);

            // Clear permission cache
            $this->clearUserPermissionCache($userId);
        }
    }

    public function removeRole($userId, $role)
    {
        $deleted = $this->db->table('user_roles')
            ->where('user_id', $userId)
            ->where('role', $role)
            ->delete();

        if ($deleted > 0) {
            $this->clearUserPermissionCache($userId);
        }

        return $deleted > 0;
    }

    public function grantPermission($userId, $permission, $resource = null)
    {
        $data = [
            'user_id' => $userId,
            'permission' => $permission,
            'granted_at' => date('Y-m-d H:i:s'),
            'granted_by' => Instance::get()->auth->id()
        ];

        if ($resource) {
            $data['resource_type'] = $resource['type'];
            $data['resource_id'] = $resource['id'];
        }

        $this->db->table('user_permissions')->insert($data);
        $this->clearUserPermissionCache($userId);
    }

    public function revokePermission($userId, $permission, $resource = null)
    {
        $query = $this->db->table('user_permissions')
            ->where('user_id', $userId)
            ->where('permission', $permission);

        if ($resource) {
            $query->where('resource_type', $resource['type'])
                  ->where('resource_id', $resource['id']);
        } else {
            $query->whereNull('resource_type')
                  ->whereNull('resource_id');
        }

        $deleted = $query->delete();

        if ($deleted > 0) {
            $this->clearUserPermissionCache($userId);
        }

        return $deleted > 0;
    }

    private function getUserPermissions($userId)
    {
        // Get direct permissions
        $directPermissions = $this->db->table('user_permissions')
            ->select(['permission'])
            ->where('user_id', $userId)
            ->whereNull('resource_type')
            ->get();

        // Get role-based permissions
        $rolePermissions = $this->db->table('user_roles')
            ->select([
                'user_roles.role',
                'role_permissions.permission'
            ])
            ->join('role_permissions', 'user_roles.role', '=', 'role_permissions.role')
            ->where('user_roles.user_id', $userId)
            ->get();

        $roles = [];
        foreach ($rolePermissions as $rp) {
            if (!isset($roles[$rp['role']])) {
                $roles[$rp['role']] = [];
            }
            $roles[$rp['role']][] = $rp['permission'];
        }

        return [
            'direct' => array_column($directPermissions, 'permission'),
            'roles' => $roles
        ];
    }

    private function checkResourcePermission($userId, $permission, $resource)
    {
        if (!$resource) {
            return true; // Global permission
        }

        // Check for specific resource permission
        $resourcePermission = $this->db->table('user_permissions')
            ->where('user_id', $userId)
            ->where('permission', $permission)
            ->where('resource_type', $resource['type'])
            ->where('resource_id', $resource['id'])
            ->first();

        return $resourcePermission !== null;
    }

    private function clearUserPermissionCache($userId)
    {
        if ($this->cache) {
            $this->cache->delete("user_permissions_{$userId}");
        }
    }
}
```

### **Authorization Middleware**

```php
class AuthorizeMiddleware
{
    private $auth;
    private $permission;

    public function __construct(Auth $auth, Permission $permission)
    {
        $this->auth = $auth;
        $this->permission = $permission;
    }

    public function handle($request, $next, $requiredPermission = null, $resource = null)
    {
        if (!$this->auth->check()) {
            return $this->handleUnauthorized($request);
        }

        if ($requiredPermission) {
            $user = $this->auth->user();

            if (!$this->permission->can($user, $requiredPermission, $resource)) {
                return $this->handleForbidden($request, $requiredPermission);
            }
        }

        return $next($request);
    }

    private function handleUnauthorized($request)
    {
        if ($request->wantsJson()) {
            return Instance::get()->response
                ->setStatusCode(401)
                ->json(['error' => 'Authentication required']);
        } else {
            return Instance::get()->response
                ->redirect('/login')
                ->with('error', 'Please log in to continue');
        }
    }

    private function handleForbidden($request, $permission)
    {
        if ($request->wantsJson()) {
            return Instance::get()->response
                ->setStatusCode(403)
                ->json([
                    'error' => 'Access denied',
                    'required_permission' => $permission
                ]);
        } else {
            return Instance::get()->response
                ->setStatusCode(403)
                ->render('errors/403', ['permission' => $permission]);
        }
    }
}

class RoleMiddleware
{
    private $auth;
    private $permission;

    public function __construct(Auth $auth, Permission $permission)
    {
        $this->auth = $auth;
        $this->permission = $permission;
    }

    public function handle($request, $next, ...$roles)
    {
        if (!$this->auth->check()) {
            return Instance::get()->response
                ->redirect('/login')
                ->with('error', 'Authentication required');
        }

        $user = $this->auth->user();

        foreach ($roles as $role) {
            if ($this->permission->hasRole($user, $role)) {
                return $next($request);
            }
        }

        return Instance::get()->response
            ->setStatusCode(403)
            ->json(['error' => 'Insufficient privileges']);
    }
}
```

## Multi-Factor Authentication

### **2FA Implementation**

```php
class TwoFactorAuth
{
    private $userModel;
    private $secretLength = 32;

    public function __construct(UserModel $userModel)
    {
        $this->userModel = $userModel;
    }

    public function generateSecret()
    {
        return bin2hex(random_bytes($this->secretLength / 2));
    }

    public function generateQRCodeUrl($user, $secret, $issuer = 'Hoist PHP App')
    {
        $email = $user['email'];
        $encodedIssuer = urlencode($issuer);
        $encodedEmail = urlencode($email);

        return "otpauth://totp/{$encodedIssuer}:{$encodedEmail}?secret={$secret}&issuer={$encodedIssuer}";
    }

    public function verifyCode($secret, $code, $window = 1)
    {
        $currentTime = floor(time() / 30);

        for ($i = -$window; $i <= $window; $i++) {
            $testTime = $currentTime + $i;
            $expectedCode = $this->generateCode($secret, $testTime);

            if (hash_equals($expectedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    public function enable2FA($userId, $code)
    {
        $user = $this->userModel->find($userId);
        if (!$user || !$user['totp_secret']) {
            throw new InvalidArgumentException('User not found or 2FA not set up');
        }

        if (!$this->verifyCode($user['totp_secret'], $code)) {
            throw new InvalidArgumentException('Invalid verification code');
        }

        $this->userModel->update($userId, [
            'totp_enabled' => true,
            'totp_enabled_at' => date('Y-m-d H:i:s')
        ]);

        // Generate backup codes
        $backupCodes = $this->generateBackupCodes($userId);

        return $backupCodes;
    }

    public function disable2FA($userId, $code)
    {
        $user = $this->userModel->find($userId);
        if (!$user || !$user['totp_enabled']) {
            throw new InvalidArgumentException('2FA not enabled for this user');
        }

        if (!$this->verifyCode($user['totp_secret'], $code)) {
            throw new InvalidArgumentException('Invalid verification code');
        }

        $this->userModel->update($userId, [
            'totp_enabled' => false,
            'totp_secret' => null,
            'totp_enabled_at' => null
        ]);

        // Clear backup codes
        $this->clearBackupCodes($userId);
    }

    public function verifyBackupCode($userId, $code)
    {
        $backupCode = Instance::get()->database->table('user_backup_codes')
            ->where('user_id', $userId)
            ->where('code', hash('sha256', $code))
            ->where('used_at', null)
            ->first();

        if ($backupCode) {
            // Mark as used
            Instance::get()->database->table('user_backup_codes')
                ->where('id', $backupCode['id'])
                ->update([
                    'used_at' => date('Y-m-d H:i:s'),
                    'used_ip' => $_SERVER['REMOTE_ADDR'] ?? null
                ]);

            return true;
        }

        return false;
    }

    private function generateCode($secret, $time)
    {
        $data = pack('N*', 0, $time);
        $hash = hash_hmac('sha1', $data, hex2bin($secret), true);
        $offset = ord($hash[19]) & 0xf;
        $code = (
            ((ord($hash[$offset + 0]) & 0x7f) << 24) |
            ((ord($hash[$offset + 1]) & 0xff) << 16) |
            ((ord($hash[$offset + 2]) & 0xff) << 8) |
            (ord($hash[$offset + 3]) & 0xff)
        ) % 1000000;

        return str_pad($code, 6, '0', STR_PAD_LEFT);
    }

    private function generateBackupCodes($userId, $count = 10)
    {
        $codes = [];

        for ($i = 0; $i < $count; $i++) {
            $code = strtoupper(bin2hex(random_bytes(4)));
            $codes[] = $code;

            Instance::get()->database->table('user_backup_codes')->insert([
                'user_id' => $userId,
                'code' => hash('sha256', $code),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        return $codes;
    }

    private function clearBackupCodes($userId)
    {
        Instance::get()->database->table('user_backup_codes')
            ->where('user_id', $userId)
            ->delete();
    }
}
```

## Session Security

### **Secure Session Management**

```php
class SecureSession
{
    private $config;
    private $isStarted = false;

    public function __construct($config = [])
    {
        $this->config = array_merge([
            'lifetime' => 3600,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict',
            'regenerate_interval' => 300 // 5 minutes
        ], $config);
    }

    public function start()
    {
        if ($this->isStarted) {
            return;
        }

        // Configure session settings
        ini_set('session.use_only_cookies', 1);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', $this->config['secure'] ? 1 : 0);
        ini_set('session.cookie_samesite', $this->config['samesite']);

        session_set_cookie_params([
            'lifetime' => $this->config['lifetime'],
            'path' => $this->config['path'],
            'domain' => $this->config['domain'],
            'secure' => $this->config['secure'],
            'httponly' => $this->config['httponly'],
            'samesite' => $this->config['samesite']
        ]);

        session_start();
        $this->isStarted = true;

        // Check for session hijacking
        $this->validateSession();

        // Regenerate session ID periodically
        $this->maybeRegenerateId();
    }

    public function regenerate($deleteOld = true)
    {
        if (!$this->isStarted) {
            return;
        }

        session_regenerate_id($deleteOld);
        $_SESSION['_last_regenerated'] = time();
        $_SESSION['_fingerprint'] = $this->generateFingerprint();
    }

    public function invalidate()
    {
        if (!$this->isStarted) {
            return;
        }

        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']
            );
        }

        session_destroy();
        $this->isStarted = false;
    }

    public function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public function put($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    public function forget($key)
    {
        if (is_array($key)) {
            foreach ($key as $k) {
                unset($_SESSION[$k]);
            }
        } else {
            unset($_SESSION[$key]);
        }
    }

    public function flash($key, $value = null)
    {
        if ($value === null) {
            // Get flash value
            $value = $_SESSION['_flash'][$key] ?? null;
            unset($_SESSION['_flash'][$key]);
            return $value;
        } else {
            // Set flash value
            $_SESSION['_flash'][$key] = $value;
        }
    }

    private function validateSession()
    {
        // Check session fingerprint
        $currentFingerprint = $this->generateFingerprint();
        $sessionFingerprint = $_SESSION['_fingerprint'] ?? null;

        if ($sessionFingerprint && $sessionFingerprint !== $currentFingerprint) {
            $this->invalidate();
            throw new SecurityException('Session fingerprint mismatch - possible hijacking attempt');
        }

        if (!$sessionFingerprint) {
            $_SESSION['_fingerprint'] = $currentFingerprint;
        }

        // Check session timeout
        $lastActivity = $_SESSION['_last_activity'] ?? time();
        if (time() - $lastActivity > $this->config['lifetime']) {
            $this->invalidate();
            throw new SessionExpiredException('Session has expired');
        }

        $_SESSION['_last_activity'] = time();
    }

    private function maybeRegenerateId()
    {
        $lastRegenerated = $_SESSION['_last_regenerated'] ?? 0;

        if (time() - $lastRegenerated > $this->config['regenerate_interval']) {
            $this->regenerate();
        }
    }

    private function generateFingerprint()
    {
        $factors = [
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '',
            $_SERVER['HTTP_ACCEPT_ENCODING'] ?? '',
        ];

        return hash('sha256', implode('|', $factors));
    }
}
```

## JWT Token Authentication

### **JSON Web Token Implementation**

```php
class JWTAuth
{
    private $secret;
    private $algorithm;
    private $issuer;
    private $audience;
    private $tokenLifetime;

    public function __construct($config)
    {
        $this->secret = $config['secret'];
        $this->algorithm = $config['algorithm'] ?? 'HS256';
        $this->issuer = $config['issuer'] ?? 'hoist-php';
        $this->audience = $config['audience'] ?? 'hoist-php-app';
        $this->tokenLifetime = $config['lifetime'] ?? 3600;
    }

    public function generateToken($user, $customClaims = [])
    {
        $header = [
            'typ' => 'JWT',
            'alg' => $this->algorithm
        ];

        $payload = array_merge([
            'iss' => $this->issuer,
            'aud' => $this->audience,
            'iat' => time(),
            'exp' => time() + $this->tokenLifetime,
            'sub' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name'],
            'roles' => $this->getUserRoles($user['id'])
        ], $customClaims);

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));

        $signature = $this->sign($headerEncoded . '.' . $payloadEncoded);
        $signatureEncoded = $this->base64UrlEncode($signature);

        return $headerEncoded . '.' . $payloadEncoded . '.' . $signatureEncoded;
    }

    public function validateToken($token)
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new InvalidTokenException('Invalid token format');
        }

        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;

        // Verify signature
        $expectedSignature = $this->sign($headerEncoded . '.' . $payloadEncoded);
        $providedSignature = $this->base64UrlDecode($signatureEncoded);

        if (!hash_equals($expectedSignature, $providedSignature)) {
            throw new InvalidTokenException('Invalid token signature');
        }

        // Decode payload
        $payload = json_decode($this->base64UrlDecode($payloadEncoded), true);

        if (!$payload) {
            throw new InvalidTokenException('Invalid token payload');
        }

        // Check expiration
        if (isset($payload['exp']) && time() > $payload['exp']) {
            throw new TokenExpiredException('Token has expired');
        }

        // Check issuer and audience
        if ($payload['iss'] !== $this->issuer || $payload['aud'] !== $this->audience) {
            throw new InvalidTokenException('Invalid token issuer or audience');
        }

        return $payload;
    }

    public function refreshToken($token)
    {
        try {
            $payload = $this->validateToken($token);

            // Get fresh user data
            $user = Instance::get()->models->user->find($payload['sub']);

            if (!$user || $user['status'] !== 'active') {
                throw new InvalidTokenException('User not found or inactive');
            }

            // Generate new token
            return $this->generateToken($user);

        } catch (TokenExpiredException $e) {
            // Allow refresh of recently expired tokens (within 24 hours)
            $parts = explode('.', $token);
            $payload = json_decode($this->base64UrlDecode($parts[1]), true);

            if (time() - $payload['exp'] < 86400) { // 24 hours grace period
                $user = Instance::get()->models->user->find($payload['sub']);
                if ($user && $user['status'] === 'active') {
                    return $this->generateToken($user);
                }
            }

            throw $e;
        }
    }

    private function sign($data)
    {
        switch ($this->algorithm) {
            case 'HS256':
                return hash_hmac('sha256', $data, $this->secret, true);
            case 'HS384':
                return hash_hmac('sha384', $data, $this->secret, true);
            case 'HS512':
                return hash_hmac('sha512', $data, $this->secret, true);
            default:
                throw new InvalidArgumentException("Unsupported algorithm: {$this->algorithm}");
        }
    }

    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode($data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    private function getUserRoles($userId)
    {
        $roles = Instance::get()->database->table('user_roles')
            ->select(['role'])
            ->where('user_id', $userId)
            ->get();

        return array_column($roles, 'role');
    }
}
```

---

This authentication and authorization system provides comprehensive security features including password management, role-based access control, multi-factor authentication, secure session handling, and JWT token support. Use these components to build secure applications with proper user management.

**Next:** [Caching](caching.md) - Learn about caching strategies and performance optimization.
