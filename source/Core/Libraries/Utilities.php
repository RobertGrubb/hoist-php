<?php

/**
 * ===================================================================
 * HOIST PHP FRAMEWORK - UTILITY FUNCTIONS LIBRARY
 * ===================================================================
 * 
 * The Utilities class provides a comprehensive collection of static helper
 * methods for common programming tasks. These utilities cover a wide range
 * of functionality including string manipulation, data generation, HTTP
 * requests, time formatting, and data processing.
 * 
 * Key Features:
 * - UUID Generation: RFC 4122 compliant UUID v4 generation
 * - Random Data: Secure random string and password generation
 * - String Processing: Text formatting, truncation, and slugification
 * - HTTP Requests: CURL-based HTTP client functionality
 * - Time Formatting: Human-readable time difference calculations
 * - Data Manipulation: Array sorting and text processing
 * - URL Processing: Domain extraction and encoding
 * 
 * All methods are static and can be called directly without instantiation:
 * Utilities::uuidv4()
 * Utilities::timeAgo($date)
 * 
 * @package HoistPHP\Core\Libraries
 * @author  Hoist PHP Framework Team
 * @version 1.0.0
 */
class Utilities
{
    // ===============================================================
    // UNIQUE IDENTIFIER GENERATION
    // ===============================================================

    /**
     * Generates a RFC 4122 compliant UUID version 4.
     * 
     * Creates a universally unique identifier using cryptographically
     * secure random bytes. UUID v4 identifiers are randomly generated
     * and have extremely low probability of collision.
     * 
     * Format: xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
     * Where x is a random hexadecimal digit and y is random but with
     * specific bit constraints for RFC compliance.
     * 
     * Use cases:
     * - Database primary keys
     * - Session identifiers
     * - File naming
     * - API request tracking
     * - Unique reference generation
     * 
     * @return string A valid UUID v4 string (36 characters including hyphens)
     */
    public static function uuidv4()
    {
        $data = random_bytes(16);

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    // ===============================================================
    // URL AND STRING ENCODING
    // ===============================================================

    /**
     * Encodes a string for safe inclusion in URLs (JavaScript encodeURIComponent equivalent).
     * 
     * Performs URL encoding similar to JavaScript's encodeURIComponent function,
     * with special handling for certain characters that should remain unencoded
     * for better compatibility and readability.
     * 
     * Preserved characters: ! * ' ( )
     * These characters are often safe in URLs and preserving them improves readability.
     * 
     * Use cases:
     * - Query parameter encoding
     * - URL fragment encoding
     * - JavaScript compatibility
     * - API parameter encoding
     * 
     * @param string $str The string to encode for URL inclusion
     * @return string URL-encoded string safe for use in URLs
     */
    public static function encodeURIComponent($str)
    {
        $revert = array('%21' => '!', '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')');
        return strtr(rawurlencode($str), $revert);
    }

    // ===============================================================
    // RANDOM DATA GENERATION
    // ===============================================================

    /**
     * Generates a cryptographically secure random confirmation code.
     * 
     * Creates an 8-digit random number suitable for use as a confirmation
     * code, verification token, or temporary access code. Uses secure
     * random number generation to prevent prediction attacks.
     * 
     * Range: 11,111,112 to 99,999,999
     * 
     * Use cases:
     * - Email verification codes
     * - SMS confirmation codes
     * - Two-factor authentication tokens
     * - Password reset codes
     * - Account activation codes
     * 
     * @return int An 8-digit random confirmation code
     */
    public static function generateConfirmationCode()
    {
        return random_int(11111112, 99999999);
    }

    /**
     * Generates a secure random password with mixed character types.
     * 
     * Creates a strong password by combining:
     * - 17 characters from alphanumeric + basic symbols
     * - 3 additional special symbols for enhanced security
     * 
     * Total length: 20 characters
     * Character set includes: a-z, A-Z, 0-9, and various symbols
     * 
     * Use cases:
     * - Temporary password generation
     * - API key generation
     * - Secure token creation
     * - Default password assignment
     * 
     * @return string A 20-character secure random password
     */
    public static function generateRandomPassword()
    {
        return self::generateRandomString(17) . self::generateRandomSymbols(3);
    }

    /**
     * Generates a random string with alphanumeric characters and basic symbols.
     * 
     * Creates a cryptographically secure random string using a character set
     * that includes letters, numbers, and safe symbols. Suitable for tokens,
     * identifiers, and other applications requiring random strings.
     * 
     * Character set: 0-9, a-z, A-Z, !, @
     * 
     * Use cases:
     * - API tokens
     * - Session identifiers
     * - File names
     * - Cache keys
     * - Temporary identifiers
     * 
     * @param int $length The desired length of the random string (default: 10)
     * @return string A random string of the specified length
     */
    public static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * Generates a random string composed of special symbols only.
     * 
     * Creates a string containing only special characters for enhanced
     * password security or when symbols-only strings are needed.
     * 
     * Character set: @, $, %, &, ^, *, !, ?
     * 
     * Use cases:
     * - Password complexity enhancement
     * - Special character requirements
     * - Symbol-based tokens
     * - Security challenge generation
     * 
     * @param int $length The desired length of the symbol string (default: 3)
     * @return string A random string of special symbols
     */
    public static function generateRandomSymbols($length = 3)
    {
        $characters = '@$%&^*!?';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[random_int(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    // ===============================================================
    // ARRAY MANIPULATION AND SORTING
    // ===============================================================

    /**
     * Sorts an array numerically by a specified field.
     * 
     * Performs numeric sorting on a multidimensional array based on
     * a specific field within each array element. Currently sorts in
     * descending order regardless of the $order parameter.
     * 
     * Note: The $order parameter is currently not implemented and
     * sorting is hardcoded to descending order.
     * 
     * Use cases:
     * - Sorting user records by score/rating
     * - Ordering products by price
     * - Ranking items by popularity
     * - Date-based sorting (using timestamps)
     * 
     * @param array $array The multidimensional array to sort
     * @param string $on The field name to sort by
     * @param string $order Sort order: 'ASC' or 'DESC' (currently not implemented)
     * @return array The sorted array
     */
    public static function array_sort_numeric($array, $on, $order = 'ASC')
    {
        $sortable_array = $array;
        $new_array = array();

        usort($sortable_array, function ($a, $b) use ($on) {
            return ($b[$on] - $a[$on]);
        });

        $new_array = $sortable_array;
        return $new_array;
    }

    // ===============================================================
    // TIME AND DATE FORMATTING
    // ===============================================================

    /**
     * Converts a date to a human-readable "time ago" format.
     * 
     * Calculates the time difference between a given date and the current
     * time, returning a user-friendly string representation. Automatically
     * selects the most appropriate time unit (seconds, minutes, hours, etc.).
     * 
     * Supported formats:
     * - "X second(s) ago" (< 1 minute)
     * - "X minute(s) ago" (< 1 hour)
     * - "X hour(s) ago" (< 1 day)
     * - "X day(s) ago" (< 1 month)
     * - "X month(s) ago" (< 1 year)
     * - "X year(s) ago" (>= 1 year)
     * 
     * Use cases:
     * - Social media post timestamps
     * - Comment and activity feeds
     * - Last login displays
     * - Content publication dates
     * - User activity tracking
     * 
     * @param string $date The date to compare (any format accepted by date_create)
     * @param string $timezone Target timezone (default: 'America/New_York')
     * @return string Human-readable time difference string
     */
    public static function timeAgo($date, $timezone = 'America/New_York')
    {
        $mydate = date("Y-m-d H:i:s");
        $theDiff = "";
        $datetime1 = date_create($date);
        $datetime2 = date_create($mydate);
        $interval = date_diff($datetime1, $datetime2);
        $min = $interval->format('%i');
        $sec = $interval->format('%s');
        $hour = $interval->format('%h');
        $mon = $interval->format('%m');
        $day = $interval->format('%d');
        $year = $interval->format('%y');

        if ($interval->format('%i%h%d%m%y') == "00000") {
            return $sec . " second" . ($sec == "1" ? '' : 's') . " ago";
        } else if ($interval->format('%h%d%m%y') == "0000") {
            return $min . " minute" . ($min == "1" ? '' : 's') . " ago";
        } else if ($interval->format('%d%m%y') == "000") {
            return $hour . " hour" . ($hour == "1" ? '' : 's') . " ago";
        } else if ($interval->format('%m%y') == "00") {
            return $day . " day" . ($day == "1" ? '' : 's') . " ago";
        } else if ($interval->format('%y') == "0") {
            return $mon . " month" . ($mon == "1" ? '' : 's') . " ago";
        } else {
            return $year . " year" . ($year == "1" ? '' : 's') . " ago";
        }
    }

    /**
     * Generates the appropriate ordinal suffix for a number.
     * 
     * Returns the correct English ordinal suffix (st, nd, rd, th) for
     * any given number. Handles special cases like 11th, 12th, 13th
     * which don't follow the standard pattern.
     * 
     * Examples:
     * - 1 → "st" (1st)
     * - 2 → "nd" (2nd)
     * - 3 → "rd" (3rd)
     * - 4 → "th" (4th)
     * - 11 → "th" (11th)
     * - 21 → "st" (21st)
     * 
     * Use cases:
     * - Date formatting (1st, 2nd, 3rd of the month)
     * - Ranking displays (1st place, 2nd place)
     * - Sequential numbering
     * - Anniversary celebrations
     * 
     * @param int $n The number to get the ordinal suffix for
     * @return string The ordinal suffix (st, nd, rd, or th)
     */
    public static function ordinalSuffix($n)
    {
        return date('S', mktime(1, 1, 1, 1, ((($n >= 10) + ($n >= 20) + ($n == 0)) * 10 + $n % 10)));
    }

    // ===============================================================
    // STRING PROCESSING AND FORMATTING
    // ===============================================================

    /**
     * Truncates a string to a specified length with optional HTML tooltip.
     * 
     * Shortens long strings to improve display formatting while preserving
     * the full content through an optional HTML tooltip. Handles both plain
     * text and HTML contexts.
     * 
     * Features:
     * - Smart truncation with ellipsis
     * - HTML tooltip showing full original text
     * - Quote escaping for HTML attributes
     * - Plain text mode for non-HTML contexts
     * 
     * HTML mode creates: <span title="full text">truncated text&hellip;</span>
     * Plain text mode creates: truncated text...
     * 
     * Use cases:
     * - Table cell content display
     * - Card summaries and previews
     * - List item descriptions
     * - Comment snippets
     * - Product descriptions
     * 
     * @param string $string The text to truncate
     * @param int $length Maximum length before truncation
     * @param bool $html Whether to include HTML tooltip (default: true)
     * @return string Truncated string with optional HTML tooltip
     */
    public static function truncate($string, $length, $html = true)
    {
        if (strlen($string) > $length) {
            if ($html) {
                // Grabs the original and escapes any quotes
                $original = str_replace('"', '"', $string);
            }

            // Truncates the string
            $string = substr($string, 0, $length);

            // Appends ellipses and optionally wraps in a hoverable span
            if ($html) {
                $string = '<span title="' . $original . '">' . $string . '&hellip;</span>';
            } else {
                $string .= '...';
            }
        }

        return $string;
    }

    /**
     * Converts text to a URL-friendly slug format.
     * 
     * Transforms any text into a clean, URL-safe slug by removing special
     * characters, handling Unicode, and standardizing formatting. Perfect
     * for creating SEO-friendly URLs and file names.
     * 
     * Process:
     * 1. Replace non-alphanumeric characters with divider
     * 2. Convert Unicode to ASCII representation
     * 3. Remove remaining special characters
     * 4. Trim excess dividers
     * 5. Consolidate multiple dividers
     * 6. Convert to lowercase
     * 
     * Examples:
     * - "Hello World!" → "hello-world"
     * - "Café & Restaurant" → "cafe-restaurant" 
     * - "Special @#$% Characters" → "special-characters"
     * 
     * Use cases:
     * - URL slug generation for pages/posts
     * - File name sanitization
     * - SEO-friendly identifiers
     * - Category and tag slugs
     * - API endpoint naming
     * 
     * @param string $text The text to convert to slug format
     * @param string $divider Character to use as word separator (default: '-')
     * @return string Clean URL-safe slug, or 'n-a' if empty
     */
    public static function slugify($text, string $divider = '-')
    {
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, $divider);
        $text = preg_replace('~-+~', $divider, $text);
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }

    // ===============================================================
    // HTTP REQUEST HANDLING
    // ===============================================================

    /**
     * Performs flexible HTTP requests using cURL with comprehensive options.
     * 
     * A versatile HTTP client that supports multiple request methods, different
     * data formats, custom headers, and comprehensive error handling. Designed
     * for API interactions, webhooks, and external service integration.
     * 
     * Supported Methods:
     * - GET: Query parameters in URL
     * - POST: JSON payload in body
     * - PUT: JSON payload in body  
     * - DELETE: JSON payload in body
     * - POST_NOT_JSON: Form-encoded payload
     * 
     * Features:
     * - Automatic JSON encoding/decoding
     * - Custom header support
     * - Error handling with status codes
     * - Request URL and parameter logging
     * - Flexible content-type handling
     * 
     * Use cases:
     * - API integrations and webhooks
     * - External service communication
     * - Data synchronization
     * - Third-party authentication
     * - Microservice communication
     * 
     * @param string $do Target URL for the request
     * @param array $vars Data to send (query params for GET, body for POST/PUT/DELETE)
     * @param string $methodRequest HTTP method: GET, POST, PUT, DELETE, POST_NOT_JSON
     * @param array $headers Additional HTTP headers to include
     * @return object Response object with data, or error object with details
     */
    public static function httpRequest($do, $vars = array(), $methodRequest = 'GET', $headers = [])
    {
        // Initiate CURL
        $ch = curl_init();

        $link = $do;

        if ($methodRequest == 'GET' && count($vars) >= 1) {
            $link = $link . '?' . http_build_query($vars);
        }

        // Set the URL
        curl_setopt($ch, CURLOPT_URL, $link);

        // If it is POST or PUT, set it up
        if ($methodRequest == 'POST' || $methodRequest == 'PUT' || $methodRequest == 'DELETE') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($vars));
        }

        if ($methodRequest == 'POST_NOT_JSON') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($vars));
        }

        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
            'Content-Type: application/json'
        ], $headers));

        // Get the response
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            return (object) [
                'error' => $error_msg,
                'status' => $http_status,
                'link' => $link,
                'vars' => http_build_query($vars)
            ];
        }

        // Close CURL
        curl_close($ch);

        // Decode the response
        $response = json_decode($response);

        if (!$response)
            $response = (object) [];

        $response->request_link = $link;
        $response->vars = http_build_query($vars);

        return $response;
    }

    /**
     * Executes an asynchronous "fire and forget" HTTP POST request.
     * 
     * Performs a non-blocking HTTP POST request that doesn't wait for a
     * response. Perfect for triggering background processes, sending
     * notifications, or logging events without impacting user response time.
     * 
     * Features:
     * - Asynchronous execution (doesn't block)
     * - Uses system curl command for maximum performance
     * - JSON payload support
     * - Error code detection
     * - Base URL integration
     * 
     * Security Note: Uses --insecure flag which bypasses SSL verification
     * for speed. Should be enhanced for production use.
     * 
     * Use cases:
     * - Webhook notifications
     * - Background task triggering
     * - Analytics event tracking
     * - Email queue processing
     * - Cache invalidation
     * 
     * @param string $url Relative URL path (will be prefixed with BASE_URL)
     * @param array $data Data to send as JSON payload
     * @return bool True if command executed successfully, false otherwise
     */
    public static function fireAndForget($url, $data)
    {
        $endpoint = $_ENV['BASE_URL'] . $url;
        $postData = json_encode($data);
        $cmd = "curl -X POST -H 'Content-Type: application/json'";
        $cmd .= " -d '" . $postData . "' '" . $endpoint . "'";
        $cmd .= "  --insecure"; // this can speed things up, though it's not secure
        $cmd .= " > /dev/null 2>&1 &"; //just dismiss the response

        exec($cmd, $output, $exit);
        return $exit == 0;
    }

    // ===============================================================
    // URL PROCESSING AND DOMAIN EXTRACTION
    // ===============================================================

    /**
     * Extracts the domain name from a given URL.
     * 
     * Parses a URL and extracts just the domain portion, handling various
     * URL formats and edge cases. Uses regex pattern matching to identify
     * valid domain structures.
     * 
     * Supports:
     * - Full URLs with protocols (http://example.com/path)
     * - Domain-only strings (example.com)
     * - Subdomains (www.example.com, api.example.com)
     * - Various TLDs (.com, .org, .co.uk, etc.)
     * 
     * Use cases:
     * - URL validation and processing
     * - Domain-based routing or filtering
     * - Analytics and tracking
     * - Security policy enforcement
     * - Link validation
     * 
     * @param string $url The URL to extract domain from
     * @return string|false The domain name, or false if invalid URL
     */
    public static function get_domain($url)
    {
        $pieces = parse_url($url);
        $domain = isset($pieces['host']) ? $pieces['host'] : $pieces['path'];
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{0,63}\.[a-z\.]{1,5})$/i', $domain, $regs)) {
            return $regs['domain'];
        }
        return false;
    }
}
