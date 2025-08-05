<?php

/**
 * ===================================================================
 * HOIST PHP FRAMEWORK - COMPREHENSIVE DATA CLEANING & SECURITY SERVICE
 * ===================================================================
 * 
 * The Cleaner class provides enterprise-grade data sanitization, security
 * filtering, and text processing capabilities for the Hoist PHP framework.
 * It offers both fluent interface and direct method access for comprehensive
 * data cleaning, XSS prevention, injection attack mitigation, and advanced
 * text normalization.
 * 
 * Key Features:
 * - Security First: XSS prevention, SQL injection protection, script removal
 * - Fluent Interface: Chainable methods for complex cleaning operations
 * - Batch Processing: Clean multiple fields with different rules
 * - Advanced Text Processing: Unicode normalization, smart trimming, case conversion
 * - File Safety: Comprehensive file name and path sanitization
 * - HTML Processing: Safe HTML cleaning with configurable allowed tags
 * - Format Normalization: Phone numbers, emails, URLs, dates
 * - Framework Integration: Works seamlessly with Validation and Request classes
 * - Custom Rules: Extensible with user-defined cleaning functions
 * - Performance Optimized: Efficient regex patterns and caching
 * 
 * Usage Patterns:
 * 
 * // Fluent interface for complex cleaning
 * $cleaned = $cleaner->set($userInput)
 *     ->removeXSS()
 *     ->trimSmart()
 *     ->normalizeSpaces()
 *     ->limitLength(100)
 *     ->get();
 * 
 * // Direct method calls
 * $email = $cleaner->cleanEmail($rawEmail);
 * $phone = $cleaner->formatPhone($phoneNumber, 'US');
 * $filename = $cleaner->sanitizeFilename($uploadedName);
 * 
 * // Batch cleaning with rules
 * $cleaned = $cleaner->cleanBatch([
 *     'name' => 'trim|normalize_spaces|remove_xss',
 *     'email' => 'trim|lowercase|clean_email',
 *     'phone' => 'numeric_only|format_phone:US',
 *     'bio' => 'trim|clean_html:basic|limit:500'
 * ], $data);
 * 
 * // Security-focused cleaning
 * $safe = $cleaner->security($input)
 *     ->removeXSS()
 *     ->removeSQLChars()
 *     ->escapeHTML()
 *     ->get();
 * 
 * @package HoistPHP\Core\Libraries
 * @author  Hoist PHP Framework Team
 * @version 2.0.0
 */
class Cleaner
{
    // ===============================================================
    // CLASS PROPERTIES AND CONFIGURATION
    // ===============================================================

    /**
     * Current value being cleaned in fluent interface.
     * 
     * @var mixed The value under cleaning
     */
    private $value = null;

    /**
     * Application instance for framework integration.
     * 
     * @var Instance|null Framework application instance
     */
    private $instance = null;

    /**
     * Custom cleaning rules registry.
     * 
     * @var array User-defined cleaning functions
     */
    private $customRules = [];

    /**
     * HTML tag whitelist for safe HTML cleaning.
     * 
     * @var array Allowed HTML tags and attributes
     */
    private $allowedTags = [
        'basic' => ['p', 'br', 'strong', 'em', 'u', 'i', 'b'],
        'extended' => ['p', 'br', 'strong', 'em', 'u', 'i', 'b', 'a', 'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'],
        'full' => ['p', 'br', 'strong', 'em', 'u', 'i', 'b', 'a', 'ul', 'ol', 'li', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'img', 'div', 'span', 'table', 'tr', 'td', 'th', 'thead', 'tbody']
    ];

    /**
     * Dangerous HTML patterns for XSS prevention.
     * 
     * @var array Patterns that should be removed for security
     */
    private $dangerousPatterns = [
        '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
        '/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi',
        '/<object\b[^<]*(?:(?!<\/object>)<[^<]*)*<\/object>/mi',
        '/<embed\b[^<]*(?:(?!<\/embed>)<[^<]*)*<\/embed>/mi',
        '/<applet\b[^<]*(?:(?!<\/applet>)<[^<]*)*<\/applet>/mi',
        '/<meta\b[^>]*>/i',
        '/<link\b[^>]*>/i',
        '/<style\b[^<]*(?:(?!<\/style>)<[^<]*)*<\/style>/mi',
        '/on\w+\s*=\s*["\'][^"\']*["\']/i', // Event handlers
        '/javascript\s*:/i',
        '/vbscript\s*:/i',
        '/data\s*:/i'
    ];

    // ===============================================================
    // CONSTRUCTOR AND INITIALIZATION
    // ===============================================================

    /**
     * Initializes the cleaning service.
     * 
     * @param Instance|null $instance Framework application instance
     */
    public function __construct($instance = null)
    {
        $this->instance = $instance;
    }

    // ===============================================================
    // FLUENT INTERFACE METHODS
    // ===============================================================

    /**
     * Sets the value to be cleaned and starts the cleaning chain.
     * 
     * @param mixed $value The value to clean
     * @return Cleaner Returns self for method chaining
     */
    public function set($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Returns the cleaned value from the fluent chain.
     * 
     * @return mixed The cleaned value
     */
    public function get()
    {
        return $this->value;
    }

    /**
     * Starts a security-focused cleaning chain.
     * 
     * @param mixed $value Value to clean securely
     * @return Cleaner Returns self for method chaining
     */
    public function security($value)
    {
        $this->value = $value;
        return $this;
    }

    // ===============================================================
    // CORE SECURITY METHODS
    // ===============================================================

    /**
     * Removes XSS (Cross-Site Scripting) threats from input.
     * 
     * Comprehensive XSS protection that removes dangerous HTML tags,
     * JavaScript code, event handlers, and malicious attributes while
     * preserving safe content structure.
     * 
     * @param string|null $input Input to clean (uses fluent value if null)
     * @return string|Cleaner Cleaned string or self for chaining
     */
    public function removeXSS($input = null)
    {
        $value = $input ?? $this->value;

        if (!is_string($value)) {
            return $input === null ? $this : $value;
        }

        // Remove dangerous patterns
        foreach ($this->dangerousPatterns as $pattern) {
            $value = preg_replace($pattern, '', $value);
        }

        // Remove null bytes and control characters
        $value = str_replace(["\0", "\x0B"], '', $value);

        // Remove invisible characters that could hide malicious code
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);

        // Decode HTML entities to catch encoded attacks
        $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');

        // Remove dangerous protocols
        $value = preg_replace('/(?:javascript|vbscript|data|about|chrome|file):/i', '', $value);

        if ($input === null) {
            $this->value = $value;
            return $this;
        }

        return $value;
    }

    /**
     * Removes characters commonly used in SQL injection attacks.
     * 
     * @param string|null $input Input to clean
     * @return string|Cleaner Cleaned string or self for chaining
     */
    public function removeSQLChars($input = null)
    {
        $value = $input ?? $this->value;

        if (!is_string($value)) {
            return $input === null ? $this : $value;
        }

        // Remove SQL injection characters and patterns
        $dangerous = [
            "'",
            '"',
            ';',
            '--',
            '/*',
            '*/',
            'UNION',
            'SELECT',
            'INSERT',
            'DELETE',
            'UPDATE',
            'DROP',
            'CREATE',
            'ALTER',
            'EXEC',
            'EXECUTE'
        ];

        $value = str_ireplace($dangerous, '', $value);

        if ($input === null) {
            $this->value = $value;
            return $this;
        }

        return $value;
    }

    /**
     * Escapes HTML characters for safe output.
     * 
     * @param string|null $input Input to escape
     * @return string|Cleaner Escaped string or self for chaining
     */
    public function escapeHTML($input = null)
    {
        $value = $input ?? $this->value;

        if (!is_string($value)) {
            return $input === null ? $this : $value;
        }

        $value = htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if ($input === null) {
            $this->value = $value;
            return $this;
        }

        return $value;
    }

    /**
     * Cleans HTML while preserving safe tags.
     * 
     * @param string|null $input Input to clean
     * @param string $level Safety level: 'basic', 'extended', 'full'
     * @return string|Cleaner Cleaned HTML or self for chaining
     */
    public function cleanHTML($input = null, $level = 'basic')
    {
        $value = $input ?? $this->value;

        if (!is_string($value)) {
            return $input === null ? $this : $value;
        }

        // First remove XSS threats
        $value = $this->removeXSS($value);

        // Get allowed tags for this level
        $allowedTags = $this->allowedTags[$level] ?? $this->allowedTags['basic'];

        // Create allowed tags string for strip_tags
        $allowedString = '<' . implode('><', $allowedTags) . '>';

        // Strip disallowed tags
        $value = strip_tags($value, $allowedString);

        // Remove dangerous attributes from remaining tags
        $value = preg_replace('/(<[^>]+)(on\w+|style|class|id)\s*=\s*["\'][^"\']*["\']([^>]*>)/i', '$1$3', $value);

        if ($input === null) {
            $this->value = $value;
            return $this;
        }

        return $value;
    }

    // ===============================================================
    // TEXT PROCESSING METHODS
    // ===============================================================

    /**
     * Smart trimming that handles multiple whitespace types.
     * 
     * @param string|null $input Input to trim
     * @return string|Cleaner Trimmed string or self for chaining
     */
    public function trimSmart($input = null)
    {
        $value = $input ?? $this->value;

        if (!is_string($value)) {
            return $input === null ? $this : $value;
        }

        // Remove various whitespace characters from beginning and end
        $value = preg_replace('/^[\s\x00-\x20\x7F\xA0\x{2000}-\x{200F}\x{2028}-\x{202F}\x{205F}-\x{206F}\x{FEFF}]+|[\s\x00-\x20\x7F\xA0\x{2000}-\x{200F}\x{2028}-\x{202F}\x{205F}-\x{206F}\x{FEFF}]+$/u', '', $value);

        if ($input === null) {
            $this->value = $value;
            return $this;
        }

        return $value;
    }

    /**
     * Normalizes spaces by replacing multiple spaces with single spaces.
     * 
     * @param string|null $input Input to normalize
     * @return string|Cleaner Normalized string or self for chaining
     */
    public function normalizeSpaces($input = null)
    {
        $value = $input ?? $this->value;

        if (!is_string($value)) {
            return $input === null ? $this : $value;
        }

        // Replace multiple whitespace characters with single space
        $value = preg_replace('/\s+/', ' ', $value);

        if ($input === null) {
            $this->value = $value;
            return $this;
        }

        return $value;
    }

    /**
     * Limits string length with smart truncation.
     * 
     * @param int $length Maximum length
     * @param string $suffix Suffix to add when truncated
     * @param string|null $input Input to limit
     * @return string|Cleaner Limited string or self for chaining
     */
    public function limitLength($length, $suffix = '...', $input = null)
    {
        $value = $input ?? $this->value;

        if (!is_string($value)) {
            return $input === null ? $this : $value;
        }

        if (mb_strlen($value, 'UTF-8') <= $length) {
            if ($input === null) {
                return $this;
            }
            return $value;
        }

        // Find last space before limit to avoid cutting words
        $truncated = mb_substr($value, 0, $length - mb_strlen($suffix, 'UTF-8'), 'UTF-8');
        $lastSpace = mb_strrpos($truncated, ' ', 0, 'UTF-8');

        if ($lastSpace !== false && $lastSpace > $length * 0.7) {
            $truncated = mb_substr($truncated, 0, $lastSpace, 'UTF-8');
        }

        $value = $truncated . $suffix;

        if ($input === null) {
            $this->value = $value;
            return $this;
        }

        return $value;
    }

    /**
     * Normalizes Unicode characters.
     * 
     * @param string|null $input Input to normalize
     * @param string $form Normalization form (NFC, NFD, NFKC, NFKD)
     * @return string|Cleaner Normalized string or self for chaining
     */
    public function normalizeUnicode($input = null, $form = 'NFC')
    {
        $value = $input ?? $this->value;

        if (!is_string($value)) {
            return $input === null ? $this : $value;
        }

        if (class_exists('Normalizer')) {
            $forms = [
                'NFC' => Normalizer::FORM_C,
                'NFD' => Normalizer::FORM_D,
                'NFKC' => Normalizer::FORM_KC,
                'NFKD' => Normalizer::FORM_KD
            ];

            $value = Normalizer::normalize($value, $forms[$form] ?? Normalizer::FORM_C);
        }

        if ($input === null) {
            $this->value = $value;
            return $this;
        }

        return $value;
    }

    // ===============================================================
    // FORMAT-SPECIFIC CLEANING METHODS
    // ===============================================================

    /**
     * Cleans and validates email addresses.
     * 
     * @param string|null $input Email to clean
     * @return string|Cleaner Cleaned email or self for chaining
     */
    public function cleanEmail($input = null)
    {
        $value = $input ?? $this->value;

        if (!is_string($value)) {
            return $input === null ? $this : $value;
        }

        // Remove whitespace and convert to lowercase
        $value = strtolower(trim($value));

        // Remove dangerous characters
        $value = preg_replace('/[^\w@.\-+]/', '', $value);

        // Validate basic email format
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $value = '';
        }

        if ($input === null) {
            $this->value = $value;
            return $this;
        }

        return $value;
    }

    /**
     * Formats phone numbers according to country standards.
     * 
     * @param string|null $input Phone number to format
     * @param string $country Country code (US, UK, etc.)
     * @return string|Cleaner Formatted phone or self for chaining
     */
    public function formatPhone($input = null, $country = 'US')
    {
        $value = $input ?? $this->value;

        if (!is_string($value)) {
            return $input === null ? $this : $value;
        }

        // Extract only digits
        $digits = preg_replace('/[^0-9]/', '', $value);

        // Format based on country
        switch (strtoupper($country)) {
            case 'US':
                if (strlen($digits) === 10) {
                    $value = sprintf(
                        '(%s) %s-%s',
                        substr($digits, 0, 3),
                        substr($digits, 3, 3),
                        substr($digits, 6, 4)
                    );
                } elseif (strlen($digits) === 11 && $digits[0] === '1') {
                    $value = sprintf(
                        '+1 (%s) %s-%s',
                        substr($digits, 1, 3),
                        substr($digits, 4, 3),
                        substr($digits, 7, 4)
                    );
                } else {
                    $value = $digits; // Return digits if format doesn't match
                }
                break;

            case 'UK':
                // Basic UK format - can be expanded
                if (strlen($digits) === 11 && substr($digits, 0, 2) === '07') {
                    $value = sprintf(
                        '%s %s %s',
                        substr($digits, 0, 5),
                        substr($digits, 5, 3),
                        substr($digits, 8, 3)
                    );
                } else {
                    $value = $digits;
                }
                break;

            default:
                $value = $digits;
        }

        if ($input === null) {
            $this->value = $value;
            return $this;
        }

        return $value;
    }

    /**
     * Sanitizes file names for safe storage.
     * 
     * @param string|null $input Filename to sanitize
     * @param int $maxLength Maximum filename length
     * @return string|Cleaner Safe filename or self for chaining
     */
    public function sanitizeFilename($input = null, $maxLength = 255)
    {
        $value = $input ?? $this->value;

        if (!is_string($value)) {
            return $input === null ? $this : $value;
        }

        // Remove path separators and dangerous characters
        $value = preg_replace('/[\/\\\:*?"<>|]/', '', $value);

        // Remove control characters
        $value = preg_replace('/[\x00-\x1F\x7F]/', '', $value);

        // Remove leading/trailing dots and spaces
        $value = trim($value, '. ');

        // Prevent reserved names on Windows
        $reserved = ['CON', 'PRN', 'AUX', 'NUL', 'COM1', 'COM2', 'COM3', 'COM4', 'COM5', 'COM6', 'COM7', 'COM8', 'COM9', 'LPT1', 'LPT2', 'LPT3', 'LPT4', 'LPT5', 'LPT6', 'LPT7', 'LPT8', 'LPT9'];

        $name = pathinfo($value, PATHINFO_FILENAME);
        if (in_array(strtoupper($name), $reserved)) {
            $value = '_' . $value;
        }

        // Limit length
        if (strlen($value) > $maxLength) {
            $extension = pathinfo($value, PATHINFO_EXTENSION);
            $name = pathinfo($value, PATHINFO_FILENAME);
            $name = substr($name, 0, $maxLength - strlen($extension) - 1);
            $value = $name . '.' . $extension;
        }

        // Ensure we have a filename
        if (empty($value)) {
            $value = 'unnamed_file';
        }

        if ($input === null) {
            $this->value = $value;
            return $this;
        }

        return $value;
    }

    /**
     * Cleans and validates URLs.
     * 
     * @param string|null $input URL to clean
     * @param bool $requireProtocol Whether to require http/https protocol
     * @return string|Cleaner Cleaned URL or self for chaining
     */
    public function cleanURL($input = null, $requireProtocol = true)
    {
        $value = $input ?? $this->value;

        if (!is_string($value)) {
            return $input === null ? $this : $value;
        }

        $value = trim($value);

        // Add protocol if missing and required
        if ($requireProtocol && !preg_match('/^https?:\/\//', $value)) {
            $value = 'http://' . $value;
        }

        // Validate URL
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $value = '';
        }

        if ($input === null) {
            $this->value = $value;
            return $this;
        }

        return $value;
    }

    // ===============================================================
    // LEGACY CHARACTER FILTERING METHODS (ENHANCED)
    // ===============================================================

    /**
     * Enhanced alphabetic character filtering with Unicode support.
     * 
     * @param string $input Input string to clean
     * @param bool $allowUnicode Whether to allow Unicode letters
     * @return string String containing only alphabetic characters
     */
    public function alphaOnly($input, $allowUnicode = false)
    {
        if ($allowUnicode) {
            return preg_replace('/[^\p{L}]/u', '', $input);
        }
        return preg_replace('/[^A-Za-z]/', '', $input);
    }

    /**
     * Enhanced alphanumeric filtering with Unicode support.
     * 
     * @param string $input Input string to clean
     * @param bool $allowUnicode Whether to allow Unicode letters
     * @return string String containing only letters and numbers
     */
    public function alphaNumericOnly($input, $allowUnicode = false)
    {
        if ($allowUnicode) {
            return preg_replace('/[^\p{L}\p{N}]/u', '', $input);
        }
        return preg_replace('/[^A-Za-z0-9]/', '', $input);
    }

    /**
     * Enhanced numeric filtering with decimal support.
     * 
     * @param string $input Input string to clean
     * @param bool $allowDecimals Whether to preserve decimal points
     * @param bool $allowNegative Whether to preserve negative signs
     * @return string String containing only numeric characters
     */
    public function numericOnly($input, $allowDecimals = false, $allowNegative = false)
    {
        $pattern = '[^0-9';
        if ($allowDecimals)
            $pattern .= '.';
        if ($allowNegative)
            $pattern .= '-';
        $pattern .= ']';

        return preg_replace('/' . $pattern . '/', '', $input);
    }

    /**
     * Enhanced alphanumeric with dashes filtering.
     * 
     * @param string $input Input string to clean
     * @param bool $allowSpaces Whether to preserve spaces
     * @return string Cleaned string
     */
    public function alphaNumericDashesOnly($input, $allowSpaces = false)
    {
        $pattern = $allowSpaces ? '/[^A-Za-z0-9\s-]/' : '/[^A-Za-z0-9-]/';
        return preg_replace($pattern, '', $input);
    }

    /**
     * Enhanced alphanumeric with dashes and underscores.
     * 
     * @param string $input Input string to clean
     * @param bool $allowSpaces Whether to preserve spaces
     * @return string Cleaned string
     */
    public function alphaNumericDashesUnderscoresOnly($input, $allowSpaces = false)
    {
        $pattern = $allowSpaces ? '/[^A-Za-z0-9\s_-]/' : '/[^A-Za-z0-9_-]/';
        return preg_replace($pattern, '', $input);
    }

    // ===============================================================
    // BATCH PROCESSING METHODS
    // ===============================================================

    /**
     * Cleans multiple fields using rule strings.
     * 
     * @param array $rules Cleaning rules for each field
     * @param array $data Data to clean
     * @return array Cleaned data
     */
    public function cleanBatch($rules, $data)
    {
        $cleaned = [];

        foreach ($rules as $field => $ruleString) {
            $value = $data[$field] ?? '';
            $rules = explode('|', $ruleString);

            foreach ($rules as $rule) {
                if (strpos($rule, ':') !== false) {
                    [$ruleName, $parameters] = explode(':', $rule, 2);
                    $parameters = explode(',', $parameters);
                } else {
                    $ruleName = $rule;
                    $parameters = [];
                }

                $value = $this->executeCleaningRule($value, $ruleName, $parameters);
            }

            $cleaned[$field] = $value;
        }

        return $cleaned;
    }

    /**
     * Executes a single cleaning rule.
     * 
     * @param mixed $value Value to clean
     * @param string $rule Rule name
     * @param array $parameters Rule parameters
     * @return mixed Cleaned value
     */
    private function executeCleaningRule($value, $rule, $parameters)
    {
        switch ($rule) {
            case 'trim':
                return is_string($value) ? trim($value) : $value;

            case 'trim_smart':
                return $this->trimSmart($value);

            case 'lowercase':
                return is_string($value) ? strtolower($value) : $value;

            case 'uppercase':
                return is_string($value) ? strtoupper($value) : $value;

            case 'normalize_spaces':
                return $this->normalizeSpaces($value);

            case 'remove_xss':
                return $this->removeXSS($value);

            case 'remove_sql':
                return $this->removeSQLChars($value);

            case 'escape_html':
                return $this->escapeHTML($value);

            case 'clean_html':
                $level = $parameters[0] ?? 'basic';
                return $this->cleanHTML($value, $level);

            case 'clean_email':
                return $this->cleanEmail($value);

            case 'format_phone':
                $country = $parameters[0] ?? 'US';
                return $this->formatPhone($value, $country);

            case 'sanitize_filename':
                $maxLength = (int) ($parameters[0] ?? 255);
                return $this->sanitizeFilename($value, $maxLength);

            case 'clean_url':
                $requireProtocol = ($parameters[0] ?? 'true') === 'true';
                return $this->cleanURL($value, $requireProtocol);

            case 'alpha_only':
                $allowUnicode = ($parameters[0] ?? 'false') === 'true';
                return $this->alphaOnly($value, $allowUnicode);

            case 'numeric_only':
                $allowDecimals = ($parameters[0] ?? 'false') === 'true';
                $allowNegative = ($parameters[1] ?? 'false') === 'true';
                return $this->numericOnly($value, $allowDecimals, $allowNegative);

            case 'limit':
                $length = (int) ($parameters[0] ?? 100);
                $suffix = $parameters[1] ?? '...';
                return $this->limitLength($length, $suffix, $value);

            default:
                // Check for custom rules
                if (isset($this->customRules[$rule])) {
                    return call_user_func($this->customRules[$rule], $value, $parameters);
                }
                return $value;
        }
    }

    // ===============================================================
    // CUSTOM RULES AND EXTENSIBILITY
    // ===============================================================

    /**
     * Registers a custom cleaning rule.
     * 
     * @param string $name Rule name
     * @param callable $callback Cleaning function
     */
    public function addRule($name, $callback)
    {
        $this->customRules[$name] = $callback;
    }

    // ===============================================================
    // UTILITY METHODS
    // ===============================================================

    /**
     * Applies multiple cleaning operations in sequence.
     * 
     * @param mixed $value Value to clean
     * @param array $operations Array of cleaning method names
     * @return mixed Cleaned value
     */
    public function pipe($value, $operations)
    {
        $this->set($value);

        foreach ($operations as $operation) {
            if (is_array($operation)) {
                $method = array_shift($operation);
                $this->$method(...$operation);
            } else {
                $this->$operation();
            }
        }

        return $this->get();
    }
}
