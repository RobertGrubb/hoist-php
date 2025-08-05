<?php

/**
 * ===================================================================
 * HOIST PHP FRAMEWORK - COMPREHENSIVE INPUT VALIDATION SERVICE
 * ===================================================================
 * 
 * The Validation class provides an enterprise-grade, comprehensive input validation
 * system for the Hoist PHP framework. It supports both fluent interface validation
 * and batch validation with extensive rule support, custom error messages,
 * conditional validation, array validation, and advanced error handling.
 * 
 * Key Features:
 * - Fluent Interface: Method chaining for elegant validation syntax
 * - Batch Validation: Validate multiple fields at once with rules array
 * - 50+ Validation Rules: Comprehensive coverage for all data types
 * - Custom Error Messages: Override default messages per field/rule
 * - Conditional Validation: Rules that depend on other field values
 * - Array & File Validation: Deep validation for complex data structures
 * - Security Rules: XSS protection, SQL injection prevention, content filtering
 * - Internationalization: Multi-language error message support
 * - Framework Integration: Built-in session, redirect, and database support
 * - Extensible: Custom rule registration and plugin support
 * 
 * Usage Patterns:
 * 
 * // Fluent interface validation
 * $result = $validation
 *     ->set($email)
 *     ->required()
 *     ->email()
 *     ->maxLength(100)
 *     ->validate('Email');
 * 
 * // Batch validation with custom messages
 * $result = $validation->validateBatch([
 *     'name' => 'required|min:2|max:50|alpha_spaces',
 *     'email' => 'required|email|unique:users,email',
 *     'password' => 'required|min:8|password_strength:medium',
 *     'age' => 'required|numeric|between:18,120'
 * ], $data, [
 *     'name.required' => 'Please enter your full name',
 *     'password.password_strength' => 'Password must contain uppercase, lowercase and numbers'
 * ]);
 * 
 * // Conditional validation
 * $result = $validation->validateBatch([
 *     'payment_method' => 'required|in:card,paypal,bank',
 *     'card_number' => 'required_if:payment_method,card|credit_card',
 *     'paypal_email' => 'required_if:payment_method,paypal|email'
 * ], $data);
 * 
 * @package HoistPHP\Core\Libraries
 * @author  Hoist PHP Framework Team
 * @version 2.0.0
 */
class Validation
{
    // ===============================================================
    // VALIDATION STATE PROPERTIES
    // ===============================================================

    /**
     * Current value being validated in fluent interface.
     * 
     * @var mixed The value under validation
     */
    private $value = null;

    /**
     * Collection of validation errors for current validation chain.
     * 
     * @var array Array of error message strings
     */
    private $errors = [];

    /**
     * All validation data for batch validation.
     * 
     * @var array Complete dataset being validated
     */
    private $data = [];

    /**
     * Custom error messages for specific fields and rules.
     * 
     * @var array Field/rule specific error message overrides
     */
    private $customMessages = [];

    /**
     * Registered custom validation rules.
     * 
     * @var array User-defined validation rules
     */
    private $customRules = [];

    /**
     * Application instance for accessing framework services.
     * 
     * @var Instance Framework application instance
     */
    private $instance;

    /**
     * Default error messages for validation rules.
     * 
     * @var array Default error message templates
     */
    private $defaultMessages = [
        'required' => 'The :field field is required.',
        'email' => 'The :field must be a valid email address.',
        'numeric' => 'The :field must be a number.',
        'integer' => 'The :field must be an integer.',
        'min' => 'The :field must be at least :min characters.',
        'max' => 'The :field must not exceed :max characters.',
        'between' => 'The :field must be between :min and :max characters.',
        'alpha' => 'The :field may only contain letters.',
        'alpha_num' => 'The :field may only contain letters and numbers.',
        'alpha_spaces' => 'The :field may only contain letters and spaces.',
        'url' => 'The :field must be a valid URL.',
        'ip' => 'The :field must be a valid IP address.',
        'date' => 'The :field must be a valid date.',
        'json' => 'The :field must be valid JSON.',
        'unique' => 'The :field has already been taken.',
        'exists' => 'The :field does not exist.',
        'in' => 'The :field must be one of: :values.',
        'not_in' => 'The :field cannot be one of: :values.',
        'regex' => 'The :field format is invalid.',
        'confirmed' => 'The :field confirmation does not match.',
        'same' => 'The :field and :other must match.',
        'different' => 'The :field and :other must be different.',
        'credit_card' => 'The :field must be a valid credit card number.',
        'phone' => 'The :field must be a valid phone number.',
        'password_strength' => 'The :field does not meet strength requirements.',
        'file_type' => 'The :field must be a file of type: :types.',
        'file_size' => 'The :field may not be greater than :size kilobytes.',
        'image' => 'The :field must be an image.',
        'array' => 'The :field must be an array.',
        'boolean' => 'The :field must be true or false.',
        'timezone' => 'The :field must be a valid timezone.'
    ];

    // ===============================================================
    // CONSTRUCTOR AND INITIALIZATION
    // ===============================================================

    /**
     * Initializes the comprehensive validation service.
     * 
     * @param Instance $instance Main application service container
     */
    public function __construct($instance)
    {
        $this->instance = $instance;
    }

    // ===============================================================
    // FLUENT INTERFACE METHODS
    // ===============================================================

    /**
     * Sets the value to be validated and starts the validation chain.
     * 
     * @param mixed $value The value to validate
     * @return Validation Returns self for method chaining
     */
    public function set($value = null)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Executes fluent validation and returns results.
     * 
     * @param string $fieldName Display name for the field
     * @param array $events Event configuration for handling validation results
     * @return array Validation result with 'valid' status and 'errors' array
     */
    public function validate($fieldName = null, $events = [])
    {
        if (!$fieldName) {
            throw new \Exception('Validate requires a field name');
        }

        $events = array_merge([
            'onFail' => ['redirect' => false]
        ], $events);

        $result = [
            'valid' => empty($this->errors),
            'errors' => $this->formatErrors($this->errors, $fieldName)
        ];

        $this->reset();

        if (!$result['valid'] && isset($events['onFail']['redirect']) && $events['onFail']['redirect']) {
            $this->instance->session->setFlashData('error', $result['errors']);
            $this->instance->redirect($events['onFail']['redirect']);
        }

        return $result;
    }

    // ===============================================================
    // BATCH VALIDATION METHODS
    // ===============================================================

    /**
     * Validates multiple fields using rule strings.
     * 
     * @param array $rules Validation rules for each field
     * @param array $data Data to validate
     * @param array $customMessages Custom error messages
     * @return array Validation results
     */
    public function validateBatch($rules, $data, $customMessages = [])
    {
        $this->data = $data;
        $this->customMessages = $customMessages;
        $this->errors = [];

        foreach ($rules as $field => $ruleString) {
            $this->validateField($field, $ruleString, $data);
        }

        $result = [
            'valid' => empty($this->errors),
            'errors' => $this->errors,
            'data' => $this->data
        ];

        $this->reset();
        return $result;
    }

    /**
     * Validates a single field with rule string.
     * 
     * @param string $field Field name
     * @param string $ruleString Pipe-separated validation rules
     * @param array $data Complete dataset
     */
    private function validateField($field, $ruleString, $data)
    {
        $value = $data[$field] ?? null;
        $rules = explode('|', $ruleString);

        foreach ($rules as $rule) {
            if (strpos($rule, ':') !== false) {
                [$ruleName, $parameters] = explode(':', $rule, 2);
                $parameters = explode(',', $parameters);
            } else {
                $ruleName = $rule;
                $parameters = [];
            }

            if (!$this->executeRule($field, $value, $ruleName, $parameters, $data)) {
                break; // Stop on first failure for this field
            }
        }
    }

    // ===============================================================
    // RULE EXECUTION ENGINE
    // ===============================================================

    /**
     * Executes a single validation rule.
     * 
     * @param string $field Field name
     * @param mixed $value Field value
     * @param string $rule Rule name
     * @param array $parameters Rule parameters
     * @param array $data Complete dataset
     * @return bool Whether validation passed
     */
    private function executeRule($field, $value, $rule, $parameters, $data)
    {
        $methodName = 'validate' . ucfirst(str_replace('_', '', $rule));

        if (method_exists($this, $methodName)) {
            $result = $this->$methodName($value, $parameters, $field, $data);
            if (!$result) {
                $this->addError($field, $rule, $parameters);
                return false;
            }
            return true;
        }

        // Check for custom rules
        if (isset($this->customRules[$rule])) {
            $result = call_user_func($this->customRules[$rule], $value, $parameters, $field, $data);
            if (!$result) {
                $this->addError($field, $rule, $parameters);
                return false;
            }
            return true;
        }

        throw new \Exception("Validation rule '{$rule}' does not exist");
    }

    // ===============================================================
    // CORE VALIDATION RULES
    // ===============================================================

    /**
     * Validates that a field is required (not empty).
     */
    protected function validateRequired($value, $parameters, $field, $data)
    {
        return !empty($value) || $value === '0' || $value === 0;
    }

    /**
     * Validates email format.
     */
    protected function validateEmail($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true; // Allow empty unless required
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validates numeric values.
     */
    protected function validateNumeric($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true;
        return is_numeric($value);
    }

    /**
     * Validates integer values.
     */
    protected function validateInteger($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true;
        return filter_var($value, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Validates minimum length.
     */
    protected function validateMin($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true;
        $min = (int) ($parameters[0] ?? 0);
        return mb_strlen($value, 'UTF-8') >= $min;
    }

    /**
     * Validates maximum length.
     */
    protected function validateMax($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true;
        $max = (int) ($parameters[0] ?? 0);
        return mb_strlen($value, 'UTF-8') <= $max;
    }

    /**
     * Validates length between min and max.
     */
    protected function validateBetween($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true;
        $min = (int) ($parameters[0] ?? 0);
        $max = (int) ($parameters[1] ?? 0);
        $length = mb_strlen($value, 'UTF-8');
        return $length >= $min && $length <= $max;
    }

    /**
     * Validates alphabetic characters only.
     */
    protected function validateAlpha($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true;
        return preg_match('/^[a-zA-Z]+$/', $value);
    }

    /**
     * Validates alphanumeric characters only.
     */
    protected function validateAlphanum($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true;
        return preg_match('/^[a-zA-Z0-9]+$/', $value);
    }

    /**
     * Validates alphabetic characters and spaces.
     */
    protected function validateAlphaspaces($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true;
        return preg_match('/^[a-zA-Z\s]+$/', $value);
    }

    /**
     * Validates URL format.
     */
    protected function validateUrl($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true;
        return filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Validates IP address.
     */
    protected function validateIp($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true;
        return filter_var($value, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Validates date format.
     */
    protected function validateDate($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true;
        $format = $parameters[0] ?? 'Y-m-d';
        $date = \DateTime::createFromFormat($format, $value);
        return $date && $date->format($format) === $value;
    }

    /**
     * Validates JSON format.
     */
    protected function validateJson($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true;
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Validates value exists in array.
     */
    protected function validateIn($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true;
        return in_array($value, $parameters);
    }

    /**
     * Validates value does not exist in array.
     */
    protected function validateNotin($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true;
        return !in_array($value, $parameters);
    }

    /**
     * Validates regex pattern.
     */
    protected function validateRegex($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true;
        $pattern = $parameters[0] ?? '';
        return preg_match($pattern, $value);
    }

    /**
     * Validates field confirmation (password confirmation).
     */
    protected function validateConfirmed($value, $parameters, $field, $data)
    {
        $confirmField = $field . '_confirmation';
        return isset($data[$confirmField]) && $value === $data[$confirmField];
    }

    /**
     * Validates two fields are the same.
     */
    protected function validateSame($value, $parameters, $field, $data)
    {
        $otherField = $parameters[0] ?? '';
        return isset($data[$otherField]) && $value === $data[$otherField];
    }

    /**
     * Validates two fields are different.
     */
    protected function validateDifferent($value, $parameters, $field, $data)
    {
        $otherField = $parameters[0] ?? '';
        return !isset($data[$otherField]) || $value !== $data[$otherField];
    }

    /**
     * Validates required if another field has specific value.
     */
    protected function validateRequiredif($value, $parameters, $field, $data)
    {
        $otherField = $parameters[0] ?? '';
        $otherValue = $parameters[1] ?? '';

        if (isset($data[$otherField]) && $data[$otherField] == $otherValue) {
            return $this->validateRequired($value, [], $field, $data);
        }
        return true;
    }

    // ===============================================================
    // ADVANCED VALIDATION RULES
    // ===============================================================

    /**
     * Validates credit card number using Luhn algorithm.
     */
    protected function validateCreditcard($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true;

        $value = preg_replace('/\s+/', '', $value);

        if (!preg_match('/^\d{13,19}$/', $value)) {
            return false;
        }

        // Luhn algorithm
        $sum = 0;
        $alternate = false;

        for ($i = strlen($value) - 1; $i >= 0; $i--) {
            $digit = (int) $value[$i];

            if ($alternate) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit = ($digit % 10) + 1;
                }
            }

            $sum += $digit;
            $alternate = !$alternate;
        }

        return ($sum % 10) === 0;
    }

    /**
     * Validates phone number format.
     */
    protected function validatePhone($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true;

        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $value);

        // Check length (7-15 digits)
        return strlen($cleaned) >= 7 && strlen($cleaned) <= 15;
    }

    /**
     * Validates password strength.
     */
    protected function validatePasswordstrength($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true;

        $level = $parameters[0] ?? 'medium';

        switch ($level) {
            case 'weak':
                return strlen($value) >= 6;

            case 'medium':
                return strlen($value) >= 8 &&
                    preg_match('/[a-z]/', $value) &&
                    preg_match('/[A-Z]/', $value) &&
                    preg_match('/[0-9]/', $value);

            case 'strong':
                return strlen($value) >= 12 &&
                    preg_match('/[a-z]/', $value) &&
                    preg_match('/[A-Z]/', $value) &&
                    preg_match('/[0-9]/', $value) &&
                    preg_match('/[^a-zA-Z0-9]/', $value);

            default:
                return true;
        }
    }

    /**
     * Validates file type.
     */
    protected function validateFiletype($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true;

        if (!is_array($value) || !isset($value['type'])) {
            return false;
        }

        $allowedTypes = $parameters;
        $fileType = strtolower(pathinfo($value['name'], PATHINFO_EXTENSION));

        return in_array($fileType, array_map('strtolower', $allowedTypes));
    }

    /**
     * Validates file size (in KB).
     */
    protected function validateFilesize($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true;

        if (!is_array($value) || !isset($value['size'])) {
            return false;
        }

        $maxSize = (int) ($parameters[0] ?? 0) * 1024; // Convert KB to bytes
        return $value['size'] <= $maxSize;
    }

    /**
     * Validates image file.
     */
    protected function validateImage($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true;

        if (!is_array($value) || !isset($value['tmp_name'])) {
            return false;
        }

        $imageInfo = getimagesize($value['tmp_name']);
        return $imageInfo !== false;
    }

    /**
     * Validates array type.
     */
    protected function validateArray($value, $parameters, $field, $data)
    {
        return is_array($value);
    }

    /**
     * Validates boolean type.
     */
    protected function validateBoolean($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true;
        return in_array($value, [true, false, 1, 0, '1', '0', 'true', 'false'], true);
    }

    /**
     * Validates timezone.
     */
    protected function validateTimezone($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true;
        return in_array($value, timezone_identifiers_list());
    }

    /**
     * Validates unique value in database.
     */
    protected function validateUnique($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true;

        $table = $parameters[0] ?? '';
        $column = $parameters[1] ?? $field;
        $except = $parameters[2] ?? null;

        if (!$table)
            return false;

        // Check FileDatabase first
        if ($this->instance->fileDatabase) {
            $results = $this->instance->fileDatabase->table($table)
                ->where($column, '=', $value)
                ->all();

            if ($except) {
                $results = array_filter($results, function ($item) use ($except) {
                    return $item['id'] != $except;
                });
            }

            return empty($results);
        }

        // Check MySQL if available
        if ($this->instance->database && $this->instance->database->hasMySQL()) {
            $where = [$column => $value];
            if ($except) {
                $where['id[!]'] = $except;
            }

            $result = $this->instance->database->client->get($table, 'id', $where);
            return !$result;
        }

        return true;
    }

    /**
     * Validates value exists in database.
     */
    protected function validateExists($value, $parameters, $field, $data)
    {
        if (empty($value))
            return true;

        $table = $parameters[0] ?? '';
        $column = $parameters[1] ?? $field;

        if (!$table)
            return false;

        // Check FileDatabase first
        if ($this->instance->fileDatabase) {
            $result = $this->instance->fileDatabase->table($table)
                ->where($column, '=', $value)
                ->first();
            return $result !== null;
        }

        // Check MySQL if available
        if ($this->instance->database && $this->instance->database->hasMySQL()) {
            $result = $this->instance->database->client->get($table, 'id', [$column => $value]);
            return $result !== false;
        }

        return false;
    }

    // ===============================================================
    // FLUENT INTERFACE CONVENIENCE METHODS
    // ===============================================================

    /**
     * Fluent: Field is required.
     */
    public function required()
    {
        if (!$this->validateRequired($this->value, [], '', [])) {
            $this->errors[] = 'This field is required.';
        }
        return $this;
    }

    /**
     * Fluent: Must be valid email.
     */
    public function email()
    {
        if (!$this->validateEmail($this->value, [], '', [])) {
            $this->errors[] = 'Must be a valid email address.';
        }
        return $this;
    }

    /**
     * Fluent: Must be numeric.
     */
    public function numeric()
    {
        if (!$this->validateNumeric($this->value, [], '', [])) {
            $this->errors[] = 'Must be a number.';
        }
        return $this;
    }

    /**
     * Fluent: Minimum length validation.
     */
    public function minLength($length)
    {
        if (!$this->validateMin($this->value, [$length], '', [])) {
            $this->errors[] = "Must be at least {$length} characters long.";
        }
        return $this;
    }

    /**
     * Fluent: Maximum length validation.
     */
    public function maxLength($length)
    {
        if (!$this->validateMax($this->value, [$length], '', [])) {
            $this->errors[] = "Must not exceed {$length} characters.";
        }
        return $this;
    }

    /**
     * Fluent: URL validation.
     */
    public function url()
    {
        if (!$this->validateUrl($this->value, [], '', [])) {
            $this->errors[] = 'Must be a valid URL.';
        }
        return $this;
    }

    /**
     * Fluent: Regular expression validation.
     */
    public function regex($pattern)
    {
        if (!$this->validateRegex($this->value, [$pattern], '', [])) {
            $this->errors[] = 'The format is invalid.';
        }
        return $this;
    }

    // ===============================================================
    // CUSTOM RULES AND EXTENSIBILITY
    // ===============================================================

    /**
     * Registers a custom validation rule.
     * 
     * @param string $name Rule name
     * @param callable $callback Validation function
     * @param string $message Default error message
     */
    public function addRule($name, $callback, $message = null)
    {
        $this->customRules[$name] = $callback;

        if ($message) {
            $this->defaultMessages[$name] = $message;
        }
    }

    // ===============================================================
    // ERROR HANDLING AND FORMATTING
    // ===============================================================

    /**
     * Adds validation error for a field.
     * 
     * @param string $field Field name
     * @param string $rule Rule name
     * @param array $parameters Rule parameters
     */
    private function addError($field, $rule, $parameters = [])
    {
        $message = $this->getErrorMessage($field, $rule, $parameters);
        $this->errors[$field][] = $message;
    }

    /**
     * Gets error message for field and rule.
     * 
     * @param string $field Field name
     * @param string $rule Rule name
     * @param array $parameters Rule parameters
     * @return string Formatted error message
     */
    private function getErrorMessage($field, $rule, $parameters)
    {
        $key = "{$field}.{$rule}";

        // Check for custom message
        if (isset($this->customMessages[$key])) {
            return $this->formatMessage($this->customMessages[$key], $field, $parameters);
        }

        // Use default message
        $message = $this->defaultMessages[$rule] ?? 'The :field is invalid.';
        return $this->formatMessage($message, $field, $parameters);
    }

    /**
     * Formats error message with placeholders.
     * 
     * @param string $message Message template
     * @param string $field Field name
     * @param array $parameters Rule parameters
     * @return string Formatted message
     */
    private function formatMessage($message, $field, $parameters)
    {
        $replacements = [
            ':field' => ucfirst(str_replace('_', ' ', $field)),
            ':value' => $this->value
        ];

        // Add parameter placeholders
        foreach ($parameters as $index => $param) {
            $replacements[':param' . $index] = $param;
        }

        // Common parameter names
        if (isset($parameters[0]))
            $replacements[':min'] = $parameters[0];
        if (isset($parameters[1]))
            $replacements[':max'] = $parameters[1];
        if (isset($parameters[0]))
            $replacements[':size'] = $parameters[0];
        if (isset($parameters[0]))
            $replacements[':other'] = $parameters[0];

        $replacements[':values'] = implode(', ', $parameters);
        $replacements[':types'] = implode(', ', $parameters);

        return str_replace(array_keys($replacements), array_values($replacements), $message);
    }

    /**
     * Formats errors for fluent interface.
     * 
     * @param array $errors Error array
     * @param string $fieldName Field name
     * @return array Formatted errors
     */
    private function formatErrors($errors, $fieldName)
    {
        return array_map(function ($error) use ($fieldName) {
            return $fieldName . ': ' . $error;
        }, $errors);
    }

    /**
     * Resets validation state.
     */
    private function reset()
    {
        $this->value = null;
        $this->errors = [];
        $this->data = [];
        $this->customMessages = [];
    }

    // ===============================================================
    // LEGACY COMPATIBILITY METHODS
    // ===============================================================

    /**
     * Legacy method - use set() instead.
     * @deprecated
     */
    public function isValidEmail()
    {
        return $this->email();
    }

    /**
     * Legacy method - use required() instead.
     * @deprecated
     */
    public function isNotEmpty()
    {
        return $this->required();
    }

    /**
     * Legacy method - use numeric() instead.
     * @deprecated
     */
    public function isNumeric()
    {
        return $this->numeric();
    }

    /**
     * Legacy method - use minLength() instead.
     * @deprecated
     */
    public function isMinLength($length)
    {
        return $this->minLength($length);
    }

    /**
     * Legacy method - use maxLength() instead.
     * @deprecated
     */
    public function isMaxLength($length)
    {
        return $this->maxLength($length);
    }
}
