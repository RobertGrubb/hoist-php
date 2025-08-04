<?php

/**
 * ===================================================================
 * HOIST PHP FRAMEWORK - DATA CLEANING AND SANITIZATION SERVICE
 * ===================================================================
 * 
 * The Cleaner class provides a comprehensive set of data sanitization
 * and cleaning methods using regular expressions. It helps ensure data
 * integrity and security by filtering out unwanted characters from
 * user input and external data sources.
 * 
 * Key Features:
 * - Character Filtering: Remove unwanted characters based on type
 * - Input Sanitization: Clean user input for database storage
 * - Data Validation: Ensure data matches expected formats
 * - Security: Prevent injection attacks through character filtering
 * - Consistency: Standardize data formats across the application
 * 
 * Common Use Cases:
 * - User input sanitization before database storage
 * - API data cleaning and validation
 * - File name sanitization for uploads
 * - URL parameter cleaning
 * - Form data preprocessing
 * 
 * All methods are static-like (instance methods) and return cleaned strings.
 * 
 * @package HoistPHP\Core\Libraries
 * @author  Hoist PHP Framework Team
 * @version 1.0.0
 */
class Cleaner
{
    // ===============================================================
    // ALPHABETIC CHARACTER FILTERING
    // ===============================================================

    /**
     * Filters string to contain only alphabetic characters (A-Z, a-z).
     * 
     * Removes all characters except letters, including numbers, spaces,
     * punctuation, and special characters. Useful for:
     * - Name validation and cleaning
     * - Text-only fields
     * - Language detection
     * - Alphabetic sorting keys
     * 
     * Example:
     * Input:  "John123 Doe!"
     * Output: "JohnDoe"
     * 
     * @param string $string The input string to clean
     * @return string String containing only alphabetic characters
     */
    public function alphaOnly($string)
    {
        return preg_replace("/[^A-Za-z]/", "", $string);
    }

    // ===============================================================
    // ALPHANUMERIC CHARACTER FILTERING
    // ===============================================================

    /**
     * Filters string to contain only alphanumeric characters (A-Z, a-z, 0-9).
     * 
     * Removes all characters except letters and numbers, including spaces,
     * punctuation, and special characters. Useful for:
     * - Username validation
     * - Product codes and SKUs
     * - Simple identifiers
     * - Clean text processing
     * 
     * Example:
     * Input:  "User123_Name!"
     * Output: "User123Name"
     * 
     * @param string $string The input string to clean
     * @return string String containing only letters and numbers
     */
    public function alphaNumericOnly($string)
    {
        return preg_replace("/[^A-Za-z0-9]/", "", $string);
    }

    // ===============================================================
    // NUMERIC CHARACTER FILTERING
    // ===============================================================

    /**
     * Filters string to contain only numeric characters (0-9).
     * 
     * Removes all non-digit characters including letters, spaces,
     * punctuation, and symbols. Useful for:
     * - Phone number cleaning
     * - ID extraction from mixed strings
     * - Numeric validation
     * - Integer parsing preparation
     * 
     * Example:
     * Input:  "Phone: (555) 123-4567"
     * Output: "5551234567"
     * 
     * @param string $string The input string to clean
     * @return string String containing only numeric digits
     */
    public function numericOnly($string)
    {
        return preg_replace("/[^0-9]/", "", $string);
    }

    /**
     * Filters string to contain only numeric characters and commas.
     * 
     * Preserves numbers and commas while removing all other characters.
     * Useful for:
     * - Comma-separated number lists
     * - CSV numeric data cleaning
     * - Formatted number processing
     * - Currency amount cleaning
     * 
     * Example:
     * Input:  "1,234.56 USD"
     * Output: "1,234"
     * 
     * @param string $string The input string to clean
     * @return string String containing only digits and commas
     */
    public function numericCommasOnly($string)
    {
        return preg_replace("/[^0-9],/", "", $string);
    }

    // ===============================================================
    // EXTENDED ALPHANUMERIC FILTERING
    // ===============================================================

    /**
     * Filters string to contain only alphanumeric characters and dashes.
     * 
     * Preserves letters, numbers, and dash characters while removing
     * all other characters. Useful for:
     * - URL slug generation and cleaning
     * - Product code validation
     * - File name sanitization
     * - SEO-friendly identifier creation
     * 
     * Example:
     * Input:  "My Product Name (2024)!"
     * Output: "MyProductName-2024"
     * 
     * @param string $string The input string to clean
     * @return string String containing only letters, numbers, and dashes
     */
    public function alphaNumericDashesOnly($string)
    {
        return preg_replace("/[^-A-Za-z0-9]/", "", $string);
    }

    /**
     * Filters string to contain alphanumeric characters, dashes, and underscores.
     * 
     * Preserves letters, numbers, dashes, and underscores while removing
     * all other characters. Useful for:
     * - Database field name validation
     * - Variable name cleaning
     * - File name sanitization with separators
     * - API endpoint identifier cleaning
     * 
     * Example:
     * Input:  "user_name-2024 (final).txt"
     * Output: "user_name-2024final"
     * 
     * @param string $string The input string to clean
     * @return string String with letters, numbers, dashes, and underscores only
     */
    public function alphaNumericDashesUnderscoresOnly($string)
    {
        return preg_replace("/[^A-Za-z0-9_-]/", "", $string);
    }

    // ===============================================================
    // EXTENDED CHARACTER SET FILTERING
    // ===============================================================

    /**
     * Filters string to contain alphanumeric characters and common symbols.
     * 
     * Preserves a broader set of characters including letters, numbers,
     * and commonly used symbols in text: hyphens, parentheses, periods,
     * commas, ampersands, apostrophes, forward slashes, and spaces.
     * 
     * Useful for:
     * - Address and location data cleaning
     * - Business name sanitization
     * - General text content filtering
     * - User-generated content cleaning
     * - Description field validation
     * 
     * Allowed characters: A-Z, a-z, 0-9, -, (, ), ., ,, &, ', /, spaces
     * 
     * Example:
     * Input:  "John's Café & Restaurant (Downtown) #1"
     * Output: "John's Caf & Restaurant (Downtown) "
     * 
     * @param string $string The input string to clean
     * @return string String with alphanumeric characters and valid symbols only
     */
    public function alphaNumericWithValidSymbolsOnly($string)
    {
        return preg_replace("/[^-().,&'\/\sA-Za-z0-9]/", "", $string);
    }
}
