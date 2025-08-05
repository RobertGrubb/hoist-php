<?php

/**
 * Button Component with Tailwind CSS styling
 * Supports various button types, sizes, and states
 */
return function ($instance, $data = []) {
    $text = $data['text'] ?? 'Button';
    $type = $data['type'] ?? 'button'; // button, submit, reset
    $variant = $data['variant'] ?? 'primary'; // primary, secondary, danger, success
    $size = $data['size'] ?? 'md'; // sm, md, lg
    $disabled = $data['disabled'] ?? false;
    $loading = $data['loading'] ?? false;
    $icon = $data['icon'] ?? '';
    $iconPosition = $data['iconPosition'] ?? 'left'; // left, right
    $fullWidth = $data['fullWidth'] ?? false;
    $id = $data['id'] ?? '';
    $onclick = $data['onclick'] ?? '';

    // Base classes
    $baseClasses = 'inline-flex items-center justify-center border font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200';

    // Size classes
    $sizeClasses = [
        'sm' => 'px-3 py-2 text-sm',
        'md' => 'px-4 py-2 text-sm',
        'lg' => 'px-6 py-3 text-base'
    ];

    // Variant classes
    $variantClasses = [
        'primary' => 'border-transparent text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-500',
        'secondary' => 'border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:ring-blue-500',
        'danger' => 'border-transparent text-white bg-red-600 hover:bg-red-700 focus:ring-red-500',
        'success' => 'border-transparent text-white bg-green-600 hover:bg-green-700 focus:ring-green-500'
    ];

    $classes = $baseClasses . ' ' . $sizeClasses[$size] . ' ' . $variantClasses[$variant];

    if ($fullWidth)
        $classes .= ' w-full';
    if ($disabled || $loading)
        $classes .= ' opacity-50 cursor-not-allowed';

    $html = "<button type=\"{$type}\" class=\"{$classes}\"";

    if ($id)
        $html .= " id=\"{$id}\"";
    if ($onclick)
        $html .= ' onclick="' . htmlspecialchars($onclick, ENT_QUOTES, 'UTF-8') . '"';
    if ($disabled)
        $html .= ' disabled';

    $html .= '>';

    // Loading state
    if ($loading) {
        $html .= '<svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">';
        $html .= '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>';
        $html .= '<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>';
        $html .= '</svg>';
        $html .= 'Loading...';
    } else {
        // Icon and text
        if ($icon && $iconPosition === 'left') {
            $html .= "<i class=\"{$icon} mr-2\"></i>";
        }

        $html .= $text;

        if ($icon && $iconPosition === 'right') {
            $html .= "<i class=\"{$icon} ml-2\"></i>";
        }
    }

    $html .= '</button>';

    return $html;
};
